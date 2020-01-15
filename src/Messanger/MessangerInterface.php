<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Messanger;

/**
 * Collects and returns messages.
 */
interface MessangerInterface {

  /**
   * @return string
   */
  public function getMessages(): string;

}
