<?php

declare(strict_types=1);

namespace Lemberg\Tests\Unit\Draft\Environment;

use Composer\Composer;
use Composer\Config as ComposerConfig;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\DependencyResolver\PolicyInterface;
use Composer\DependencyResolver\Pool;
use Composer\DependencyResolver\Request;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use Composer\Repository\CompositeRepository;
use Composer\Repository\RepositoryManager;
use Lemberg\Draft\Environment\App;
use Lemberg\Draft\Environment\Config\Manager\InstallManagerInterface;
use Lemberg\Draft\Environment\Config\Manager\UpdateManagerInterface;
use Lemberg\Tests\Traits\Draft\Environment\ComposerPackageEventFactoryTrait;
use PHPUnit\Framework\TestCase;

/**
 * Tests Draft Environment app.
 *
 * @covers \Lemberg\Draft\Environment\App
 */
final class AppTest extends TestCase {

  use ComposerPackageEventFactoryTrait;

  /**
   * @var \Composer\Composer
   */
  private $composer;

  /**
   * @var \Composer\IO\IOInterface
   */
  private $io;

  /**
   * @var \Lemberg\Draft\Environment\App
   */
  private $app;

  /**
   * @var \Lemberg\Draft\Environment\Config\Manager\InstallManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private $configInstallManager;

  /**
   * @var \Lemberg\Draft\Environment\Config\Manager\UpdateManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private $configUpdateManager;

  /**
   *
   * @var \Composer\DependencyResolver\PolicyInterface
   */
  private $policy;

  /**
   *
   * @var \Composer\DependencyResolver\Pool
   */
  private $pool;

  /**
   *
   * @var \Composer\DependencyResolver\Request
   */
  private $request;

  /**
   *
   * @var \Composer\Repository\CompositeRepository
   */
  private $installedRepo;

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

    // Mock required PackageEvent constructor arguments.
    $this->policy = $this->createMock(PolicyInterface::class);
    $this->pool = $this->createMock(Pool::class);
    $this->request = new Request();
    $this->installedRepo = $this->createMock(CompositeRepository::class);

    $this->configInstallManager = $this->createMock(InstallManagerInterface::class);
    $this->configUpdateManager = $this->createMock(UpdateManagerInterface::class);

    $this->app = new App($this->composer, $this->io, $this->configInstallManager, $this->configUpdateManager);
  }

  /**
   * Tests Composer PackageEvents::PRE_PACKAGE_UNINSTAL event handler.
   */
  public function testComposerPrePackageUninstallEventHandlerDoesNotRunWithOtherPackages(): void {
    // Clean up must not run when any package other than
    // "lemberg/draft-environment" is being uninstalled.
    $package = new Package('dummy', '1.0.0.0', '^1.0');
    $operation = new UninstallOperation($package);
    $event = $this->createPackageEvent(PackageEvents::PRE_PACKAGE_UNINSTALL, $this->composer, $this->io, FALSE, $this->policy, $this->pool, $this->installedRepo, $this->request, [$operation], $operation);
    $this->configInstallManager
      ->expects(self::never())
      ->method('uninstall');
    $this->app->handleEvent($event);
  }

  /**
   * Tests Composer PackageEvents::PRE_PACKAGE_UNINSTAL event handler.
   */
  public function testComposerPrePackageUninstallEventHandlerDoesNotRunWithOtherEvents(): void {
    // Clean up must not run when other than
    // PackageEvents::PRE_PACKAGE_UNINSTALL event is dispatched.
    $package = new Package('dummy', '1.0.0.0', '^1.0');
    $operation = new InstallOperation($package);
    $event = $this->createPackageEvent(PackageEvents::PRE_PACKAGE_INSTALL, $this->composer, $this->io, FALSE, $this->policy, $this->pool, $this->installedRepo, $this->request, [$operation], $operation);
    $this->configInstallManager
      ->expects(self::never())
      ->method('uninstall');
    $this->app->handleEvent($event);
  }

  /**
   * Tests Composer PackageEvents::PRE_PACKAGE_UNINSTAL event handler.
   */
  public function testComposerPrePackageUninstallEventHandlerDoesRun(): void {
    // Clean up must run when "lemberg/draft-environment" is being uninstalled.
    $package = new Package(App::PACKAGE_NAME, '1.0.0.0', '^1.0');
    $operation = new UninstallOperation($package);
    $event = $this->createPackageEvent(PackageEvents::PRE_PACKAGE_UNINSTALL, $this->composer, $this->io, FALSE, $this->policy, $this->pool, $this->installedRepo, $this->request, [$operation], $operation);
    $this->configInstallManager
      ->expects(self::once())
      ->method('uninstall');
    $this->app->handleEvent($event);
  }

  /**
   * Tests Composer PackageEvents::POST_PACKAGE_INSTALL event handler.
   */
  public function testComposerPostPackageInstallEventHandlerDoesNotRunWithOtherPackages(): void {
    // Clean up must not run when any package other than
    // "lemberg/draft-environment" is being uninstalled.
    $package = new Package('dummy', '1.0.0.0', '^1.0');
    $operation = new InstallOperation($package);
    $event = $this->createPackageEvent(PackageEvents::POST_PACKAGE_INSTALL, $this->composer, $this->io, FALSE, $this->policy, $this->pool, $this->installedRepo, $this->request, [$operation], $operation);
    $this->configInstallManager
      ->expects(self::never())
      ->method('install');
    $this->app->handleEvent($event);
  }

  /**
   * Tests Composer PackageEvents::POST_PACKAGE_INSTALL event handler.
   */
  public function testComposerPostPackageInstallEventHandlerDoesNotRunWithOtherEvents(): void {
    // Clean up must not run when other than
    // PackageEvents::PRE_PACKAGE_UNINSTALL event is dispatched.
    $package = new Package(App::PACKAGE_NAME, '1.0.0.0', '^1.0');
    $operation = new InstallOperation($package);
    $event = $this->createPackageEvent(PackageEvents::PRE_PACKAGE_INSTALL, $this->composer, $this->io, FALSE, $this->policy, $this->pool, $this->installedRepo, $this->request, [$operation], $operation);
    $this->configInstallManager
      ->expects(self::never())
      ->method('install');
    $this->app->handleEvent($event);
  }

  /**
   * Tests Composer PackageEvents::POST_PACKAGE_INSTALL event handler.
   */
  public function testComposerPostPackageInstallEventHandlerDoesRun(): void {
    // Clean up must run when "lemberg/draft-environment" is being uninstalled.
    $package = new Package(App::PACKAGE_NAME, '1.0.0.0', '^1.0');
    $operation = new InstallOperation($package);
    $event = $this->createPackageEvent(PackageEvents::POST_PACKAGE_INSTALL, $this->composer, $this->io, FALSE, $this->policy, $this->pool, $this->installedRepo, $this->request, [$operation], $operation);
    $this->configInstallManager
      ->expects(self::once())
      ->method('install');
    $this->app->handleEvent($event);
  }

  /**
   * Tests Composer PackageEvents::POST_PACKAGE_UPDATE event handler.
   */
  public function testComposerPostPackageUpdateEventHandlerDoesNotRunWithOtherPackages(): void {
    // Update must not run when any package other than
    // "lemberg/draft-environment" is being updated.
    $initial = new Package('dummy', '1.0.0.0', '^1.0');
    $target = new Package('dummy', '1.2.0.0', '^1.0');
    $operation = new UpdateOperation($initial, $target);
    $packageEvent = $this->createPackageEvent(PackageEvents::POST_PACKAGE_UPDATE, $this->composer, $this->io, FALSE, $this->policy, $this->pool, $this->installedRepo, $this->request, [$operation], $operation);

    $this->configUpdateManager
      ->expects(self::never())
      ->method('update');

    $this->app->handleEvent($packageEvent);
  }

  /**
   * Tests Composer PackageEvents::POST_PACKAGE_UPDATE event handler.
   */
  public function testComposerPostPackageUpdateEventHandlerDoesNotRunWithOtherEvents(): void {
    // Update must not run when other than
    // PackageEvents::PRE_PACKAGE_UNINSTALL event is dispatched.
    $initial = new Package(App::PACKAGE_NAME, '1.0.0.0', '^1.0');
    $operation = new InstallOperation($initial);
    $packageEvent = $this->createPackageEvent(PackageEvents::PRE_PACKAGE_INSTALL, $this->composer, $this->io, FALSE, $this->policy, $this->pool, $this->installedRepo, $this->request, [$operation], $operation);

    $this->configUpdateManager
      ->expects(self::never())
      ->method('update');

    $this->app->handleEvent($packageEvent);
  }

  /**
   * Tests Composer PackageEvents::POST_PACKAGE_UPDATE event handler.
   */
  public function testComposerPostPackageUpdateEventHandlerDoesNotRunWhenDowngrading(): void {
    // Update must run when "lemberg/draft-environment" is being updated.
    $initial = new Package(App::PACKAGE_NAME, '1.0.0.0', '^1.0');
    $initial->setReleaseDate(new \DateTime());
    $target = new Package(App::PACKAGE_NAME, '1.2.0.0', '^1.0');
    $target->setReleaseDate(new \DateTime('yesterday'));
    $operation = new UpdateOperation($initial, $target);
    $packageEvent = $this->createPackageEvent(PackageEvents::POST_PACKAGE_UPDATE, $this->composer, $this->io, FALSE, $this->policy, $this->pool, $this->installedRepo, $this->request, [$operation], $operation);

    $this->configUpdateManager
      ->expects(self::never())
      ->method('update');

    $this->app->handleEvent($packageEvent);
  }

  /**
   * Tests Composer PackageEvents::POST_PACKAGE_UPDATE event handler.
   */
  public function testComposerPostPackageUpdateEventHandlerDoesRun(): void {
    // Update must run when "lemberg/draft-environment" is being updated.
    $initial = new Package(App::PACKAGE_NAME, '1.0.0.0', '^1.0');
    $target = new Package(App::PACKAGE_NAME, '1.2.0.0', '^1.0');
    $operation = new UpdateOperation($initial, $target);
    $packageEvent = $this->createPackageEvent(PackageEvents::POST_PACKAGE_UPDATE, $this->composer, $this->io, FALSE, $this->policy, $this->pool, $this->installedRepo, $this->request, [$operation], $operation);

    $this->configUpdateManager
      ->expects(self::once())
      ->method('update');

    $this->app->handleEvent($packageEvent);
  }

}
