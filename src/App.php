<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment;

use Composer\Composer;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\EventDispatcher\Event;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Draft Environment application.
 */
final class App {

  public const PACKAGE_NAME = 'lemberg/draft-environment';
  private const SETTINGS_FILENAME = 'vm-settings.yml';
  private const VIRTUAL_MACHINE_FILENAME = 'Vagrantfile';
  private const CONFIGURATION_FILENAMES = [
    self::SETTINGS_FILENAME,
    self::VIRTUAL_MACHINE_FILENAME,
  ];

  /**
   * @var \Composer\Composer
   */
  private $composer;

  /**
   * @var \Composer\IO\IOInterface
   */
  private $io;

  /**
   * @var string
   */
  private $workingDirectory;

  /**
   * Draft Environment app constructor.
   *
   * @param \Composer\Composer $composer
   * @param \Composer\IO\IOInterface $io
   * @param string $directory
   */
  public function __construct(Composer $composer, IOInterface $io, string $directory) {
    $this->composer = $composer;
    $this->io = $io;
    $this->workingDirectory = $directory;
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
   * Generates and array of file paths to the Draft Environment configuration
   * files.
   *
   * @return \Iterator<int, string>
   */
  public function getConfigurationFilepaths(): \Iterator {
    foreach (static::CONFIGURATION_FILENAMES as $filename) {
      yield $this->workingDirectory . DIRECTORY_SEPARATOR . $filename;
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
      $fs = new Filesystem();
      $fs->remove($this->getConfigurationFilepaths());
    }
  }

}
