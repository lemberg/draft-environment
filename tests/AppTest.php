<?php

declare(strict_types=1);

namespace Lemberg\Tests\Draft\Environment;

use Composer\Composer;
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
  protected $composer;

  /**
   * @var \Composer\IO\IOInterface
   */
  protected $io;

  /**
   * @var \Lemberg\Draft\Environment\App
   */
  protected $app;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->io = $this->createMock(IOInterface::class);
    $this->composer = $this->createMock(Composer::class);
    $this->app = new App($this->composer, $this->io, vfsStream::setup()->url());
  }

  /**
   * Tests Composer PackageEvents::PRE_PACKAGE_UNINSTAL event handler.
   */
  public function testComposerPrePackageUninstallEvent(): void {

    // Configuration files must exists before the test execution.
    $fs = new Filesystem();
    foreach ($this->app->getConfigurationFilepaths() as $filepath) {
      $fs->dumpFile($filepath, '');
    }

    // Mock required PackageEvent constructor arguments.
    $policy = $this->createMock(PolicyInterface::class);
    $pool = $this->createMock(Pool::class);
    $request = new Request();
    $installedRepo = $this->createMock(CompositeRepository::class);

    // Clean up must not run when any package other than
    // "lemberg/draft-environment" is being uninstalled.
    $package = new Package('dummy', '1.0.0.0', '^1.0');
    $operation = new UninstallOperation($package);
    $event = new PackageEvent(PackageEvents::PRE_PACKAGE_UNINSTALL, $this->composer, $this->io, FALSE, $policy, $pool, $installedRepo, $request, [$operation], $operation);
    $this->app->onPrePackageUninstall($event);
    foreach ($this->app->getConfigurationFilepaths() as $filepath) {
      self::assertFileExists($filepath);
    }

    // Clean up must run when "lemberg/draft-environment" is being uninstalled.
    $package = new Package(App::PACKAGE_NAME, '1.0.0.0', '^1.0');
    $operation = new UninstallOperation($package);
    $event = new PackageEvent(PackageEvents::PRE_PACKAGE_UNINSTALL, $this->composer, $this->io, FALSE, $policy, $pool, $installedRepo, $request, [$operation], $operation);
    $this->app->onPrePackageUninstall($event);
    foreach ($this->app->getConfigurationFilepaths() as $filepath) {
      self::assertFileNotExists($filepath);
    }
  }

}
