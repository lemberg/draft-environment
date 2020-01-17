<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Utility;

use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

/**
 * File system utility.
 */
final class Filesystem extends SymfonyFilesystem {

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
  final public function loadFile(string $filename, string $filepath): string {
    $content = file_get_contents($filepath);
    if ($content === FALSE) {
      throw new \RuntimeException(sprintf("Draft Environment Composer plugin was not able to read %s at '%s'", $filename, $filepath));
    }

    return $content;
  }

}
