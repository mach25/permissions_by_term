#!/bin/bash

project_root_dir=~/PhpstormProjects/permissions-by-term-behat/
drush_executable_path=~/.config/composer/vendor/bin/drush
chromedriver_binary_path=${project_root_dir}/binaries/chromedriver/chromedriver

function pbtTestingRun {
    pbtBehatRunFeature access
    pbtBehatRunFeature entityMetaInfo
    pbtTestingRunUnitTests
    cd ${project_root_dir}/modules/permissions_by_term/js
    yarn test
}

function pbtBehatRunFeature {
    featureName=$1
    pbtTestingPrepare
    echo "Run Behat tests."
    ${project_root_dir}/binaries/behat -vv --strict --config ${project_root_dir}/modules/permissions_by_term/tests/src/Behat/behat.yml ${project_root_dir}/modules/permissions_by_term/tests/src/Behat/Features/${featureName}.feature
}

function pbtTestingPrepare() {
    echo 'Run Chromedriver'
    pbtTestingRunChromedriver
    echo 'Wipe old database'
    rm ${project_root_dir}/sites/default/db.*
    cp ${project_root_dir}/modules/permissions_by_term/tests/fixtures/db.* ${project_root_dir}/sites/default/
    echo 'Make database readable'
    chmod 777 ${project_root_dir}/sites/default/db*
    pbtTestingRebuildDrupalCache
    pbtTestingSwitchBackToTestingRoot
}

function pbtTestingRunUnitTests {
    echo "Run Unit tests."
    ${project_root_dir}/vendor/bin/phpunit -c ${project_root_dir}/modules/permissions_by_term/tests/phpunit.xml
}

function pbtTestingRunChromedriver() {
    if ! screen -list | grep -q "Chromedriver"; then
        screen -S Chromedriver -d -m ${project_root_dir}/chromedriver --url-base=wd/hub --port=4444
    fi
}

function pbtTestingShutdown() {
    echo 'Quit Chromedriver'
    pbtTestingQuitChromedriver
}

function pbtTestingSwitchBackToTestingRoot() {
    cd ${project_root_dir}
}

function pbtTestingRebuildDrupalCache() {
    cd ${project_root_dir} && ${drush_executable_path} cr
}

function pbtTestingRunChromedriver() {
    if ! screen -list | grep -q "Chromedriver"; then
        screen -S Chromedriver -d -m ${project_root_dir}/binaries/chromedriver --url-base=wd/hub --port=4444
    fi
}

function pbtTestingQuitChromedriver() {
    screen -S Chromedriver -X quit
}

function pbtTestingListAllTests() {
    find ${project_root_dir}/tests -maxdepth 1 -type f
}

function pbtTestingKillChromedriver() {
    killall -9 chromedriver
}