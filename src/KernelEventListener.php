<?php

namespace Drupal\permissions_by_term;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\permissions_by_term\AccessCheckService;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class KernelEventListener implements EventSubscriberInterface {

  public function __construct() {
    $oDbConnection = \Drupal::database();
    $this->accessCheckService = new AccessCheckService();
    $this->accessStorageService = new AccessStorageService($oDbConnection);
  }

  public function onKernelRequest($event)
  {
    // Restricts access to nodes (views/edit).
    if (!empty($event->getRequest()->attributes->get('node'))) {
      $nid = $event->getRequest()->attributes->get('node')->get('nid')->getValue()['0']['value'];
      if (!$this->accessCheckService->canUserAccessByNodeId($nid)) {
        $response = new RedirectResponse('/access-restricted-by-taxonomy-term');
        $response->send();
        return;
      }
    }

    // Restrict access to taxonomy terms by autocomplete list.
    if ($event->getRequest()->attributes->get('target_type') == 'taxonomy_term' &&
      $event->getRequest()->attributes->get('_route') == 'system.entity_autocomplete') {
      $query_string = $event->getRequest()->get('q');
      $query_string = trim($query_string);

      $tid = $this->accessStorageService->getTermIdByName($query_string);
      if (!$this->accessCheckService->isAccessAllowedByDatabase($tid)) {
        exit();
      }
    }
  }

  public function onKernelResponse(FilterResponseEvent $event) {
    $this->restrictTermAccessAtAutoCompletion($event);
  }

  private function restrictTermAccessAtAutoCompletion(FilterResponseEvent $event) {
    if ($event->getRequest()->attributes->get('target_type') == 'taxonomy_term' &&
      $event->getRequest()->attributes->get('_route') == 'system.entity_autocomplete') {
      $json_suggested_terms = $event->getResponse()->getContent();
      $suggested_terms = json_decode($json_suggested_terms);
      $allowed_terms = array();
      foreach ($suggested_terms as $term) {
        $tid = $this->accessStorageService->getTermIdByName($term->label);
        if ($this->accessCheckService->isAccessAllowedByDatabase($tid)) {
          $allowed_terms[] = [
            'value' => $term->value,
            'label' => $term->label,
          ];
        }
      }

      $json_response = new JsonResponse($allowed_terms);
      $event->setResponse($json_response);
    }
  }

  public static function getSubscribedEvents()
  {
    return [
      KernelEvents::REQUEST  => 'onKernelRequest',
      KernelEvents::RESPONSE => 'onKernelResponse',
    ];
  }

}
