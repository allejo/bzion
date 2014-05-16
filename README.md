# BZiON [![Build Status](https://travis-ci.org/allejo/bzion.png?branch=master)](https://travis-ci.org/allejo/bzion) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/allejo/bzion/badges/quality-score.png?s=291afbdf9d3ff68b2e2f44e9d02533795bcbf107)](https://scrutinizer-ci.com/g/allejo/bzion/)

A Content Mangement System (CMS) intended for BZFlag leagues to manage players, teams, matches and more.

## Authors

_Alphabetical by last name_

Vladimir Jimenez (allejo)

Konstantinos Kanavouras (kongr45gpen/alezakos)

Matthew Pavia (tw1sted)

Ashvala Vinay (ashvala)

## Demo

A demo BZiON installation is set up at http://bzpro.net/bzion/ with the latest version of the master branch.

## Documentation

BZiON's source code is thoroughly documented in order for anyone to be able to jump into the project. All of the phpDoc for the classes can be found on [alezakos' website](http://helit.org/bziondoc/phpdoc/).

## Installation

1. Clone the repository

      `git clone https://github.com/allejo/bzion.git league; cd league`

2. If you do not have PHP Composer installed, install it

      `curl -sS https://getcomposer.org/installer | php`

3. Install the required libraries using Composer via the `composer.phar` file

      `php composer.phar install --no-dev`

4. `app/console bzion:install`

5. Make sure that the app/cache directory is writeable by you and the web server:

   <sub>_Example for Apache2 on Ubuntu_</sub>
   ```
   sudo setfacl -R  -m u:www-data:rwX -m u:`whoami`:rwX app/cache app/logs
   sudo setfacl -dR -m u:www-data:rwX -m u:`whoami`:rwX app/cache app/logs
   ```

6. Use the `DATABASE.sql` file to create the necessary database structure

7. Rename `bzion-config-example.php` to `bzion-config.php` and configure the settings.

## License
GNU Lesser General Public License 3.0<br\>
http://www.gnu.org/licenses/lgpl.txt
