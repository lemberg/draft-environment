<?php

declare(strict_types=1);

namespace Lemberg\Tests\Draft\Environment\Config\Update\Step;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\RootPackage;
use Composer\Script\ScriptEvents;
use Lemberg\Draft\Environment\App;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Manager\UpdateManager;
use Lemberg\Draft\Environment\Config\Update\Step\RemoveConfigurerComposerScript;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Tests Draft Environment configuration install manager.
 *
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\AbstractUpdateStep
 * @covers \Lemberg\Draft\Environment\Config\Update\Step\RemoveConfigurerComposerScript
 */
final class RemoveConfigurerComposerScriptTest extends TestCase {

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
    $this->io = $this->createMock(IOInterface::class);

    // Mock source and target configuration directories.
    $this->root = vfsStream::setup()->url();
    $fs = new Filesystem();
    $wd = sys_get_temp_dir() . '/draft-environment';
    $fs->mkdir(["$this->root/source", "$this->root/target", $wd]);
    chdir($wd);

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
   * @param array<int|string,array> $before
   * @param array<int|string,array> $after
   *
   * @dataProvider updateDataProvider
   */
  final public function testUpdate(array $before, array $after): void {
    /** @var \Composer\Package\RootPackage $rootPackage */
    $rootPackage = $this->composer->getPackage();
    $rootPackage->setScripts($before);
    $this->composer->setPackage($rootPackage);
    $step = new RemoveConfigurerComposerScript($this->composer, $this->io, $this->configUpdateManager);
    $config = [];

    $filename = Factory::getComposerFile();
    $json = new JsonFile($filename);
    $json->write(['name' => App::PACKAGE_NAME, 'scripts' => $before]);

    $step->update($config);

    self::assertSame($after, $this->composer->getPackage()->getScripts());
    $expected = count($after) > 0 ? ['name' => App::PACKAGE_NAME, 'scripts' => $after] : ['name' => App::PACKAGE_NAME];
    self::assertSame(JsonFile::encode($expected) . "\n", file_get_contents($filename));
  }

  /**
   * Data provider for the ::testUpdate().
   *
   * @return array<int|string,array>
   */
  final public function updateDataProvider(): array {
    return [
      [
        [],
        [],
      ],
      [
        [
          ScriptEvents::POST_INSTALL_CMD => [
            'Lemberg\Draft\Environment\Dummy::setUp',
          ],
        ],
        [
          ScriptEvents::POST_INSTALL_CMD => [
            'Lemberg\Draft\Environment\Dummy::setUp',
          ],
        ],
      ],
      [
        [
          ScriptEvents::POST_UPDATE_CMD => [
            'Lemberg\Draft\Environment\Dummy::setUp',
          ],
        ],
        [
          ScriptEvents::POST_UPDATE_CMD => [
            'Lemberg\Draft\Environment\Dummy::setUp',
          ],
        ],
      ],
      [
        [
          ScriptEvents::POST_INSTALL_CMD => [
            'Lemberg\Draft\Environment\Dummy::setUp',
          ],
          ScriptEvents::POST_UPDATE_CMD => [
            'Lemberg\Draft\Environment\Dummy::setUp',
          ],
        ],
        [
          ScriptEvents::POST_INSTALL_CMD => [
            'Lemberg\Draft\Environment\Dummy::setUp',
          ],
          ScriptEvents::POST_UPDATE_CMD => [
            'Lemberg\Draft\Environment\Dummy::setUp',
          ],
        ],
      ],
      [
        [
          ScriptEvents::POST_INSTALL_CMD => [
            'Lemberg\Draft\Environment\Dummy::setUp',
            'Lemberg\Draft\Environment\Configurer::setUp',
          ],
        ],
        [
          ScriptEvents::POST_INSTALL_CMD => [
            'Lemberg\Draft\Environment\Dummy::setUp',
          ],
        ],
      ],
      [
        [
          ScriptEvents::POST_UPDATE_CMD => [
            'Lemberg\Draft\Environment\Dummy::setUp',
            'Lemberg\Draft\Environment\Configurer::setUp',
          ],
        ],
        [
          ScriptEvents::POST_UPDATE_CMD => [
            'Lemberg\Draft\Environment\Dummy::setUp',
          ],
        ],
      ],
      [
        [
          ScriptEvents::POST_INSTALL_CMD => [
            'Lemberg\Draft\Environment\Dummy::setUp',
            'Lemberg\Draft\Environment\Configurer::setUp',
          ],
          ScriptEvents::POST_UPDATE_CMD => [
            'Lemberg\Draft\Environment\Dummy::setUp',
            'Lemberg\Draft\Environment\Configurer::setUp',
          ],
        ],
        [
          ScriptEvents::POST_INSTALL_CMD => [
            'Lemberg\Draft\Environment\Dummy::setUp',
          ],
          ScriptEvents::POST_UPDATE_CMD => [
            'Lemberg\Draft\Environment\Dummy::setUp',
          ],
        ],
      ],
      [
        [
          ScriptEvents::POST_INSTALL_CMD => [
            'Lemberg\Draft\Environment\Configurer::setUp',
          ],
        ],
        [],
      ],
      [
        [
          ScriptEvents::POST_UPDATE_CMD => [
            'Lemberg\Draft\Environment\Configurer::setUp',
          ],
        ],
        [],
      ],
      [
        [
          ScriptEvents::POST_INSTALL_CMD => [
            'Lemberg\Draft\Environment\Configurer::setUp',
          ],
          ScriptEvents::POST_UPDATE_CMD => [
            'Lemberg\Draft\Environment\Configurer::setUp',
          ],
        ],
        [],
      ],
    ];
  }

}
