<?php

declare(strict_types=1);

namespace Lemberg\Tests\Draft\Environment\Composer;

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
use Lemberg\Draft\Environment\Composer\Plugin;
use PHPUnit\Framework\TestCase;

/**
 * Tests Draft Environment composer plugin.
 *
 * @covers \Lemberg\Draft\Environment\Composer\Plugin
 * @uses \Lemberg\Draft\Environment\App
 */
final class PluginTest extends TestCase {

  /**
   * Tests composer plugin activation, as well as correct configuration of the
   * event subscribers.
   */
  public function testComposerPlugin(): void {

    // Ensure that plugin activation does not produce any errors.
    $io = $this->createMock(IOInterface::class);
    $composer = $this->createMock(Composer::class);
    $plugin = new Plugin();
    $plugin->activate($composer, $io);

    // Ensure that plugin is subscribed to the correct events.
    $expected = [
      PackageEvents::PRE_PACKAGE_UNINSTALL => 'onPrePackageUninstall',
    ];
    self::assertSame($expected, Plugin::getSubscribedEvents());

    // Ensure that event handlers do not produce any errors.
    $policy = $this->createMock(PolicyInterface::class);
    $pool = $this->createMock(Pool::class);
    $request = new Request();
    $installedRepo = $this->createMock(CompositeRepository::class);

    $package = new Package('dummy', '1.0.0.0', '^1.0');
    $operation = new UninstallOperation($package);
    $event = new PackageEvent(PackageEvents::PRE_PACKAGE_UNINSTALL, $composer, $io, FALSE, $policy, $pool, $installedRepo, $request, [$operation], $operation);

    // Ensure that plugin passes events to the app.
    $app = $this->createMock(App::class);
    $app->expects(self::once())->method('onPrePackageUninstall');
    $plugin->setApp($app);

    $plugin->onPrePackageUninstall($event);
  }

}
