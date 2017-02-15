#!/bin/bash
#
# Windows shell provisioner for Ansible playbooks based on geerlingguy's
# JJG-Ansible-Windows https://github.com/geerlingguy/JJG-Ansible-Windows

ANSIBLE_BASE_PATH=$1
GIT_USER_EMAIL=$2
GIT_USER_NAME=$3

# Make sure Ansible playbook exists.
if [ ! -f /var/www/default.localhost/${ANSIBLE_BASE_PATH}/main.yml ]; then
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
ansible-galaxy install --force -r /var/www/default.localhost/${ANSIBLE_BASE_PATH}/requirements.yml

# Run the playbook.
echo "Running Ansible provisioner defined in the Vagrantfile."
echo ""
ansible-playbook -i "localhost," /var/www/default.localhost/${ANSIBLE_BASE_PATH}/main.yml --connection=local -e "git_user_email=${GIT_USER_EMAIL}" -e "git_user_name=${GIT_USER_NAME}"
