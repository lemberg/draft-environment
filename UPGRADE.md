## Execute all steps with higher version than one that is being used

### 2.0.0

TBD

### 2.1.0

- Remove globally installed drush/drush composer package by running `composer global remove drush/drush` in order to avoid conflicts with a local Drush version (if added as a dependency in project's `composer.json`)

### 2.2.0

- Add `mailhog` to the `draft_features` variable in your `vm-settings.yml` in order to get MailHog set up. More details about MailHog is [here](/docs/mailhog.md)

### 3.0.0

- Destroy existing VM by running (all data will be lost)

    ```
    vagrant destroy -f
    ```

- Update guest OS by editing `vm-settings.yml` file:

    ```
    vagrant:
      box: "ubuntu/xenial64"
      box_version: ">=0"
    ```

- Add new variable `vagrant.box_check_update` to the `vm-settings.yml` file in order to get rid of the "box is out of date" message:

    ```
    vagrant:
      box_check_update: false
    ```
- Add new variable `ansible.version` to the `vm-settings.yml` file in order to lock supported Ansible version:

    ```
    ansible:
      version: 2.6.*
    ```

- If `solr_cores` variable was overwritten, them convert it to the new format, see [UPGRADE.md 1.x.x -> 2.0.x](https://github.com/T2L/ansible-role-solr/blob/2.0.0/UPGRADE.md#1xx---20x)

## Then run

```
vagrant reload --provision
```
