<?php

declare(strict_types=1);

namespace Lemberg\Tests\Unit\Draft\Environment\Config\Update\Step;

use Composer\Composer;
use Composer\Config as ComposerConfig;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Package\RootPackage;
use Lemberg\Draft\Environment\App;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Manager\UpdateManager;
use Lemberg\Draft\Environment\Config\Update\Step\RemoveConfigurerComposerScript;
use Lemberg\Draft\Environment\Utility\Filesystem;
use org\bovigo\vfs\vfsStream;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

/**
 * Tests remove composer scripts update step.
 *
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\AbstractUpdateStep
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\RemoveConfigurerComposerScript
 */
final class RemoveConfigurerComposerScriptTest extends TestCase {

  use PHPMock;

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
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  private $fs;

  /**
   * Path to a directory with test composer.json files.
   *
   * @var string
   */
  private $basePath;

  /**
   * @var \Lemberg\Draft\Environment\Config\Manager\UpdateManagerInterface
   */
  private $configUpdateManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->composer = new Composer();
    $package = new RootPackage(App::PACKAGE_NAME, '^3.0', '3.0.0.0');
    $this->composer->setPackage($package);
    $this->composer->setConfig(new ComposerConfig());
    $this->io = $this->createMock(IOInterface::class);

    // Mock source and target configuration directories.
    $this->root = vfsStream::setup()->url();
    $this->fs = new Filesystem();
    $this->fs->mkdir([
      "$this->root/source",
      "$this->root/target",
      "$this->root/wd",
    ]);

    // Point composer to a test composer.json.
    putenv("COMPOSER=$this->root/wd/composer.json");

    // Build path to the test composer.json files based on this class name.
    $this->basePath = './tests/fixtures/Unit' . str_replace('\\', DIRECTORY_SEPARATOR, substr(__CLASS__, strlen('Lemberg\Tests\Unit\Draft\Environment')));

    $configObject = new Config("$this->root/source", "$this->root/target");
    $this->configUpdateManager = new UpdateManager($this->composer, $this->io, $configObject);
  }

  /**
   * Tests step weight getter.
   */
  final public function testGetWeight(): void {
    $step = new RemoveConfigurerComposerScript($this->composer, $this->io, $this->configUpdateManager);
    self::assertSame(1, $step->getWeight());
  }

  /**
   * Tests update step execution.
   *
   * @param string $composer_before
   * @param string $composer_after
   *
   * @dataProvider updateDataProvider
   */
  final public function testUpdate(string $composer_before, string $composer_after): void {

    $composer_wd = Factory::getComposerFile();
    // Copy test composer,json to the virtual working directory.
    $this->fs->copy("$this->basePath/$composer_before", $composer_wd, TRUE);

    $before_content = file_get_contents($composer_wd);
    if ($before_content === FALSE) {
      throw new \RuntimeException(sprintf('File %s could not be read', $composer_wd));
    }
    $decoded_before_content = json_decode($before_content, TRUE);

    /** @var \Composer\Package\RootPackage $rootPackage */
    $rootPackage = $this->composer->getPackage();
    $rootPackage->setScripts($decoded_before_content['scripts'] ?? []);
    $this->composer->setPackage($rootPackage);

    // Run update.
    $step = new RemoveConfigurerComposerScript($this->composer, $this->io, $this->configUpdateManager);
    $config = [];
    $step->update($config);

    // Verify that Composer root package has the script removed.
    $after_content = file_get_contents("$this->basePath/$composer_after");
    if ($after_content === FALSE) {
      throw new \RuntimeException(sprintf('File %s could not be read', "$this->basePath/$composer_after"));
    }
    $decoded_after_content = json_decode($after_content, TRUE);
    self::assertSame($decoded_after_content['scripts'] ?? [], $this->composer->getPackage()->getScripts());

    // Verify that composer.json has the script removed.
    self::assertFileEquals("$this->basePath/$composer_after", $composer_wd);
  }

  /**
   * Tests that exception is thrown when composer.json file could not be read.
   *
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  final public function testUpdateFileRead(): void {
    $getcwd = $this->getFunctionMock('Lemberg\Draft\Environment\Config\Update\Step', 'file_get_contents');
    $getcwd->expects(self::once())->willReturn(FALSE);

    $composer_wd = Factory::getComposerFile();
    $this->fs->touch($composer_wd);
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(sprintf('File %s could not be read', $composer_wd));

    $step = new RemoveConfigurerComposerScript($this->composer, $this->io, $this->configUpdateManager);
    $config = [];
    $step->update($config);
  }

  /**
   * Data provider for the ::testUpdate().
   *
   * @return array<int,array<int,string>>
   */
  final public function updateDataProvider(): array {
    return [
      [
        'composer-no-scripts.json',
        'composer-no-scripts.json',
      ],
      [
        'composer-empty-scripts.json',
        'composer-no-scripts.json',
      ],
      [
        'composer-with-only-scripts-in-the-middle.json',
        'composer-no-scripts.json',
      ],
      [
        'composer-with-only-scripts-in-the-end.json',
        'composer-no-scripts.json',
      ],
      [
        'composer-with-mixed-scripts-before.json',
        'composer-with-mixed-scripts-after.json',
      ],
    ];
  }

}
