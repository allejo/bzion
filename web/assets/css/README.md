Styling BZiON
===

BZiON's stylesheet is written in Sass and we make the most of it; if you're not familiar with Sass, head on over to the [Sass Getting Started Guide](http://sass-lang.com/guide).

We choose to use Sass because of the freedom it gave us to control every aspect of the design.

## Stylesheet Structure

- **generic/**
    - This folder contains partial Sass files for styling HTML tags
- **modules/**
    - This folder contains all of the partial Sass files that don't have actual CSS classes but instead only provide mixins or placeholders.
    - Documentation for all of custom mixins used throughout the project is provided through SassDoc [hosted by alezakos](http://helit.org/bziondoc/sassdoc/)
- **pages/**
    - This folder contains all of the partial Sass files that contain all of the CSS classes for specific pages
- **partials/**
    - This folder contains all of the partial Sass files that contain reusable CSS classes or classes that handle a specific element of the website (e.g. header, footer, sidebar, menu, etc.)
    - Partial files in this folder are typically named appropriately based on the page they style or the part of the website they style.
- **vendor/**
    - This folder contains all of the Sass libraries or helpers that were written by others and are not actively maintained by BZiON developers.
- styles.scss
    - This file is the heart of the stylesheet which just includes all of the partial Sass files

## Sass Practices

### CSS Structure

Even though we use Sass, the generated CSS can still be a pain or nightmare especially when you need to debug. To make life easier, we follow the [BEM](http://csswizardry.com/2013/01/mindbemding-getting-your-head-round-bem-syntax/) syntax for our CSS classes. Not only are we using BEM, we have decided to expand on it by using "[namespaces](http://csswizardry.com/2015/03/more-transparent-ui-code-with-namespaces/)" in our CSS classes (no, I do not mean LESS namespaces).

### Libsass + Ruby Sass

In order to speed up development, Libsass is used to compile the Sass quickly but due its limitations and lack of plug-in support, it is only used for development. For the production ready stylesheet, Ruby Sass is used with plug-ins to compile everything and reorganize things. Because Libsass is used, all of the Sass written must be supported by the latest official Libsass release meaning some of the latest Sass features may not be supported and should not be used in BZiON.

For more information about Libsass, visit their [homepage](http://libsass.org/).

## Themes

BZiON will allow anyone to make their own themes or expand on the default theme by writing their own partial Sass files, which can then be compiled to make your own theme and will be automatically loaded by BZiON if it exists.

Support for theming is planned to be supported by version 1.1.0.

## Questions

### Why not LESS or Stylus?

We chose to use Sass over LESS and Stylus for several reasons. Our main reason for choosing Sass was the simplicity of its syntax and the capabilities Sass has that LESS and Stylus don't; e.g. loops, lack of namespaces, proper mixins, etc.

- We're not looking to write python-like CSS by using Stylus.
- We're not looking to write JS-like CSS by using LESS.

We have no intention of abandoning Sass.

### Why not Bootstrap?

While Bootstrap definitely has its uses, we choose not to use Bootstrap because it didn't suit our needs. Since we use our own flex-box based grid system, we have convenience mixins to perform similar functionality to Bootstrap's mixins so other than that, it's just a lot of bloat.

Bootstrap's grid system is also a limiting factor when styling for specific mobile devices in landspace mode so our own grid system gives us full control of handling that. Lastly, Bootstrap is a "mobile-first" library while BZiON is "desktop-first" so that's another limiting factor.

### Why not &lt;insert framework here&gt;?

Similarly to our reasons for not using Bootstrap, by building our own framework we are able to have full control of our Sass. We do however use Bourbon in certain parts of our code but it is due to be phased out; we will no longer be using Bourbon 5.

### How do you handle vendor prefixes?

We don't. We no longer rely on Bourbon for vendor prefixes, instead we use [-prefix-free](http://leaverou.github.io/prefixfree/) to automatically handle the vendor prefixes per client/browser.

By doing this, we reduce the size of the main stylesheet and do not add another Grunt task to our build process
