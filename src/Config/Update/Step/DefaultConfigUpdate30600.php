<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Update\Step;

/**
 * Cleanup project for the 3.6.0.
 */
final class DefaultConfigUpdate30600 extends ExportAllAvailableConfiguration {

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return 13;
  }

}
