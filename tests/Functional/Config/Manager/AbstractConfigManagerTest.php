<?php

declare(strict_types=1);

namespace Lemberg\Tests\Functional\Draft\Environment\Config\Manager;

use Composer\Json\JsonFile;
use Lemberg\Draft\Environment\App;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Base configuration manager test.
 *
 * @coversNothing
 */
abstract class AbstractConfigManagerTest extends TestCase {

  /**
   * @var string
   */
  protected $workingDir;

  /**
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $fs;

  /**
   * @var string
   */
  protected $basePath;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    $this->workingDir = sys_get_temp_dir() . '/draft-environment';

    $this->fs = new Filesystem();
    $this->fs->remove($this->workingDir);
    $this->fs->mkdir($this->workingDir);
  }

  /**
   * Asserts that composer.lock exists and contains correct data in the package
   * extra section.
   */
  final protected function assertComposerLockContainsPackageExtra(): void {
    self::assertFileExists("$this->workingDir/composer.lock");

    $composer_lock = new JsonFile("$this->workingDir/composer.lock");
    $decoded_composer_lock = $composer_lock->read();

    $key = array_search(App::PACKAGE_NAME, array_column($decoded_composer_lock['packages-dev'], 'name'), TRUE);

    self::assertTrue($decoded_composer_lock['packages-dev'][$key]['extra']['draft-environment']['already-installed']);
    self::assertSame(5, $decoded_composer_lock['packages-dev'][$key]['extra']['draft-environment']['last-update-weight']);
  }

  /**
   * {@inheritdoc}
   */
  final protected function tearDown(): void {
    $this->fs->remove($this->workingDir);

    parent::tearDown();
  }

}
