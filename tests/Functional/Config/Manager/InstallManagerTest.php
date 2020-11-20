<?php

declare(strict_types=1);

namespace Lemberg\Tests\Functional\Draft\Environment\Config\Manager;

use Symfony\Component\Process\Process;

/**
 * Tests Draft Environment configuration install manager.
 *
 * @coversNothing
 */
final class InstallManagerTest extends AbstractConfigManagerTest {

  /**
   * Tests that package installation does set up correct data in the package
   * extra.
   */
  public function testComposerInstall(): void {

    $this->fs->mirror($this->basePath, $this->workingDir, NULL, ['override' => TRUE]);

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
      '--working-dir', $this->workingDir,
    ]))
      ->mustRun();

    $this->assertComposerLockContainsPackageExtra();
  }

}
