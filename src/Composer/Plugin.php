<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Composer;

use Composer\Composer;
use Composer\EventDispatcher\Event;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use Lemberg\Draft\Environment\App;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Manager\InstallManager;
use Lemberg\Draft\Environment\Config\Manager\UpdateManager;

/**
 * Composer plugin for configuring Draft Environment.
 */
final class Plugin implements PluginInterface, EventSubscriberInterface {

  /**
   * @var \Lemberg\Draft\Environment\App
   */
  private $app;

  /**
   * {@inheritdoc}
   */
  public function activate(Composer $composer, IOInterface $io): void {
    /** @var \Composer\Package\Package|NULL $package */
    $package = $composer->getRepositoryManager()->getLocalRepository()->findPackage(App::PACKAGE_NAME, '*');
    if (is_null($package)) {
      throw new \RuntimeException(sprintf('Package %s is not found in the local repository.', App::PACKAGE_NAME));
    }
    $sourceDirectory = $composer->getInstallationManager()->getInstallPath($package);

    if (($targetDirectory = getcwd()) === FALSE) {
      throw new \RuntimeException('Unable to get the current working directory. Please check if any one of the parent directories does not have the readable or search mode set, even if the current directory does. See https://www.php.net/manual/function.getcwd.php');
    }

    $config = new Config($sourceDirectory, $targetDirectory);
    $configInstallManager = new InstallManager($composer, $io, $config);
    $configUpdateManager = new UpdateManager($composer, $io, $config);
    $this->setApp(new App($composer, $io, $configInstallManager, $configUpdateManager));
  }

  /**
   * {@inheritdoc}
   */
  public function deactivate(Composer $composer, IOInterface $io): void {
    // This method is part of the Composer 2 Plugin API.
  }

  /**
   * {@inheritdoc}
   */
  public function uninstall(Composer $composer, IOInterface $io): void {
    // This method is part of the Composer 2 Plugin API.
  }

  /**
   * Returns an array of event names this subscriber wants to listen to.
   *
   * @return array<string, string>
   */
  public static function getSubscribedEvents(): array {
    return [
      PackageEvents::POST_PACKAGE_INSTALL => 'onComposerEvent',
      PackageEvents::POST_PACKAGE_UPDATE => 'onComposerEvent',
      PackageEvents::PRE_PACKAGE_UNINSTALL => 'onComposerEvent',
      ScriptEvents::POST_AUTOLOAD_DUMP => 'onComposerEvent',
    ];
  }

  /**
   * Composer events handler.
   *
   * @param \Composer\EventDispatcher\Event $event
   */
  public function onComposerEvent(Event $event): void {
    $this->app->handleEvent($event);
  }

  /**
   * Set an app this plugin will be using for the events handling.
   *
   * @param \Lemberg\Draft\Environment\App $app
   */
  public function setApp(App $app): void {
    $this->app = $app;
  }

}
