# UPGRADE

### 1.x.x -> 2.0.x

TBD

### 2.0.x -> 2.1.x

1. Remove globally installed drush/drush composer package by running `composer global remove drush/drush` in order to avoid conflicts with a local Drush version (if added as a dependency in project's `composer.json`)

### 2.1.x -> 2.2.x

1. Add `mailhog` to the `draft_features` variable in your `vm-settings.yml` in order to get MailHog set up. More details about MailHog is [here](/docs/mailhog.md)

### After completing steps from the above run

```
vagrant reload --provision
```
