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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Build path to the test composer.json file based on this class name.
    $this->basePath = './tests/fixtures/Functional' . str_replace('\\', DIRECTORY_SEPARATOR, substr(__CLASS__, strlen('Lemberg\Tests\Functional\Draft\Environment')));
  }

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
          'symlink' => TRUE,
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

    $this->assertComposerLockContainsPackageExtra();
  }

}
