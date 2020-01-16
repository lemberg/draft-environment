<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Manager;

use Lemberg\Draft\Environment\App;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Update\UpdateStepInterface;

/**
 * Draft Environment configuration update manager.
 */
final class UpdateManager extends AbstractConfigManager {

  /**
   * Updates the Draft Environment.
   */
  public function update(): void {
    $this->discoverSteps(UpdateStepInterface::class, __DIR__ . '/../Update');
    $this->sortSteps();
    $this->filterSteps();

    if (count($this->steps) > 0) {
      $configObject = $this->getConfig();

      $targetConfigFilepath = $configObject->getTargetConfigFilepath(Config::TARGET_CONFIG_FILENAME);

      $config = $configObject->readAndParseConfigFromTheFile($targetConfigFilepath);

      /** @var \Lemberg\Draft\Environment\Config\Update\UpdateStepInterface $step */
      foreach ($this->steps as $step) {
        $step->update($config);
      }

      $configObject->writeConfigToTheFile($targetConfigFilepath, $targetConfigFilepath, $config);

      /** @var \Lemberg\Draft\Environment\Config\Update\UpdateStepInterface $lastStep */
      $lastStep = end($this->steps);
      $lastAppliedUpdateWeight = $lastStep->getWeight();
      $this->setLastAppliedUpdateWeight($lastAppliedUpdateWeight);
    }
  }

  /**
   * Sorts the given steps by weight in the ascending order.
   */
  private function filterSteps(): void {
    $lastAppliedUpdateWeight = $this->getLastAppliedUpdateWeight();

    $this->steps = array_filter($this->steps, function (int $weight) use ($lastAppliedUpdateWeight): bool {
      return $weight > $lastAppliedUpdateWeight;
    }, ARRAY_FILTER_USE_KEY);
  }

  /**
   * Get the last update weight from the local repository.
   *
   * @return int
   */
  private function getLastAppliedUpdateWeight(): int {
    $localRepository = $this->composer->getRepositoryManager()->getLocalRepository();
    /** @var \Composer\Package\Package $localPackage */
    $localPackage = $localRepository->findPackage(App::PACKAGE_NAME, '*');
    return $localPackage->getExtra()['draft_environment_last_update_weight'] ?? 0;
  }

  /**
   * Set the last update weight in the local repository.
   *
   * @param int $weight
   */
  public function setLastAppliedUpdateWeight(int $weight): void {
    $localRepository = $this->composer->getRepositoryManager()->getLocalRepository();
    /** @var \Composer\Package\Package $localPackage */
    $localPackage = $localRepository->findPackage(App::PACKAGE_NAME, '*');
    $extra = $localPackage->getExtra();
    $extra['draft_environment_last_update_weight'] = $weight;
    $localPackage->setExtra($extra);
  }

}
