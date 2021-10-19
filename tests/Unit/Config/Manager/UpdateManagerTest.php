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
use Lemberg\Draft\Environment\Config\Manager\UpdateManager;
use Lemberg\Draft\Environment\Utility\Filesystem;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * Tests Draft Environment configuration update manager.
 *
 * @covers \Lemberg\Draft\Environment\Config\Manager\AbstractConfigManager
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
   * @var \Lemberg\Draft\Environment\Utility\Filesystem
   */
  private $fs;

  /**
   * @var string
   */
  private $lockFile;

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

    // Dump empty composer.json.
    $composerFile = Factory::getComposerFile();
    $json = new JsonFile($composerFile);
    $json->write(['name' => App::PACKAGE_NAME]);

    // Dump composer.lock.
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

    // Configuration files must exists before the test execution.
    foreach ($configObject->getTargetConfigFilepaths() as $filepath) {
      $this->fs->dumpFile($filepath, 'phpunit: ' . __METHOD__);
    }
    $this->fs->dumpFile($configObject->getSourceConfigFilepath(Config::SOURCE_CONFIG_FILENAME), 'phpunit: ' . __CLASS__);

    $classLoader = new ClassLoader();
    $this->configUpdateManager = new UpdateManager($this->composer, $this->io, $configObject, $classLoader);
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
   * Tests Draft Environment configuration update.
   */
  public function testUpdate(): void {
    $configObject = $this->configUpdateManager->getConfig();

    $this->configUpdateManager->update();
    foreach ($configObject->getTargetConfigFilepaths() as $filepath) {
      self::assertFileExists($filepath);
    }

    $targetConfigFilepath = $configObject->getTargetConfigFilepath(Config::TARGET_CONFIG_FILENAME);
    $config = $configObject->readAndParseConfigFromTheFile($targetConfigFilepath);
    self::assertSame(App::LAST_AVAILABLE_UPDATE, $config['draft']['last_applied_update']);
  }

  /**
   * Tests autoloading missing classes during the update.
   */
  public function testAutoloadNewDependencies(): void {

    // Classes must not be loaded if they exist.
    $classLoader = $this->getMockBuilder(ClassLoader::class)
      ->onlyMethods([
        'addClassMap',
        'addPsr4',
        'register',
      ])
      ->getMock();
    $classLoader->expects(self::never())
      ->method('addClassMap');
    $classLoader->expects(self::never())
      ->method('addPsr4');
    $classLoader->expects(self::never())
      ->method('register');

    new UpdateManager($this->composer, $this->io, $this->configUpdateManager->getConfig(), $classLoader);

    // Classes must be loaded if they do not exist.
    $classLoader = $this->getMockBuilder(ClassLoader::class)
      ->onlyMethods([
        'addClassMap',
        'addPsr4',
        'register',
      ])
      ->getMock();
    $classLoader->expects(self::once())
      ->method('addClassMap');
    $classLoader->expects(self::once())
      ->method('addPsr4');
    $classLoader->expects(self::once())
      ->method('register');

    $this->fs->mkdir("$this->root/wd/vendor/nette/robot-loader");
    $this->composer->getConfig()->merge([
      'config' => ['vendor-dir' => "$this->root/wd/vendor"],
    ]);

    new class($this->composer, $this->io, $this->configUpdateManager->getConfig(), $classLoader) extends UpdateManager {

      protected const NEWLY_INTRODUCED_CLASSES = [
        'classmap' => [
          'FakeRobotLoader' => 'nette/robot-loader',
        ],
        'psr4' => [
          'FakeComments' => [
            'package' => 't2l/comments',
            'prefix' => 'Consolidation\\Comments\\',
            'path' => 'src',
          ],
        ],
      ];

    };

  }

}
