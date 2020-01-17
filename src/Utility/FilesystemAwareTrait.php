<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Utility;

/**
 * Implements Filesystem getter and setter.
 */
trait FilesystemAwareTrait {

  /**
   * @var \Lemberg\Draft\Environment\Utility\Filesystem
   */
  private $fs;

  /**
   * @return \Lemberg\Draft\Environment\Utility\Filesystem
   */
  final public function getFilesystem(): Filesystem {
    return $this->fs;
  }

  /**
   * @param \Lemberg\Draft\Environment\Utility\Filesystem $fs
   */
  final public function setFilesystem(Filesystem $fs): void {
    $this->fs = $fs;
  }

}
