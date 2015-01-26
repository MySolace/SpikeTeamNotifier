Spike Team Notifier
===================

CTL's Spike Team Alert System

What's inside?
--------------

- FOSUserBundle

Users handled via FOSUserBundle: https://github.com/FriendsOfSymfony/FOSUserBundle

- Twilio

SMS sent via Twilio: https://github.com/twilio/twilio-php

- FOSRestBundle

API set up via FOSRestBundle: https://github.com/FriendsOfSymfony/FOSRestBundle
Halfway through: http://www.screenfony.com/product/free/symfony-rest-api-1

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

Documentation of this app can be found at <root>/(app_dev.php)/api/doc/, provided via the Nelmio API Doc Bundle: https://github.com/nelmio/NelmioApiDocBundle
