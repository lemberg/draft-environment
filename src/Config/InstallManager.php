<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config;

use Composer\Composer;
use Composer\IO\IOInterface;
use Consolidation\Comments\Comments;
use Lemberg\Draft\Environment\Config\Install\InstallConfigStepInterface;
use Lemberg\Draft\Environment\Config\Install\InstallInitStepInterface;
use Lemberg\Draft\Environment\Config\Install\Step\InitConfig;
use Nette\Loaders\RobotLoader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

/**
 * Draft Environment configuration installer.
 */
final class InstallManager {

  use ConfigAwareTrait;

  /**
   * @var \Composer\Composer
   */
  private $composer;

  /**
   * @var \Composer\IO\IOInterface
   */
  private $io;

  /**
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  private $fs;

  /**
   * Draft Environment configuration installer constructor.
   *
   * @param \Composer\Composer $composer
   * @param \Composer\IO\IOInterface $io
   * @param Config $config
   */
  public function __construct(Composer $composer, IOInterface $io, Config $config) {
    $this->composer = $composer;
    $this->io = $io;
    $this->setConfig($config);
    $this->fs = new Filesystem();
  }

  /**
   * Installs the Draft Environment.
   */
  public function install(): void {
    $this->installInitPhase();
    $this->installConfigPhase();
  }

  /**
   * Executes the init phase of the installation process.
   */
  private function installInitPhase(): void {
    $this->writeMessage('<info>Welcome to the Draft Environment interactive installer</info>');
    /** @var \Lemberg\Draft\Environment\Config\Install\InstallInitStepInterface[] $steps */
    $steps = $this->discoverSteps(InstallInitStepInterface::class);
    $this->sortSteps($steps);
    foreach ($steps as $step) {
      $step->install();
      $this->writeMessage($step->getMessages());
    }
  }

  /**
   * Executes the configuration setup phase of the installation process.
   */
  private function installConfigPhase(): void {
    $this->writeMessage('<info>Please answer to a few questions:</info>');

    $parser = new Parser();
    $originalContent = file_get_contents($this->config->getTargetConfigFilepath(Config::TARGET_CONFIG_FILENAME));
    if ($originalContent === FALSE) {
      throw new \RuntimeException(sprintf("Draft Environment Composer plugin was not able to read config at '%s'", $this->config->getTargetConfigFilepath(Config::TARGET_CONFIG_FILENAME)));
    }
    $config = $parser->parse($originalContent);

    /** @var \Lemberg\Draft\Environment\Config\Install\InstallConfigStepInterface[] $steps */
    $steps = $this->discoverSteps(InstallConfigStepInterface::class);
    $this->sortSteps($steps);
    foreach ($steps as $step) {
      $step->install($config);
      $this->writeMessage($step->getMessages());
    }

    $yaml = new Dumper(2);
    $alteredContent = $yaml->dump($config, PHP_INT_MAX, 0, Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE);

    $commentManager = new Comments();
    $commentManager->collect(explode("\n", $originalContent));
    $alteredWithComments = $commentManager->inject(explode("\n", $alteredContent));
    $this->fs->dumpFile($this->config->getTargetConfigFilepath(Config::TARGET_CONFIG_FILENAME), implode("\n", $alteredWithComments));

    $message = <<<HERE
<info>Unfortunately, the interactive installer has quite limited functionality at the moment</info>
<info>Please check the configuration files and adjust them manually, if required</info>

<info>Then just relax and run</info> <comment>vagrant up</comment><info>. Now you can make some coffee. It won't take too long though :)</info>
<info>Project will be available at</info> <comment>http://{$config['vagrant']['hostname']}.test</comment> <info>after provisioning</info>
<info>Happy coding!</info>
HERE;

    $this->writeMessage($message);
  }

  /**
   * Looks for classes implementing a given interface.
   *
   * @return \Lemberg\Draft\Environment\Config\AbstractStepInterface[]
   *   Array of instantiated classes.
   */
  private function discoverSteps(string $interface): array {
    $loader = new RobotLoader();
    $loader->addDirectory(__DIR__ . '/Install');
    $loader->rebuild();
    /** @var array<class-string, string> $classes */
    $classes = $loader->getIndexedClasses();

    /** @var \Lemberg\Draft\Environment\Config\AbstractStepInterface[] $steps */
    $steps = [];
    foreach ($classes as $class => $filepath) {
      $reflection = new \ReflectionClass($class);
      if ($reflection->isInstantiable() && $reflection->implementsInterface($interface)) {
        $steps[] = new $class($this->composer, $this->io, $this->config);
      }
    }

    return $steps;
  }

  /**
   * Sorts the given steps by weight in the ascending order.
   *
   * @param \Lemberg\Draft\Environment\Config\AbstractStepInterface[] $steps
   */
  private function sortSteps(array &$steps): void {
    uasort($steps, function (AbstractStepInterface $a, AbstractStepInterface $b): int {
      if ($a->getWeight() === $b->getWeight()) {
        return 0;
      }
      return ($a->getWeight() > $b->getWeight()) ? 1 : -1;
    });
  }

  /**
   * Writes a given message to the IO. Empty messages won't be printed.
   *
   * @param string $message
   *   Message to print.
   */
  private function writeMessage(string $message): void {
    if ($message !== '') {
      $this->io->write("\n" . $message);
    }
  }

  /**
   * Uninstalls the Draft Environment configuration.
   */
  public function uninstall(): void {
    // Discovery mechanism (similar to the one in install()) cannot be used here
    // as Composer will remove all the dependencies of this package before
    // dispatching the PRE_PACKAGE_UNINSTALL event.
    // At the moment, only single uninstall step is required.
    $step = new InitConfig($this->composer, $this->io, $this->config);
    $step->uninstall();
    $this->writeMessage($step->getMessages());
  }

}
