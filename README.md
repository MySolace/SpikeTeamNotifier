Spike Team Notifier
===================

[![CII Best Practices](https://bestpractices.coreinfrastructure.org/projects/1431/badge)](https://bestpractices.coreinfrastructure.org/projects/1431)
[![Maintainability](https://api.codeclimate.com/v1/badges/53481b14f66c640ce96f/maintainability)](https://codeclimate.com/github/MySolace/SpikeTeamNotifier/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/53481b14f66c640ce96f/test_coverage)](https://codeclimate.com/github/MySolace/SpikeTeamNotifier/test_coverage)
[![Build Status](https://travis-ci.org/MySolace/SpikeTeamNotifier.svg?branch=master)](https://travis-ci.org/MySolace/SpikeTeamNotifier)

---

This is the app developed by Crisis Text Line (crisistextline.org) to notify CTL's spike team, a hardcore group of on-call volunteers in the event of a spike in volume in the system. It is essentially a list of phone numbers, and an API call to a Twilio account to send a standardized message to all of the volunteers. There is also API for the app itself, secured by WSSE, for CRUD operations related to the team members.

Getting Started
---------------

In order to get started, you'll need to do the following:

* Clone this repo.

* Install MySQL and, optionally, MySQL workbench:
    * http://dev.mysql.com/downloads/mysql/
    * http://mysqlworkbench.org/

* Install composer and use it globally:
    ```
    curl -s https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    ```

* Use composer to install dependencies for this app:
    ```
    composer install
    ```

* Update the database you specified above with the Doctrine schema:
    ```
    php app/console doctrine:database:create
    php app/console doctrine:migrations:migrate
    ```

* Be sure to check the following settings, as per your needs:
    ```
    alert_timeout: '24 hours'
    twilio_sid: [ YOUR TWILIO SID ]
    twilio_token: [ YOUR TWILIO TOKEN ]
    twilio_number: [ THE TWILIO NUMBER YOU WANT TO SEND FROM ]
    twilio_message: [ YOUR MESSAGE ]
    twilio_response: [ YOUR AUTO-REPLY MESSAGE TO ANY INCOMING SMS ]
    ```

* Start the sever:
    ```
    php app/console server:run &
    ```

* You might need to edit the following lines in app/config/security.yml if you aren't running an SSL on the server where you are hosting the files:
    ```
    access_control:
        - { path: ^/twilio, roles: IS_AUTHENTICATED_ANONYMOUSLY, requires_channel: http }
        - { path: ^/login$, roles: IS_AUTHENTICATED_ANONYMOUSLY, requires_channel: https }
        - { path: ^/, roles: ROLE_ADMIN, requires_channel: https }
        - { path: ^/api, roles: ROLE_API, requires_channel: http }
    ```

* If you do the step above, please be sure to reverse the changes before commiting back to the repo. Will have to find a better way to manage this, but for now, this is what we'll do.

* Now you can go to the local version of the Notifier by going to:
   ```
   localhost:8000
   ```

* Get SASS watching the scss files, if that's what you want:
    ```
    sass --watch web/scss:web/css &
    ```

* In order to get the Twilio auto-reply thing going, you'll need to follow step 2 of the instructions here: https://www.twilio.com/docs/quickstart/php/sms/hello-monkey, using the following url:
    ```
    [YOUR APP ROOT]/twilio/incoming
    ```

* Log into the default admin account at [YOUR APP ROOT], with:
    ```
    email: admin@sample.com
    password: admin
    ```

* Once logged in, navigate to [YOUR APP ROOT]/admin/admin/edit and change your email address and password as you see fit. Make sure to edit the default settings at [YOUR APP ROOT]/settings, too.

* Get started developing!

API Documentation
-----------------

Documentation of this app can be found at [app root]/api/doc/, provided via the Nelmio API Doc Bundle: https://github.com/nelmio/NelmioApiDocBundle
