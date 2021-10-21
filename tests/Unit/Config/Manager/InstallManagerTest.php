<?php

declare(strict_types=1);

namespace Lemberg\Tests\Unit\Draft\Environment\Config\Manager;

use Composer\Autoload\ClassLoader;
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

    $configObject = new Config("$this->root/source", "$this->root/target");
    $classLoader = new ClassLoader();
    $this->configInstallManager = new InstallManager($this->composer, $this->io, $configObject, $classLoader);
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

    $targetConfigFilepath = $configObject->getTargetConfigFilepath(Config::TARGET_CONFIG_FILENAME);
    $config = $configObject->readAndParseConfigFromTheFile($targetConfigFilepath);
    self::assertSame(App::LAST_AVAILABLE_UPDATE_WEIGHT, $config['draft']['last_applied_update']);

    // Remove target configuration and run installation for the 2nd time.
    $this->fs->remove($configObject->getTargetConfigFilepaths());
    $this->configInstallManager->install();
    foreach ($configObject->getTargetConfigFilepaths() as $filepath) {
      self::assertFileExists($filepath);
    }

    // Amend target configuration and run installation for the 2nd time. It
    // should not run.
    $this->fs->dumpFile($targetConfigFilepath, 'TEST: TEST');
    $this->configInstallManager->install();
    self::assertEquals('TEST: TEST', file_get_contents($targetConfigFilepath));
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
      self::assertFileDoesNotExist($filepath);
    }
  }

}
