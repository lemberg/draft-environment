<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Update\Step;

use Composer\Script\ScriptEvents;
use Lemberg\Draft\Environment\Config\Update\UpdateStepInterface;

/**
 * Removes unneeded Composer Configurer::setUp script.
 */
final class RemoveConfigurerComposerScript extends AbstractUpdateStep implements UpdateStepInterface {

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return 1;
  }

  /**
   * {@inheritdoc}
   */
  public function update(array &$config): void {
    /** @var \Composer\Package\RootPackage $rootPackage */
    $rootPackage = $this->composer->getPackage();
    $scripts = $rootPackage->getScripts();
    $this->removeScript(ScriptEvents::POST_INSTALL_CMD, $scripts);
    $this->removeScript(ScriptEvents::POST_UPDATE_CMD, $scripts);
    $rootPackage->setScripts($scripts);
  }

  /**
   * Removes Configurer::setUp event listener from a given event.
   *
   * @param string $event
   * @param array<string,array> $scripts
   */
  private function removeScript(string $event, array &$scripts): void {
    if (array_key_exists($event, $scripts)) {
      $key = NULL;
      foreach ($scripts[$event] as $i => $listener) {
        if ($listener === 'Lemberg\\Draft\\Environment\\Configurer::setUp') {
          $key = $i;
        }
      }
      if (!is_null($key)) {
        unset($scripts[$event][$key]);
      }
    }
  }

}
