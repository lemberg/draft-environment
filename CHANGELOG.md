## Draft Environment 3.x.x

- Use Ubuntu 16.04 LTS (Xenial Xerus) as default OS

## Draft Environment 2.5.0, 2018-03-15

- Set dependency on symfony/yaml to `^2.8|^3.2` so Drupal 8.5.x can be installed

## Draft Environment 2.4.0, 2018-01-23

- Drop setting a timezone
- Draft virtual host now support SSL (with self-signed certificate)

## Draft Environment 2.3.0, 2017-12-27

- Set SSH default directory via `ssh_default_directory` variable (defaults to value of `vagrant.base_directory` variable, which is `/var/www/draft` by default). Yeah too many defaults here
- Move pre_tasks from Ansible playbooks (main and test) to internal draft role

## Draft Environment 2.2.0, 2017-12-08

- Added roles:
    * MailHog (geerlingguy.mailhog: 2.1.3)
- Add MailHog - email testing tool and configured it. See [the docs](docs/mailhog.md)

## Draft Environment 2.1.1, 2017-11-20

- Move logs to `/var/log/draft` so Apache can start on machine boot

## Draft Environment 2.1.0, 2017-11-14

- Removed roles:
    * Composer global packages (T2L.composer-global-packages)
- Drop support of global Composer packages. Project must list all of its dependencies in composer.json file. Composer bin directory will be added to the system $PATH variable
- Allow creation of symbolic links in shared folders
- Allow setting of synced folder options via `vagrant.synced_folder_options` variable
- Add domain aliases via `vagrant.host_aliases` variable (defaults to empty array)
- Write error logs into `<vagrant.base_directory>/logs`

## Draft Environment 2.0.2, 2017-10-12

- Look for default.vm-settings.yml in package directory, not in project one

## Draft Environment 2.0.1, 2017-10-11

- Set dependency on symfony/yaml to ~3.2.8 so stable Drupal core can be used

## Draft Environment 2.0.0, 2017-10-11

- Default project location is changed from `/var/www/defalt.localhost` to `/var/www/draft`
- Ability to specify base directory (in guest OS) and web server document root. See [the docs](docs/base_directory_and_document_root.md)
- Add support of Drupal 8.4 (updated dependency on symfony/yaml to ~3.2)
- Added creation of default MySQL database and user (db: drupal, user: drupal, pass: drupal)
- Ability to specify what features will be installed
- Install recommended Vagrant plugins automatically
- Validate project name (must be a valid domain name)
- Updated roles:
    * PHP (T2L.php: 1.1.1)
    * Composer (T2L.composer: 2.0.2)
    * Composer Global Packages (T2L.composer-global-packages: 2.0.2)
- Added roles:
    * Java (T2L.java: 1.0.1)
    * Apache Solr (T2L.solr: 1.2.0)
- Updated PHP role allows configuration of PHP extensions which uses own ini files. Say hello to xdebug!
