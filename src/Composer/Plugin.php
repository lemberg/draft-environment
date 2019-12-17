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
    $this->setApp(new App($composer, $io));
  }

  /**
   * {@inheritdoc}
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
