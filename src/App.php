<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment;

use Composer\Composer;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\EventDispatcher\Event;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Lemberg\Draft\Environment\Config\InstallManager;

/**
 * Draft Environment application.
 */
final class App {

  public const PACKAGE_NAME = 'lemberg/draft-environment';

  /**
   * @var \Composer\Composer
   */
  private $composer;

  /**
   * @var \Composer\IO\IOInterface
   */
  private $io;

  /**
   * @var \Lemberg\Draft\Environment\Config\InstallManager
   */
  private $configInstallManager;

  /**
   * Draft Environment app constructor.
   *
   * @param \Composer\Composer $composer
   * @param \Composer\IO\IOInterface $io
   * @param \Lemberg\Draft\Environment\Config\InstallManager $configInstallManager
   */
  public function __construct(Composer $composer, IOInterface $io, InstallManager $configInstallManager) {
    $this->composer = $composer;
    $this->io = $io;
    $this->configInstallManager = $configInstallManager;
  }

  /**
   * Composer events handler.
   *
   * @param \Composer\EventDispatcher\Event $event
   */
  public function handleEvent(Event $event): void {
    if ($event instanceof PackageEvent) {
      $this->handlePackageEvent($event);
    }
  }

  /**
   * Composer package events handler.
   *
   * @param \Composer\Installer\PackageEvent $event
   */
  private function handlePackageEvent(PackageEvent $event): void {
    if ($event->getName() === PackageEvents::PRE_PACKAGE_UNINSTALL && $event->getOperation() instanceof UninstallOperation) {
      $this->onPrePackageUninstall($event->getOperation());
    }
  }

  /**
   * Pre package uninstall event callback.
   *
   * @param \Composer\DependencyResolver\Operation\UninstallOperation $operation
   */
  private function onPrePackageUninstall(UninstallOperation $operation): void {
    // Clean up Draft Environment config files upon package uninstallation.
    if ($operation->getPackage()->getName() === self::PACKAGE_NAME) {
      $this->configInstallManager->uninstall();
    }
  }

}
