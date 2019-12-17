<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Lemberg\Draft\Environment\App;

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
    if (($cwd = getcwd()) === FALSE) {
      throw new \RuntimeException('Unable to get the current working directory. Please check if any one of the parent directories does not have the readable or search mode set, even if the current directory does. See https://www.php.net/manual/function.getcwd.php');
    }

    $this->setApp(new App($composer, $io, $cwd));
  }

  /**
   * Returns an array of event names this subscriber wants to listen to.
   *
   * @return array<string, string>
   */
  public static function getSubscribedEvents(): array {
    return [
      PackageEvents::PRE_PACKAGE_UNINSTALL => 'onPrePackageUninstall',
    ];
  }

  /**
   * Pre package uninstall event callback.
   *
   * @param \Composer\Installer\PackageEvent $event
   */
  public function onPrePackageUninstall(PackageEvent $event): void {
    $this->app->onPrePackageUninstall($event);
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
