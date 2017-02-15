<?php

namespace Draft\Environment;

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
  public static function setUpProject(Event $event) {
    $package = $event
        ->getOperation()
        ->getPackage();
    $installPath = $event
        ->getComposer()
        ->getInstallationManager()
        ->getInstallPath($package);

    if (!file_exists("$installPath/settings.yml")) {
      $parser = new Parser();
      $config = $parser->parse(file_get_contents("$installPath/default.settings.yml"));
      $config['vagrant']['hostname'] = $event->getIO()->ask('Please specify project name (lowercase letters, numbers, and underscores): ', 'default');

      $yaml = new Dumper();
      $yaml->setIndentation(2);
      file_put_contents("$installPath/settings.yml", $yaml->dump($config, PHP_INT_MAX));
    }
  }
}
