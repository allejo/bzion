Styling BZiON
===

BZiON's stylesheet is written in SASS and we make the most of it; if you're not familiar with SASS, head on over to the [SASS Getting Started Guide](http://sass-lang.com/guide).

We choose to use SASS, Bourbon, and Neat as our main languages because of the freedom it gave us to control every aspect of the design.

## Stylesheet Structure

- **modules/**
    - This folder contains all of the partial SASS files that don't have actual CSS classes but instead only provide mixins or placeholders.
    - Documentation for all of custom mixins used throughout the project is provided through SassDoc, which is [hosted by alezakos](http://helit.org/bziondoc/sassdoc/).
- **partials/**
    - This folder contains all of the partial SASS files that contain all of the CSS classes and rules that make up the BZiON stylesheet.
    - Partial files in this folder are typically named appropriately based on the page they style or the part of the website they style.
- **vendor/**
    - This folder contains all of the SASS libraries or helpers that were written by others and are not actively maintained by BZiON developers.
- styles.scss
    - This file is the heart of the stylesheet which just includes all of the partial SASS files

## Themes

BZiON will allow anyone to make their own themes or expand on the default theme by writing their own partial SASS files, which can then be compiled to make your own theme and will be automatically loaded by BZiON if it exists.

Support for theming is planned to be supported by version 1.1.0.

## Questions

### Why not Less?

We chose to use SASS over Less for several reasons. Our main reason for choosing SASS was the simplicity of its syntax and the capabilities SASS has that Less doesn't; e.g. loops, lack of namespaces, proper mixins, etc.

### Why not Bootstrap?

While Bootstrap definitely has its uses, we choose not to use Bootstrap because it didn't suit our needs. Since we use ThoughtBot's Neat library, it already provides us with a simple to use grid system and we have convenience mixins to perform similar functionality to Bootstrap's mixins so other than that, it's just a lot of bloat.