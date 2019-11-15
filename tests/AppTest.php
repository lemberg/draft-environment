<?php

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

/**
 * Tests Draft Environment app.
 *
 * @covers \Lemberg\Draft\Environment\App
 */
class AppTest extends TestCase {

  /**
   * @var \Lemberg\Draft\Environment\App $app
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
    foreach ($this->app->getConfigurationFilepaths() as $filepath) {
      file_put_contents($filepath, '');
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
      $this->assertFileExists($filepath);
    }

    // Clean up must run when "lemberg/draft-environment" is being uninstalled.
    $package = new Package(App::PACKAGE_NAME, '1.0.0.0', '^1.0');
    $operation = new UninstallOperation($package);
    $event = new PackageEvent(PackageEvents::PRE_PACKAGE_UNINSTALL, $this->composer, $this->io, FALSE, $policy, $pool, $installedRepo, $request, [$operation], $operation);
    $this->app->onPrePackageUninstall($event);
    foreach ($this->app->getConfigurationFilepaths() as $filepath) {
      $this->assertFileNotExists($filepath);
    }
  }

}
