<?php

declare(strict_types=1);

namespace Lemberg\Tests\Draft\Environment\Config\Manager;

use Composer\Composer;
use Composer\Config as ComposerConfig;
use Composer\IO\IOInterface;
use Composer\Package\RootPackage;
use Composer\Repository\RepositoryManager;
use Lemberg\Draft\Environment\App;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Manager\InstallManager;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Tests Draft Environment configuration install manager.
 *
 * @covers \Lemberg\Draft\Environment\Config\Manager\AbstractConfigManager
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
   * @var \Lemberg\Draft\Environment\Config\Manager\InstallManagerInterface
   */
  private $configInstallManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->composer = new Composer();
    $this->composer->setConfig(new ComposerConfig());
    $package = new RootPackage(App::PACKAGE_NAME, '^3.0', '3.0.0.0');
    $this->composer->setPackage($package);
    $manager = $this->getMockBuilder(RepositoryManager::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'getLocalRepository',
        'findPackage',
      ])
      ->getMock();
    $manager->expects(self::any())
      ->method('getLocalRepository')
      ->willReturnSelf();
    $manager->expects(self::any())
      ->method('findPackage')
      ->with(App::PACKAGE_NAME, '*')
      ->willReturn($package);
    $this->composer->setRepositoryManager($manager);
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

    // Run the installation and check that configuration files exist after that.
    $this->configInstallManager->install();
    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths() as $filepath) {
      self::assertFileExists($filepath);
    }

    // Remove target configuration and run installation for the 2nd time. It
    // should not run (i.e. files should not be created).
    $fs->remove($this->configInstallManager->getConfig()->getTargetConfigFilepaths());
    $this->configInstallManager->install();
    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths() as $filepath) {
      self::assertFileNotExists($filepath);
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
