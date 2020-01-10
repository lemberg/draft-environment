<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config;

/**
 *
 * @author roman
 */
trait ConfigAwareTrait {

  /**
   * @var \Lemberg\Draft\Environment\Config\Config
   */
  protected $config;

  /**
   * @return \Lemberg\Draft\Environment\Config\Config
   */
  final public function getConfig(): Config {
    return $this->config;
  }

  /**
   * @param \Lemberg\Draft\Environment\Config\Config $config
   */
  final public function setConfig(Config $config): void {
    $this->config = $config;
  }

}
