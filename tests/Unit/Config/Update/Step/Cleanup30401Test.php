<?php

declare(strict_types=1);

namespace Lemberg\Tests\Unit\Draft\Environment\Config\Update\Step;

use Composer\Autoload\ClassLoader;
use Composer\Composer;
use Composer\Config as ComposerConfig;
use Composer\IO\IOInterface;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Manager\UpdateManager;
use Lemberg\Draft\Environment\Config\Update\Step\Cleanup30401;
use Lemberg\Draft\Environment\Utility\Filesystem;
use org\bovigo\vfs\vfsStream;

/**
 * Tests updating PHP configuration.
 *
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\AbstractUpdateStep
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\Cleanup30401
 */
final class Cleanup30401Test extends Cleanup30400Test {

  /**
   * @var \Composer\Composer
   */
  private $composer;

  /**
   * @var \Composer\IO\IOInterface
   */
  private $io;

  /**
   * @var string
   */
  private $root;

  /**
   * @var \Lemberg\Draft\Environment\Config\Manager\UpdateManagerInterface
   */
  private $configUpdateManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->composer = new Composer();
    $this->composer->setConfig(new ComposerConfig());
    $this->io = $this->createMock(IOInterface::class);

    // Mock source and target configuration directories.
    $this->root = vfsStream::setup()->url();
    $fs = new Filesystem();
    $fs->mkdir(["$this->root/source", "$this->root/target"]);

    $configObject = new Config("$this->root/source", "$this->root/target");
    $classLoader = new ClassLoader();
    $this->configUpdateManager = new UpdateManager($this->composer, $this->io, $configObject, $classLoader);
  }

  /**
   * {@inheritdoc}
   */
  final public function testGetWeight(): void {
    $step = new Cleanup30401($this->composer, $this->io, $this->configUpdateManager);
    self::assertSame(11, $step->getWeight());
  }

  /**
   * Tests update step execution.
   *
   * @param array<string,mixed> $config
   * @param array<string,mixed> $expectedConfig
   *
   * @dataProvider updateDataProvider
   */
  final public function testUpdate(array $config, array $expectedConfig): void {
    $step = new Cleanup30401($this->composer, $this->io, $this->configUpdateManager);

    $step->update($config);
    self::assertSame($expectedConfig, $config);
  }

  /**
   * Data provider for the ::testUpdate().
   *
   * @return array<int,array<int,string|array<string,mixed>>>
   */
  public function updateDataProvider(): array {
    $data = parent::updateDataProvider();
    $data[] = [
      [
        'mysql_sql_mode' => '~',
      ],
      [
        'mysql_sql_mode' => NULL,
      ],
    ];

    return $data;
  }

}
