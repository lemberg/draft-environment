<?php

declare(strict_types=1);

namespace Lemberg\Tests\Draft\Environment\Config;

use Composer\Composer;
use Composer\IO\IOInterface;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\InstallManager;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Tests Draft Environment configuration install manager.
 *
 * @covers \Lemberg\Draft\Environment\Config\InstallManager
 * @uses \Lemberg\Draft\Environment\Config\Config
 */
final class InstallManagerTest extends TestCase {

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
   * @var \Lemberg\Draft\Environment\Config\InstallManager
   */
  private $configInstallManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->io = $this->createMock(IOInterface::class);
    $this->composer = $this->createMock(Composer::class);

    // Mock source and target configuration directories.
    $this->root = vfsStream::setup()->url();
    $fs = new Filesystem();
    $fs->mkdir(["$this->root/source", "$this->root/target"]);

    $this->configInstallManager = new InstallManager($this->composer, $this->io, "$this->root/source", "$this->root/target");

    // Configuration files must exists before the test execution.
    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths() as $filepath) {
      $fs->dumpFile($filepath, '');
    }
  }

  /**
   * Tests configuration object getter.
   */
  public function testConfigGetter(): void {
    $expected = new Config("$this->root/source", "$this->root/target");
    $actual = $this->configInstallManager->getConfig();
    self::assertEquals($expected, $actual);
  }

  /**
   * Tests configuration object setter.
   */
  public function testConfigSetter(): void {
    $expected = new Config("$this->root/a", "$this->root/b");
    $this->configInstallManager->setConfig($expected);
    $actual = $this->configInstallManager->getConfig();
    self::assertSame($expected, $actual);
  }

  /**
   * Tests Draft Environment configuration uninstall.
   */
  public function testUninstall(): void {
    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths() as $filepath) {
      self::assertFileExists($filepath);
    }
    $this->configInstallManager->uninstall();
    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths() as $filepath) {
      self::assertFileNotExists($filepath);
    }
  }

}
