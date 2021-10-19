<?php

declare(strict_types=1);

namespace Lemberg\Tests\Unit\Draft\Environment\Config\Update\Step;

use Composer\Autoload\ClassLoader;
use Composer\Composer;
use Composer\Config as ComposerConfig;
use Composer\IO\IOInterface;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Manager\UpdateManager;
use Lemberg\Draft\Environment\Config\Update\Step\Xdebug2To3;
use Lemberg\Draft\Environment\Utility\Filesystem;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * Tests updating PHP configuration.
 *
 * @link https://github.com/lemberg/draft-environment/issues/204
 *
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\AbstractUpdateStep
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\Xdebug2To3
 */
final class Xdebug2To3Test extends TestCase {

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
    $step = new Xdebug2To3($this->composer, $this->io, $this->configUpdateManager);
    self::assertSame(8, $step->getWeight());
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
    $step = new Xdebug2To3($this->composer, $this->io, $this->configUpdateManager);

    $step->update($config);
    self::assertSame($expectedConfig, $config);
  }

  /**
   * Data provider for the ::testUpdate().
   *
   * @return array<int,array<int,string|array<string,mixed>>>
   */
  final public function updateDataProvider(): array {
    return [
      [
        [],
        [],
      ],
      [
        [
          'php_extensions_configuration' => [
            'opcache' => [
              'opcache.error_log' => '/var/log/draft/php_opcache_error.log',
            ],
          ],
        ],
        [
          'php_extensions_configuration' => [
            'opcache' => [
              'opcache.error_log' => '/var/log/draft/php_opcache_error.log',
            ],
          ],
        ],
      ],
      [
        [
          'php_extensions_configuration' => [
            'opcache' => [
              'opcache.error_log' => '/var/log/draft/php_opcache_error.log',
            ],
            'xdebug' => [
              'xdebug.mode' => 'debug',
              'xdebug.discover_client_host' => 'true',
              'xdebug.remote_log' => '/var/log/draft/php_xdebug_remote.log',
            ],
          ],
        ],
        [
          'php_extensions_configuration' => [
            'opcache' => [
              'opcache.error_log' => '/var/log/draft/php_opcache_error.log',
            ],
            'xdebug' => [
              'xdebug.mode' => 'debug',
              'xdebug.discover_client_host' => 'true',
              'xdebug.remote_log' => '/var/log/draft/php_xdebug_remote.log',
            ],
          ],
        ],
      ],
      [
        [
          'php_extensions_configuration' => [
            'opcache' => [
              'opcache.error_log' => '/var/log/draft/php_opcache_error.log',
            ],
            'xdebug' => [
              'xdebug.remote_enable' => 'Off',
              'xdebug.remote_connect_back' => 'Off',
              'xdebug.remote_log' => '/var/log/draft/php_xdebug_remote.log',
            ],
          ],
        ],
        [
          'php_extensions_configuration' => [
            'opcache' => [
              'opcache.error_log' => '/var/log/draft/php_opcache_error.log',
            ],
            'xdebug' => [
              'xdebug.mode' => 'off',
              'xdebug.discover_client_host' => 'false',
              'xdebug.remote_log' => '/var/log/draft/php_xdebug_remote.log',
            ],
          ],
        ],
      ],
      [
        [
          'php_extensions_configuration' => [
            'opcache' => [
              'opcache.error_log' => '/var/log/draft/php_opcache_error.log',
            ],
            'xdebug' => [
              'xdebug.remote_enable' => 'On',
              'xdebug.remote_connect_back' => 'On',
              'xdebug.remote_log' => '/var/log/draft/php_xdebug_remote.log',
            ],
          ],
        ],
        [
          'php_extensions_configuration' => [
            'opcache' => [
              'opcache.error_log' => '/var/log/draft/php_opcache_error.log',
            ],
            'xdebug' => [
              'xdebug.mode' => 'debug',
              'xdebug.discover_client_host' => 'true',
              'xdebug.remote_log' => '/var/log/draft/php_xdebug_remote.log',
            ],
          ],
        ],
      ],
    ];
  }

}
