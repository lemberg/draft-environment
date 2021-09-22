<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\Event;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Lemberg\Draft\Environment\Config\Manager\InstallManagerInterface;
use Lemberg\Draft\Environment\Config\Manager\UpdateManagerInterface;

/**
 * Draft Environment application.
 */
final class App {

  public const PACKAGE_NAME = 'lemberg/draft-environment';

  public const LAST_AVAILABLE_UPDATE = 12;

  /**
   * @var \Composer\Composer
   */
  private $composer;

  /**
   * @var \Composer\IO\IOInterface
   */
  private $io;

  /**
   * @var \Lemberg\Draft\Environment\Config\Manager\InstallManagerInterface
   */
  private $configInstallManager;

  /**
   * @var \Lemberg\Draft\Environment\Config\Manager\UpdateManagerInterface
   */
  private $configUpdateManager;

  /**
   * Draft Environment app constructor.
   *
   * @param \Composer\Composer $composer
   * @param \Composer\IO\IOInterface $io
   * @param \Lemberg\Draft\Environment\Config\Manager\InstallManagerInterface $configInstallManager
   * @param \Lemberg\Draft\Environment\Config\Manager\UpdateManagerInterface $configUpdateManager
   */
  public function __construct(Composer $composer, IOInterface $io, InstallManagerInterface $configInstallManager, UpdateManagerInterface $configUpdateManager) {
    $this->composer = $composer;
    $this->io = $io;
    $this->configInstallManager = $configInstallManager;
    $this->configUpdateManager = $configUpdateManager;
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
    if ($event->getName() === PackageEvents::POST_PACKAGE_INSTALL && $event->getOperation() instanceof InstallOperation) {
      $this->onPostPackageInstall($event->getOperation());
    }
    if ($event->getName() === PackageEvents::POST_PACKAGE_UPDATE && $event->getOperation() instanceof UpdateOperation) {
      $this->onPostPackageUpdate($event->getOperation());
    }
    if ($event->getName() === PackageEvents::PRE_PACKAGE_UNINSTALL && $event->getOperation() instanceof UninstallOperation) {
      $this->onPrePackageUninstall($event->getOperation());
    }
  }

  /**
   * Post package install event callback.
   *
   * @param \Composer\DependencyResolver\Operation\InstallOperation $operation
   */
  private function onPostPackageInstall(InstallOperation $operation): void {
    // Clean up Draft Environment config files upon package uninstallation.
    if ($operation->getPackage()->getName() === self::PACKAGE_NAME) {
      $this->configInstallManager->install();
    }
  }

  /**
   * Post package update event callback.
   *
   * @param \Composer\DependencyResolver\Operation\UpdateOperation $operation
   */
  private function onPostPackageUpdate(UpdateOperation $operation): void {
    // Update Draft Environment configuration upon package update.
    if ($operation->getTargetPackage()->getName() === self::PACKAGE_NAME) {
      // Release date may be empty in rare cases. Assume the latest
      // version is being used.
      $now = new \DateTime();
      $initialReleaseDate = $operation->getInitialPackage()->getReleaseDate() ?? $now;
      $targetReleaseDate = $operation->getTargetPackage()->getReleaseDate() ?? $now;
      // Package downgrading is not supported by the update manager.
      if ($targetReleaseDate >= $initialReleaseDate) {
        $this->configUpdateManager->update();
      }
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
