<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Install;

use Lemberg\Draft\Environment\Config\AbstractStepInterface;

/**
 * General uninstall step.
 */
interface UninstallStepInterface extends AbstractStepInterface {

  /**
   * Contains uninstall step business logic.
   */
  public function uninstall(): void;

}
