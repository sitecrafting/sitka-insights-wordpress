#!/usr/bin/env bash

RED="tput setaf 1"
BOLD="tput bold"
RESET="tput sgr 0"


function usage() {
  echo 'Usage:'
  echo
  echo "  $0 <RELEASE>"
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
  if ! [[ -f ./sitka-insights.php ]] ; then
    fail 'Error: not in root sitka-insights-wordpress directory?'
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

  tar_name="sitka-insights-${RELEASE}.tar.gz"
  zip_name="sitka-insights-${RELEASE}.zip"
  composer install --no-dev --prefer-dist

  # hackishly create a symlink sitka-insights directory, so that when
  # extracted, the archives we create have a top-level directory
  ln -sfn . sitka-insights

  # archive plugins distro files inside a top-level sitka-insights/ dir
  tar -cvzf "$tar_name" \
    sitka-insights/vendor/autoload.php \
    sitka-insights/sitka-insights.php \
    sitka-insights/wp-api.php \
    sitka-insights/src \
    sitka-insights/cli \
    sitka-insights/js \
    sitka-insights/css \
    sitka-insights/vendor \
    sitka-insights/views \
    sitka-insights/LICENSE.txt \
    sitka-insights/README.md

  # ditto for zip
  zip -r "${zip_name}" \
    sitka-insights/sitka-insights.php \
    sitka-insights/wp-api.php \
    sitka-insights/src \
    sitka-insights/cli \
    sitka-insights/js \
    sitka-insights/css \
    sitka-insights/vendor \
    sitka-insights/views \
    sitka-insights/LICENSE.txt \
    sitka-insights/README.md

  # remove hackish symlink
  rm ./sitka-insights

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
      git push origin main
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
