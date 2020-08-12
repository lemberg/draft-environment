<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Update\Step;

use Lemberg\Draft\Environment\Config\Update\UpdateStepInterface;

/**
 * Allows connecting to the MySQL instance from any host.
 */
final class AllowAllHostsMysql extends AbstractUpdateStep implements UpdateStepInterface {

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return 6;
  }

  /**
   * {@inheritdoc}
   */
  public function update(array &$config): void {
    if (array_key_exists('mysql_users', $config)) {
      foreach ($config['mysql_users'] as &$value) {
        $value['host'] = $value['host'] ?? '%';
      }
    }
  }

}
