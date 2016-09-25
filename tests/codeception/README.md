# INTRODUCTION TO TESTING WITH CODECEPTION

## Overview
We have two docker containers described in the respective docker files in folders "docker/phantomjs and docker/codeception". The codeception image is generated from "php:5.6-cli" image and codeception version 2.1.4 is installed.  Phantomjs image is generated from Ubuntu 14.04, adding "libfreetype6 libfontconfig bzip2 libicu52 libjpeg8 libwebp5" and phantomjs version 2.1.1 specified in "ENV PHANTOMJS_VERSION 2.1.1" variable.

Both docker images are then built and run by a docker compose file making a link between them.

### Run

```sh
$ cd [AUTOMATION_TEST_DIR]
$ sudo docker-compose run --rm codeception codecept run
```

#### Additional Params
http://codeception.com/docs/reference/Commands

