<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Update\Step;

use Composer\Factory;
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
    $scripts = $this->removeScript($rootPackage->getScripts());
    $rootPackage->setScripts($scripts);
    $this->composer->setPackage($rootPackage);

    // Composer won't update composer.json automatically. Do it manually.
    $file = Factory::getComposerFile();
    $contents = file_get_contents($file);
    if ($contents === FALSE) {
      throw new \RuntimeException(sprintf('File %s could not be read', $file));
    }

    // Parse composer.json contents into the object in order to preserve empty
    // object that might be there. If converted to the associative array,
    // empty objects later will be exported as empty arrays producing invalid
    // composer.json file.
    $decoded_contents = json_decode($contents);

    if (count($scripts) > 0) {
      $decoded_contents->scripts = $scripts;
    }
    else {
      unset($decoded_contents->scripts);
    }

    $this->getFilesystem()->dumpFile($file, json_encode($decoded_contents, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);
  }

  /**
   * Removes Configurer::setUp event listener from all events.
   *
   * @param array<string,array<int,string>> $scripts
   *
   * @return array<string,array<int,string>>
   */
  private function removeScript(array $scripts): array {
    // Remove script from all events.
    foreach (array_keys($scripts) as $event) {
      $scripts[$event] = array_values(
        array_filter($scripts[$event], function (string $value): bool {
          return $value !== 'Lemberg\Draft\Environment\Configurer::setUp';
        })
      );
    }

    // Remove all empty events altogether.
    return array_filter($scripts);
  }

}
