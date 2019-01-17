# Draft Environment

[![Build Status](https://travis-ci.org/lemberg/draft-environment.svg?branch=3.0.0-beta3)](https://travis-ci.org/lemberg/draft-environment)

This is Vagrant-based development environment for Drupal projects. This project is a part of a [Draft](https://github.com/lemberg/draft-template) Drupal project template.

## Prerequisites

- PHP and Composer
- Vagrant (2.1.2+)
- VirtualBox

## Vagrant plugins (will be automatically installed)

#### Vagrant Host Manager

Manages host and/or guest `hosts` files. Draft is configured to create a `hostname.test` DNS record on a host machine.

#### vagrant-vbguest

Automatically installs the host's VirtualBox Guest Additions on the guest system.

#### Vagrant WinNFSd (WINDOWS only)

Dramatically increases disk IO on Windows by adding NFS support.

## How to

1. Add `post-install-cmd` and `post-update-cmd` events handler `Lemberg\\Draft\\Environment\\Configurer::setUp` to the `scripts` property of the root `composer.json` file. Resulting file might look like this:

    ```json
    "scripts": {
        "post-install-cmd": [
            "Lemberg\\Draft\\Environment\\Configurer::setUp"
        ],
        "post-update-cmd": [
            "Lemberg\\Draft\\Environment\\Configurer::setUp"
        ]
    },
    ```

1. Add Draft to the project (as a dev dependency):

    ```
    $ composer require --dev lemberg/draft-environment
    ```

1. Configure guest machine by answering installer's questions. At the moment, project name (hostname) is the only setting that can be set interactively. More to come. Adjust other settings by editing `vm-settings.yml` manually

1. Create and configure guest machine:

    ```
    $ vagrant up
    ```

1. Override any variable used in any Ansible role by including it in the `vm-settings.yml`. For details see [default.vm-settings.yml](/default.vm-settings.yml)

    Here's the list of used roles and available variables (and their default values):

    - [draft (internal)](/provisioning/playbooks/roles/draft/defaults/main.yml)
    - [oefenweb.swapfile](https://github.com/Oefenweb/ansible-swapfile/blob/v2.0.7/defaults/main.yml)
    - [geerlingguy.mailhog](https://github.com/geerlingguy/ansible-role-mailhog/blob/2.1.4/defaults/main.yml)
    - git_config (internal)
    - [apache2 (internal)](/provisioning/playbooks/roles/apache2/defaults/main.yml)
    - mysql (internal)
    - [T2L.php](https://github.com/T2L/ansible-role-php/blob/1.1.2/defaults/main.yml)
    - [T2L.composer](https://github.com/T2L/ansible-role-composer/blob/2.0.2/defaults/main.yml)
    - [T2L.java](https://github.com/T2L/ansible-role-java/blob/1.1.0/defaults/main.yml)
    - [T2L.solr](https://github.com/T2L/ansible-role-solr/blob/2.0.1/defaults/main.yml)

    Some of those variables are already overridden. Find them [here](/provisioning/playbooks/vars).

1. Commit `Vagrantfile` and `vm-settings.yml` to lock the VM state

1. File `vm-settings.yml` is project-specific, not a machine specific. Configuration can be overridden in `vm-settings.local.yml` (and this file must not be committed)

1. Run `vagrant ssh`. Project is located in `/var/www/draft` by default (see [default.vm-settings.yml](/default.vm-settings.yml#L28))

## Documentation

Everybody loves documentation. We do too! [Check this out](/docs).

## Changelog

Changelog can be found here [CHANGELOG.md](/CHANGELOG.md)

## Upgrade

Upgrade instructions can be found here [UPGRADE.md](/UPGRADE.md)
