<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Manager;

use Composer\Autoload\ClassLoader;
use Composer\Composer;
use Composer\IO\IOInterface;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\ConfigAwareInterface;

/**
 * Configuration manager interface.
 */
interface ManagerInterface extends ConfigAwareInterface {

  /**
   * @param \Composer\Composer $composer
   * @param \Composer\IO\IOInterface $io
   * @param \Lemberg\Draft\Environment\Config\Config $config
   * @param \Composer\Autoload\ClassLoader $classLoader
   */
  public function __construct(Composer $composer, IOInterface $io, Config $config, ClassLoader $classLoader);

}
