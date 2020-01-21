# Draft Environment 3.x.x

![Packagist](https://img.shields.io/packagist/dm/lemberg/draft-environment)
![Build Status](https://img.shields.io/travis/lemberg/draft-environment/3.x.x)
![Codecov](https://img.shields.io/codecov/c/github/lemberg/draft-environment)

This is Vagrant-based development environment for Drupal projects. This project is a part of a [Draft](https://github.com/lemberg/draft-template) Drupal project template.

## Prerequisites

- PHP (7.2+) and Composer
- Vagrant (2.2.6+)
- VirtualBox (5.2+)

## Vagrant plugins (will be automatically installed)

#### [Vagrant Host Manager](https://github.com/devopsgroup-io/vagrant-hostmanager)

Manages host and/or guest `hosts` files. Draft is configured to create a `hostname.test` DNS record on a host machine.

#### [vagrant-vbguest](https://github.com/dotless-de/vagrant-vbguest)

Automatically installs the host's VirtualBox Guest Additions on the guest system.

#### [vagrant-disksize](https://github.com/sprotheroe/vagrant-disksize)

A Vagrant plugin to resize disks in VirtualBox.

#### [Vagrant WinNFSd (WINDOWS only)](https://github.com/winnfsd/vagrant-winnfsd)

Dramatically increases disk IO on Windows by adding NFS support.

## How to

1. Add Draft Environment to the project (as a dev dependency):

    ```
    $ composer require --dev lemberg/draft-environment
    ```

1. Configure guest machine by answering installer's questions. At the moment, project name (hostname) and PHP version are the only settings that can be set interactively

1. Override any variable used in any Ansible role by manually editing the `vm-settings.yml`. All available variables alongside with their default values are listed in [default.vm-settings.yml](/default.vm-settings.yml)

  Here's the list of used roles:

  - draft (internal)
  - [oefenweb.swapfile @ v2.0.24](https://github.com/Oefenweb/ansible-swapfile/tree/v2.0.24)
  - [geerlingguy.mailhog @ 2.2.0](https://github.com/geerlingguy/ansible-role-mailhog/tree/2.2.0)
  - git_config (internal)
  - apache2 (internal)
  - [geerlingguy.mysql @ 2.9.5](https://github.com/geerlingguy/ansible-role-mysql/tree/2.9.5)
  - [T2L.php @ 1.2.1](https://github.com/T2L/ansible-role-php/tree/1.2.1)
  - [T2L.composer @ 2.0.2](https://github.com/T2L/ansible-role-composer/tree/2.0.2)
  - [T2L.java @ 1.2.0](https://github.com/T2L/ansible-role-java/tree/1.2.0)
  - [T2L.solr @ 2.1.1](https://github.com/T2L/ansible-role-solr/tree/2.1.1)

1. Create and provision the guest machine:

    ```
    $ vagrant up
    ```

1. Commit `.gitignore`, `Vagrantfile` and `vm-settings.yml` to lock the VM state

1. Configuration can be overridden locally by creating and editing `vm-settings.local.yml` (and this file must not be committed)

## Documentation

Everybody loves documentation. We do too! [Check this out](/docs).

## Changelog

Changelog can be found here [CHANGELOG.md](/CHANGELOG.md)
