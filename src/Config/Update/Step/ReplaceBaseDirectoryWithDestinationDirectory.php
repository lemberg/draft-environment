<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Update\Step;

use Lemberg\Draft\Environment\Config\Update\UpdateStepInterface;

/**
 * Replace base directory with destination directory.
 */
final class ReplaceBaseDirectoryWithDestinationDirectory extends AbstractUpdateStep implements UpdateStepInterface {

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return 4;
  }

  /**
   * {@inheritdoc}
   */
  public function update(array &$config): void {
    $config['vagrant']['source_directory'] = '.';
    $config['vagrant']['destination_directory'] = $config['vagrant']['base_directory'] ?? '/var/www/draft';
    unset($config['vagrant']['base_directory']);

    // Replace the default SSH directory setting as well.
    if (array_key_exists('ssh_default_directory', $config)) {
      $config['ssh_default_directory'] = str_replace('base_directory', 'destination_directory', $config['ssh_default_directory']);
    }
  }

}
