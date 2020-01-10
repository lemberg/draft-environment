<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Install\Step;

use Composer\Composer;
use Composer\IO\IOInterface;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\ConfigAwareTrait;
use Lemberg\Draft\Environment\Config\AbstractStepInterface;
use Lemberg\Draft\Environment\Messanger\MessangerTrait;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Default implementation of the installation/uninstall step.
 */
abstract class AbstractInstallStep implements AbstractStepInterface {

  use ConfigAwareTrait;
  use MessangerTrait;

  /**
   * @var \Composer\Composer
   */
  protected $composer;

  /**
   * @var \Composer\IO\IOInterface
   */
  protected $io;

  /**
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $fs;

  /**
   * {@inheritdoc}
   */
  final public function __construct(Composer $composer, IOInterface $io, Config $config) {
    $this->composer = $composer;
    $this->io = $io;
    $this->fs = new Filesystem();
    $this->setConfig($config);
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return 0;
  }

}
