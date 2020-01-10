<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Install;

use Lemberg\Draft\Environment\Config\AbstractStepInterface;

/**
 * Init installation step.
 */
interface InstallInitStepInterface extends AbstractStepInterface {

  /**
   * Contains installation step business logic.
   */
  public function install(): void;

}
