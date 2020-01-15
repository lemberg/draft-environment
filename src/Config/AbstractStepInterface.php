<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config;

use Composer\Composer;
use Composer\IO\IOInterface;
use Lemberg\Draft\Environment\Config\Manager\ManagerInterface;
use Lemberg\Draft\Environment\Messanger\MessangerInterface;

/**
 * Abstract installation/uninstall step.
 */
interface AbstractStepInterface extends MessangerInterface {

  /**
   * @param \Composer\Composer $composer
   * @param \Composer\IO\IOInterface $io
   * @param \Lemberg\Draft\Environment\Config\Manager\ManagerInterface $configManager
   */
  public function __construct(Composer $composer, IOInterface $io, ManagerInterface $configManager);

  /**
   * Returns the weight of the step. Lighter steps will be executed sooner.
   *
   * @return int
   *   Weight of the step.
   */
  public function getWeight(): int;

}
