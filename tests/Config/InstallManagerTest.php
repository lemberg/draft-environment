<?php

declare(strict_types=1);

namespace Lemberg\Tests\Draft\Environment\Config;

use Composer\Composer;
use Composer\Config as ComposerConfig;
use Composer\IO\IOInterface;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Manager\InstallManager;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Tests Draft Environment configuration install manager.
 *
 * @covers \Lemberg\Draft\Environment\Config\Manager\InstallManager
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
   * @var \Lemberg\Draft\Environment\Config\Manager\InstallManager
   */
  private $configInstallManager;

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

    $config = new Config("$this->root/source", "$this->root/target");
    $this->configInstallManager = new InstallManager($this->composer, $this->io, $config);
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
   * Tests Draft Environment configuration installation.
   */
  public function testInstall(): void {
    // Configuration files must exists before the test execution.
    $fs = new Filesystem();
    foreach ($this->configInstallManager->getConfig()->getSourceConfigFilepaths() as $filepath) {
      $fs->dumpFile($filepath, 'phpunit: ' . __METHOD__);
    }

    $this->configInstallManager->install();
    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths() as $filepath) {
      self::assertFileExists($filepath);
    }
  }

  /**
   * Tests Draft Environment configuration uninstall.
   */
  public function testUninstall(): void {
    // Configuration files must exists before the test execution.
    $fs = new Filesystem();
    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths() as $filepath) {
      $fs->dumpFile($filepath, '');
    }
    $this->configInstallManager->uninstall();
    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths(FALSE) as $filepath) {
      self::assertFileNotExists($filepath);
    }
  }

}
