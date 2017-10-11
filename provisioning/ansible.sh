#!/bin/bash
#
# Windows shell provisioner for Ansible playbooks based on geerlingguy's
# JJG-Ansible-Windows https://github.com/geerlingguy/JJG-Ansible-Windows

ANSIBLE_BASE_PATH=$1
ANSIBLE_EXTRA_VARS=$2

# Make sure Ansible playbook exists.
if [ ! -f ${ANSIBLE_BASE_PATH}/main.yml ]; then
  echo "Cannot find Ansible playbook."
  exit 1
fi

# Install Ansible and its dependencies if it's not installed already. Redirect
# stdout and stderr to /dev/null.
if [ ! -f /usr/bin/ansible ]; then
  echo "Installing Ansible on virtual machine."
  echo ""
  # Install required dependencies.
  apt-get -y install software-properties-common > /dev/null 2>&1
  # Add Ansible PPA.
  add-apt-repository ppa:ansible/ansible > /dev/null 2>&1
  # Update repositories.
  apt-get -y update > /dev/null 2>&1
  # Install Ansible.
  apt-get -y install ansible > /dev/null 2>&1
fi

# Install playbook requirements.
ansible-galaxy install --ignore-errors -r ${ANSIBLE_BASE_PATH}/requirements.yml

# Export configuration to the temporary file to avoid issues with escaping.
echo ${ANSIBLE_EXTRA_VARS} > /tmp/ansible-extra-vars.json

# Run the playbook.
echo "Running Ansible provisioner defined in the Vagrantfile."
echo ""
ansible-playbook -i 'localhost,' ${ANSIBLE_BASE_PATH}/main.yml --connection=local --extra-vars '@/tmp/ansible-extra-vars.json'
