<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Composer;

use Composer\Composer;
use Composer\EventDispatcher\Event;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Lemberg\Draft\Environment\App;
use Lemberg\Draft\Environment\Config\InstallManager;

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

    $configInstallManager = new InstallManager($composer, $io, $sourceDirectory, $targetDirectory);
    $this->setApp(new App($composer, $io, $configInstallManager));
  }

  /**
   * Returns an array of event names this subscriber wants to listen to.
   *
   * @return array<string, string>
   */
  public static function getSubscribedEvents(): array {
    return [
      PackageEvents::PRE_PACKAGE_UNINSTALL => 'onComposerEvent',
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
