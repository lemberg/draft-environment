<?php

declare(strict_types=1);

namespace Lemberg\Tests\Functional\Draft\Environment;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Base functional test.
 *
 * @coversNothing
 */
abstract class AbstractFunctionalTest extends TestCase {

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
  final protected function setUp(): void {

    $this->workingDir = sys_get_temp_dir() . '/draft-environment';

    $this->fs = new Filesystem();
    $this->fs->remove($this->workingDir);
    $this->fs->mkdir($this->workingDir);

    // Build path to the test composer.json file based on the current class
    // name.
    $this->basePath = './tests/fixtures/Functional' . str_replace('\\', DIRECTORY_SEPARATOR, substr(static::class, strlen('Lemberg\Tests\Functional\Draft\Environment')));
  }

  /**
   * {@inheritdoc}
   */
  final protected function tearDown(): void {
    $this->fs->remove($this->workingDir);

    parent::tearDown();
  }

}
