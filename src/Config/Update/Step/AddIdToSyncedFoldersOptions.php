<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Update\Step;

use Lemberg\Draft\Environment\Config\Update\UpdateStepInterface;

/**
 * Adds id to the synced folders configuration.
 */
final class AddIdToSyncedFoldersOptions extends AbstractUpdateStep implements UpdateStepInterface {

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return 5;
  }

  /**
   * {@inheritdoc}
   */
  public function update(array &$config): void {
    $config['vagrant']['synced_folder_options']['id'] = 'default';
  }

}
