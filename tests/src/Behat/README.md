Permissions by Term Module Behat
================================

Purpose
-------
Use this library to provide automated testing for a project, which is driven
by the [Permissions by Term Drupal 8 module](https://www.drupal.org/project/permissions_by_term).

This project provides automated testing via [Behat](http://behat.org/en/latest/). It's based on the 
[Drupal Extension to Behat and Mink](https://www.drupal.org/project/drupalextension) project. You can
find it's documentation [here](http://behat-drupal-extension.readthedocs.io/en/3.1/index.html). There
you will get information about the basics. Since "Permissions by Term Module Behat Library for Drupal 8"
is an extension of it. 

Installation
------------
This project is a PHP library which can be installed via [Composer](hśttps://getcomposer.org/):

`composer require jepster/permissions-by-term-behat`

Examples
--------
* Behat configuration: In the `features` folder you will find a `behat.yml` which configures Behat to work with the
[ChromeDriver](https://sites.google.com/a/chromium.org/chromedriver/)
* Behat Feature: There's also an access.feature file, with test scenarios for the basic functionality of the
"Permissions by Term" Drupal module
* Bash extension: The `bash.sh` file contains service commands which you can source into your `.bashrc / .bash_profile` file via
 the `source ~/permissions-by-term-behat/bash.sh` command. The service commands ease the tests execution. They will wipe a 
 SQLite database and copy your base database for tests, run the ChromeDriver and run the Behat tests afterwards. This you
 can achieve by executing `pbtTestingRunTest` only.
    * Notice: You need to copy this file and modify the path to your project root etc. in the head area of this file to 
    suite your needs.

Questions or Suggestions?
-------------------------
Feel free to open an issue here on GitHub.