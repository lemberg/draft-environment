<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Update\Step;

use Lemberg\Draft\Environment\Config\Update\UpdateStepInterface;

/**
 * Cleanup project for the 3.6.0.
 */
final class DefaultConfigUpdate30600 extends AbstractUpdateStep implements UpdateStepInterface {

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return 13;
  }

  /**
   * {@inheritdoc}
   */
  public function update(array &$config): void {
    // Empty update will trigger updating configuration.
  }

}
