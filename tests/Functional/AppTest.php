<?php

declare(strict_types=1);

namespace Lemberg\Tests\Functional\Draft\Environment;

use Lemberg\Draft\Environment\App;
use Symfony\Component\Process\Process;

/**
 * Tests Draft Environment installation and removal.
 *
 * @coversNothing
 */
final class AppTest extends AbstractFunctionalTest {

  /**
   * Tests that package installation and removal works as expected.
   *
   * @doesNotPerformAssertions
   */
  public function testComposerInstallAndRemove(): void {

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

    // Run composer remove.
    (new Process([
      'vendor/bin/composer', 'remove',
      '--dev', App::PACKAGE_NAME,
      '--working-dir', $this->workingDir,
    ]))
      ->mustRun();
  }

}
