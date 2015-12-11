<?php

namespace Drupal\permissions_by_term;

// This is the interface we are going to implement.
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class TermSubscriberService implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    /*
    $events[KernelEvents::RESPONSE][] = array('checkTermPermissionUpdate');
    return $events;
    */
  }

  public function checkTermPermissionUpdate() {
    $debug = true;
  }

}
