<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Update\Step;

use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Update\UpdateStepInterface;

/**
 * Merges all available Ansible variables with existing VM configuration.
 */
final class ExportAllAvailableConfiguration extends AbstractUpdateStep implements UpdateStepInterface {

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return 2;
  }

  /**
   * {@inheritdoc}
   */
  public function update(array &$config): void {
    $configObject = $this->configUpdateManager->getConfig();

    $sourceConfigFilepath = $configObject->getSourceConfigFilepath(Config::SOURCE_CONFIG_FILENAME);
    $targetConfigFilepath = $configObject->getTargetConfigFilepath(Config::TARGET_CONFIG_FILENAME);

    $defaultConfig = $configObject->readAndParseConfigFromTheFile($sourceConfigFilepath);
    $config = array_merge($defaultConfig, $config);

    $configObject->writeConfigToTheFile($sourceConfigFilepath, $targetConfigFilepath, $config);
  }

}
