<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Update\Step;

use Composer\Factory;
use Composer\Json\JsonFile;
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
    $this->composer->setPackage($rootPackage);

    // Composer won't update composer.json automatically. Do it manually.
    $file = Factory::getComposerFile();
    $json = new JsonFile($file);
    $composerDefinition = $json->read();
    if (count($scripts) > 0) {
      $composerDefinition['scripts'] = $scripts;
    }
    else {
      unset($composerDefinition['scripts']);
    }
    $json->write($composerDefinition);
  }

  /**
   * Removes Configurer::setUp event listener from a given event.
   *
   * @param string $event
   * @param array<string,array> $scripts
   */
  private function removeScript(string $event, array &$scripts): void {
    if (!array_key_exists($event, $scripts)) {
      return;
    }

    $scripts[$event] = array_filter($scripts[$event], function (string $value): bool {
      return $value !== 'Lemberg\Draft\Environment\Configurer::setUp';
    });

    if (count($scripts[$event]) === 0) {
      unset($scripts[$event]);
    }
  }

}
