<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment;

use Composer\Composer;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;

/**
 * Draft Environment application.
 */
class App {

  const PACKAGE_NAME = 'lemberg/draft-environment';

  const SETTINGS_FILENAME = 'vm-settings.yml';

  const VIRTUAL_MACHINE_FILENAME = 'Vagrantfile';

  const CONFIGURATION_FILENAMES = [
    self::SETTINGS_FILENAME,
    self::VIRTUAL_MACHINE_FILENAME,
  ];

  /**
   * @var \Composer\Composer
   */
  protected $composer;

  /**
   * @var \Composer\IO\IOInterface
   */
  protected $io;

  /**
   * @var string
   */
  protected $workingDirectory;

  /**
   * Draft Environment app constructor.
   *
   * @param \Composer\Composer $composer
   * @param \Composer\IO\IOInterface $io
   * @param string $directory
   */
  public function __construct(Composer $composer, IOInterface $io, string $directory = NULL) {
    $this->composer = $composer;
    $this->io = $io;
    $this->workingDirectory = $directory ?: getcwd();
  }

  /**
   * Pre package uninstall event callback.
   *
   * @param \Composer\Installer\PackageEvent $event
   */
  public function onPrePackageUninstall(PackageEvent $event): void {
    // Clean up Draft Environment config files upon package uninstallation.
    if ($event->getOperation()->getPackage()->getName() === static::PACKAGE_NAME) {
      foreach ($this->getConfigurationFilepaths() as $filepath) {
        if (file_exists($filepath)) {
          unlink($filepath);
        }
      }
    }
  }

  /**
   * Generates and array of file paths to the Draft Environment configuration
   * files.
   *
   * @return \Iterator
   */
  public function getConfigurationFilepaths(): \Iterator {
    foreach (static::CONFIGURATION_FILENAMES as $filename) {
      yield $this->workingDirectory . DIRECTORY_SEPARATOR . $filename;
    }
  }

}
