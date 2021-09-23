<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Install;

use Lemberg\Draft\Environment\Config\AbstractStepInterface;

/**
 * Configuration setup installation step.
 */
interface InstallConfigStepInterface extends AbstractStepInterface {

  /**
   * Contains installation step business logic.
   *
   * @param array<int|string,mixed> $config
   *   Draft Environment configuration nested array.
   */
  public function install(array &$config): void;

}
