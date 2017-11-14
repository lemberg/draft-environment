<?php

namespace Lemberg\Draft\Environment;

use Composer\Config;
use Composer\Script\Event;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

class Configurer {

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
      $package = $composer
          ->getRepositoryManager()
          ->getLocalRepository()
          ->findPackage('lemberg/draft-environment', '*');

      if ($package) {
        $installPath = $composer
            ->getInstallationManager()
            ->getInstallPath($package);
      }
      else {
        throw new \RuntimeException('lemberg/draft-environment package not found in local repository.');
      }
    }

    // Assume VM settings has already been set.
    if (!file_exists("./vm-settings.yml")) {
      $io = $event->getIO();

      $parser = new Parser();
      $config = $parser->parse(file_get_contents("$installPath/default.vm-settings.yml"));

      // Set project name.
      $project_name_question = <<<HERE
Please specify project name. Must be valid domain name:
  - Allowed characters: lowercase letters (a-z), numbers (0-9), period (.) and
    dash (-)
  - Should not start or end with dash (-) (e.g. -google-)
  - Should be between 3 and 63 characters long

Project name
HERE;
      $default_project_name = 'default-' . time();
      $config['vagrant']['hostname'] = $io->askAndValidate(static::addQuestionMarkup($project_name_question, $default_project_name), [__CLASS__, 'validateProjectName'], NULL, $default_project_name);

      // Set timezone.
      $timezone_question = 'Please specify valid timezone identifier, like "Europe/Kiev"';
      $default_timezone = static::getSystemTimezone();
      $config['vagrant']['timezone'] = $io->askAndValidate(static::addQuestionMarkup($timezone_question, $default_timezone), [__CLASS__, 'validateTimezone'], NULL, $default_timezone);

      $io->write('');
      $io->write('<info>Now you can make some coffee. It won\'t take too long though. Just relax and run</info> <comment>vagrant up</comment>');
      $io->write('<info>Project will be available at</info> <comment>http://' . $config['vagrant']['hostname'] . '.test</comment> <info>after provisioning</info>');
      $io->write('<info>Happy coding!</info>');
      $io->write('');

      $yaml = new Dumper();
      $yaml->setIndentation(2);
      file_put_contents("./vm-settings.yml", $yaml->dump($config, PHP_INT_MAX));
    }

    // Assume Vagrantfile has already been configured.
    if (!file_exists("./Vagrantfile")) {
      $vendor_dir = trim($composer->getConfig()->get('vendor-dir', Config::RELATIVE_PATHS), DIRECTORY_SEPARATOR);
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

  /**
   * Validates that given value is a valid project name.
   *
   * @param string $value
   *   Project name.
   *
   * @throws \UnexpectedValueException
   *   When project name is not valid.
   */
  public static function validateProjectName($value) {
    if (!preg_match('/^[a-z0-9][a-z0-9-]{1,61}[a-zA-Z0-9]$/', $value)) {
      throw new \UnexpectedValueException('Specified value is not a valid project name. Please try again');
    }

    return $value;
  }

  /**
   * Validates that given value is a valid project name.
   *
   * @param string $value
   *   Timnezone.
   *
   * @throws \UnexpectedValueException
   *   When timezone is not valid.
   */
  public static function validateTimezone($value) {
    if (!in_array($value, timezone_identifiers_list())) {
      throw new \UnexpectedValueException('Specified value is not a valid timezone. Please try again');
    }

    return $value;
  }

  /**
   * Get system timezone.
   *
   * PHP 5.4 has removed the autodetection of the system timezone,
   * so it needs to be done manually.
   * Fallback to timezone in php.ini in case autodetection fails.
   */
  public static function getSystemTimezone() {
    $timezone = date_default_timezone_get();
    if (is_link('/etc/localtime')) {
      // Mac OS X (and older Linuxes)
      // /etc/localtime is a symlink to the timezone in /usr/share/zoneinfo.
      $filename = readlink('/etc/localtime');
      if (strpos($filename, '/var/db/timezone/zoneinfo/') === 0) {
        $timezone = substr($filename, 26);
      }
    }
    elseif (file_exists('/etc/timezone')) {
      // Ubuntu/Debian.
      $data = file_get_contents('/etc/timezone');
      if ($data) {
        $timezone = trim($data);
      }
    }
    elseif (file_exists('/etc/sysconfig/clock')) {
      // RHEL/CentOS
      $data = parse_ini_file('/etc/sysconfig/clock');
      if (!empty($data['ZONE'])) {
        $timezone = trim($data['ZONE']);
      }
    }
    return $timezone;
  }

  /**
   * Adds markup to the given question.
   *
   * @param string $question
   *   Question raw text.
   *
   * @return string
   *   Question with markup.
   */
  protected static function addQuestionMarkup($question, $default_value = NULL) {
    if ($default_value) {
      return $question . ' <question>[' . $default_value . ']</question> ';
    }
    else {
      return $question . ' ';
    }
  }

}
