# Base directory and web server document root

Draft environment allows setting of base project directory (in guest OS) and web server document root.

## How to

1. Base directory can be set by overriding `vagrant.base_directory` variable (defaults to `/var/www/draft`). See [default.vm-settings.yml](/default.vm-settings.yml#L23).

1. Web server document root can be specified by overriding `apache2_document_root` variable (defaults to `docroot`). See [Apache2 role defaults](/provisioning/playbooks/roles/apache2/defaults/main.yml).
