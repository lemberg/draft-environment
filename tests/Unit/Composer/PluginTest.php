<?php

declare(strict_types=1);

namespace Lemberg\Tests\Unit\Draft\Environment\Composer;

use Composer\Composer;
use Composer\Config;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\PolicyInterface;
use Composer\DependencyResolver\Pool;
use Composer\DependencyResolver\Request;
use Composer\Installer\InstallationManager;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Repository\CompositeRepository;
use Composer\Repository\RepositoryManager;
use Composer\Repository\WritableRepositoryInterface;
use Composer\Script\ScriptEvents;
use Lemberg\Draft\Environment\App;
use Lemberg\Draft\Environment\Composer\Plugin;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

/**
 * Tests Draft Environment composer plugin.
 *
 * @covers \Lemberg\Draft\Environment\Composer\Plugin
 */
final class PluginTest extends TestCase {

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
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->io = $this->createMock(IOInterface::class);
  }

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass(): void {
    self::defineFunctionMock('Lemberg\Draft\Environment\Composer', 'getcwd');
  }

  /**
   * Tests composer plugin activation, as well as correct configuration of the
   * event subscribers.
   */
  public function testComposerPlugin(): void {
    $this->setUpComposerMock(TRUE);

    // Ensure that plugin activation does not produce any errors.
    $plugin = new Plugin();
    $plugin->activate($this->composer, $this->io);

    // Ensure that plugin is subscribed to the correct events.
    $expected = [
      PackageEvents::POST_PACKAGE_INSTALL => 'onComposerEvent',
      PackageEvents::POST_PACKAGE_UPDATE => 'onComposerEvent',
      PackageEvents::PRE_PACKAGE_UNINSTALL => 'onComposerEvent',
      ScriptEvents::POST_AUTOLOAD_DUMP => 'onComposerEvent',
    ];
    self::assertSame($expected, Plugin::getSubscribedEvents());

    // Ensure that event handlers do not produce any errors.
    $policy = $this->createMock(PolicyInterface::class);
    $pool = $this->createMock(Pool::class);
    $request = new Request();
    $installedRepo = $this->createMock(CompositeRepository::class);

    $package = new Package('dummy', '1.0.0.0', '^1.0');
    $operation = new UninstallOperation($package);
    $event = new PackageEvent(PackageEvents::PRE_PACKAGE_UNINSTALL, $this->composer, $this->io, FALSE, $policy, $pool, $installedRepo, $request, [$operation], $operation);

    // Ensure that plugin passes events to the app.
    $app = $this->createMock(App::class);
    $app->expects(self::once())->method('handleEvent');
    $plugin->setApp($app);

    $plugin->onComposerEvent($event);
  }

  /**
   * Tests composer plugin throws an exception when getcwd() returns FALSE.
   */
  public function testComposerPluginThrowsExceptionWhenGetcwdReturnsFalse(): void {
    $this->setUpComposerMock(TRUE);

    $getcwd = $this->getFunctionMock('Lemberg\Draft\Environment\Composer', 'getcwd');
    $getcwd->expects(self::once())->willReturn(FALSE);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('Unable to get the current working directory. Please check if any one of the parent directories does not have the readable or search mode set, even if the current directory does. See https://www.php.net/manual/function.getcwd.php');

    $plugin = new Plugin();
    $plugin->activate($this->composer, $this->io);
  }

  /**
   * Tests composer plugin throws an exception when package does not exist.
   */
  public function testComposerPluginThrowsExceptionWhenPackageDoesNotExist(): void {
    $this->setUpComposerMock(FALSE);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(sprintf('Package %s is not found in the local repository.', App::PACKAGE_NAME));

    $plugin = new Plugin();
    $plugin->activate($this->composer, $this->io);
  }

  /**
   * Sets up Composer instance mock.
   *
   * @param bool $returnPackage
   *   Boolean indicating whether the local repository should find the package
   *   or not (causing an exception).
   */
  private function setUpComposerMock(bool $returnPackage): void {
    $findPackageReturnValue = $returnPackage ? new Package(App::PACKAGE_NAME, '1.0.0.0', '^1.0') : NULL;

    $localRepository = $this->createMock(WritableRepositoryInterface::class);
    $localRepository->expects(self::any())
      ->method('findPackage')
      ->with(App::PACKAGE_NAME, '*')
      ->willReturn($findPackageReturnValue);

    $repositoryManager = $this->createMock(RepositoryManager::class);
    $repositoryManager->expects(self::any())
      ->method('getLocalRepository')
      ->willReturn($localRepository);

    $this->composer = $this->createMock(Composer::class);
    $this->composer->expects(self::any())
      ->method('getRepositoryManager')
      ->willReturn($repositoryManager);

    $installationManager = $this->createMock(InstallationManager::class);
    $installationManager->expects(self::any())
      ->method('getInstallPath')
      ->with($findPackageReturnValue)
      ->willReturn(sys_get_temp_dir());

    $this->composer->expects(self::any())
      ->method('getInstallationManager')
      ->willReturn($installationManager);

    $this->composer->expects(self::any())
      ->method('getConfig')
      ->willReturn(new Config());
  }

}
