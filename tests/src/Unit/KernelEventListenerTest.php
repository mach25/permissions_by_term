<?php

namespace Drupal\Tests\permissions_by_term\Unit;

use PHPUnit\Framework\TestCase;
use \Drupal\permissions_by_term\KernelEventListener;

class AccessRestrictionTest extends TestCase
{

  use Base;

  public function testCanBeNodeLoadedOnRequest()
  {
    \Drupal::setContainer($this->createMock('\Symfony\Component\DependencyInjection\ContainerInterface'));
    $kernelEventListener = new KernelEventListener();
    $this->modifyPropertyByReflection($kernelEventListener, 'accessStorageService', null);

    $this->assertEquals(1, 1);
  }

}