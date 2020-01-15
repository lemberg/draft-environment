<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Install\Step;

use Composer\Composer;
use Composer\IO\IOInterface;
use Lemberg\Draft\Environment\Config\AbstractStepInterface;
use Lemberg\Draft\Environment\Config\Manager\ManagerInterface;
use Lemberg\Draft\Environment\Helper\FileReaderTrait;
use Lemberg\Draft\Environment\Messanger\MessangerTrait;

/**
 * Default implementation of the installation/uninstall step.
 */
abstract class AbstractInstallStep implements AbstractStepInterface {

  use FileReaderTrait;
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
   * @var \Lemberg\Draft\Environment\Config\Manager\ManagerInterface
   */
  protected $configInstallManager;

  /**
   * {@inheritdoc}
   */
  final public function __construct(Composer $composer, IOInterface $io, ManagerInterface $configManager) {
    $this->composer = $composer;
    $this->io = $io;
    $this->configInstallManager = $configManager;
    $this->initFileSystem();
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return 0;
  }

}
