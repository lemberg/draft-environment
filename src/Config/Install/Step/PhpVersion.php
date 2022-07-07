<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Install\Step;

use Lemberg\Draft\Environment\Config\Install\InstallConfigStepInterface;

/**
 * Configures PHP version.
 */
final class PhpVersion extends AbstractInstallStep implements InstallConfigStepInterface {

  /**
   * {@inheritdoc}
   */
  public function install(array &$config): void {
    $choices = ['7.4' => '7.4', '8.0' => '8.0', '8.1' => '8.1'];
    $default = '8.0';
    $question = "\nPlease specify PHP version <question>[$default]</question>: ";
    $config['php_version'] = $this->io->select($question, $choices, $default, FALSE, "\nSpecified value '%s' is not a valid PHP version");
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return 0;
  }

}
