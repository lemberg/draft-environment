<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config;

use Consolidation\Comments\Comments;
use Lemberg\Draft\Environment\Utility\Filesystem;
use Lemberg\Draft\Environment\Utility\FilesystemAwareTrait;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

/**
 * Draft Environment configuration helper.
 */
final class Config {

  use FilesystemAwareTrait;

  public const SOURCE_CONFIG_FILENAME = 'default.vm-settings.yml';
  public const SOURCE_VM_FILENAME = 'Vagrantfile.proxy';
  public const SOURCE_CONFIGURATION_FILENAMES = [
    self::SOURCE_CONFIG_FILENAME,
    self::SOURCE_VM_FILENAME,
  ];
  public const TARGET_CONFIG_FILENAME = 'vm-settings.yml';
  public const TARGET_LOCAL_CONFIG_FILENAME = 'vm-settings.local.yml';
  public const TARGET_VM_FILENAME = 'Vagrantfile';
  public const TARGET_GITIGNORE = '.gitignore';
  public const TARGET_CONFIGURATION_FILENAMES = [
    self::TARGET_CONFIG_FILENAME,
    self::TARGET_VM_FILENAME,
    self::TARGET_GITIGNORE,
  ];
  public const TARGET_OPTIONAL_CONFIGURATION_FILENAMES = [
    self::TARGET_LOCAL_CONFIG_FILENAME,
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
    $this->setFilesystem(new Filesystem());
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
   *   File path to a given source configuration file.
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

    throw new \InvalidArgumentException(sprintf("Non-existing Draft Environment source configuration filename '%s' has been passed.", $filename));
  }

  /**
   * Generates and array of file paths to the Draft Environment target
   * configuration files.
   *
   * @param bool $includeGitIgnore
   *   Whether to include .gitignore file or not.
   *
   * @return \Iterator<int, string>
   */
  public function getTargetConfigFilepaths(bool $includeGitIgnore = TRUE): \Iterator {
    foreach (self::TARGET_CONFIGURATION_FILENAMES as $filename) {
      if ($filename === self::TARGET_GITIGNORE && !$includeGitIgnore) {
        continue;
      }
      yield $this->targetDirectory . DIRECTORY_SEPARATOR . $filename;
    }
  }

  /**
   * Returns file path to the given Draft Environment target configuration file.
   *
   * @return string
   *   File path to a given source configuration file.
   *
   * @throws \InvalidArgumentException
   *   When non-existing Draft Environment target configuration filename has
   *   been passed.
   */
  public function getTargetConfigFilepath(string $filename): string {
    $allowedFilenames = array_merge(self::TARGET_CONFIGURATION_FILENAMES, self::TARGET_OPTIONAL_CONFIGURATION_FILENAMES);
    foreach ($allowedFilenames as $existingFilename) {
      if ($filename === $existingFilename) {
        return $this->targetDirectory . DIRECTORY_SEPARATOR . $filename;
      }
    }

    throw new \InvalidArgumentException(sprintf("Non-existing Draft Environment target configuration filename '%s' has been passed.", $filename));
  }

  /**
   * Reads and returns raw configuration from a given source file.
   *
   * @param string $source
   *
   * @return string
   */
  public function readConfigFromTheFile(string $source): string {
    return $this->getFilesystem()->loadFile('config', $source);
  }

  /**
   * Reads, parses and returns configuration from a given source file.
   *
   * @param string $source
   *
   * @return array<int|string, array>
   */
  public function readAndParseConfigFromTheFile(string $source): array {
    $content = $this->readConfigFromTheFile($source);
    $parser = new Parser();
    return $parser->parse($content);
  }

  /**
   * Dumps and writes configuration to a given target file, preserving comments
   * from a given source file.
   *
   * @param string $source
   * @param string $target
   * @param array<int|string,array> $config
   */
  public function writeConfigToTheFile(string $source, string $target, array $config): void {
    $yaml = new Dumper(2);

    $flags = Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE;
    if (defined(Yaml::class . '::DUMP_NULL_AS_TILDE')) {
      $flags |= Yaml::DUMP_NULL_AS_TILDE;
    }

    $alteredContent = $yaml->dump($config, PHP_INT_MAX, 0, $flags);

    $originalContent = $this->readConfigFromTheFile($source);

    $commentManager = new Comments();
    $commentManager->collect(explode("\n", $originalContent));
    $alteredWithComments = $commentManager->inject(explode("\n", $alteredContent));
    $this->getFilesystem()->dumpFile($target, implode("\n", $alteredWithComments));
  }

}
