<?php

declare(strict_types=1);

namespace Lemberg\Tests\Unit\Draft\Environment\Config\Update\Step;

use Composer\Autoload\ClassLoader;
use Composer\Composer;
use Composer\Config as ComposerConfig;
use Composer\IO\IOInterface;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Manager\UpdateManager;
use Lemberg\Draft\Environment\Config\Update\Step\DefaultConfigUpdate30600;
use Lemberg\Draft\Environment\Utility\Filesystem;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * Tests updating PHP configuration.
 *
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\AbstractUpdateStep
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\DefaultConfigUpdate30600
 */
class DefaultConfigUpdate30600Test extends TestCase {

  /**
   * @var \Composer\Composer
   */
  protected $composer;

  /**
   * @var \Composer\IO\IOInterface
   */
  protected $io;

  /**
   * @var string
   */
  protected $root;

  /**
   * @var \Lemberg\Draft\Environment\Config\Manager\UpdateManagerInterface
   */
  protected $configUpdateManager;

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
  public function testGetWeight(): void {
    $step = new DefaultConfigUpdate30600($this->composer, $this->io, $this->configUpdateManager);
    self::assertSame(13, $step->getWeight());
  }

  /**
   * Tests update step execution.
   *
   * @param array<string,mixed> $config
   * @param array<string,mixed> $expectedConfig
   *
   * @dataProvider updateDataProvider
   */
  public function testUpdate(array $config, array $expectedConfig): void {
    $step = new DefaultConfigUpdate30600($this->composer, $this->io, $this->configUpdateManager);

    $step->update($config);
    self::assertSame($expectedConfig, $config);
  }

  /**
   * Data provider for the ::testUpdate().
   *
   * @return array<int,array<int,string|array<string,mixed>>>
   */
  public function updateDataProvider(): array {
    return [
      [
        [],
        [],
      ],
      [
        [
          'ansible' => [],
          'virtualbox' => [],
          'php_extensions_configuration' => [
            'opcache' => [
              'opcache.error_log' => '/var/log/draft/php_opcache_error.log',
            ],
          ],
        ],
        [
          'ansible' => [],
          'virtualbox' => [],
          'php_extensions_configuration' => [
            'opcache' => [
              'opcache.error_log' => '/var/log/draft/php_opcache_error.log',
            ],
          ],
        ],
      ],
    ];
  }

}
