<?php

declare(strict_types=1);

namespace Lemberg\Tests\Functional\Draft\Environment\Config\Manager;

use Lemberg\Draft\Environment\App;
use Lemberg\Tests\Functional\Draft\Environment\AbstractFunctionalTest;
use Symfony\Component\Yaml\Parser;

/**
 * Base configuration manager test.
 *
 * @coversNothing
 */
abstract class AbstractConfigManagerTest extends AbstractFunctionalTest {

  /**
   * Asserts that composer.lock exists and contains correct data in the package
   * extra section.
   */
  final protected function assertVmSettingsContainsLastAppliedUpdate(): void {
    self::assertFileExists("$this->workingDir/vm-settings.yml");

    $parser = new Parser();
    $config = $parser->parseFile("$this->workingDir/vm-settings.yml");

    self::assertSame(App::LAST_AVAILABLE_UPDATE_WEIGHT, $config['draft']['last_applied_update']);
  }

}
