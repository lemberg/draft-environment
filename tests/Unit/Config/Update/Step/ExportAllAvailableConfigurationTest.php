<?php

declare(strict_types=1);

namespace Lemberg\Tests\Unit\Draft\Environment\Config\Update\Step;

use Composer\Autoload\ClassLoader;
use Composer\Composer;
use Composer\Config as ComposerConfig;
use Composer\IO\IOInterface;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Manager\UpdateManager;
use Lemberg\Draft\Environment\Config\Update\Step\ExportAllAvailableConfiguration;
use Lemberg\Draft\Environment\Utility\Filesystem;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * Tests all available configuration variables update step.
 *
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\AbstractUpdateStep
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\ExportAllAvailableConfiguration
 */
final class ExportAllAvailableConfigurationTest extends TestCase {

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
   * @var \Lemberg\Draft\Environment\Utility\Filesystem
   */
  private $fs;

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
    $this->fs = new Filesystem();
    $this->fs->mkdir(["$this->root/source", "$this->root/target"]);

    $configObject = new Config("$this->root/source", "$this->root/target");
    $classLoader = new ClassLoader();
    $this->configUpdateManager = new UpdateManager($this->composer, $this->io, $configObject, $classLoader);
  }

  /**
   * Tests step weight getter.
   */
  final public function testGetWeight(): void {
    $step = new ExportAllAvailableConfiguration($this->composer, $this->io, $this->configUpdateManager);
    self::assertSame(2, $step->getWeight());
  }

  /**
   * Tests update step execution.
   *
   * @param string $defaultConfig
   * @param array<string,mixed> $config
   * @param string $expectedConfig
   *
   * @dataProvider updateDataProvider
   */
  final public function testUpdate(string $defaultConfig, array $config, string $expectedConfig): void {
    $step = new ExportAllAvailableConfiguration($this->composer, $this->io, $this->configUpdateManager);
    $configObject = $this->configUpdateManager->getConfig();
    $this->fs->dumpFile($configObject->getSourceConfigFilepath(Config::SOURCE_CONFIG_FILENAME), $defaultConfig);

    $step->update($config);

    $actualConfig = $this->fs->loadFile('config', $configObject->getTargetConfigFilepath(Config::TARGET_CONFIG_FILENAME));
    self::assertSame($expectedConfig, $actualConfig);
  }

  /**
   * Tests that exception is thrown when configuration contains unsupported
   * types data.
   */
  final public function testConfigMergeThrowsAnExceptionWhenUnsupportedDataTypeIsPassed(): void {
    $step = new ExportAllAvailableConfiguration($this->composer, $this->io, $this->configUpdateManager);
    $configObject = $this->configUpdateManager->getConfig();
    $this->fs->dumpFile($configObject->getSourceConfigFilepath(Config::SOURCE_CONFIG_FILENAME), 'TEST: TEST');

    $config = ['test' => new \stdClass()];

    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage(sprintf("Unexpected value type '%s' in the configuration array", gettype($config['test'])));

    $step->update($config);
  }

  /**
   * Data provider for the ::testUpdate().
   *
   * @return array<int,array<int,string|array<string,mixed>>>
   */
  final public function updateDataProvider(): array {

    $defaultConfig = <<<EOT
# Comment A
a: b
# Comment C
c:
  # Comment D
  - d
  # Comment E
  - e
  # Comment F
  - f
  # Comment G
  - g
# Comment H
h:
  # Comment I
  i: j
  # Comment K
  k:
    # Comment L
    l: m
    # Comment N
    'n':
      # Comment O
      o: p
      # Comment R
      r: s
    # Comment T
    t: u
# Comment V
v: w
# Comment X
# Comment X2
x:
  # Comment Y
  - 'y'
  # Comment Z
  - z
aa: 34
EOT;

    $configA = [
      'a' => 'b',
      'c' => [
        'd',
        'e',
        'f',
        'g',
      ],
      'h' => [
        'i' => 'j',
        'k' => [
          'l' => 'm',
          'n' => [
            'o' => 'p',
            'r' => 's',
          ],
          't' => 'u',
        ],
      ],
      'v' => 'w',
      'x' => [
        'y',
        'z',
      ],
      'aa' => 34,
    ];

    $configB = [
      'a' => 'aa',
      'c' => [
        'd',
        'g',
      ],
      'h' => [
        'i' => 'jj',
        'k' => [
          'l' => [],
          'n' => [
            'o' => 'pp',
            'r' => [
              'rr' => 'ss',
            ],
          ],
        ],
      ],
      'v' => 'w',
      'aa' => [
        'bb' => [
          'cc' => 'dd',
        ],
      ],
      'new' => 'new',
    ];

    $expectedConfigB = <<<EOT
# Comment A
a: aa
# Comment C
c:
  # Comment D
  - d
  # Comment G
  - g
# Comment H
h:
  # Comment I
  i: jj
  # Comment K
  k:
    # Comment L
    l: []
    # Comment N
    'n':
      # Comment O
      o: pp
      # Comment R
      r:
        rr: ss
    # Comment T
    t: u
# Comment V
v: w
# Comment X
# Comment X2
x:
  # Comment Y
  - 'y'
  # Comment Z
  - z
aa:
  bb:
    cc: dd
new: new
EOT;

    return [
      [
        $defaultConfig,
        $configA,
        $defaultConfig,
      ],
      [
        $defaultConfig,
        $configB,
        $expectedConfigB,
      ],
    ];
  }

}
