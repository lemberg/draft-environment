# Host name, IP address and extra domains

Draft environment automatically manages hosts file on guest and host machines. It relies on [Vagrant Host Manager](https://github.com/devopsgroup-io/vagrant-hostmanager) plugin (will be automatically installed on host system).

## Host name

Draft uses `vagrant.hostname` variable for constructing default domain: `http://<vagrant.hostname>.test`. This fact imposes restrictions on the possible value for the variable, which must be a valid domain name:

  * Allowed characters: lowercase letters (a-z), numbers (0-9), period (.) and
    dash (-)
  * Should not start or end with dash (-) or dot (.) (e.g. -google- or .apple.)
  * Should be between 3 and 63 characters long

Draft Environment performs validation for you upon package installation. If somehow `vagrant.hostname` contains invalid domain name value, `vagrant up` will fail with an error.

## IP address

IP address for each VM is determined as a function of hostname. Assigned IP address will be in the range of 10.10.1.2 - 10.10.255.255, i.e. skip 0, 1 and 256. When running a lot (I mean **a lot**) of VMs simultaneously one can run into IP address conflict, but this scenario is not likely.

## Extra domain (a.k.a. domain aliases)

Domain aliases can be specified by setting `vagrant.host_aliases` (see [default.vm-settings.yml](/default.vm-settings.yml#L26)) variable. It expects an array of values, i.e. multiple aliases can be used.

**IMPORTANT. Vagrant Host Manager DOES NOT update hosts file(s) on `vagrant reload`. Run `vagrant hostmanager` when aliases have been changed.**
