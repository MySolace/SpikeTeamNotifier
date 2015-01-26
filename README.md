Spike Team Notifier
===================
CTL's Spike Team Alert System

Getting Started
---------------

In order to get started, you'll need to do the following:

* Clone this repo.

* Install MySQL and, optionally, MySQL workbench:
    http://dev.mysql.com/downloads/mysql/
    http://mysqlworkbench.org/

* Install composer and use it globally:
    ```
    curl -s https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    ```

* Use composer to install dependencies for this app:
    ```
    composer install
    ```

* Add the following options to app/config/Parameters.yml, according to your needs:
    ```
    alert_wait:
    twilio_sid:
    twilio_tok:
    twilio_number_dev:
    twilio_msg:
    ```

* Update the database you specified above with the Doctrine schema:
    ```
    php app/console doctrine:database:create
    php app/console doctrine:schema:update --force
    ```

* Create a super-admin user:
    ```
    php app/console fos:user:create --super-admin
    ```

* Start the sever:
    ```
    php app/console server:run
    ```

* Get started developing!

API Documentation
-----------------

Documentation of this app can be found at [app root]/(app_dev.php)/api/doc/, provided via the Nelmio API Doc Bundle: https://github.com/nelmio/NelmioApiDocBundle
