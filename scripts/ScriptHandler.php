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
    if (!file_exists('./settings.yml')) {
      $parser = new Parser();
      $config = $parser->parse(file_get_contents('./default.settings.yml'));
      $config['vagrant']['hostname'] = $event->getIO()->ask('Please specify project name (lowercase letters, numbers, and underscores): ', 'default');

      $yaml = new Dumper();
      $yaml->setIndentation(2);
      file_put_contents('./settings.yml', $yaml->dump($config, PHP_INT_MAX));
    }
  }
}
