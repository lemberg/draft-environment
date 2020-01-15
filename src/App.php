<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\EventDispatcher\Event;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Script\Event as ScriptEvent;
use Composer\Script\ScriptEvents;
use Lemberg\Draft\Environment\Config\Manager\InstallManager;

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
   * @var \Lemberg\Draft\Environment\Config\Manager\InstallManager
   */
  private $configInstallManager;

  /**
   * Boolean indicating whether the installation process should run.
   *
   * @var bool
   */
  private $shouldRunInstallation = FALSE;

  /**
   * Draft Environment app constructor.
   *
   * @param \Composer\Composer $composer
   * @param \Composer\IO\IOInterface $io
   * @param \Lemberg\Draft\Environment\Config\Manager\InstallManager $configInstallManager
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
    elseif ($event instanceof ScriptEvent) {
      $this->handleScriptEvent($event);
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

  /**
   * Pre package uninstall event callback.
   *
   * @param \Composer\DependencyResolver\Operation\InstallOperation $operation
   */
  private function onPostPackageInstall(InstallOperation $operation): void {
    // Clean up Draft Environment config files upon package uninstallation.
    if ($operation->getPackage()->getName() === self::PACKAGE_NAME) {
      $this->shouldRunInstallation = TRUE;
    }
  }

  /**
   * Composer script events handler.
   *
   * @param \Composer\Script\Event $event
   */
  private function handleScriptEvent(ScriptEvent $event): void {
    if ($event->getName() === ScriptEvents::POST_INSTALL_CMD || $event->getName() === ScriptEvents::POST_UPDATE_CMD) {
      $this->onPostInstallCommand($event);
    }
  }

  /**
   * Post install command event handler.
   *
   * @param \Composer\Script\Event $event
   */
  private function onPostInstallCommand(ScriptEvent $event): void {
    if ($this->shouldRunInstallation) {
      $this->configInstallManager->install();
    }
    $this->shouldRunInstallation = FALSE;
  }

}
