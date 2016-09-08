<?php

namespace Drupal\permissions_by_term;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\permissions_by_term\AccessCheckService;

class KernelEventListener implements EventSubscriberInterface {

  public function __construct() {
    $oDbConnection = \Drupal::database();
    $this->accessCheckService = new AccessCheckService();
    $this->accessStorageService = new AccessStorageService($oDbConnection);
  }

  public function onKernelRequest($event)
  {
    if ($event->getRequest()->attributes->get('target_type') == 'taxonomy_term' &&
      $event->getRequest()->attributes->get('_route') == 'system.entity_autocomplete') {
      $query_string = $event->getRequest()->get('q');
      $query_string = trim($query_string);

      // @TODO: react on reponse by autocomplete list. This list under the field.

      $tid = $this->accessStorageService->getTermIdByName($query_string);

      if (!$this->accessCheckService->isAccessAllowedByDatabase($tid)) {
        exit();
      }
    }
  }

  public function onKernelResponse($event) {
    xdebug_break();
  }

  public static function getSubscribedEvents()
  {
    return [
      KernelEvents::REQUEST  => 'onKernelRequest',
      KernelEvents::RESPONSE => 'onKernelResponse',
    ];
  }

}
