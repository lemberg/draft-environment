<?php

declare(strict_types=1);

namespace Lemberg\Tests\Draft\Environment\Config;

use Lemberg\Draft\Environment\Config\Config;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    // Mock source and target configuration directories.
    $this->root = vfsStream::setup()->url();
    $fs = new Filesystem();
    $fs->mkdir(["$this->root/source", "$this->root/target"]);

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
    $this->expectExceptionMessage(sprintf('Non-existing Draft Environment source configuration filename %s has been passed.', $filename));
    $this->config->getSourceConfigFilepath($filename);
  }

  /**
   * Tests the target configuration file path getter.
   */
  public function testTargetConfigFilepathsGetter(): void {
    $expected = [
      "$this->root/target/" . Config::TARGET_CONFIG_FILENAME,
      "$this->root/target/" . Config::TARGET_VM_FILENAME,
    ];
    $generator = $this->config->getTargetConfigFilepaths();
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
    $this->expectExceptionMessage(sprintf('Non-existing Draft Environment target configuration filename %s has been passed.', $filename));
    $this->config->getTargetConfigFilepath($filename);
  }

}
