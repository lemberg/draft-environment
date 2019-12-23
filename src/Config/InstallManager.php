<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config;

use Composer\Composer;
use Composer\IO\IOInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Draft Environment configuration installer.
 */
final class InstallManager {

  /**
   * @var \Composer\Composer
   */
  private $composer;

  /**
   * @var \Composer\IO\IOInterface
   */
  private $io;

  /**
   * @var \Lemberg\Draft\Environment\Config\Config
   */
  private $config;

  /**
   * Draft Environment configuration installer constructor.
   *
   * @param \Composer\Composer $composer
   * @param \Composer\IO\IOInterface $io
   * @param string $sourceDirectory
   * @param string $targetDirectory
   */
  public function __construct(Composer $composer, IOInterface $io, string $sourceDirectory, string $targetDirectory) {
    $this->composer = $composer;
    $this->io = $io;
    $this->setConfig(new Config($sourceDirectory, $targetDirectory));
  }

  /**
   * @return \Lemberg\Draft\Environment\Config\Config
   */
  public function getConfig(): Config {
    return $this->config;
  }

  /**
   * @param \Lemberg\Draft\Environment\Config\Config $config
   */
  public function setConfig(Config $config): void {
    $this->config = $config;
  }

  /**
   * Uninstalls Draft Environment configuration.
   */
  public function uninstall(): void {
    $fs = new Filesystem();
    $fs->remove($this->config->getTargetConfigFilepaths());
  }

}
