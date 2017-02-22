<?php

namespace Lemberg\Draft\Environment;

use Composer\Script\Event;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

class ScriptHandler {

  /**
   * Sets up new project environment.
   *
   * @param \Composer\Script\Event $event
   *   The script event.
   */
  public static function setUp(Event $event) {
    $composer = $event->getComposer();

    // This package can be utilized as a root package (for example in Travis CI).
    if ($composer->getPackage()->getName() === 'lemberg/draft-environment') {
      $installPath = '.';
    }
    else {
      // Use Composer's local repository to find the path to Draft Environment.
      $packages = $composer
          ->getRepositoryManager()
          ->findPackage('lemberg/draft-environment');

      if ($packages) {
        $installPath = $composer
            ->getInstallationManager()
            ->getInstallPath($packages[0]);
      }
      else {
        throw new \RuntimeException('lemberg/draft-environment package not found in local repository.');
      }
    }

    // Assume VM settings has already been set.
    if (!file_exists("./vm-settings.yml")) {
      $parser = new Parser();
      $config = $parser->parse(file_get_contents("$installPath/default.vm-settings.yml"));
      $config['vagrant']['hostname'] = $event->getIO()->ask('Please specify project name (lowercase letters, numbers, and underscores): ', 'default');

      $yaml = new Dumper();
      $yaml->setIndentation(2);
      file_put_contents("./vm-settings.yml", $yaml->dump($config, PHP_INT_MAX));
    }

    // Assume Vagrantfile has already been configured.
    if (!file_exists("./Vagrantfile")) {
      $vendor_dir = $composer->getConfig()->get('vendor-dir');

      if ($vendor_dir !== 'vendor') {
        $vagrantfile = file_get_contents("$installPath/Vagrantfile.proxy");
        $vagrantfile = str_replace('/vendor/', "/$vendor_dir/", $vagrantfile);
        file_put_contents("./Vagrantfile", $vagrantfile);
      }
      else {
        copy("$installPath/Vagrantfile.proxy", "./Vagrantfile");
      }
    }
  }

}
