<?php

declare(strict_types=1);

namespace Lemberg\Tests\Draft\Environment;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\PolicyInterface;
use Composer\DependencyResolver\Pool;
use Composer\DependencyResolver\Request;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Repository\CompositeRepository;
use Lemberg\Draft\Environment\App;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Tests Draft Environment app.
 *
 * @covers \Lemberg\Draft\Environment\App
 */
final class AppTest extends TestCase {

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
    $this->io = $this->createMock(IOInterface::class);
    $this->composer = $this->createMock(Composer::class);
    $this->app = new App($this->composer, $this->io, vfsStream::setup()->url());

    // Mock required PackageEvent constructor arguments.
    $this->policy = $this->createMock(PolicyInterface::class);
    $this->pool = $this->createMock(Pool::class);
    $this->request = new Request();
    $this->installedRepo = $this->createMock(CompositeRepository::class);

    // Configuration files must exists before the test execution.
    $fs = new Filesystem();
    foreach ($this->app->getConfigurationFilepaths() as $filepath) {
      $fs->dumpFile($filepath, '');
    }
  }

  /**
   * Tests Composer PackageEvents::PRE_PACKAGE_UNINSTAL event handler.
   */
  public function testComposerPrePackageUninstallEventHandler(): void {
    // Clean up must not run when any package other than
    // "lemberg/draft-environment" is being uninstalled.
    $package = new Package('dummy', '1.0.0.0', '^1.0');
    $operation = new UninstallOperation($package);
    $event = new PackageEvent(PackageEvents::PRE_PACKAGE_UNINSTALL, $this->composer, $this->io, FALSE, $this->policy, $this->pool, $this->installedRepo, $this->request, [$operation], $operation);
    $this->app->handleEvent($event);
    foreach ($this->app->getConfigurationFilepaths() as $filepath) {
      self::assertFileExists($filepath);
    }

    // Clean up must not run when other than
    // PackageEvents::PRE_PACKAGE_UNINSTALL event is dispatched.
    $package = new Package('dummy', '1.0.0.0', '^1.0');
    $operation = new InstallOperation($package);
    $event = new PackageEvent(PackageEvents::PRE_PACKAGE_INSTALL, $this->composer, $this->io, FALSE, $this->policy, $this->pool, $this->installedRepo, $this->request, [$operation], $operation);
    $this->app->handleEvent($event);
    foreach ($this->app->getConfigurationFilepaths() as $filepath) {
      self::assertFileExists($filepath);
    }

    // Clean up must run when "lemberg/draft-environment" is being uninstalled.
    $package = new Package(App::PACKAGE_NAME, '1.0.0.0', '^1.0');
    $operation = new UninstallOperation($package);
    $event = new PackageEvent(PackageEvents::PRE_PACKAGE_UNINSTALL, $this->composer, $this->io, FALSE, $this->policy, $this->pool, $this->installedRepo, $this->request, [$operation], $operation);
    $this->app->handleEvent($event);
    foreach ($this->app->getConfigurationFilepaths() as $filepath) {
      self::assertFileNotExists($filepath);
    }
  }

}
