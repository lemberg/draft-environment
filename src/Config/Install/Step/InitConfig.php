<?php

declare(strict_types=1);

namespace Lemberg\Draft\Environment\Config\Install\Step;

use Composer\Config as ComposerConfig;
use Lemberg\Draft\Environment\Config\Config;
use Lemberg\Draft\Environment\Config\Install\InstallInitStepInterface;
use Lemberg\Draft\Environment\Config\Install\UninstallStepInterface;

/**
 * Copies source configuration files to their destination, modifies Vagrantfile
 * and .gitignore.
 */
final class InitConfig extends AbstractInstallStep implements InstallInitStepInterface, UninstallStepInterface {

  private const GITIGNORE_VAGRANT_LINE = "\n# Ignore Vagrant virtual machine data.\n/.vagrant\n";
  private const GITIGNORE_TARGET_LOCAL_CONFIG_FILENAME_LINE = "\n# Ignore Draft Environment local configuration overrides.\n/" . Config::TARGET_LOCAL_CONFIG_FILENAME . "\n";

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return -100;
  }

  /**
   * {@inheritdoc}
   */
  public function install(): void {
    $config = $this->configInstallManager->getConfig();
    $fs = $this->getFilesystem();

    $sourceConfigFilepath = $config->getSourceConfigFilepath(Config::SOURCE_CONFIG_FILENAME);
    $targetConfigFilepath = $config->getTargetConfigFilepath(Config::TARGET_CONFIG_FILENAME);

    $sourceVmFilepath = $config->getSourceConfigFilepath(Config::SOURCE_VM_FILENAME);
    $targetVmFilepath = $config->getTargetConfigFilepath(Config::TARGET_VM_FILENAME);

    // Copy default configuration and Vagrantfile to the project's root
    // directory.
    $fs->copy($sourceConfigFilepath, $targetConfigFilepath);
    $fs->copy($sourceVmFilepath, $targetVmFilepath);

    // Adjust path to the Draft Environment package if non-standard Composer
    // vendor directory is being used.
    $vendorDir = trim($this->composer->getConfig()->get('vendor-dir', ComposerConfig::RELATIVE_PATHS), DIRECTORY_SEPARATOR);
    if ($vendorDir !== 'vendor') {
      $vagrantfile = $fs->loadFile('Vagrantfile', $targetVmFilepath);
      $fs->dumpFile($targetVmFilepath, str_replace('/vendor/', "/$vendorDir/", $vagrantfile));
    }

    // Add Draft Environment local overrides and Vagrant VM data directory
    // to the project's root .gitignore file.
    // This code runs in Composer context and is affected by the intresting side
    // effect: some classes are being used by Composer internally and might be
    // already autoloaded. Composer itself allows for pretty old
    // symfony/filesystem version, thus Filesystem::appendToFile()
    // might not be available.
    $targetGitIgnore = $config->getTargetConfigFilepath(Config::TARGET_GITIGNORE);

    $gitIgnoreContent = '';
    if ($fs->exists($targetGitIgnore)) {
      $gitIgnoreContent = $fs->loadFile('.gitignore', $targetGitIgnore);
    }
    if (strpos($gitIgnoreContent, '.vagrant') === FALSE) {
      $gitIgnoreContent .= self::GITIGNORE_VAGRANT_LINE;
    }
    if (strpos($gitIgnoreContent, Config::TARGET_LOCAL_CONFIG_FILENAME) === FALSE) {
      $gitIgnoreContent .= self::GITIGNORE_TARGET_LOCAL_CONFIG_FILENAME_LINE;
    }
    $fs->dumpFile($targetGitIgnore, $gitIgnoreContent);

    $this->addMessage($this->getMessageText('added'));
  }

  /**
   * {@inheritdoc}
   */
  public function uninstall(): void {
    $config = $this->configInstallManager->getConfig();
    $fs = $this->getFilesystem();

    // Remove Draft Environment configuration files, except .gitignore.
    foreach ($config->getTargetConfigFilepaths(FALSE) as $filepath) {
      $fs->remove($filepath);
    }

    // Clean up .gitignore.
    $targetGitIgnore = $config->getTargetConfigFilepath(Config::TARGET_GITIGNORE);
    $gitIgnoreContent = $fs->loadFile('.gitignore', $targetGitIgnore);
    $gitIgnoreContent = str_replace([self::GITIGNORE_VAGRANT_LINE, trim(self::GITIGNORE_VAGRANT_LINE)], '', $gitIgnoreContent);
    $gitIgnoreContent = str_replace([self::GITIGNORE_TARGET_LOCAL_CONFIG_FILENAME_LINE, trim(self::GITIGNORE_TARGET_LOCAL_CONFIG_FILENAME_LINE)], '', $gitIgnoreContent);

    // Check if those where the only lines in the .gitignore.
    if (preg_replace('/\R+/m', '', $gitIgnoreContent) === '') {
      $fs->remove($targetGitIgnore);
    }
    else {
      $fs->dumpFile($targetGitIgnore, $gitIgnoreContent);
    }

    $this->addMessage($this->getMessageText('removed'));
  }

  /**
   *
   * @param string $verb
   *
   *
   * @return string
   *   Install or uninstall message text.
   */
  private function getMessageText(string $verb): string {
    $message = implode("\n  - /", [
      "The following configuration files have been $verb or modified:",
      Config::TARGET_VM_FILENAME,
      Config::TARGET_CONFIG_FILENAME,
      Config::TARGET_GITIGNORE,
    ]);
    $message .= "\n<comment>Do not forget to commit them!</comment>";

    return $message;
  }

}
