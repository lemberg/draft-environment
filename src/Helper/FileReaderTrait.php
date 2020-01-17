<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Helper;

use Symfony\Component\Filesystem\Filesystem;

/**
 * File reader helper.
 */
trait FileReaderTrait {

  /**
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $fs;

  /**
   * Init file system object.
   */
  final protected function initFileSystem(): void {
    $this->fs = new Filesystem();
  }

  /**
   * Reads and returns content of a given file.
   *
   * @param string $filename
   * @param string $filepath
   *
   * @return string
   *
   * @throws \RuntimeException
   *   When file cannot be read for any reason.
   */
  final protected function readFile(string $filename, string $filepath): string {
    $content = file_get_contents($filepath);
    if ($content === FALSE) {
      throw new \RuntimeException(sprintf("Draft Environment Composer plugin was not able to read %s at '%s'", $filename, $filepath));
    }

    return $content;
  }

  /**
   * Writes content to a given file.
   *
   * @param string $filepath
   * @param string $content
   */
  final protected function writeFile(string $filepath, string $content): void {
    $this->fs->dumpFile($filepath, $content);
  }

}
