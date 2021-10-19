<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Manager;

use Lemberg\Draft\Environment\Config\AbstractStepInterface;
use Lemberg\Draft\Environment\Config\Update\UpdateStepInterface;

/**
 * Draft Environment configuration update manager.
 */
class UpdateManager extends AbstractConfigManager implements UpdateManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function update(): void {
    $config = $this->readConfig();
    $this->discoverSteps(UpdateStepInterface::class, __DIR__ . '/../Update');
    $this->sortSteps();
    $this->filterSteps($config);

    if (count($this->steps) > 0) {

      /** @var \Lemberg\Draft\Environment\Config\Update\UpdateStepInterface $step */
      foreach ($this->steps as $step) {
        $step->update($config);
      }

      $this->setLastAppliedUpdateWeight($config, $this->getLastAvailableUpdateWeight());
      $this->writeConfig($config);
    }
  }

  /**
   * Sorts the given steps by weight in the ascending order.
   *
   * @param array<int|string,mixed> $config
   *   Draft Environment configuration nested array.
   */
  private function filterSteps(array $config): void {
    $lastAppliedUpdateWeight = $this->getLastAppliedUpdateWeight($config);

    $this->steps = array_filter($this->steps, function (AbstractStepInterface $step) use ($lastAppliedUpdateWeight): bool {
      return $step->getWeight() > $lastAppliedUpdateWeight;
    });
  }

}
