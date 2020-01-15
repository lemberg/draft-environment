<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config;

/**
 * Implements Config getter and setter.
 */
interface ConfigAwareInterface {

  /**
   * @return \Lemberg\Draft\Environment\Config\Config
   */
  public function getConfig(): Config;

  /**
   * @param \Lemberg\Draft\Environment\Config\Config $config
   */
  public function setConfig(Config $config): void;

}
