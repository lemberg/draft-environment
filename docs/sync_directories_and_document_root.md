# Sync directories and web server document root

Draft environment allows setting of the source (in the host OS) and the destination (in the guest OS) directories, and web server document root.

## How to

Source directory can be set by overriding `vagrant.source_directory` variable (defaults to `.`). See [default.vm-settings.yml](/default.vm-settings.yml#L28).

Destination directory can be set by overriding `vagrant.destination_directory` variable (defaults to `/var/www/draft`). See [default.vm-settings.yml](/default.vm-settings.yml#L30).

Web server document root can be specified by overriding `apache2_document_root` variable (defaults to `docroot`). See [default.vm-settings.yml](/default.vm-settings.yml#L127).

SSH default directory can be set by overriding `ssh_default_directory` variable (defaults to value of `vagrant.destination_directory` variable, which is `/var/www/draft` by default :) ). See [default.vm-settings.yml](/default.vm-settings.yml#L104).

## Synced folder options

Sometimes default sync folder options needs to be changed. This can be done by setting/amending `vagrant.synced_folder_options` variable in `vm-settings.yml` file. Default options (see [default.vm-settings.yml](/default.vm-settings.yml#L32)):

```
vagrant:
  synced_folder_options:
    type: nfs
    create: true
```

Find list of available options in [Vagrant's docs](https://www.vagrantup.com/docs/synced-folders/basic_usage.html#options). In addition to these options, the specific synced folder type might allow more options. See the [documentation for NFS](https://www.vagrantup.com/docs/synced-folders/nfs.html#nfs-synced-folder-options) synced folder type for more details.
