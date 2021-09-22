<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Manager;

/**
 * Configuration update manager interface.
 */
interface UpdateManagerInterface extends ManagerInterface {

  /**
   * Updates the Draft Environment.
   */
  public function update(): void;

}
