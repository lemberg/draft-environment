<?php

declare(strict_types=1);

namespace Lemberg\Tests\Draft\Environment\Config;

use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Utility\Filesystem;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * Tests Draft Environment configuration install manager.
 *
 * @covers \Lemberg\Draft\Environment\Config\Config
 */
final class ConfigTest extends TestCase {

  /**
   * @var string
   */
  private $root;

  /**
   * @var \Lemberg\Draft\Environment\Config\Config
   */
  private $config;

  /**
   * @var \Lemberg\Draft\Environment\Utility\Filesystem
   */
  private $fs;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    // Mock source and target configuration directories.
    $this->root = vfsStream::setup()->url();
    $this->fs = new Filesystem();
    $this->fs->mkdir(
      ["$this->root/source", "$this->root/target", "$this->root/wd"]
    );

    $this->config = new Config("$this->root/source", "$this->root/target");
  }

  /**
   * Tests the source configuration file paths getter.
   */
  public function testSourceConfigFilepathsGetter(): void {
    $expected = [
      "$this->root/source/" . Config::SOURCE_CONFIG_FILENAME,
      "$this->root/source/" . Config::SOURCE_VM_FILENAME,
    ];
    $generator = $this->config->getSourceConfigFilepaths();
    self::assertInstanceOf(\Generator::class, $generator);
    self::assertSame($expected, iterator_to_array($generator));
  }

  /**
   * Tests a single source configuration file path getter.
   */
  public function testSourceConfigFilepathGetter(): void {
    $expected = "$this->root/source/" . Config::SOURCE_CONFIG_FILENAME;
    $actual = $this->config->getSourceConfigFilepath(Config::SOURCE_CONFIG_FILENAME);
    self::assertSame($expected, $actual);

    $this->expectException(\InvalidArgumentException::class);
    $filename = 'this-file-is-not-part-of-the-draft-environment-project.yml';
    $this->expectExceptionMessage(sprintf("Non-existing Draft Environment source configuration filename '%s' has been passed.", $filename));
    $this->config->getSourceConfigFilepath($filename);
  }

  /**
   * Tests the target configuration file path getter.
   */
  public function testTargetConfigFilepathsGetter(): void {
    // Test including .gitignore.
    $expected = [
      "$this->root/target/" . Config::TARGET_CONFIG_FILENAME,
      "$this->root/target/" . Config::TARGET_VM_FILENAME,
      "$this->root/target/" . Config::TARGET_GITIGNORE,
    ];
    $generator = $this->config->getTargetConfigFilepaths();
    self::assertInstanceOf(\Generator::class, $generator);
    self::assertSame($expected, iterator_to_array($generator));

    // Test excluding .gitignore.
    $expected = [
      "$this->root/target/" . Config::TARGET_CONFIG_FILENAME,
      "$this->root/target/" . Config::TARGET_VM_FILENAME,
    ];
    $generator = $this->config->getTargetConfigFilepaths(FALSE);
    self::assertInstanceOf(\Generator::class, $generator);
    self::assertSame($expected, iterator_to_array($generator));
  }

  /**
   * Tests a single target configuration file path getter.
   */
  public function testTargetConfigFilepathGetter(): void {
    $expected = "$this->root/target/" . Config::TARGET_CONFIG_FILENAME;
    $actual = $this->config->getTargetConfigFilepath(Config::TARGET_CONFIG_FILENAME);
    self::assertSame($expected, $actual);

    $this->expectException(\InvalidArgumentException::class);
    $filename = 'this-file-is-not-part-of-the-draft-environment-project.yml';
    $this->expectExceptionMessage(sprintf("Non-existing Draft Environment target configuration filename '%s' has been passed.", $filename));
    $this->config->getTargetConfigFilepath($filename);
  }

  /**
   * Tests ::readConfigFromTheFile().
   */
  public function testReadConfigFromTheFile(): void {
    $content = 'phpunit: ' . __METHOD__;
    $filename = "$this->root/wd/config.yml";
    $this->fs->dumpFile($filename, $content);

    self::assertSame($content, $this->config->readConfigFromTheFile($filename));
  }

  /**
   * Tests ::readAndParseConfigFromTheFile().
   *
   * @depends testReadConfigFromTheFile
   */
  public function testReadAndParseConfigFromTheFile(): void {
    $content = "phunit:\n  method: " . __METHOD__;
    $filename = "$this->root/wd/config.yml";
    $this->fs->dumpFile($filename, $content);
    self::assertSame(['phunit' => ['method' => __METHOD__]], $this->config->readAndParseConfigFromTheFile($filename));
  }

  /**
   * Tests ::writeConfigToTheFile().
   *
   * @param string $sorceYaml
   * @param string $expectedYaml
   * @param array<int|string,array> $config
   *
   * @depends testReadAndParseConfigFromTheFile
   * @dataProvider writeConfigToTheFileDataProvider
   */
  public function testWriteConfigToTheFile(string $sorceYaml, string $expectedYaml, array $config): void {
    $source = "$this->root/wd/config-source.yml";
    $target = "$this->root/wd/config-target.yml";
    $this->fs->dumpFile($source, $sorceYaml);
    $this->config->writeConfigToTheFile($source, $target, $config);

    self::assertSame($expectedYaml, $this->config->readConfigFromTheFile($target));
  }

  /**
   * Data provider for the ::testWriteConfigToTheFile().
   *
   * @return array<int|string,array>
   */
  public function writeConfigToTheFileDataProvider(): array {
    return [
      [
        "# One\none:\n  # Two\n  two: two",
        "# One\none:\n  # Two\n  two: 2",
        [
          'one' => [
            'two' => 2,
          ],
        ],
      ],
      [
        "# One\none:\n  # Two\n  two: two",
        "# One\none: one",
        [
          'one' => 'one',
        ],
      ],
      [
        "# One\none:\n  # Two\n  two: two",
        "# One\none:\n  # Two\n  two: two\nthree: 3",
        [
          'one' => [
            'two' => 'two',
          ],
          'three' => 3,
        ],
      ],
    ];
  }

}
