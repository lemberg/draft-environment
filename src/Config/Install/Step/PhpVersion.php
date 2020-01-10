<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Install\Step;

use Lemberg\Draft\Environment\Config\Install\InstallConfigStepInterface;

/**
 * Configures project name (will be used as a host name as well).
 */
final class PhpVersion extends AbstractInstallStep implements InstallConfigStepInterface {

  /**
   * {@inheritdoc}
   */
  public function install(array &$config): void {
    $choices = ['7.2' => '7.2', '7.3' => '7.3', '7.4' => '7.4'];
    $default = '7.3';
    $question = "\nPlease specify PHP version <question>[$default]</question>: ";
    $config['php_version'] = $this->io->select($question, $choices, $default, FALSE, "\nSpecified value '%s' is not a valid PHP version");
  }

}
