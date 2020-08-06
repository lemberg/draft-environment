<?php

declare(strict_types=1);

namespace Lemberg\Tests\Unit\Draft\Environment\Config\Update\Step;

use Composer\Composer;
use Composer\Config as ComposerConfig;
use Composer\IO\IOInterface;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Manager\UpdateManager;
use Lemberg\Draft\Environment\Config\Update\Step\ReplaceBaseDirectoryWithDestinationDirectory;
use Lemberg\Draft\Environment\Utility\Filesystem;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * Tests replacing base directory with destination directory update step.
 *
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\AbstractUpdateStep
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\ReplaceBaseDirectoryWithDestinationDirectory
 */
final class ReplaceBaseDirectoryWithDestinationDirectoryTest extends TestCase {

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
    $step = new ReplaceBaseDirectoryWithDestinationDirectory($this->composer, $this->io, $this->configUpdateManager);
    self::assertSame(4, $step->getWeight());
  }

  /**
   * Tests update step execution.
   *
   * @param array<int,array<string,array<string,string>>> $config
   * @param array<int,array<string,array<string,string>>> $expectedConfig
   *
   * @dataProvider updateDataProvider
   */
  final public function testUpdate(array $config, array $expectedConfig): void {
    $step = new ReplaceBaseDirectoryWithDestinationDirectory($this->composer, $this->io, $this->configUpdateManager);

    $step->update($config);
    self::assertSame($config, $expectedConfig);
  }

  /**
   * Data provider for the ::testUpdate().
   *
   * @return array<int,array<string,array<string,string>>>
   */
  final public function updateDataProvider(): array {
    return [
      [
        [
          'vagrant' => [
            'base_directory' => '/var/www/draft',
          ],
          'ssh_default_directory' => '{{ vagrant.base_directory }}',
        ],
        [
          'vagrant' => [
            'source_directory' => '.',
            'destination_directory' => '/var/www/draft',
          ],
          'ssh_default_directory' => '{{ vagrant.destination_directory }}',
        ],
      ],
    ];
  }

}
