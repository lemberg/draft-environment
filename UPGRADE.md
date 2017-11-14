# UPGRADE

## 1.x.x -> 2.0.x

TBD

## 2.0.x -> 2.1.x

1. Draft environment writes some error logs to `<vagrant.base_directory>/logs` folder. This folder is synchronized with host system as `<root_of_the_project>/logs`. Add `/logs` to the project's root `.gitignore` file in order to exclude log files from being accidentally committed

1. Remove globally installed drush/drush composer package by running `composer global remove drush/drush` in order to avoid conflicts with a local Drush version (if added as a dependency in project's `composer.json`)
