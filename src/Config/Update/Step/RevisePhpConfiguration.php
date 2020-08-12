<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Update\Step;

use Lemberg\Draft\Environment\Config\Update\UpdateStepInterface;

/**
 * Updates PHP configuration.
 *
 * @link https://github.com/lemberg/draft-environment/issues/204
 */
final class RevisePhpConfiguration extends AbstractUpdateStep implements UpdateStepInterface {

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return 7;
  }

  /**
   * {@inheritdoc}
   */
  public function update(array &$config): void {
    if (array_key_exists('php_configuration', $config)) {
      $updates = [
        'max_execution_time' => 300,
      ];

      foreach ($updates as $option => $value) {
        $config['php_configuration']['PHP'][$option] = $config['php_configuration']['PHP'][$option] ?? $value;
      }
    }

    if (array_key_exists('php_cli_configuration', $config)) {
      $updates = [
        'error_reporting' => 'E_ALL',
        'error_log' => '/var/log/draft/php_error.log',
        'max_execution_time' => 0,
        'output_buffering' => 'Off',
        'sendmail_path' => '{{ mailhog_install_dir }}/mhsendmail',
      ];

      foreach ($updates as $option => $value) {
        $config['php_cli_configuration']['PHP'][$option] = $config['php_cli_configuration']['PHP'][$option] ?? $value;
      }
    }
  }

}
