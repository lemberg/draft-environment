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
      version: 2.9.*
    ```

- Add new variable `virtualbox.disk_size` to the `vm-settings.yml` file in order to set VirtualBox disk size:

    ```
    virtualbox:
      disk_size: 10Gb
    ```

- If `solr_cores` variable was overwritten, them convert it to the new format, see [UPGRADE.md 1.x.x -> 2.0.x](https://github.com/T2L/ansible-role-solr/blob/2.0.0/UPGRADE.md#1xx---20x)

- Remove `Lemberg\\Draft\\Environment\\Configurer::setUp` from your `composer.json` scripts section

- `ggerlingguy.mailhog@2.2.0` role has introduced new variables containing mailhog and mhsendmail versions. Convert your `mailhog_binary_url` and `mhsendmail_binary_url` variables to reflect this change. See the [original commit](https://github.com/geerlingguy/ansible-role-mailhog/commit/d8e1c265820c374b7fa772f5b8f450364b1e13a7)

## Then run

```
vagrant reload --provision
```
