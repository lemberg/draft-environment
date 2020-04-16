## Draft Environment 3.x.x (unreleased)

Updates:

- [GH-161](https://github.com/lemberg/draft-environment/issues/161) - Updated Ansible MySQL role to 3.0.0
- [GH-159](https://github.com/lemberg/draft-environment/issues/159) - Upgraded Molecule framework (2 => 3)
- [GH-157](https://github.com/lemberg/draft-environment/issues/157) - Switched to unofficial PPA for Vagrant on Travis; Vagrant 2.2.7 supports VirtualBox 6.1.x

Fixes:

- [GH-172](https://github.com/lemberg/draft-environment/issues/172) - Ensure that composer.json is not broken after running updates; remove Configurer:setUp listener from all events
- [GH-168](https://github.com/lemberg/draft-environment/issues/168) - Ansible role geerlingguy.mysql @ 3.0.0 was failing to install due to incorrect python configuration in certain cases (fixed by setting `ansible_python_interpreter` to `/usr/bin/python3`

## Draft Environment 3.0.0-rc2, 2020-02-12

New features:

- [GH-142](https://github.com/lemberg/draft-environment/issues/142) - Added trusted dev SSL certificates support (requires [mkcert](https://mkcert.dev))

Updates:

- [GH-152](https://github.com/lemberg/draft-environment/issues/152) - Updated Ansible Solr role to 2.1.1

Fixes:

- [GH-145](https://github.com/lemberg/draft-environment/issues/145) - Downgrading the package causes PHP fatal error
- [GH-144](https://github.com/lemberg/draft-environment/issues/144) - Export all configuration update step does not preserve all overridden values
- [GH-147](https://github.com/lemberg/draft-environment/issues/147) - Settings are not being saved in extra section of the package in composer.lock
- [GH-143](https://github.com/lemberg/draft-environment/issues/143) - Remove composer scripts update step does not reset script indexes

## Draft Environment 3.0.0-rc1, 2020-01-18

- [GH-90](https://github.com/lemberg/draft-environment/issues/90) - Replaced internal MySQL role with [geerlingguy.mysql @ 2.9.5](https://github.com/geerlingguy/ansible-role-mysql) Ansible role
- [GH-83](https://github.com/lemberg/draft-environment/issues/83) - Added configuration update manager
- [GH-119](https://github.com/lemberg/draft-environment/issues/119) - Updated all project dependencies, including supported Ansible version (2.9), Composer packages and Ansible Roles:
    * oefenweb.swapfile (v2.0.7 => v2.0.24)
    * geerlingguy.mailhog (2.1.4 => 2.2.0)
    * T2L.php (1.1.2 => 1.2.1)
    * T2L.java (1.1.0 => 1.2.0)
    * T2L.solr (2.0.1 => 2.1.0)

    Bump minimum supported PHP version to 7.2

    Bump the following defaults:

    * PHP version: 7.3
    * Solr version: 7.7.2

- [GH-84](https://github.com/lemberg/draft-environment/issues/84) - Exported all available Ansible role variables to the [default.vm-settings.yml](/default.vm-settings.yml)
- [GH-92](https://github.com/lemberg/draft-environment/issues/92) - Added mod_expires and mod_headers for Apache2
- [GH-117](https://github.com/lemberg/draft-environment/issues/117) - Replaced Configurer with Composer event handler
- [GH-94](https://github.com/lemberg/draft-environment/issues/94) - Converted project to a composer-plugin. Clean up Draft Environment configuration files upon package uninstall
- [GH-96](https://github.com/lemberg/draft-environment/issues/96) - Added vagrant-disksize plugin, which allows to alter VirtualBox disk size. By default VirtualBox disk size is capped at 10GB, which is fine for most of the projects, unless project has huge database. Introduced new variable `virtualbox.disk_size` (defaults to `10GB`)
- [GH-104](https://github.com/lemberg/draft-environment/issues/104) - Bump minimum supported Vagrant version to 2.2.6
- [GH-106](https://github.com/lemberg/draft-environment/issues/106) - Converted tests to support Molecule 2

    Side effect: locked Ansible at `2.6.*` (compared to `2.6.6` before): actually, locking to the specific patch version does not make a lot of sense due to deprecations being introduced in major/minor versions only. Locking a patch version does not allow Ansible to update causing more harm than stability
- [GH-102](https://github.com/lemberg/draft-environment/issues/102) - Tune the guest additions time synchronization parameters (force virtual machine to sync time with host)
- [GH-98](https://github.com/lemberg/draft-environment/issues/98) - Follow-up: lock Travis to Ansible 2.6.6

## Draft Environment 3.0.0-beta4, 2019-05-13

- [GH-98](https://github.com/lemberg/draft-environment/issues/98) - Fix broken Travis CI; update Vagrant to version 2.2.4 on Travis CI
- [GH-82](https://github.com/lemberg/draft-environment/issues/82) - Ensure that PasswordAuthentication and ChallengeResponseAuthentication are enabled. See https://serverfault.com/questions/98289/ssh-doesnt-ask-for-password-gives-permission-denied-immediately
- [GH-81](https://github.com/lemberg/draft-environment/issues/81) - Fixed Ansible warning:

    ```
     [WARNING]: The input password appears not to have been hashed. The 'password'
    argument must be encrypted for this module to work properly.
    ```
- [GH-80](https://github.com/lemberg/draft-environment/issues/80) - Fixed Ansible warning:

    ```
    [DEPRECATION WARNING]: Invoking "apt" only once while using a loop via
    squash_actions is deprecated. Instead of using a loop to supply multiple items
    and specifying `name: "{{ item }}"`, please use `name: ['package-name']` and
    remove the loop. This feature will be removed in version 2.11. Deprecation
    warnings can be disabled by setting deprecation_warnings=False in ansible.cfg.
    ```
- [GH-86](https://github.com/lemberg/draft-environment/issues/86) - Amended post `vagrant up` message in order to make it more readable

## Draft Environment 3.0.0-beta3, 2018-10-26

- Updated roles:
    * Apache Solr (T2L.solr: 2.0.1)
- Fixed Ansible warning:

    ```
     [WARNING]: Module remote_tmp /root/.ansible/tmp did not exist and was created
    with a mode of 0700, this may cause issues when running as another user. To
    avoid this, create the remote_tmp dir with the correct permissions manually
    ```

## Draft Environment 3.0.0-beta1/3.0.0-beta2, 2018-10-24

- Use Ubuntu 16.04 LTS (Xenial Xerus) as default OS
- Add option to disable automatic Vagrant box update checking: `vagrant.box_check_update` (disabled by default)
- Updated roles:
    * Java (T2L.java: 1.1.0)
    * Apache Solr (T2L.solr: 2.0.0)
    * Mailhog (T2L.mailhog: 2.1.4)
- Switch to Ansible Local provisioner and lock Ansible version
- Introduced new variable `ansible.version` that controls Ansible version
- Replaced kamaln7.swapfile role with oefenweb.swapfile@v2.0.7

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
