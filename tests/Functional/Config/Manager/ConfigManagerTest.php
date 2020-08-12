<?php

declare(strict_types=1);

namespace Lemberg\Tests\Functional\Draft\Environment\Config\Manager;

use Lemberg\Draft\Environment\App;
use Symfony\Component\Process\Process;

/**
 * Tests Draft Environment configuration update manager.
 *
 * @coversNothing
 */
final class ConfigManagerTest extends AbstractConfigManagerTest {

  /**
   * Tests that package update does set up correct data in the package extra.
   *
   * @param string $directory
   *
   * @testWith ["update-before-update-manager"]
   *           ["update-after-update-manager"]
   */
  public function testComposerUpdate(string $directory): void {

    $this->fs->mirror("$this->basePath/$directory", $this->workingDir, NULL, ['override' => TRUE]);

    // Link package working directory, so tests runs against the proper
    // source code.
    (new Process([
      'vendor/bin/composer', 'config',
      'repositories.test', json_encode([
        'type' => 'path',
        'url' => getcwd(),
        'options' => [
          'symlink' => FALSE,
        ],
      ]),
      '--working-dir', $this->workingDir,
    ]))
      ->mustRun();

    // Run composer install.
    (new Process([
      'vendor/bin/composer', 'install',
      '--prefer-dist',
      '--no-interaction',
      '--no-suggest',
      '--working-dir', $this->workingDir,
    ]))
      ->mustRun();

    // Get the current working branch to test against.
    $working_branch = (new Process([
      'git', 'rev-parse', '--abbrev-ref', 'HEAD',
    ]))
      ->mustRun()
      ->getOutput();

    // Update the package by requiring the latest dev version.
    (new Process([
      'vendor/bin/composer', 'require',
      '--dev', App::PACKAGE_NAME . ':' . rtrim($working_branch) . '-dev',
      '--no-suggest',
      '--working-dir', $this->workingDir,
    ]))
      ->mustRun();

    $this->assertComposerLockContainsPackageExtra();
  }

}
