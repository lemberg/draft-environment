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
    $config = $this->configMerge($defaultConfig, $config);

    $configObject->writeConfigToTheFile($sourceConfigFilepath, $targetConfigFilepath, $config);
  }

  /**
   * Merges default configuration with actual configuration.
   *
   * Rules:
   *   - scalar values from the actual configuration always win
   *   - indexed array values from the actual configuration always win
   *   - associative arrays are merged recursively, values from the actual
   *     configuration overrides default ones
   *   - missing configuration parameters are being added recursively.
   *
   * @param mixed $defaultConfig
   * @param array<int|string,mixed> $config
   *
   * @return array<int|string,mixed>
   *
   * @throws \UnexpectedValueException
   */
  private function configMerge($defaultConfig, array $config): array {
    $result = [];

    foreach ($config as $key => $value) {
      if (is_scalar($value) || is_null($value)) {
        $result[$key] = $value;
      }
      elseif (is_array($value) && is_int(key($value))) {
        $result[$key] = $value;
      }
      elseif (is_array($value)) {

        if (is_array($defaultConfig) && array_key_exists($key, $defaultConfig)) {
          $result[$key] = $this->configMerge($defaultConfig[$key] ?? [], $value);
        }
        else {
          $result[$key] = $value;
        }
      }
      else {
        throw new \UnexpectedValueException(sprintf("Unexpected value type '%s' in the configuration array", gettype($value)));
      }
    }

    return array_merge(is_array($defaultConfig) ? $defaultConfig : [], $result);
  }

}
