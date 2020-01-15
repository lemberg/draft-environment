<?php

declare(strict_types=1);

namespace Lemberg\Tests\Draft\Environment;

use Composer\Composer;
use Composer\Config as ComposerConfig;
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
use Composer\Script\Event as ScriptEvent;
use Composer\Script\ScriptEvents;
use Lemberg\Draft\Environment\App;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Manager\InstallManager;
use Lemberg\Draft\Environment\Config\Manager\UpdateManager;
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
   * @var \Lemberg\Draft\Environment\Config\Manager\InstallManager
   */
  private $configInstallManager;

  /**
   * @var \Lemberg\Draft\Environment\Config\Manager\UpdateManager
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
    $this->io = $this->createMock(IOInterface::class);

    // Mock required PackageEvent constructor arguments.
    $this->policy = $this->createMock(PolicyInterface::class);
    $this->pool = $this->createMock(Pool::class);
    $this->request = new Request();
    $this->installedRepo = $this->createMock(CompositeRepository::class);

    // Mock source and target configuration directories.
    $root = vfsStream::setup()->url();
    $fs = new Filesystem();
    $fs->mkdir(["$root/source", "$root/target"]);

    $config = new Config("$root/source", "$root/target");
    $this->configInstallManager = new InstallManager($this->composer, $this->io, $config);
    $this->configUpdateManager = new UpdateManager($this->composer, $this->io, $config);
    $this->app = new App($this->composer, $this->io, $this->configInstallManager, $this->configUpdateManager);
  }

  /**
   * Tests Composer PackageEvents::PRE_PACKAGE_UNINSTAL event handler.
   */
  public function testComposerPrePackageUninstallEventHandler(): void {
    // Target configuration files must exists before the test execution.
    $fs = new Filesystem();
    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths() as $filepath) {
      $fs->dumpFile($filepath, 'phpunit: ' . __METHOD__);
    }

    // Clean up must not run when any package other than
    // "lemberg/draft-environment" is being uninstalled.
    $package = new Package('dummy', '1.0.0.0', '^1.0');
    $operation = new UninstallOperation($package);
    $event = new PackageEvent(PackageEvents::PRE_PACKAGE_UNINSTALL, $this->composer, $this->io, FALSE, $this->policy, $this->pool, $this->installedRepo, $this->request, [$operation], $operation);
    $this->app->handleEvent($event);
    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths() as $filepath) {
      self::assertFileExists($filepath);
    }

    // Clean up must not run when other than
    // PackageEvents::PRE_PACKAGE_UNINSTALL event is dispatched.
    $package = new Package('dummy', '1.0.0.0', '^1.0');
    $operation = new InstallOperation($package);
    $event = new PackageEvent(PackageEvents::PRE_PACKAGE_INSTALL, $this->composer, $this->io, FALSE, $this->policy, $this->pool, $this->installedRepo, $this->request, [$operation], $operation);
    $this->app->handleEvent($event);
    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths() as $filepath) {
      self::assertFileExists($filepath);
    }

    // Clean up must run when "lemberg/draft-environment" is being uninstalled.
    $package = new Package(App::PACKAGE_NAME, '1.0.0.0', '^1.0');
    $operation = new UninstallOperation($package);
    $event = new PackageEvent(PackageEvents::PRE_PACKAGE_UNINSTALL, $this->composer, $this->io, FALSE, $this->policy, $this->pool, $this->installedRepo, $this->request, [$operation], $operation);
    $this->app->handleEvent($event);
    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths(FALSE) as $filepath) {
      self::assertFileNotExists($filepath);
    }
  }

  /**
   * Tests Composer ScriptEvents::POST_INSTALL_CMD and
   * ScriptEvents::POST_UPDATE_CMD event handler.
   */
  public function testComposerPostInstallOrUpdateCommandEventHandler(): void {
    // Source configuration files must exists before the test execution.
    $fs = new Filesystem();
    foreach ($this->configInstallManager->getConfig()->getSourceConfigFilepaths() as $filepath) {
      $fs->dumpFile($filepath, 'phpunit: ' . __METHOD__);
    }

    // Install should not run on every ScriptEvents::POST_INSTALL_CMD dispatch.
    $event = new ScriptEvent(ScriptEvents::POST_INSTALL_CMD, $this->composer, $this->io);
    $this->app->handleEvent($event);
    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths() as $filepath) {
      self::assertFileNotExists($filepath);
    }

    // Installation process should be triggered by the
    // PackageEvents::POST_PACKAGE_INSTALL event only.
    $package = new Package(App::PACKAGE_NAME, '1.0.0.0', '^1.0');
    $operation = new InstallOperation($package);
    $packageEvent = new PackageEvent(PackageEvents::POST_PACKAGE_INSTALL, $this->composer, $this->io, FALSE, $this->policy, $this->pool, $this->installedRepo, $this->request, [$operation], $operation);
    $this->app->handleEvent($packageEvent);
    $this->app->handleEvent($event);
    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths() as $filepath) {
      self::assertFileExists($filepath);
    }

    // Clean up target configuration.
    $fs->remove($this->configInstallManager->getConfig()->getTargetConfigFilepaths());

    // Install should not run on every ScriptEvents::POST_INSTALL_CMD dispatch.
    $this->app->handleEvent($event);
    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths() as $filepath) {
      self::assertFileNotExists($filepath);
    }

    // Install should not run on every ScriptEvents::POST_UPDATE_CMD dispatch.
    $event = new ScriptEvent(ScriptEvents::POST_UPDATE_CMD, $this->composer, $this->io);
    $this->app->handleEvent($event);
    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths() as $filepath) {
      self::assertFileNotExists($filepath);
    }

    // Installation process should be triggered by the
    // PackageEvents::POST_PACKAGE_INSTALL only.
    $this->app->handleEvent($packageEvent);
    $this->app->handleEvent($event);
    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths() as $filepath) {
      self::assertFileExists($filepath);
    }

    // Clean up target configuration.
    $fs->remove($this->configInstallManager->getConfig()->getTargetConfigFilepaths());

    // Install should not run on every ScriptEvents::POST_UPDATE_CMD dispatch.
    $this->app->handleEvent($event);
    foreach ($this->configInstallManager->getConfig()->getTargetConfigFilepaths() as $filepath) {
      self::assertFileNotExists($filepath);
    }
  }

}
