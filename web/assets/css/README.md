Styling BZiON
===

BZiON's stylesheet is written in Sass and we make the most of it; if you're not familiar with Sass, head on over to the [Sass Getting Started Guide](http://sass-lang.com/guide).

We choose to use Sass because of the freedom it gave us to control every aspect of the design.

## Stylesheet Structure

- **generic/**
    - This folder contains partial Sass files for styling HTML tags
- **modules/**
    - This folder contains all of the partial Sass files that don't have actual CSS classes but instead only provide mixins or placeholders.
    - Documentation for all of custom mixins used throughout the project is provided through SassDoc [hosted by alezakos](http://bziondoc.helit.tech/sassdoc/)
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

### Libsass

We use [libsass](http://libsass.org/) bindings to compile our Sass through our Gulp tasks (`sass:dev` or `sass:dist`). The production-ready stylesheet (`styles.css`) provided with BZiON should **not** be committed until a BZiON release is being prepared; it is to be expected that developers will compile the Sass locally.

It is sometimes the case where Ruby Sass has some features that are not yet supported by LibSass. Should this be the case, these features cannot be used until LibSass officially implements them; we have no intention of re-introducing Ruby as a dependency.

### Documentation

All of our Sass functions and mixins are documented with [SassDoc](http://sassdoc.com/) and may be built with our `sass:docs` Gulp task. All code located in **modules/** should be documented and be available in our SassDoc.

### Linting

Our Sass code should always pass our `sass:lint` Gulp task. Our unit tests will test for Sass formatting and will fail when violations are found.

## Themes

BZiON provides an easy way of creating new themes by defining a color scheme in a Yaml file which is loaded by our Gulp process when building our stylesheet.

This feature is currently in development.

## Questions

### Why not Less or Stylus?

We chose to use Sass over Less and Stylus for several reasons. Our main reason for choosing Sass was the simplicity of its syntax and the capabilities Sass has that Less and Stylus don't.

We have no intention of moving away from Sass.

### Why not Bootstrap?

Bootstrap 3 would introduce a lot of limitations such as missing breakpoints and a float based grid-system. This led us to building our own flexbox based grid system, which can easily accept any number of breakpoints. We needed to build several components than Bootstrap did not provide and we had no use for some that it did provide, this would lead to a lot of unused CSS and bloat. Lastly, because Bootstrap doesn't follow our naming scheme, there would be a lot of inconsistency in our CSS classes and we were not looking for more work by maintaining two separate codebases.

While Bootstrap 4 no longer has some of the limitations of its predecessor, we already have a system that works for us and will continue to use it.

### Why not &lt;insert framework here&gt;?

Similarly to our reasons for not using Bootstrap, by building our own framework we are able to have full control of our Sass.

### How do you handle vendor prefixes?

We don't. We use [-prefix-free](http://leaverou.github.io/prefixfree/) to automatically handle the majority of vendor prefixes per client/browser. There are some rare occasions where we must specify the vendor prefix because of technical limitations of -prefix-free.

### Why don't you use CSS variables?

CSS variables do not fit or work with the way our themes work. In addition, IE 11 and Edge do not support them so we do not want to have a JavaScript fallback to change the behavior of something that should work without JavaScript.