#!/usr/bin/env bash

RED="tput setaf 1"
BOLD="tput bold"
RESET="tput sgr 0"


function usage() {
  echo 'Usage:'
  echo
  echo '  build-release.sh <RELEASE>'
  echo
  echo 'RELEASE: the name of the release, e.g. "v1.2.3"'
  echo
}

function fail() {
  echo $($RED; $BOLD)
  echo "$1"
  echo $($RESET)
  usage
  exit 1
}

function main() {
  if ! [[ -f ./gearlab-tools.php ]] ; then
    fail 'Error: not in root gearlab-tools-wordpress directory?'
  fi

  RELEASE="$1"

  if [[ -z "$RELEASE" ]] ; then
    fail 'Error: no release number specified'
  fi

  # prompt for the letter "v"
  first_char="${RELEASE:0:1}"
  if ! [[ "$first_char" = 'v' ]] ; then
    read -p "Prepend a 'v' (v${RELEASE})? (y/N) " prepend
    if [[ "$prepend" = "y" ]] ; then
      RELEASE="v${RELEASE}"
    fi
  fi

  if [[ -z $NO_INTERACTION ]] ; then
    # check tag
    git rev-parse --verify "$RELEASE" 2>/dev/null
    if ! [[ "$?" -eq 0 ]] ; then

      # prompt for creating a tag
      read -p "'${RELEASE}' is not a Git revision. Create tag ${RELEASE}? (y/N) " create
      if ! [[ "$create" = "y" ]] ; then
        echo 'aborted.'
        exit
      fi

      git tag "$RELEASE"
    fi
  fi

  backup_vendor

  tar_name="gearlab-tools-wordpress-${RELEASE}.tar.gz"
  zip_name="gearlab-tools-wordpress-${RELEASE}.zip"
  composer install --no-dev --prefer-dist

  # hackishly create a symlink gearlab-tools-wordpress directory, so that when
  # extracted, the archives we create have a top-level directory
  ln -sfn . gearlab-tools-wordpress

  # archive plugins distro files inside a top-level gearlab-tools-wordpress/ dir
  tar -cvzf "$tar_name" \
    gearlab-tools-wordpress/vendor/autoload.php \
    gearlab-tools-wordpress/gearlab-tools.php \
    gearlab-tools-wordpress/wp-api.php \
    gearlab-tools-wordpress/src \
    gearlab-tools-wordpress/cli \
    gearlab-tools-wordpress/js \
    gearlab-tools-wordpress/css \
    gearlab-tools-wordpress/vendor \
    gearlab-tools-wordpress/views \
    gearlab-tools-wordpress/LICENSE.txt \
    gearlab-tools-wordpress/README.md

  # ditto for zip
  zip -r "${zip_name}" \
    gearlab-tools-wordpress/gearlab-tools.php \
    gearlab-tools-wordpress/wp-api.php \
    gearlab-tools-wordpress/src \
    gearlab-tools-wordpress/cli \
    gearlab-tools-wordpress/js \
    gearlab-tools-wordpress/css \
    gearlab-tools-wordpress/vendor \
    gearlab-tools-wordpress/views \
    gearlab-tools-wordpress/LICENSE.txt \
    gearlab-tools-wordpress/README.md

  # remove hackish symlink
  rm ./gearlab-tools-wordpress

  restore_vendor

  echo "Created ${tar_name}, ${zip_name}"

  if [[ -z $NO_INTERACTION ]] ; then
    create_github_release $RELEASE $tar_name $zip_name
  fi

  echo 'Done.'
}

function backup_vendor() {
  echo 'backing up vendor...'
  if [[ -d vendor ]] ; then
    mv vendor vendor.bak
  fi
}

function restore_vendor() {
  echo 'restoring vendor...'
  if [[ -d vendor.bak ]] ; then
    rm -rf vendor
    mv vendor.bak vendor
  fi
}

function create_github_release() {
  if [[ $(which hub) ]] ; then
    echo $($BOLD)hub detected! You win at Git!$($RESET)
    read -p 'Create a GitHub release? (y/N) ' create
    if [[ "$create" = "y" ]] ; then
      read -p 'Is this a pre-release? (y/N) ' prerelease
      if [[ "$prerelease" = "y" ]] ; then
        prerelease_opt='--prerelease'
      fi

      echo 'pushing latest changes and tags...'
      git push origin master
      git push --tags
      hub release create $prerelease_opt -a "$2" -a "$3" -e "$1"
    else
      echo 'skipping GitHub release.'
    fi
  fi
}



POSITIONAL=()
while [[ $# -gt 0 ]]
do
key="$1"

case $key in
  -h|--help)
    # show usage and bail
    usage
    exit
    ;;
  -n|--no-interaction)
    NO_INTERACTION='1'
    shift # past argument
    ;;
  *)
    POSITIONAL+=("$1") # save it in an array for later
    shift # past argument
    ;;
esac
done
set -- "${POSITIONAL[@]}" # restore positional parameters



main "$@"
