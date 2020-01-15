<?php

declare(strict_types=1);

namespace Lemberg\Tests\Draft\Environment\Config;

use Composer\Package\RootPackage;
use Composer\Composer;
use Composer\Config as ComposerConfig;
use Composer\IO\IOInterface;
use Composer\Repository\RepositoryManager;
use Lemberg\Draft\Environment\App;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Manager\UpdateManager;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Tests Draft Environment configuration update manager.
 *
 * @covers \Lemberg\Draft\Environment\Config\Manager\UpdateManager
 */
final class UpdateManagerTest extends TestCase {

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
   * @var \Lemberg\Draft\Environment\Config\Manager\UpdateManager
   */
  private $configUpdateManager;

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

    $configObject = new Config("$this->root/source", "$this->root/target");

    $this->configUpdateManager = new UpdateManager($this->composer, $this->io, $configObject);
  }

  /**
   * Tests configuration object getter.
   */
  public function testConfigGetter(): void {
    $expected = new Config("$this->root/source", "$this->root/target");
    $actual = $this->configUpdateManager->getConfig();
    self::assertEquals($expected, $actual);
  }

  /**
   * Tests configuration object setter.
   */
  public function testConfigSetter(): void {
    $expected = new Config("$this->root/a", "$this->root/b");
    $this->configUpdateManager->setConfig($expected);
    $actual = $this->configUpdateManager->getConfig();
    self::assertSame($expected, $actual);
  }

  /**
   * Tests Draft Environment configuration installation.
   */
  public function testUpdate(): void {
    $configObject = $this->configUpdateManager->getConfig();

    // Configuration files must exists before the test execution.
    $fs = new Filesystem();
    foreach ($configObject->getTargetConfigFilepaths() as $filepath) {
      $fs->dumpFile($filepath, 'phpunit: ' . __METHOD__);
    }
    $fs->dumpFile($configObject->getSourceConfigFilepath(Config::SOURCE_CONFIG_FILENAME), 'phpunit: ' . __CLASS__);

    $this->configUpdateManager->update();
    foreach ($configObject->getTargetConfigFilepaths() as $filepath) {
      self::assertFileExists($filepath);
    }

    $configObject->readAndParseConfigFromTheFile($configObject->getTargetConfigFilepath(Config::TARGET_CONFIG_FILENAME));
  }

}