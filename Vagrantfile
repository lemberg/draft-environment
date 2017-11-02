# -*- mode: ruby -*-
# vi: set ft=ruby :

# Before we start: auto-install recommended plugins. Code borrowed here:
# https://stackoverflow.com/a/28801317
# Install Vagrant Host Manager and vagrant-vbguest.
required_plugins = %w(vagrant-hostmanager vagrant-vbguest)

# Additionally install Vagrant WinNFSd on Windows hosts.
require "rbconfig"
if (RbConfig::CONFIG["host_os"] =~ /cygwin|mswin|mingw|bccwin|wince|emx/)
  required_plugins.push('vagrant-winnfsd')
end

plugins_to_install = required_plugins.select { |plugin| not Vagrant.has_plugin? plugin }
if not plugins_to_install.empty?
  puts "Installing plugins: #{plugins_to_install.join(' ')}"
  if system "vagrant plugin install #{plugins_to_install.join(' ')}"
    exec "vagrant #{ARGV.join(' ')}"
  else
    abort "Installation of one or more plugins has failed. Aborting."
  end
end

# Some features used in this configuration file require specific version of
# Vagrant.
Vagrant.require_version ">= 1.8.0"

# Vagrant API version.
VAGRANTFILE_API_VERSION = "2"

# Set base path.
VM_BASE_PATH = File.dirname(__FILE__)

# Draft package base path.
DRAFT_BASE_PATH = File.dirname(__FILE__)

# Project base path.
PROJECT_BASE_PATH = DRAFT_BASE_PATH unless defined? PROJECT_BASE_PATH

# Include configuration.
require "#{VM_BASE_PATH}/helpers/configuration"

# Initialize configuration.
#
# Configuration settings are being loaded from YAML file(s).
#
# Default configuration is being stored in vm-settings.yml.
# Local overrides should be placed in vm-settings.local.yml.
#
# Settings are being merged recursively. Values from local settings file
# overwrites ones from the default settings; missing values are not being touch;
# new values will be added to the resulting settings hash.
configuration = Configuration.new(PROJECT_BASE_PATH, DRAFT_BASE_PATH)

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  # Boxes
  #
  # Box is a package format for Vagrant environments. See more at
  # http://docs.vagrantup.com/v2/boxes.html
  #
  # HashiCorp provides publicly available list of Vagrant boxes at
  # https://atlas.hashicorp.com/boxes/search

  # Set box and box version.
  config.vm.box = configuration.get("vagrant.box")
  config.vm.box_version = configuration.get("vagrant.box_version")

  # Networking
  #
  # By default Vagrant doesn't require explicit network setup. However, in
  # various situations this action is mandatory.
  #
  # See https://docs.vagrantup.com/v2/networking/index.html

  # Set machine's hostname.
  config.vm.hostname = configuration.get("vagrant.hostname")
  # Set VM name.
  config.vm.define configuration.get("virtualbox.name")

  # Hosts records
  #
  # Configure entries in a hosts file. All users are encouraged to install
  # vagrant-hostmanager plugin by running:
  #
  # vagrant plugin install vagrant-hostmanager

  # Record HOSTNAME.test will be created.
  if Vagrant.has_plugin?("vagrant-hostmanager")
    config.hostmanager.enabled = true
    config.hostmanager.manage_host = true
  end

  # Network File System (NFS) requires private network to be specified when
  # VirtualBox is used (due to a limitation of VirtualBox's built-in networking)
  #
  # See http://docs.vagrantup.com/v2/synced-folders/nfs.html
  config.vm.network "private_network", ip: configuration.get("vagrant.ip_address")

  # SSH settings
  #
  # See https://docs.vagrantup.com/v2/vagrantfile/ssh_settings.html

  # Enable SSH agent forwarding
  config.ssh.forward_agent = true

  # Fix annoying "stdin: is not a tty" error.
  #
  # See https://github.com/mitchellh/vagrant/issues/1673#issuecomment-40278692
  config.ssh.shell = "bash -c 'BASH_ENV=/etc/profile exec bash'"

  # VirtualBox configuration
  #
  # VirtualBox allows for some additional virtual machine tuning. List of
  # available options can be found here: http://www.virtualbox.org/manual/ch08.html
  #
  # See https://docs.vagrantup.com/v2/virtualbox/configuration.html

  # Tune VirtualBox powered machine.
  config.vm.provider :virtualbox do |v|
    # Use linked clones instead of importing the base box every time.
    #
    # See https://www.vagrantup.com/docs/virtualbox/configuration.html#linked-clones
    v.linked_clone = true
    # Set VM name.
    v.name = configuration.get("virtualbox.name")
    # Set CPUs count.
    v.cpus = configuration.get("virtualbox.cpus")
    # Set memory limit (in MB).
    v.memory = configuration.get("virtualbox.memory")
    # Set CPU execution cap (in %).
    v.customize ["modifyvm", :id, "--cpuexecutioncap", configuration.get("virtualbox.cpuexecutioncap")]
    # Use host's resolver mechanisms to handle DNS requests.
    v.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    # Allow creation of symlinks in VirtualBox shared folders (works with both
    # VirtualBox shared folders and NFS).
    v.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate/" + configuration.get("vagrant.base_directory"), "1"]
  end

  # Synced Folders
  #
  # See https://docs.vagrantup.com/v2/synced-folders/index.html

  # NFS sync method is much faster than others. It's not supported on Windows
  # hosts by Vagrant itself, but there is a Vagrant plugin entitled "Vagrant
  # WinNFSd" aimed to resolve this issue.
  #
  # Windows users are encouraged to install vagrant-winnfsd plugin by running:
  #
  # vagrant plugin install vagrant-winnfsd
  #
  # See https://docs.vagrantup.com/v2/synced-folders/nfs.html

  # Configure synched folders.
  config.vm.synced_folder ".", configuration.get("vagrant.base_directory"), create: true, type: "nfs"

  # Provisioning
  #
  # See https://docs.vagrantup.com/v2/provisioning/index.html

  # IMPORTANT. Vagrant has an issue with Shell provisioner (described here
  # https://github.com/mitchellh/vagrant/issues/1673). To avoid annoying
  # "stdin: is not a tty" and/or "dpkg-reconfigure: unable to re-open stdin: No
  # file or directory" error messages, stdout and sterr have been redirected
  # to /dev/null. See provisioning/windows.sh

  # Get correct path to the Ansible playbook.
  require 'pathname'
  project_pathname = Pathname.new PROJECT_BASE_PATH
  vm_pathname = Pathname.new VM_BASE_PATH

  # Pass configuration to Ansible.
  require 'json'
  settings = configuration.getConfiguration()

  # Run Ansible provisioner from within the virtual machine using proxy shell
  # script so the developer experience is the same on all platforms (this means
  # there is no need to install Ansible and playbook's dependencies on the host
  # operating system).
  config.vm.provision "shell" do |shell|
    shell.path = "#{VM_BASE_PATH}/provisioning/ansible.sh"
    shell.args = [File.join(configuration.get("vagrant.base_directory"), vm_pathname.relative_path_from(project_pathname), "/provisioning/playbooks"), settings.to_json]
  end

  # Display an informational message to the user.
  config.vm.post_up_message = "The app is running at http://" + configuration.get("vagrant.ip_address") + " and http://" + configuration.get("vagrant.hostname")

end
