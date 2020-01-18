<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Manager;

/**
 * Configuration install/uninstall manager interface.
 */
interface InstallManagerInterface extends ManagerInterface {

  /**
   * Installs the Draft Environment.
   */
  public function install(): void;

  /**
   * Uninstalls the Draft Environment.
   */
  public function uninstall(): void;

  /**
   * Check whether Draft Environment has been already installed.
   */
  public function hasBeenAlreadyInstalled(): bool;

  /**
   * Set Draft Environment as already installed.
   */
  public function setAsAlreadyInstalled(): void;

}
