<?php

declare(strict_types=1);

namespace Drupal\tracer;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class O11yTracesServiceProvider.
 */
class TracerServiceProvider extends ServiceProviderBase {

  /**
   * Alter the container to replace services.
   */
  public function alter(ContainerBuilder $container): void {
    // Replace the event dispatcher service with a traceable one.
    $container->getDefinition('event_dispatcher')
      ->setClass('Drupal\tracer\EventDispatcher\TraceableEventDispatcher')
      ->addMethodCall('setTracer',
        [new Reference('tracer.tracer')]);

    // Replace the controller resolver service with a traceable one.
    $container->getDefinition('http_kernel.basic')
      ->replaceArgument(1, new Reference('tracer.debug.controller_resolver'));
  }

}
