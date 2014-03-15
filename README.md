# BZiON [![Build Status](https://travis-ci.org/allejo/bzion.png?branch=master)](https://travis-ci.org/allejo/bzion) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/allejo/bzion/badges/quality-score.png?s=291afbdf9d3ff68b2e2f44e9d02533795bcbf107)](https://scrutinizer-ci.com/g/allejo/bzion/)

A Content Mangement System (CMS) intended for BZFlag leagues to manage players, teams, matches and more.

## Authors

_Alphabetical by last name_

Vladimir Jimenez (allejo)  
Konstantinos Kanavouras (kongr45gpen/alezakos)  
Matthew Pavia (tw1sted)  
Ashvala Vinay (ashvala)

## Documentation

BZiON's source code is thoroughly documented in order for anyone to be able to jump into the project. All of the phpDoc for the classes can be found on [alezakos' website](http://helit.org/bziondoc/phpdoc/).

## Development Setup

1. Clone the repository

     `git clone https://github.com/allejo/bzion.git league`

2. Change into the directory and get all of the necessary submodules

      `git submodule update --init`

3. (Optional) If you do not have PHP Composer installed, install it.

      `curl -sS https://getcomposer.org/installer | php`

4. Install the required libraries via the Composer file

      `php /path/to/composer.phar install`

5. Use the `DATABASE.sql` file to create the database structuce

6. Duplicate the `bzion-config-example.php` file and configure the settings.

## License
GNU Lesser General Public License 3.0<br\>
http://www.gnu.org/licenses/lgpl.txt
