#!/bin/bash

if [[ ! -d "$1" ]]
then
  echo 'ERROR: No project directory given! Usage:'
  echo
  echo '  zip.sh /path/to/my/wordpress/project'
  echo
  exit 1
fi

# remove local release artifacts
rm *.zip *.tar.gz

# build a release called vtest
bin/build-release.sh --no-interaction vtest

# install in the plugin dir of the project given in the sole CLI arg
rm -rf "$1/wp-content/plugins/gearlab-tools-wordpress/"
unzip -d "$1/wp-content/plugins/" gearlab-tools-wordpress-vtest.zip