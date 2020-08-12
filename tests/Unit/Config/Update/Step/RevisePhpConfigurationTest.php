<?php

declare(strict_types=1);

namespace Lemberg\Tests\Unit\Draft\Environment\Config\Update\Step;

use Composer\Composer;
use Composer\Config as ComposerConfig;
use Composer\IO\IOInterface;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Manager\UpdateManager;
use Lemberg\Draft\Environment\Config\Update\Step\RevisePhpConfiguration;
use Lemberg\Draft\Environment\Utility\Filesystem;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * Tests updating PHP configuration.
 *
 * @link https://github.com/lemberg/draft-environment/issues/204
 *
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\AbstractUpdateStep
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\RevisePhpConfiguration
 */
final class RevisePhpConfigurationTest extends TestCase {

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
  final public function testGetWeight(): void {
    $step = new RevisePhpConfiguration($this->composer, $this->io, $this->configUpdateManager);
    self::assertSame(7, $step->getWeight());
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
    $step = new RevisePhpConfiguration($this->composer, $this->io, $this->configUpdateManager);

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
        [],
        [],
      ],
      [
        [
          'php_configuration' => [
            'PHP' => [
              'display_errors' => 'On',
              'display_startup_errors' => 'On',
            ],
          ],
          'php_cli_configuration' => [
            'PHP' => [
              'memory_limit' => -1,
            ],
          ],
        ],
        [
          'php_configuration' => [
            'PHP' => [
              'display_errors' => 'On',
              'display_startup_errors' => 'On',
              'max_execution_time' => 300,
            ],
          ],
          'php_cli_configuration' => [
            'PHP' => [
              'memory_limit' => -1,
              'error_reporting' => 'E_ALL',
              'error_log' => '/var/log/draft/php_error.log',
              'max_execution_time' => 0,
              'output_buffering' => 'Off',
              'sendmail_path' => '{{ mailhog_install_dir }}/mhsendmail',
            ],
          ],
        ],
      ],
    ];
  }

}
