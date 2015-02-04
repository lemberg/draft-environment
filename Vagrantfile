# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrant API version.
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  # Boxes
  #
  # Box is a package format for Vagrant environments. See more at
  # http://docs.vagrantup.com/v2/boxes.html
  #
  # HashiCorp provides publicly available list of Vagrant boxes at
  # https://atlas.hashicorp.com/boxes/search

  # Official Ubuntu Server 14.04 LTS (Trusty Tahr).
  config.vm.box = "ubuntu/trusty64"
  config.vm.box_version = "14.04"

  # Networking
  #
  # By default Vagrant doesn't require explicit network setup. However, in
  # various situations this action is mandatory.
  # See https://docs.vagrantup.com/v2/networking/index.html

  # Network File System (NFS) requires private network to be specified when
  # VirtualBox is used (due to a limitation of VirtualBox's built-in networking)
  # See http://docs.vagrantup.com/v2/synced-folders/nfs.html
  config.vm.network "private_network", ip: "192.168.192.168"

  # SSH settings
  #
  # See https://docs.vagrantup.com/v2/vagrantfile/ssh_settings.html

  # Enable SSH agent forwarding
  config.ssh.forward_agent = true

  # VirtualBox configuration
  #
  # VirtualBox allows for some additional virtual machine tuning. List of
  # available options can be found here: http://www.virtualbox.org/manual/ch08.html
  # See https://docs.vagrantup.com/v2/virtualbox/configuration.html

  # Tune VirtualBox powered machine.
  config.vm.provider :virtualbox do |v|
    # Set CPUs count.
    v.customize ["modifyvm", :id, "--cpus", 2]
    # Set memory limit (in MB).
    v.customize ["modifyvm", :id, "--memory", 1024]
    # Set CPU execution cap (in %).
    v.customize ["modifyvm", :id, "--cpuexecutioncap", 100]
    # Use host's resolver mechanisms to handle DNS requests.
    v.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
  end

  # Synced Folders
  #
  # See https://docs.vagrantup.com/v2/synced-folders/index.html

  # Disable the default "/vagrant" share.
  config.vm.synced_folder ".", "/vagrant", disabled: true

  # NFS sync method is much faster than others. It's not supported on Windows
  # hosts by Vagrant itself, but there is a Vagrant plugin entitled "Vagrant
  # WinNFSd" aimed to resolve this issue. However, at the moment of writing this
  # comment (27.01.2015), plugin has significant issue with some VM OS (like
  # Ubuntu). See https://github.com/GM-Alex/vagrant-winnfsd/issues/27. When the
  # aforementioned issue will be resolved, installation of the plugin would be
  # enforced on Windows hosts. Without WinNFSd plugin Vagrant will fall back to
  # the default VirtualBox folder sync.
  # See https://docs.vagrantup.com/v2/synced-folders/nfs.html
  config.vm.synced_folder ".", "/var/www/vhosts/default", type: "nfs"

end
