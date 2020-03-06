<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Manager;

use Composer\Autoload\ClassLoader;
use Composer\Autoload\ClassMapGenerator;
use Composer\Composer;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Lemberg\Draft\Environment\App;
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

    // This code is running in Composer context, newly added packages might
    // not be autoloaded.
    if (!class_exists(RobotLoader::class)) {
      $this->autoloadDependencies();
    }
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

  /**
   * @return array<mixed>
   */
  final protected function getPackageExtra(): array {
    $localRepository = $this->composer->getRepositoryManager()->getLocalRepository();
    /** @var \Composer\Package\Package $localPackage */
    $localPackage = $localRepository->findPackage(App::PACKAGE_NAME, '*');
    return $localPackage->getExtra();
  }

  /**
   * @param array<mixed> $extra
   */
  final protected function setPackageExtra(array $extra): void {
    $localRepository = $this->composer->getRepositoryManager()->getLocalRepository();
    /** @var \Composer\Package\Package $localPackage */
    $localPackage = $localRepository->findPackage(App::PACKAGE_NAME, '*');
    $localPackage->setExtra($extra);

    // This code might run after Composer has written the lock file.
    $composerFile = Factory::getComposerFile();
    $lockFile = 'json' === pathinfo($composerFile, PATHINFO_EXTENSION) ? substr($composerFile, 0, -4) . 'lock' : $composerFile . '.lock';
    if ($this->getConfig()->getFilesystem()->exists($lockFile)) {
      $json = new JsonFile($lockFile);
      $content = $json->read();

      foreach (['packages', 'packages-dev'] as $type) {
        $key = array_search(App::PACKAGE_NAME, array_column($content[$type], 'name'), TRUE);
        if ($key !== FALSE) {
          $content[$type][$key]['extra'] = $extra;
          $json->write($content);
        }
      }
    }
  }

  /**
   * Manually autoload dependencies from Nette framework.
   */
  final private function autoloadDependencies(): void {
    $loader = new ClassLoader();

    $vendorDir = $this->composer->getConfig()->get('vendor-dir');
    foreach (['nette/utils', 'nette/finder', 'nette/robot-loader'] as $path) {
      $loader->addClassMap(ClassMapGenerator::createMap("$vendorDir/$path"));
    }

    $loader->register();
  }

}
