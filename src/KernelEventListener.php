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

      // @TODO: separate the words, validate it.
      $query_string = $event->getRequest()->getQueryString();


      $query_string = 'No Access';

      $tid = $this->accessStorageService->getTermIdByName($query_string);

      $this->accessCheckService->isAccessAllowedByDatabase();

      $response = new JsonResponse();
      $response->setData(array(
        'data' => 123
      ));
      $response->send();
      exit();
     }
  }

  public static function getSubscribedEvents()
  {
    return [
      KernelEvents::REQUEST => 'onKernelRequest',
    ];
  }

}
