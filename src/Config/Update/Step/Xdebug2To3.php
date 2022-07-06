<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Update\Step;

use Lemberg\Draft\Environment\Config\Update\UpdateStepInterface;

/**
 * Updates PHP configuration to support Xdebug 3.
 *
 * @link https://github.com/lemberg/draft-environment/issues/231
 */
final class Xdebug2To3 extends AbstractUpdateStep implements UpdateStepInterface {

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return 8;
  }

  /**
   * {@inheritdoc}
   */
  public function update(array &$config): void {
    /**
     * @var array{
     *   php_extensions_configuration?: array{
     *     xdebug?: string[],
     *   },
     * } $config
     */
    if (array_key_exists('php_extensions_configuration', $config)) {
      if (array_key_exists('xdebug', $config['php_extensions_configuration'])) {
        /**
         * @var array{
         *   'xdebug.remote_enable'?: string,
         *   'xdebug.discover_client_host'?: string,
         * } $xdebug_config
         */
        $xdebug_config = &$config['php_extensions_configuration']['xdebug'];
        if (array_key_exists('xdebug.remote_enable', $xdebug_config)) {
          $this->replaceArrayKey($xdebug_config, 'xdebug.remote_enable', 'xdebug.mode');
          $xdebug_config['xdebug.mode'] = $xdebug_config['xdebug.mode'] === 'On' ? 'debug' : 'off';
        }
        if (array_key_exists('xdebug.remote_connect_back', $xdebug_config)) {
          $this->replaceArrayKey($xdebug_config, 'xdebug.remote_connect_back', 'xdebug.discover_client_host');
          $xdebug_config['xdebug.discover_client_host'] = $xdebug_config['xdebug.discover_client_host'] === 'On' ? 'true' : 'false';
        }
      }
    }
  }

  /**
   * Replaces key in the array in order to maintain the elements order.
   *
   * @param array<string,string> $array
   *   Input array.
   * @param string $search
   *   Key to be replaced.
   * @param string $replacement
   *   New key.
   *
   * @throws \UnexpectedValueException
   *   Thrown when a searched key does not exist in the array.
   */
  private function replaceArrayKey(array &$array, string $search, string $replacement): void {
    $keys = array_keys($array);
    if (FALSE === ($index = array_search($search, $keys, TRUE))) {
      throw new \UnexpectedValueException(sprintf('Key "%s" does not exist in the config array', $search));
    }
    $keys[$index] = $replacement;
    $array = array_combine($keys, array_values($array));
  }

}
