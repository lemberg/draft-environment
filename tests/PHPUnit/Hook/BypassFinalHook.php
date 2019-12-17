<?php

declare(strict_types=1);

namespace Lemberg\Tests\Draft\Environment\PHPUnit\Hook;

use DG\BypassFinals;
use PHPUnit\Runner\BeforeTestHook;

/**
 * PHPUnit BeforeTestHook handler.
 */
final class BypassFinalHook implements BeforeTestHook {

  /**
   * Ensure that final classes can be mocked.
   */
  public function executeBeforeTest(string $test): void {
    BypassFinals::enable();
  }

}
