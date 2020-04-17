<?php

declare(strict_types=1);

namespace Lemberg\Tests\Unit\Draft\Environment\Config\Install\Step;

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
 * Tests init configuration install step.
 *
 * @covers \Lemberg\Draft\Environment\Config\Install\Step\AbstractInstallStep
 * @covers \Lemberg\Draft\Environment\Config\Install\Step\InitConfig
 */
final class InitConfigTest extends TestCase {

  // Those are copied from the InitConfig class.
  private const GITIGNORE_VAGRANT_LINE = "\n# Ignore Vagrant virtual machine data.\n/.vagrant\n";
  private const GITIGNORE_TARGET_LOCAL_CONFIG_FILENAME_LINE = "\n# Ignore Draft Environment local configuration overrides.\n/" . Config::TARGET_LOCAL_CONFIG_FILENAME . "\n";

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
    $contents = file_get_contents($configObject->getTargetConfigFilepath(Config::TARGET_VM_FILENAME));
    if ($contents === FALSE) {
      throw new \RuntimeException(sprintf('File %s could not be read', $configObject->getTargetConfigFilepath(Config::TARGET_VM_FILENAME)));
    }
    self::assertStringContainsString('load File.dirname(__FILE__) + "/' . $vendorDir . '/lemberg/draft-environment/Vagrantfile"', $contents);
  }

  /**
   * Tests that install can process .gitignore correctly.
   *
   * @param string $gitIgnoreContent
   * @param string $expected
   *
   * @dataProvider installProcessGitIgnoreDataProvider
   */
  final public function testInstallProcessGitIgnore(string $gitIgnoreContent, string $expected): void {
    $configObject = $this->configInstallManager->getConfig();
    $this->fs->dumpFile($configObject->getTargetConfigFilepath(Config::TARGET_GITIGNORE), $gitIgnoreContent);

    $step = new InitConfig($this->composer, $this->io, $this->configInstallManager);
    $step->install();

    self::assertSame($expected, file_get_contents($configObject->getTargetConfigFilepath(Config::TARGET_GITIGNORE)));
  }

  /**
   * Data provider for ::testInstallProcessGitIgnore().
   *
   * @return array<int,array<int,bool|string>>
   */
  final public function installProcessGitIgnoreDataProvider(): array {
    return [
      [
        '',
        self::GITIGNORE_VAGRANT_LINE . self::GITIGNORE_TARGET_LOCAL_CONFIG_FILENAME_LINE,
      ],
      [
        "# Ignore Composer-managed dependencies.\n/vendor",
        "# Ignore Composer-managed dependencies.\n/vendor" . self::GITIGNORE_VAGRANT_LINE . self::GITIGNORE_TARGET_LOCAL_CONFIG_FILENAME_LINE,
      ],
      [
        "# Ignore VM data.\n/.vagrant",
        "# Ignore VM data.\n/.vagrant" . self::GITIGNORE_TARGET_LOCAL_CONFIG_FILENAME_LINE,
      ],
      [
        "# Ignore local VM settings.\n/" . Config::TARGET_LOCAL_CONFIG_FILENAME . "\n",
        "# Ignore local VM settings.\n/" . Config::TARGET_LOCAL_CONFIG_FILENAME . "\n" . self::GITIGNORE_VAGRANT_LINE,
      ],
      [
        "# Ignore VM data.\n/.vagrant\n# Ignore local VM settings.\n/" . Config::TARGET_LOCAL_CONFIG_FILENAME . "\n",
        "# Ignore VM data.\n/.vagrant\n# Ignore local VM settings.\n/" . Config::TARGET_LOCAL_CONFIG_FILENAME . "\n",
      ],
    ];
  }

  /**
   * Tests ::uninstall().
   */
  final public function testUninstall(): void {
    $step = new InitConfig($this->composer, $this->io, $this->configInstallManager);
    $configObject = $this->configInstallManager->getConfig();

    foreach ($configObject->getTargetConfigFilepaths() as $filepath) {
      $this->fs->dumpFile($filepath, 'phpunit: ' . __METHOD__);
    }

    $step->uninstall();

    foreach ($configObject->getTargetConfigFilepaths(FALSE) as $filepath) {
      self::assertFileNotExists($filepath);
    }
  }

  /**
   * Tests that uninstall can process .gitignore correctly.
   *
   * @param string $gitIgnoreContent
   * @param string $expected
   *
   * @dataProvider uninstallProcessGitIgnoreDataProvider
   */
  final public function testUninstallProcessGitIgnore(string $gitIgnoreContent, string $expected): void {
    $configObject = $this->configInstallManager->getConfig();
    $this->fs->dumpFile($configObject->getTargetConfigFilepath(Config::TARGET_GITIGNORE), $gitIgnoreContent);

    $step = new InitConfig($this->composer, $this->io, $this->configInstallManager);
    $step->uninstall();

    if (preg_replace('/\R+/m', '', $expected) === '') {
      self::assertFileNotExists($configObject->getTargetConfigFilepath(Config::TARGET_GITIGNORE));
    }
    else {
      self::assertSame($expected, file_get_contents($configObject->getTargetConfigFilepath(Config::TARGET_GITIGNORE)));
    }
  }

  /**
   * Data provider for ::testUnistallProcessGitIgnore().
   *
   * @return array<int,array<int,bool|string>>
   */
  final public function uninstallProcessGitIgnoreDataProvider(): array {

    $reverseInstall = [];
    foreach ($this->installProcessGitIgnoreDataProvider() as [$a, $b]) {
      $reverseInstall[] = [$b, $a];
    }

    $extraCases = [
      [
        "# Ignore Composer-managed dependencies.\n/vendor\n" . self::GITIGNORE_VAGRANT_LINE . self::GITIGNORE_TARGET_LOCAL_CONFIG_FILENAME_LINE . "# Ignore binaries.\n/bin\n",
        "# Ignore Composer-managed dependencies.\n/vendor\n# Ignore binaries.\n/bin\n",
      ],
      [
        ltrim(self::GITIGNORE_VAGRANT_LINE) . self::GITIGNORE_TARGET_LOCAL_CONFIG_FILENAME_LINE,
        '',
      ],
      [
        self::GITIGNORE_VAGRANT_LINE . ltrim(self::GITIGNORE_TARGET_LOCAL_CONFIG_FILENAME_LINE),
        '',
      ],
      [
        ltrim(self::GITIGNORE_VAGRANT_LINE) . ltrim(self::GITIGNORE_TARGET_LOCAL_CONFIG_FILENAME_LINE),
        '',
      ],
      [
        "\n\n" . self::GITIGNORE_VAGRANT_LINE . self::GITIGNORE_TARGET_LOCAL_CONFIG_FILENAME_LINE,
        '',
      ],
      [
        self::GITIGNORE_VAGRANT_LINE . self::GITIGNORE_TARGET_LOCAL_CONFIG_FILENAME_LINE . "\n\n\n",
        '',
      ],
      [
        "\n\n" . self::GITIGNORE_VAGRANT_LINE . self::GITIGNORE_TARGET_LOCAL_CONFIG_FILENAME_LINE . "\n",
        '',
      ],
    ];

    return array_merge($reverseInstall, $extraCases);
  }

}
