<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Update;

use Lemberg\Draft\Environment\Config\AbstractStepInterface;

/**
 * General update step.
 */
interface UpdateStepInterface extends AbstractStepInterface {

  /**
   * Contains update step business logic.
   *
   * @param array<int|string,mixed> $config
   *   Draft Environment configuration nested array.
   */
  public function update(array &$config): void;

}
