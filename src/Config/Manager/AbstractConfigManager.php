<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Manager;

use Nette\IOException;
use Nette\Utils\Finder;
use Composer\Autoload\ClassLoader;
use Composer\Autoload\ClassMapGenerator;
use Composer\Composer;
use Composer\IO\IOInterface;
use Consolidation\Comments\Comments;
use Lemberg\Draft\Environment\App;
use Lemberg\Draft\Environment\Config\AbstractStepInterface;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\ConfigAwareTrait;
use Lemberg\Draft\Environment\Utility\Filesystem;
use Lemberg\Draft\Environment\Utility\FilesystemAwareTrait;
use Nette\Loaders\RobotLoader;

/**
 * Base configuration manager class.
 */
abstract class AbstractConfigManager implements ManagerInterface {

  use ConfigAwareTrait;
  use FilesystemAwareTrait;

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
    $this->setFilesystem(new Filesystem());

    // This code is running in Composer context, newly added packages might
    // not be autoloaded.
    $this->autoloadDependencies();
  }

  /**
   * Reads configuration from the target config file.
   *
   * @return array<int|string,mixed> $config
   */
  final protected function readConfig(): array {
    $configObject = $this->getConfig();
    $targetConfigFilepath = $configObject->getTargetConfigFilepath(Config::TARGET_CONFIG_FILENAME);
    return $configObject->readAndParseConfigFromTheFile($targetConfigFilepath);
  }

  /**
   * Writes given configuration to the target config file.
   *
   * @param array<int|string,mixed> $config
   *   Draft Environment configuration nested array.
   */
  final protected function writeConfig(array $config): void {
    $configObject = $this->getConfig();
    $targetConfigFilepath = $configObject->getTargetConfigFilepath(Config::TARGET_CONFIG_FILENAME);
    $configObject->writeConfigToTheFile($targetConfigFilepath, $targetConfigFilepath, $config);
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
   * Get the last update weight from the local repository.
   *
   * @param array<int|string,mixed> $config
   *
   * @return int
   */
  final protected function getLastAppliedUpdateWeight(array $config): int {
    return $config['draft']['last_applied_update'] ?? 0;
  }

  /**
   * Set the last update weight in the local repository.
   *
   * @param array<int|string,mixed> $config
   * @param int $weight
   */
  final protected function setLastAppliedUpdateWeight(array &$config, int $weight): void {
    $config['draft']['last_applied_update'] = $weight;
  }

  /**
   * Get the weight of the last available step.
   *
   * @return int
   */
  final protected function getLastAvailableUpdateWeight(): int {
    return App::LAST_AVAILABLE_UPDATE;
  }

  /**
   * Manually autoload new dependencies.
   *
   * @link https://github.com/lemberg/draft-environment/issues/232
   */
  private function autoloadDependencies(): void {
    $loader = new ClassLoader();
    $vendorDir = $this->composer->getConfig()->get('vendor-dir');
    $classes = [
      'classmap' => [
        RobotLoader::class => 'nette/robot-loader',
        Finder::class => 'nette/finder',
        IOException::class => 'nette/utils',
      ],
      'psr4' => [
        Comments::class => [
          'package' => 't2l/comments',
          'prefix' => 'Consolidation\\Comments\\',
          'path' => 'src',
        ],
      ],
    ];

    foreach ($classes['classmap'] as $class_name => $package_name) {
      if (!class_exists($class_name)) {
        $loader->addClassMap(ClassMapGenerator::createMap("$vendorDir/$package_name"));
      }
    }

    foreach ($classes['psr4'] as $class_name => $autoload_data) {
      if (!class_exists($class_name)) {
        $loader->addPsr4($autoload_data['prefix'], $vendorDir . '/' . $autoload_data['package'] . '/' . $autoload_data['path']);
      }
    }
  }

}
