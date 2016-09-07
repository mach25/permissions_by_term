<?php

namespace Drupal\permissions_by_term;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\permissions_by_term\AccessCheckService;

class KernelEventListener implements EventSubscriberInterface {

  public function onKernelRequest($event)
  {
    if ($event->getRequest()->attributes->get('target_type') == 'taxonomy_term' &&
      $event->getRequest()->attributes->get('_route') == 'system.entity_autocomplete') {
      $query_string = $event->getRequest()->getQueryString();

      $accessCheckService = new AccessCheckService();
      $accessCheckService->isAccessAllowedByDatabase()

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
