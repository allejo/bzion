# BZiON [![Build Status](https://travis-ci.org/allejo/bzion.png?branch=master)](https://travis-ci.org/allejo/bzion) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/allejo/bzion/badges/quality-score.png?s=291afbdf9d3ff68b2e2f44e9d02533795bcbf107)](https://scrutinizer-ci.com/g/allejo/bzion/)

BZiON is a modern content management system (CMS) written for BZFlag leagues to manage players, teams, matches, and tournaments.

## Contributors

### Developers

These individuals have made multiple significant contributions to the project on a sustained basis. They become actively involved on improving and adding new features to the project.

- Vladimir Jimenez ([allejo](https://github.com/allejo))
- Konstantinos Kanavouras ([kongr45gpen/alezakos](https://github.com/kongr45gpen))
- Matthew Pavia ([tw1sted](https://github.com/mattpavia))
- Ashvala Vinay ([ashvala](https://github.com/Ashvala))

### Thanks to

These individuals have assisted significantly with guiding the project in its current direction and have contributed several suggestions to continuously improve the project.

- [blast007](https://github.com/blast007)

## Setting Up

### Demo

A demo BZiON installation can be found at [BZPro](http://bzpro.net/bzion/web/dev.php) with the latest version of the master branch and sample data.

### Installation

1. Go to the directory where you want to install BZiON

      `cd league`

2. If you do not have PHP Composer installed, install it

      `curl -sS https://getcomposer.org/installer | php`

3. Install the required libraries using Composer via the `composer.phar` file

      `php composer.phar create-project allejo/bzion --keep-vcs --no-dev -s dev .`

4. Use the `DATABASE.sql` file to create the necessary database structure

5. Make sure that the app/cache, app/logs and web/assets/imgs/avatars/ directories
   are writable by you and the web server:
   
   1. First get the current web server user.
   
      ```
      HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
      ```

   2. Set the permissions for the appropriate directories.

      <sub>_Example for Apache2 on Ubuntu_</sub>  
      ```
      sudo setfacl -R  -m u:$HTTPDUSER:rwX -m u:`whoami`:rwX app/cache app/logs web/assets/imgs/avatars/
      sudo setfacl -dR -m u:$HTTPDUSER:rwX -m u:`whoami`:rwX app/cache app/logs web/assets/imgs/avatars/
      ```
      
      <sub>_Example for Apache2 on OS X_</sub>  
      ```
      sudo chmod +a "$HTTPDUSER allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs web/assets/imgs/avatars/
      sudo chmod +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs web/assets/imgs/avatars/
      ```

## Contributing

This is an open source project and everyone is welcome to hack on the project. Don't see a feature you want? Write it and submit a pull request! Can't write code? Request it as a feature!

### Bugs and Issues

If you're not comfortable with hacking on BZiON or you're not a coder, we are always looking for assistance in finding bugs and weird issues. As developers, we only imagine our code to be used the way it's intended but that's never the case! If you found a way to break our code, please [let us know](https://github.com/allejo/bzion/issues)!

### Documentation

BZiON uses a lot of Symfony2 components internally, so the respective documentation can be found on the [Symfony2 website](http://symfony.com/doc/current/index.html). Aside from the components, the source code for the project has been thoroughly documented and the phpDoc can be found on [alezakos' website](http://helit.org/bziondoc/phpdoc/).

Most modern IDEs will easily be able to read BZiON's phpDoc and provide you with the respective documentation on how certain classes or functions are used.

### Pull Requests

Wrote a feature you'd like to see available in BZiON or fixed a bug? Send us a [pull request](https://github.com/allejo/bzion/pulls) and we'll gladly take a look.

Before you send us a pull request, here are a few things we look for:

- Your code is neatly written and follows the coding style of the project as closely as possible
- Your pull request is actually functional and works as intended
- Your pull request does not break functionality of the master branch nor the unit tests
- Your new feature will benefit the league community or will improve the experience for league members
- Your pull request is not an update to the "TODO.md" file giving the developers more things to do
- If submitting a bug fix, specify what the bug is, when it occurs, and how your pull request has fixed the bug

To increase the probability of your pull request being accepted, ensure you consider the things we look for when evaluating pull requests. We're not actually mean, we absolutely love pull requests! We simply would like the pull request process to go smoothly and quickly.

### Feature Requests

While BZiON is still in development, there are a lot of features that are planned but aren't thoroughly documented or specified anywhere and a lot of functionality still needs to be written so feature requests are not a high priority for developers.

While feature requests may not be a high priority, we would still love to hear your ideas and look forward to implementing the best ideas so [submit an issue](https://github.com/allejo/bzion/issues) and label it as "enhancement" and we'll be able to discuss the feature thoroughly.

### Questions?

All of the development discussions for this project occur on #sujevo on irc.freenode.net, feel free to stop by and talk with a developer! We love the company.

## License

[GNU General Public License 3.0](https://github.com/allejo/bzion/blob/master/LICENSE.md)
