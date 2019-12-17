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
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

/**
 * Tests Draft Environment composer plugin.
 *
 * @covers \Lemberg\Draft\Environment\Composer\Plugin
 * @uses \Lemberg\Draft\Environment\App
 */
final class PluginTest extends TestCase {

  use PHPMock;

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
    $app->expects(self::once())->method('handle');
    $plugin->setApp($app);

    $plugin->onPrePackageUninstall($event);
  }

  /**
   * Tests composer plugin throws an exception when getcwd() returns FALSE.
   */
  public function testComposerPluginThrowsExceptionWhenGetcwdReturnsFalse(): void {
    $getcwd = $this->getFunctionMock('Lemberg\Draft\Environment\Composer', 'getcwd');
    $getcwd->expects(self::once())->willReturn(FALSE);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('Unable to get the current working directory. Please check if any one of the parent directories does not have the readable or search mode set, even if the current directory does. See https://www.php.net/manual/function.getcwd.php');

    $io = $this->createMock(IOInterface::class);
    $composer = $this->createMock(Composer::class);
    $plugin = new Plugin();
    $plugin->activate($composer, $io);
  }

}
