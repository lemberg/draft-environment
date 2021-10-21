<?php

declare(strict_types=1);

namespace Lemberg\Tests\Unit\Draft\Environment\Config\Update\Step;

use Composer\Autoload\ClassLoader;
use Composer\Composer;
use Composer\Config as ComposerConfig;
use Composer\IO\IOInterface;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Manager\UpdateManager;
use Lemberg\Draft\Environment\Config\Update\Step\AllowAllHostsMysql;
use Lemberg\Draft\Environment\Utility\Filesystem;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * Tests adding id to the synced folders configuration.
 *
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\AbstractUpdateStep
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\AllowAllHostsMysql
 */
final class AllowAllHostsMysqlTest extends TestCase {

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
   * Tests step weight getter.
   */
  final public function testGetWeight(): void {
    $step = new AllowAllHostsMysql($this->composer, $this->io, $this->configUpdateManager);
    self::assertSame(6, $step->getWeight());
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
    $step = new AllowAllHostsMysql($this->composer, $this->io, $this->configUpdateManager);

    $step->update($config);
    self::assertSame($config, $expectedConfig);
  }

  /**
   * Data provider for the ::testUpdate().
   *
   * @return array<int,array<int,string|array<string,mixed>>>
   */
  final public function updateDataProvider(): array {
    return [
      [
        [
          'mysql_users' => [
            [
              'name' => 'drupal',
              'password' => 'drupal',
              'priv' => 'drupal.*:ALL',
            ],
            [
              'name' => 'drupal2',
              'password' => 'drupal2',
              'priv' => 'drupal.*:ALL',
            ],
            [
              'name' => 'drupal3',
              'password' => 'drupal3',
              'priv' => 'drupal.*:ALL',
              'host' => 'localhost',
            ],
          ],
        ],
        [
          'mysql_users' => [
            [
              'name' => 'drupal',
              'password' => 'drupal',
              'priv' => 'drupal.*:ALL',
              'host' => '%',
            ],
            [
              'name' => 'drupal2',
              'password' => 'drupal2',
              'priv' => 'drupal.*:ALL',
              'host' => '%',
            ],
            [
              'name' => 'drupal3',
              'password' => 'drupal3',
              'priv' => 'drupal.*:ALL',
              'host' => 'localhost',
            ],
          ],
        ],
      ],
    ];
  }

}
