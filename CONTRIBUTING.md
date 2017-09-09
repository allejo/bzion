## Contributing

This is an open source project and everyone is welcome to hack on the project. Don't see a feature you want? Write it and submit a pull request! Can't write code? Request it as a feature!

### Bugs and Issues

If you're not comfortable with hacking on BZiON or you're not a coder, we are always looking for assistance in finding bugs and weird issues. As developers, we only imagine our code to be used the way it's intended but that's never the case. If you found a way to break our code, please [let us know](https://github.com/allejo/bzion/issues).

### Documentation

BZiON uses a lot of Symfony2 components internally, so the respective documentation can be found on the [Symfony2 website](http://symfony.com/doc/current/index.html). Aside from the components, the source code for the project has been thoroughly documented and the phpDoc can be found on [alezakos' website](http://bziondoc.helit.org/phpdoc/).

Most modern IDEs will easily be able to read BZiON's phpDoc and provide you with the respective documentation on how certain classes or functions are used.

### Pull Requests

If you intend on writing a large feature, create an issue first and discuss your vision with us. We would hate for you to waste your time on writing something we don't think would be best for the project.

Wrote a neat little feature you'd like to see available in BZiON or fixed a bug? Send us a [pull request](https://github.com/allejo/bzion/pulls) and we'll gladly take a look.

Before you send us a pull request, here are a few things we look for:

- Your code is neatly written and follows the coding style of the project as closely as possible
- Your pull request is actually functional and works as intended
- Your pull request does not break functionality of the master branch nor the unit tests
- Your new feature will benefit the league community or will improve the experience for league members
- If submitting a bug fix, specify what the bug is, when it occurs, and how your pull request has fixed the bug

To increase the probability of your pull request being accepted, ensure you consider the things we look for when evaluating pull requests. We're not actually mean, we love pull requests. We simply would like the pull request process to go smoothly and quickly, all the while keeping things organized.

### Feature Requests

We would love to hear your ideas and look forward to implementing the best of them, so [submit an issue](https://github.com/allejo/bzion/issues) and we'll be able to discuss the feature further.

### Questions?

All of the development discussions for this project occur on #sujevo on irc.freenode.net, feel free to stop by and talk with a developer. We love the company.

## Developing

These are notes targeted towards developers or contributors targeting more core related aspects of BZiON.

### Updating Composer Dependencies

The minimum PHP version that BZiON currently supports is **5.6**, therefore when running `composer update` it is necessary to run it with PHP 5.6. Should BZiON's minimum requirement change, updates to the lock file must be run with the lowest minimum version that BZiON targets.
