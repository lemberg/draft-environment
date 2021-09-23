<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Update\Step;

use Lemberg\Draft\Environment\Config\Update\UpdateStepInterface;

/**
 * Set package as already installed.
 */
final class SetAsAlreadyInstalledStep extends AbstractUpdateStep implements UpdateStepInterface {

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return 12;
  }

  /**
   * {@inheritdoc}
   */
  public function update(array &$config): void {
    $config['draft']['last_applied_update'] = $this->getWeight();
  }

}
