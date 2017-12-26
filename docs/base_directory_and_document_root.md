# Base directory and web server document root

Draft environment allows setting of base project directory (in guest OS) and web server document root.

## How to

Base project directory can be set by overriding `vagrant.base_directory` variable (defaults to `/var/www/draft`). See [default.vm-settings.yml](/default.vm-settings.yml#L23).

Web server document root can be specified by overriding `apache2_document_root` variable (defaults to `docroot`). See [Apache2 role defaults](/provisioning/playbooks/roles/apache2/defaults/main.yml).

SSH default directory can be set by overriding `ssh_default_directory` variable (defaults to value of `vagrant.base_directory` variable, which is `\var\www\draft` by default :) ). See [Project role defaults](/provisioning/playbooks/roles/project/defaults/main.yml). Yeah too many defaults here.

## Synced folder options

Sometimes default sync folder options needs to be changed. This can be done by setting/amending `vagrant.synced_folder_options` variable in `vm-settings.yml` file. Default options:

```
vagrant:
  synced_folder_options:
    type: nfs
    create: true
```

Find list of available options in [Vagrant's docs](https://www.vagrantup.com/docs/synced-folders/basic_usage.html#options). In addition to these options, the specific synced folder type might allow more options. See the [documentation for NFS](https://www.vagrantup.com/docs/synced-folders/nfs.html#nfs-synced-folder-options) synced folder type for more details.
