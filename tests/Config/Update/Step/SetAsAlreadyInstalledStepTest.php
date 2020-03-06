<?php

declare(strict_types=1);

namespace Lemberg\Tests\Draft\Environment\Config\Update\Step;

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
use Lemberg\Draft\Environment\Config\Update\Step\SetAsAlreadyInstalledStep;
use Lemberg\Draft\Environment\Utility\Filesystem;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * Tests remove composer scripts update step.
 *
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\AbstractUpdateStep
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\SetAsAlreadyInstalledStep
 */
final class SetAsAlreadyInstalledStepTest extends TestCase {

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
    $this->composer->setConfig(new ComposerConfig());

    $this->io = $this->createMock(IOInterface::class);

    // Mock source and target configuration directories.
    $this->root = vfsStream::setup()->url();
    $fs = new Filesystem();
    $fs->mkdir(["$this->root/source", "$this->root/target", "$this->root/wd"]);

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
    $this->configUpdateManager = new UpdateManager($this->composer, $this->io, $configObject);
  }

  /**
   * Tests step weight getter.
   */
  final public function testGetWeight(): void {
    $step = new SetAsAlreadyInstalledStep($this->composer, $this->io, $this->configUpdateManager);
    self::assertSame(3, $step->getWeight());
  }

  /**
   * Tests update step execution.
   */
  final public function testUpdate(): void {
    $step = new SetAsAlreadyInstalledStep($this->composer, $this->io, $this->configUpdateManager);
    $config = [];
    $step->update($config);
    $json = new JsonFile($this->lockFile);
    $lockData = $json->read();
    self::assertTrue($lockData['packages'][1]['extra']['draft-environment']['already-installed']);
  }

}
