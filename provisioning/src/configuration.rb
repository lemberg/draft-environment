# -*- mode: ruby -*-
# vi: set ft=ruby :

require 'yaml'

# Public: Configuration manager for Vagrant.
class Configuration

  # Public: Initialize a Configuration.
  #
  # base_path - String that contains Vagrantfile base path.
  def initialize(project_base_path, draft_base_path)
    self.load_settings(project_base_path, draft_base_path)
    self.merge_default_settings
    self.generate_mkcert_certificate
  end

  # Public: Get configuration setting value by given key.
  #
  # key - String setting key.
  #
  # Returns configuration setting value.
  def get(key)
    setting = @settings
    key.split(".").each do |k|
      setting = setting[k]
    end
    setting
  end

  # Public: Get entire configuration.
  #
  # Returns configuration.
  def getConfiguration()
    @settings
  end

  # Internal: Set configuration setting value by given key.
  #
  # key   - String setting key.
  # value - Value for a given key.
  #
  # Returns nothing.
  protected
  def set(key, value)
    @settings = self.set_value_by_path(@settings, key.split("."), value)
  end

  # Internal: Set hash value by given string key path.
  #
  # hash  - Hash to look for a path.
  # path  - String key path.
  # value - Value for a given key path.
  #
  # Returns hash with new value at a key path.
  protected
  def set_value_by_path(hash, path, value)

    key = path.shift
    if path.length == 0
      hash[key] = value
      return hash
    end

    hash[key] = self.set_value_by_path(hash[key], path, value)
    return hash
  end

  # Internal: Load settings from YAML file(s).
  #
  # base_path - String that contains Vagrantfile base path.
  #
  # Returns nothing.
  protected
  def load_settings(project_base_path, draft_base_path)
    @project_base_path = project_base_path
    @draft_base_path = draft_base_path

    default_settings = YAML::load_file("#{draft_base_path}/default.vm-settings.yml")
    if File.exist?("#{project_base_path}/vm-settings.yml")
      settings = YAML::load_file("#{project_base_path}/vm-settings.yml")
      @settings = self.merge_recursively(default_settings, settings)
    else
      @settings = default_settings
    end
    if File.exist?("#{project_base_path}/vm-settings.local.yml")
      local_settings = YAML::load_file("#{project_base_path}/vm-settings.local.yml")
      unless local_settings.nil?
        @settings = self.merge_recursively(@settings, local_settings)
      end
    end
  end

  # Internal: Merge two Hashes recursively. Values from second Hash overrides
  # values from the first Hash.
  #
  # a - first Hash.
  # b - second Hash.
  #
  # Returns merged Hash.
  protected
  def merge_recursively(a, b)
    a.merge(b) do |key, a_item, b_item|
      if a_item.is_a?(Hash) && b_item.is_a?(Hash)
        self.merge_recursively(a_item, b_item)
      else
        a_item = b_item
      end
    end
  end

  # Internal: Merge default settings into provided configuration.
  #
  # Returns nothing.
  protected
  def merge_default_settings
    # Set hostname if it's not defined.
    if self.get("vagrant.hostname").length == 0
      require 'securerandom'
      self.set("vagrant.hostname", "draft." + Time.now.to_i.to_s)
    end
    # Set VirtualBox machine name to match Vagrant host name if it's empty.
    if self.get("virtualbox.name").length == 0
      self.set("virtualbox.name", self.get("vagrant.hostname"))
    end
    # Generate virtual machine IP address.
    self.generate_ip_address
    # Get active user name and email
    self.get_git_credentials
    # Use *.test domain (RFC 2606).
    self.set("vagrant.hostname", self.get("vagrant.hostname") + '.test');
    # Symbolize synced folder options.
    self.symbolize_synced_folder_options
    # Set correct Ansible version to install when wildcard version constraint
    # has been provided.
    self.set_ansible_version
  end

  # Internal: Generate IP addres based on a Vagrant host name value (in the most
  # cases it contains project name).
  #
  # Returns nothing.
  protected
  def generate_ip_address
    sum = 0
    self.get("vagrant.hostname").each_byte do |byte|
      sum += byte
    end

    # Make sure that IP address parts are in range [2, 255], i.e. skip 0, 1 and
    # 256.
    part_3 = [[sum >> 8, 1].max, 255].min
    part_4 = [[sum % 256, 2].max, 255].min

    self.set("vagrant.ip_address", "10.10.#{part_3}.#{part_4}")
  end

  # Internal: Rehashes synced folders options to make Vagrant happy.
  #
  # Actually YAML parses returns keys as strings, and we need to convert them to
  # symbols.
  #
  # Returns nothing.
  protected
  def symbolize_synced_folder_options
    # Code borrowed here https://stackoverflow.com/a/800498
    symbolized_hash = self.get("vagrant.synced_folder_options").inject({}){|memo,(k,v)| memo[k.to_sym] = v; memo}
    self.set("vagrant.synced_folder_options", symbolized_hash)
  end

  # Internal: Get Git user name and email from the host system.
  #
  # Returns nothing.
  protected
  def get_git_credentials
    self.set("git_user_name", `git config --get user.name`.strip)
    self.set("git_user_email", `git config --get user.email`.strip)
  end

  # Internal: Vagrant doesn't support wildcards in Ansible version, but pip does.
  # This method fixes this by looking up for the latest Ansible version within
  # the given range constraint and setting up exact version to install.
  #
  # Returns nothing.
  protected
  def set_ansible_version
    version = self.get("ansible.version").split(".")
    if (version.length === 3 && version.last === "*")
      require 'net/https'
      uri = URI('https://releases.ansible.com/ansible/')
      available_releases = Net::HTTP.get(uri)

      latest_release = available_releases
        .scan(/>ansible-(#{version[0]}\.#{version[1]}\.\d+[a-zA-Z0-9\-\.]*)\.tar.gz</)
        .flatten
        .sort_by { |v| Gem::Version.new(v) }
        .last

      self.set("ansible.version", latest_release)
    end
  end

  # Internal: Generates SSL certificate and private key using mkcert.
  #
  # Returns nothing.
  protected
  def generate_mkcert_certificate
    # This feature depends on mkcert. See https://mkcert.dev
    `mkcert -CAROOT 2>&1`
    unless $?.success?
      return;
    end

    # Generate new certificate/key pair only on provisioning.
    unless self.is_provisioning
      return;
    end

    require 'tmpdir'
    require 'fileutils'

    domain_names = ([
      self.get("vagrant.ip_address"),
      self.get("vagrant.hostname"),
    ] + self.get("vagrant.host_aliases")).join(" ")

    directory = Dir.tmpdir() + "/mkcert-" + self.get("vagrant.hostname")
    unless File.directory?(directory)
      FileUtils.mkdir_p(directory)
    end

    `mkcert -cert-file #{directory}/draft.crt -key-file #{directory}/draft.key #{domain_names} 2>&1`
    unless $?.success?
      return;
    end

    self.set("mkcert", {})
    self.set("mkcert.directory", directory)
    self.set("mkcert.cert", directory + "/draft.crt")
    self.set("mkcert.key", directory + "/draft.key")

  end

  # Internal: Checks whether provisioning will be running.
  #
  # Returns boolean indicating whether provisioning will be running.
  protected
  def is_provisioning
    if ((ARGV.include?("up") && ARGV.include?("--provision")) || ARGV.include?("provision") || (ARGV.include?("reload") && ARGV.include?("--provision")))
      return true
    end

    # Pretty hacky way of checking whether machine has been provisioned or not.
    if (ARGV.include?("up") || ARGV.include?("reload"))
      return !File.exist?("#{@project_base_path}/.vagrant/machines/#{self.get('virtualbox.name')}/virtualbox/action_provision")
    end

    return false
  end

end
