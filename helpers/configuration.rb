# -*- mode: ruby -*-
# vi: set ft=ruby :

require 'yaml'

# Public: Configuration manager for Vagrant.
class Configuration

  # Public: Initialize a Configuration.
  #
  # base_path - String that contains Vagrantfile base path.
  def initialize(base_path)
    self.load_settings(base_path)
    self.merge_default_settings
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
    temp_hash = Hash.new
    key.split(".").reverse.each do |k|
      if temp_hash.empty?
        temp_hash = {k => value}
      else
        temp_hash = {k => temp_hash}
      end
    end

    @settings = self.merge_recursively(@settings, temp_hash)
  end

  # Internal: Load settings from YAML file(s).
  #
  # base_path - String that contains Vagrantfile base path.
  #
  # Returns nothing.
  protected
  def load_settings(base_path)
    default_settings = YAML::load_file("#{base_path}/default.vm-settings.yml")
    if File.exist?("#{base_path}/vm-settings.yml")
      settings = YAML::load_file("#{base_path}/vm-settings.yml")
      @settings = self.merge_recursively(default_settings, settings)
    else
      @settings = default_settings
    end
    if File.exist?("#{base_path}/vm-settings.local.yml")
      local_settings = YAML::load_file("#{base_path}/vm-settings.local.yml")
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
      self.set("virtualbox.hostname", "draft-env-" + SecureRandom.hex(6))
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
    part_4 = [[sum % 256, 1].max, 255].min

    self.set("vagrant.ip_address", "10.10.#{part_3}.#{part_4}")
  end

  # Internal: Get Git user name and email from the host system.
  #
  # Returns nothing.
  protected
  def get_git_credentials
    self.set("git_user_name", `git config --get user.name`.strip)
    self.set("git_user_email", `git config --get user.email`.strip)
  end

end
