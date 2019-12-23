<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config;

/**
 * Draft Environment configuration helper.
 */
final class Config {

  public const SOURCE_CONFIG_FILENAME = 'default.vm-settings.yml';
  public const SOURCE_VM_FILENAME = 'Vagrantfile.proxy';
  public const SOURCE_CONFIGURATION_FILENAMES = [
    self::SOURCE_CONFIG_FILENAME,
    self::SOURCE_VM_FILENAME,
  ];
  public const TARGET_CONFIG_FILENAME = 'vm-settings.yml';
  public const TARGET_VM_FILENAME = 'Vagrantfile';
  public const TARGET_CONFIGURATION_FILENAMES = [
    self::TARGET_CONFIG_FILENAME,
    self::TARGET_VM_FILENAME,
  ];

  /**
   * Source (lemberg/draft-environment install path) directory.
   *
   * @var string
   */
  private $sourceDirectory;

  /**
   * Target (project and Composer root package) directory.
   *
   * @var string
   */
  private $targetDirectory;

  /**
   * Draft Environment configuration helper constructor.
   *
   * @param string $sourceDirectory
   * @param string $targetDirectory
   */
  public function __construct(string $sourceDirectory, string $targetDirectory) {
    $this->sourceDirectory = $sourceDirectory;
    $this->targetDirectory = $targetDirectory;
  }

  /**
   * Generates and array of file paths to the Draft Environment source
   * configuration files.
   *
   * @return \Iterator<int, string>
   */
  public function getSourceConfigFilepaths(): \Iterator {
    foreach (self::SOURCE_CONFIGURATION_FILENAMES as $filename) {
      yield $this->sourceDirectory . DIRECTORY_SEPARATOR . $filename;
    }
  }

  /**
   * Returns file path to the given Draft Environment source configuration file.
   *
   * @return string
   *
   * @throws \InvalidArgumentException
   *   When non-existing Draft Environment source configuration filename has
   *   been passed.
   */
  public function getSourceConfigFilepath(string $filename): string {
    foreach (self::SOURCE_CONFIGURATION_FILENAMES as $existingFilename) {
      if ($filename === $existingFilename) {
        return $this->sourceDirectory . DIRECTORY_SEPARATOR . $filename;
      }
    }

    throw new \InvalidArgumentException(sprintf('Non-existing Draft Environment source configuration filename %s has been passed.', htmlspecialchars($filename, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')));
  }

  /**
   * Generates and array of file paths to the Draft Environment target
   * configuration files.
   *
   * @return \Iterator<int, string>
   */
  public function getTargetConfigFilepaths(): \Iterator {
    foreach (self::TARGET_CONFIGURATION_FILENAMES as $filename) {
      yield $this->targetDirectory . DIRECTORY_SEPARATOR . $filename;
    }
  }

  /**
   * Returns file path to the given Draft Environment target configuration file.
   *
   * @return string
   *
   * @throws \InvalidArgumentException
   *   When non-existing Draft Environment target configuration filename has
   *   been passed.
   */
  public function getTargetConfigFilepath(string $filename): string {
    foreach (self::TARGET_CONFIGURATION_FILENAMES as $existingFilename) {
      if ($filename === $existingFilename) {
        return $this->targetDirectory . DIRECTORY_SEPARATOR . $filename;
      }
    }

    throw new \InvalidArgumentException(sprintf('Non-existing Draft Environment target configuration filename %s has been passed.', htmlspecialchars($filename, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')));
  }

}
