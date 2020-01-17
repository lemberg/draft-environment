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

  /**
   * Get the last update weight from the local repository.
   *
   * @return int
   */
  public function getLastAppliedUpdateWeight(): int;

  /**
   * Set the last update weight in the local repository.
   *
   * @param int $weight
   */
  public function setLastAppliedUpdateWeight(int $weight): void;

  /**
   * Get the weight of the last available step.
   *
   * @return int
   */
  public function getLastAvailableUpdateWeight(): int;

}
