<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Update\Step;

use Lemberg\Draft\Environment\Config\Update\UpdateStepInterface;

/**
 * Updates guest OS to Ubuntu 20.04.
 *
 * @link https://github.com/lemberg/draft-environment/issues/235
 */
final class Xenial2Focal extends AbstractUpdateStep implements UpdateStepInterface {

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return 9;
  }

  /**
   * {@inheritdoc}
   */
  public function update(array &$config): void {
    /**
     * @var array{
     *   vagrant?: array{
     *     box?: string
     *   }
     * } $config
     */
    if (array_key_exists('vagrant', $config)) {
      if (array_key_exists('box', $config['vagrant'])) {
        if ($config['vagrant']['box'] === 'ubuntu/xenial64') {
          $config['vagrant']['box'] = 'ubuntu/focal64';
        }
      }
    }
  }

}
