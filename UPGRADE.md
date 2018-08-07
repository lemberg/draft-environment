## Execute all steps with higher version than one that is being used

### 2.0.0

TBD

### 2.1.0

- Remove globally installed drush/drush composer package by running `composer global remove drush/drush` in order to avoid conflicts with a local Drush version (if added as a dependency in project's `composer.json`)

### 2.2.0

- Add `mailhog` to the `draft_features` variable in your `vm-settings.yml` in order to get MailHog set up. More details about MailHog is [here](/docs/mailhog.md)

### 2.6.0

- Destroy existing VM by running
    
    ```
    vagrant destroy -f
    ```

- Update guest OS by editing `vm-settings.yml` file:

    ```
    box: "ubuntu/xenial64"
    box_version: ">=0"
    ```
    
- Run and provision new VM
    
    ```
    vagrant up
    ```

## Then run

```
vagrant reload --provision
```
