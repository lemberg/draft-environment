<?php

declare(strict_types=1);

namespace Lemberg\Tests\Unit\Draft\Environment\Config\Update\Step;

use Composer\Composer;
use Composer\Config as ComposerConfig;
use Composer\IO\IOInterface;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Manager\UpdateManager;
use Lemberg\Draft\Environment\Config\Update\Step\Cleanup30400;
use Lemberg\Draft\Environment\Utility\Filesystem;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * Tests updating PHP configuration.
 *
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\AbstractUpdateStep
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\Cleanup30400
 */
class Cleanup30400Test extends TestCase {

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
    $this->configUpdateManager = new UpdateManager($this->composer, $this->io, $configObject);
  }

  /**
   * Tests step weight getter.
   */
  public function testGetWeight(): void {
    $step = new Cleanup30400($this->composer, $this->io, $this->configUpdateManager);
    self::assertSame(10, $step->getWeight());
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
    $step = new Cleanup30400($this->composer, $this->io, $this->configUpdateManager);

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
      [
        [
          'ansible' => [
            'version' => '2.10.*',
          ],
          'virtualbox' => [
            'disk_size' => '20Gb',
          ],
          'mysql_sql_mode' => 'ANSI',
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
          'ansible' => [
            'version' => '2.10.*',
          ],
          'virtualbox' => [
            'disk_size' => '40GB',
          ],
          'mysql_sql_mode' => 'ANSI',
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
          'ansible' => [
            'version' => '2.9.*',
          ],
          'virtualbox' => [
            'memory' => 1024,
            'disk_size' => '10Gb',
          ],
          'mysql_sql_mode' => '',
          'php_extensions_configuration' => [
            'opcache' => [
              'opcache.error_log' => '/var/log/draft/php_opcache_error.log',
            ],
            'xdebug' => [
              'xdebug.mode' => 'debug',
              'xdebug.discover_client_host' => TRUE,
              'xdebug.remote_log' => '/var/log/draft/php_xdebug_remote.log',
            ],
          ],
        ],
        [
          'ansible' => [
            'version' => '4.*',
          ],
          'virtualbox' => [
            'memory' => 1024,
            'disk_size' => '40GB',
          ],
          'mysql_sql_mode' => NULL,
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
