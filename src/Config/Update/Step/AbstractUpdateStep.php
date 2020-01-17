<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Update\Step;

use Composer\Composer;
use Composer\IO\IOInterface;
use Lemberg\Draft\Environment\Config\AbstractStepInterface;
use Lemberg\Draft\Environment\Config\Manager\ManagerInterface;
use Lemberg\Draft\Environment\Messanger\MessangerTrait;
use Lemberg\Draft\Environment\Utility\Filesystem;
use Lemberg\Draft\Environment\Utility\FilesystemAwareTrait;

/**
 * Default implementation of the update step.
 */
abstract class AbstractUpdateStep implements AbstractStepInterface {

  use FilesystemAwareTrait;
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
  protected $configUpdateManager;

  /**
   * {@inheritdoc}
   */
  final public function __construct(Composer $composer, IOInterface $io, ManagerInterface $configManager) {
    $this->composer = $composer;
    $this->io = $io;
    $this->configUpdateManager = $configManager;
    $this->setFilesystem(new Filesystem());
  }

}
