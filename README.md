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

1. Clone this repo.

2. Install MySQL and, optionally, MySQL workbench:

http://dev.mysql.com/downloads/mysql/
http://mysqlworkbench.org/

3. Install composer and use it globally:

'''
curl -s https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
'''

4. Use composer to install dependencies for this app:

'''
composer install
'''

5. Update the database you specified above with the Doctrine schema:

'''
php app/console doctrine:schema:update --force
'''

6. Create a super-admin user:

'''
php app/console fos:user:create --super-admin
'''

7. Start the sever:

'''
php app/console server:run
'''

8. Get started developing!

API Documentation
-----------------

Documentation of this app can be found at api/doc/, provided via the Nelmio API Doc Bundle
