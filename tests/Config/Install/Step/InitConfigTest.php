<?php

declare(strict_types=1);

namespace Lemberg\Tests\Draft\Environment\Config\Install\Step;

use Composer\Composer;
use Composer\Config as ComposerConfig;
use Composer\IO\IOInterface;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Manager\InstallManager;
use Lemberg\Draft\Environment\Config\Install\Step\InitConfig;
use Lemberg\Draft\Environment\Utility\Filesystem;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * Tests Draft Environment configuration install manager.
 *
 * @covers \Lemberg\Draft\Environment\Config\Install\Step\AbstractInstallStep
 * @covers \Lemberg\Draft\Environment\Config\Install\Step\InitConfig
 */
final class InitConfigTest extends TestCase {

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
   * @var \Lemberg\Draft\Environment\Config\Manager\InstallManagerInterface
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
    $this->fs = new Filesystem();
    $this->fs->mkdir(["$this->root/target"]);

    $configObject = new Config('.', "$this->root/target");
    $this->configInstallManager = new InstallManager($this->composer, $this->io, $configObject);
  }

  /**
   * Tests step weight getter.
   */
  final public function testGetWeight(): void {
    $step = new InitConfig($this->composer, $this->io, $this->configInstallManager);
    self::assertSame(-100, $step->getWeight());
  }

  /**
   * Tests ::install().
   */
  final public function testInstall(): void {
    $step = new InitConfig($this->composer, $this->io, $this->configInstallManager);

    $step->install();

    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths() as $filepath) {
      self::assertFileExists($filepath);
    }
  }

  /**
   * Tests that install can detect custom vendor directory.
   *
   * @param string $vendorDir
   *
   * @testWith ["vendor"]
   *           ["vendor-custom"]
   */
  final public function testInstallDetectsCustomVendorDir(string $vendorDir): void {
    $composerConfig = $this->composer->getConfig();
    $composerConfig->merge(['config' => ['vendor-dir' => $vendorDir]]);
    $this->composer->setConfig($composerConfig);

    $step = new InitConfig($this->composer, $this->io, $this->configInstallManager);
    $step->install();

    $configObject = $this->configInstallManager->getConfig();
    self::assertContains('load File.dirname(__FILE__) + "/' . $vendorDir . '/lemberg/draft-environment/Vagrantfile"', file_get_contents($configObject->getTargetConfigFilepath(Config::TARGET_VM_FILENAME)));
  }

  /**
   * Tests ::uninstall().
   */
  final public function testUninstall(): void {
    $step = new InitConfig($this->composer, $this->io, $this->configInstallManager);

    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths() as $filepath) {
      $this->fs->dumpFile($filepath, 'phpunit: ' . __METHOD__);
    }

    $step->uninstall();

    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths(FALSE) as $filepath) {
      self::assertFileNotExists($filepath);
    }
  }

}
