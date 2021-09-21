<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Update\Step;

use Lemberg\Draft\Environment\Config\Update\UpdateStepInterface;

/**
 * Cleanup project for the 3.4.1.
 */
final class Cleanup30401 extends AbstractUpdateStep implements UpdateStepInterface {

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return 11;
  }

  /**
   * {@inheritdoc}
   */
  public function update(array &$config): void {

    $step30400 = new Cleanup30400($this->composer, $this->io, $this->configUpdateManager);

    $step30400->update($config);

    // Fix MySQL installation fails due to misconfiguration.
    if (array_key_exists('mysql_sql_mode', $config)) {
      if ($config['mysql_sql_mode'] === '~') {
        $config['mysql_sql_mode'] = NULL;
      }
    }
  }

}
