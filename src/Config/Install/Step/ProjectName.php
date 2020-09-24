<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Install\Step;

use Lemberg\Draft\Environment\Config\Install\InstallConfigStepInterface;

/**
 * Configures project name (will be used as a host name as well).
 */
final class ProjectName extends AbstractInstallStep implements InstallConfigStepInterface {

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return -10;
  }

  /**
   * {@inheritdoc}
   */
  public function install(array &$config): void {
    $default = 'draft.' . time();
    $question = <<<HERE

Please specify the project name. Must be a valid domain name:
  - Allowed characters: lowercase letters (a-z), numbers (0-9), period (.) and
    dash (-)
  - Should not start or end with dash (-) or dot (.) (e.g. -google- or .apple.)
  - Should be between 3 and 63 characters long

 > Project name <question>[$default]</question>: 
HERE;

    $config['vagrant']['hostname'] = $this->io->askAndValidate(
      $question, [__CLASS__, 'validateProjectName'], NULL, $default
    );
  }

  /**
   * Validates that the given value is a valid project name.
   *
   * @param string $value
   *   Entered project name.
   *
   * @return string
   *   Valid project name.
   *
   * @throws \UnexpectedValueException
   *   When project name is not valid.
   */
  public static function validateProjectName($value): string {
    if (preg_match('/^[a-z0-9][a-z0-9-\.]{1,61}[a-zA-Z0-9]$/', $value) !== 1) {
      throw new \UnexpectedValueException(sprintf("\nSpecified value '%s' is not a valid project name. Please try again", $value));
    }

    return $value;
  }

}
