<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Manager;

use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Install\InstallConfigStepInterface;
use Lemberg\Draft\Environment\Config\Install\InstallInitStepInterface;
use Lemberg\Draft\Environment\Config\Install\Step\InitConfig;

/**
 * Draft Environment configuration install/uninstall manager.
 */
final class InstallManager extends AbstractConfigManager implements InstallManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function install(): void {
    $targetConfigFilepath = $this->getConfig()->getTargetConfigFilepath(Config::TARGET_CONFIG_FILENAME);
    if (!$this->getFilesystem()->exists($targetConfigFilepath)) {
      $this->installInitPhase();
      $this->installConfigPhase();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function uninstall(): void {
    // Discovery mechanism (similar to the one in install()) cannot be used here
    // as Composer will remove all the dependencies of this package before
    // dispatching the PRE_PACKAGE_UNINSTALL event.
    // At the moment, only single uninstall step is required.
    $step = new InitConfig($this->composer, $this->io, $this);
    $step->uninstall();
    $this->writeMessage($step->getMessages());
  }

  /**
   * Executes the init phase of the installation process.
   */
  private function installInitPhase(): void {
    $this->writeMessage('<info>Welcome to the Draft Environment interactive installer</info>');
    $this->discoverSteps(InstallInitStepInterface::class, __DIR__ . '/../Install');
    $this->sortSteps();

    /** @var \Lemberg\Draft\Environment\Config\Install\InstallInitStepInterface $step */
    foreach ($this->steps as $step) {
      $step->install();
      $this->writeMessage($step->getMessages());
    }
  }

  /**
   * Executes the configuration setup phase of the installation process.
   */
  private function installConfigPhase(): void {
    $config = $this->readConfig();
    $this->writeMessage('<info>Please answer to a few questions:</info>');
    $this->discoverSteps(InstallConfigStepInterface::class, __DIR__ . '/../Install');
    $this->sortSteps();

    /** @var \Lemberg\Draft\Environment\Config\Install\InstallConfigStepInterface $step */
    foreach ($this->steps as $step) {
      $step->install($config);
      $this->writeMessage($step->getMessages());
    }

    $this->setLastAppliedUpdateWeight($config, $this->getLastAvailableUpdateWeight());
    $this->writeConfig($config);

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

}
