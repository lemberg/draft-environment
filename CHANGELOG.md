## Draft Environment 2.x.x

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
