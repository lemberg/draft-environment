<?php

namespace Lemberg\Draft\Environment;

use Composer\Composer;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;

/**
 * Draft Environment application.
 */
class App {

  /**
   * @var \Composer\Composer $composer
   */
  protected $composer;

  /**
   * @var \Composer\IO\IOInterface $io
   */
  protected $io;

  /**
   * Draft Environment app constructor.
   *
   * @param \Composer\Composer $composer
   * @param \Composer\IO\IOInterface $io
   */
  public function __construct(Composer $composer, IOInterface $io) {
    $this->composer = $composer;
    $this->io = $io;
  }

  /**
   * Pre package uninstall event callback.
   *
   * @param \Composer\Installer\PackageEvent $event
   */
  public function onPrePackageUninstall(PackageEvent $event) {
    // Clean up Draft Environment config files upon package uninstallation.
    if ($event->getOperation()->getPackage()->getName() === 'lemberg/draft-environment') {
      foreach (['./vm-settings.yml', './Vagrantfile'] as $filename) {
        if (file_exists($filename)) {
          unlink($filename);
        }
      }
    }
  }

}
