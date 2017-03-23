# Draft Environment

[![Build Status](https://travis-ci.org/lemberg/draft-environment.svg?branch=1.x.x)](https://travis-ci.org/lemberg/draft-environment)

This is Vagrant-based development environment for Drupal projects. This project is a part of a [Draft](https://github.com/lemberg/draft-template) Drupal project template.

## Prerequisites

- PHP/Composer
- Vagrant
- VirtualBox

## Vagrant plugins (highly recommended)

### Vagrant Host Manager

Manages host and/or guest `hosts` files. Draft is configured to create a `hostname.test` DNS record on a host machine.

Install plugin:

```
$ vagrant plugin install vagrant-hostmanager
```

### Vagrant WinNFSd (WINDOWS only)

Dramatically increases disk IO on Windows by adding NFS support.

Install plugin:

```
$ vagrant plugin install vagrant-winnfsd
```

## Usage

1. Install recommended Vagrant plugins

1. Add `post-install-cmd` and `post-update-cmd` events handler `Lemberg\\Draft\\Environment\\ScriptHandler::setUp` to the `scripts` property of the root `composer.json` file. Resulting file might look like this:

    ```json
    "scripts": {
        "post-install-cmd": [
            "Lemberg\\Draft\\Environment\\ScriptHandler::setUp"
        ],
        "post-update-cmd": [
            "Lemberg\\Draft\\Environment\\ScriptHandler::setUp"
        ]
    },
    ```

1. Add Draft to the project (as a dev dependency): 

   ```
   $ composer require --dev lemberg/draft-environment
   ```

1. Configure guest machine by answering installer's questions. At the moment, project name (hostname) is the only setting that can be set interactively. More to come. Adjust other settings by editing `vm-settings.yml` manually.

1. Create and configure guest machine:

   ```
   $ vagrant up
   ```

1. Commit `Vagrantfile` and `vm-settings.yml` to lock the VM state
