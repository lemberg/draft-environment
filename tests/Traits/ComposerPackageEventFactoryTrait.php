<?php

declare(strict_types=1);

namespace Lemberg\Tests\Traits\Draft\Environment;

use Composer\Composer;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\PolicyInterface;
use Composer\DependencyResolver\Pool;
use Composer\DependencyResolver\Request;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Repository\RepositoryInterface;

/**
 * Create an instance of the PackageEvent.
 */
trait ComposerPackageEventFactoryTrait {

  /**
   * Create an instance of the PackageEvent based on the Composer version it is
   * being used with.
   *
   * @param string $eventName
   *   Package event name.
   * @param \Composer\Composer $composer
   *   Composer instance.
   * @param \Composer\IO\IOInterface $io
   *   IO instance.
   * @param bool $devMode
   *   Boolean indicating whether dev mode is enabled or not.
   * @param \Composer\DependencyResolver\PolicyInterface $policy
   *   Composer policy.
   * @param \Composer\DependencyResolver\Pool $pool
   *   Composer dependency pool.
   * @param \Composer\Repository\RepositoryInterface $repository
   *   Composer repository.
   * @param \Composer\DependencyResolver\Request $request
   *   Current request.
   * @param \Composer\DependencyResolver\Operation\OperationInterface[] $operations
   *   Array of operations being run.
   * @param \Composer\DependencyResolver\Operation\OperationInterface $operation
   *   Operation triggering the event.
   *
   * @return \Composer\Installer\PackageEvent
   *   Instance of the PackageEvent.
   */
  private function createPackageEvent(string $eventName, Composer $composer, IOInterface $io, bool $devMode, PolicyInterface $policy, Pool $pool, RepositoryInterface $repository, Request $request, array $operations, OperationInterface $operation): PackageEvent {
    if (version_compare(PluginInterface::PLUGIN_API_VERSION, '2.0.0', '>=')) {
      return new PackageEvent($eventName, $composer, $io, $devMode, $repository, $operations, $operation);
    }
    return new PackageEvent($eventName, $composer, $io, $devMode, $policy, $pool, $repository, $request, $operations, $operation);
  }

}
