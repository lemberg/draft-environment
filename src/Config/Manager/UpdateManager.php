<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Manager;

use Lemberg\Draft\Environment\Config\AbstractStepInterface;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Update\UpdateStepInterface;

/**
 * Draft Environment configuration update manager.
 */
final class UpdateManager extends AbstractConfigManager implements UpdateManagerInterface {

  /**
   * {@inheritdoc}
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

      $lastAppliedUpdateWeight = $this->getLastUpdateWeightFromTheSteps();
      $this->setLastAppliedUpdateWeight($lastAppliedUpdateWeight);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getLastAppliedUpdateWeight(): int {
    $extra = $this->getPackageExtra();
    return $extra['draft-environment']['last-update-weight'] ?? 0;
  }

  /**
   * {@inheritdoc}
   */
  public function setLastAppliedUpdateWeight(int $weight): void {
    $extra = $this->getPackageExtra();
    $extra['draft-environment']['last-update-weight'] = $weight;
    $this->setPackageExtra($extra);
  }

  /**
   * {@inheritdoc}
   */
  public function getLastAvailableUpdateWeight(): int {
    $this->discoverSteps(UpdateStepInterface::class, __DIR__ . '/../Update');
    $this->sortSteps();

    return $this->getLastUpdateWeightFromTheSteps();
  }

  /**
   * Sorts the given steps by weight in the ascending order.
   */
  private function filterSteps(): void {
    $lastAppliedUpdateWeight = $this->getLastAppliedUpdateWeight();

    $this->steps = array_filter($this->steps, function (AbstractStepInterface $step) use ($lastAppliedUpdateWeight): bool {
      return $step->getWeight() > $lastAppliedUpdateWeight;
    });
  }

  /**
   * Get the weight of the last step.
   *
   * @return int
   */
  private function getLastUpdateWeightFromTheSteps(): int {
    /** @var \Lemberg\Draft\Environment\Config\Update\UpdateStepInterface $lastStep */
    $lastStep = end($this->steps);
    return $lastStep->getWeight();
  }

}
