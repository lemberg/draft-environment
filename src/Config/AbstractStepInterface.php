<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config;

use Composer\Composer;
use Composer\IO\IOInterface;

/**
 * Abstract installation/uninstall step.
 */
interface AbstractStepInterface {

  /**
   * @param \Composer\Composer $composer
   * @param \Composer\IO\IOInterface $io
   * @param \Lemberg\Draft\Environment\Config\Config $config
   */
  public function __construct(Composer $composer, IOInterface $io, Config $config);

  /**
   * Returns the weight of the step. Lighter steps will be executed sooner.
   *
   * @return int
   *   Weight of the step.
   */
  public function getWeight(): int;

}
