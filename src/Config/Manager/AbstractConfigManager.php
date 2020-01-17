<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Manager;

use Composer\Composer;
use Composer\IO\IOInterface;
use Lemberg\Draft\Environment\Config\AbstractStepInterface;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\ConfigAwareTrait;
use Nette\Loaders\RobotLoader;

/**
 * Base configuration manager class.
 */
abstract class AbstractConfigManager implements ManagerInterface {

  use ConfigAwareTrait;

  /**
   * @var \Composer\Composer
   */
  protected $composer;

  /**
   * @var \Composer\IO\IOInterface
   */
  protected $io;

  /**
   * @var \Lemberg\Draft\Environment\Config\AbstractStepInterface[]
   */
  protected $steps = [];

  /**
   * {@inheritdoc}
   */
  final public function __construct(Composer $composer, IOInterface $io, Config $config) {
    $this->composer = $composer;
    $this->io = $io;
    $this->setConfig($config);
  }

  /**
   * Looks for classes implementing a given interface.
   */
  final protected function discoverSteps(string $interface, string $directory): void {
    $loader = new RobotLoader();
    $loader->addDirectory($directory);
    $loader->rebuild();
    /** @var array<class-string, string> $classes */
    $classes = $loader->getIndexedClasses();

    $this->steps = [];
    foreach ($classes as $class => $filepath) {
      $reflection = new \ReflectionClass($class);
      if ($reflection->isInstantiable() && $reflection->implementsInterface($interface)) {
        $this->steps[] = new $class($this->composer, $this->io, $this);
      }
    }
  }

  /**
   * Sorts the given steps by weight in the ascending order.
   */
  final protected function sortSteps(): void {
    uasort($this->steps, function (AbstractStepInterface $a, AbstractStepInterface $b): int {
      if ($a->getWeight() === $b->getWeight()) {
        return 0;
      }
      return ($a->getWeight() > $b->getWeight()) ? 1 : -1;
    });
  }

}
