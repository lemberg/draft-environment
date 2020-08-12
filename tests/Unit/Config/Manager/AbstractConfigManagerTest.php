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
use Lemberg\Draft\Environment\Config\Manager\UpdateManager;
use Lemberg\Draft\Environment\Utility\Filesystem;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * Tests Draft Environment abstract configuration manager.
 *
 * @link https://github.com/lemberg/draft-environment/issues/205
 *
 * @covers \Lemberg\Draft\Environment\Config\Manager\AbstractConfigManager
 */
final class AbstractConfigManagerTest extends TestCase {

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
   * @var \Lemberg\Draft\Environment\Config\Manager\UpdateManagerInterface
   */
  private $configUpdateManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->composer = new Composer();
    $this->composer->setConfig(new ComposerConfig());
    $package = new RootPackage('dummy/root-package', '^3.0', '3.0.0.0');
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
      ->willReturn(NULL);
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
          'name' => 'dummy/package',
          'extra' => [],
        ],
        [
          'name' => 'dummy/package-two',
          'extra' => [],
        ],
      ],
    ];
    $json->write($lockData);

    $config = new Config("$this->root/source", "$this->root/target");
    $this->configInstallManager = new InstallManager($this->composer, $this->io, $config);
    $this->configUpdateManager = new UpdateManager($this->composer, $this->io, $config);
  }

  /**
   * Tests ::hasBeenAlreadyInstalled() and ::setAsAlreadyInstalled().
   */
  public function testHasBeenAlreadyInstalledFlagWithoutPackage(): void {
    self::assertFalse($this->configInstallManager->hasBeenAlreadyInstalled());
    $this->configInstallManager->setAsAlreadyInstalled();
    self::assertFalse($this->configInstallManager->hasBeenAlreadyInstalled());
  }

  /**
   * Tests ::getLastAppliedUpdateWeight() and ::setLastAppliedUpdateWeight().
   */
  public function testGetAndSetLastAppliedUpdateWeightWithoutPackage(): void {
    self::assertSame(0, $this->configUpdateManager->getLastAppliedUpdateWeight());
    $this->configUpdateManager->setLastAppliedUpdateWeight(4);
    self::assertSame(0, $this->configUpdateManager->getLastAppliedUpdateWeight());
  }

}
