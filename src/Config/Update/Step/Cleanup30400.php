<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Update\Step;

use Lemberg\Draft\Environment\Config\Update\UpdateStepInterface;

/**
 * Cleanup project for the 3.4.0.
 */
final class Cleanup30400 extends AbstractUpdateStep implements UpdateStepInterface {

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return 10;
  }

  /**
   * {@inheritdoc}
   */
  public function update(array &$config): void {
    // Update target Ansible version.
    if (array_key_exists('ansible', $config)) {
      if (array_key_exists('version', $config['ansible'])) {
        if ($config['ansible']['version'] === '2.9.*') {
          $config['ansible']['version'] = '4.*';
        }
      }
    }

    // Ubuntu 20.04 uses 40Gb disk by default.
    if (array_key_exists('virtualbox', $config)) {
      if (array_key_exists('disk_size', $config['virtualbox'])) {
        if (intval($config['virtualbox']['disk_size']) < 40) {
          $config['virtualbox']['disk_size'] = '40GB';
        }
      }
    }

    // Fix broken xdebug configuration.
    if (array_key_exists('php_extensions_configuration', $config)) {
      if (array_key_exists('xdebug', $config['php_extensions_configuration'])) {
        if (array_key_exists('xdebug.discover_client_host', $config['php_extensions_configuration']['xdebug']) &&
          $config['php_extensions_configuration']['xdebug']['xdebug.discover_client_host'] === TRUE) {
          $config['php_extensions_configuration']['xdebug']['xdebug.discover_client_host'] = 'true';
        }
      }
    }

    // Update default value of mysql_sql_mode following update of the
    // geerlingguy.mysql (3.3.1 => 3.3.2).
    if (array_key_exists('mysql_sql_mode', $config)) {
      if ($config['mysql_sql_mode'] === '') {
        $config['mysql_sql_mode'] = NULL;
      }
    }
  }

}
