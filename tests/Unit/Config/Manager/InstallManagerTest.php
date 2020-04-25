<?php

declare(strict_types=1);

namespace Lemberg\Tests\Unit\Draft\Environment\Config\Manager;

use Composer\Composer;
use Composer\Config as ComposerConfig;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\RootPackage;
use Composer\Repository\RepositoryManager;
use Lemberg\Draft\Environment\App;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Manager\InstallManager;
use Lemberg\Draft\Environment\Utility\Filesystem;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

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
   * @var \Lemberg\Draft\Environment\Utility\Filesystem
   */
  private $fs;

  /**
   * @var string
   */
  private $lockFile;

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
      ->onlyMethods([
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
    $this->fs = new Filesystem();
    $this->fs->mkdir(
      ["$this->root/source", "$this->root/target", "$this->root/wd"]
    );

    // Point composer to a test composer.json.
    putenv("COMPOSER=$this->root/wd/composer.json");

    // Dump composer.lock.
    $composerFile = Factory::getComposerFile();
    $this->lockFile = 'json' === pathinfo($composerFile, PATHINFO_EXTENSION) ? substr($composerFile, 0, -4) . 'lock' : $composerFile . '.lock';
    $json = new JsonFile($this->lockFile);
    $lockData = [
      'packages' => [
        [
          'name' => 'dummy',
          'extra' => [],
        ],
        [
          'name' => App::PACKAGE_NAME,
          'extra' => [
            'class' => 'Lemberg\Draft\Environment\Dummy',
          ],
        ],
      ],
    ];
    $json->write($lockData);

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
    $configObject = $this->configInstallManager->getConfig();
    // Configuration files must exists before the test execution.
    foreach ($configObject->getSourceConfigFilepaths() as $filepath) {
      $this->fs->dumpFile($filepath, 'phpunit: ' . __METHOD__);
    }

    // Run the installation and check that configuration files exist after that.
    $this->configInstallManager->install();
    foreach ($configObject->getTargetConfigFilepaths() as $filepath) {
      self::assertFileExists($filepath);
    }

    $json = new JsonFile($this->lockFile);
    $lockData = $json->read();
    self::assertTrue($lockData['packages'][1]['extra']['draft-environment']['already-installed']);

    // Remove target configuration and run installation for the 2nd time. It
    // should not run (i.e. files should not be created).
    $this->fs->remove($configObject->getTargetConfigFilepaths());
    $this->configInstallManager->install();
    foreach ($configObject->getTargetConfigFilepaths() as $filepath) {
      self::assertFileNotExists($filepath);
    }
  }

  /**
   * Tests ::hasBeenAlreadyInstalled() and ::setAsAlreadyInstalled().
   */
  public function testHasBeenAlreadyInstalledFlagGetterAndSetter(): void {
    self::assertFalse($this->configInstallManager->hasBeenAlreadyInstalled());
    $this->configInstallManager->setAsAlreadyInstalled();
    self::assertTrue($this->configInstallManager->hasBeenAlreadyInstalled());

    $json = new JsonFile($this->lockFile);
    $lockData = $json->read();
    self::assertTrue($lockData['packages'][1]['extra']['draft-environment']['already-installed']);
  }

  /**
   * Tests Draft Environment configuration uninstall.
   */
  public function testUninstall(): void {
    $configObject = $this->configInstallManager->getConfig();
    // Configuration files must exists before the test execution.
    foreach ($configObject->getTargetConfigFilepaths() as $filepath) {
      $this->fs->dumpFile($filepath, '');
    }
    $this->configInstallManager->uninstall();
    foreach ($configObject->getTargetConfigFilepaths(FALSE) as $filepath) {
      self::assertFileNotExists($filepath);
    }
  }

}
