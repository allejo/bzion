# Rank Sprites

Do **not** modify our ranks sprite image, it is automatically generated through a Grunt task (`grunt sprites:ranks`).

![Current Ranks Sprite](https://raw.githubusercontent.com/allejo/bzion/master/web/assets/imgs/ranks.png)

If you would like to contribute your own set of rank images, you are going to need to create them as SVGs by using a tool like Adobe Illustrator or Inkscape. Our master file is the `ranks.ai` file and it was created using Adobe Illustrator CC 2015. Do **not** attempt to modify this AI file with a version prior to CC 2015 or with Inkscape as it will break a lot.

## How to Contribute Your Own Icons

1. Create your 12 icons as vectors/SVGs and label for what minimum ELO they are for; they should range from 900-2000.
2. Think of a theme name
3. Start an **issue** for your new icons.
    1. Attach a screenshot of all your new icons.
    2. Attach a zip of your SVGs of your icons
    3. Mention @allejo in your issue as he will handle adding the new icons

## For Project Members: Using Adobe Illustrator CC 2015

1. Install the `Symbols to PNGs.jsx` script so you can export all of the icons.
    - When exporting the symbols, be sure to hide all of the layers.
2. Each set of icons corresponding to a theme will be under their own layer; there is a `Light` and `Dark` layer in the AI file—respectively for the Light and Dark themes available in BZiON.
3. (Optional) Import the `swatch.ai` file to your swatches to have access to the gradients and colors used for the Light and Dark theme icons.
4. Expand the art board as necessary to fit your icons. There should be 12 icons total for all of the minimum ELOs in multiples of hundreds: 900...2000.
5. Each individual icon should be grouped and become a [symbol](https://helpx.adobe.com/illustrator/using/symbols.html). The naming scheme for the symbols should be as follows: `ThemeName-MinElo`.
    - e.g. `Light-900` or `Dark-900`
6. The symbols should be a size of 35px width and 40px height.
7. Do **not** make a mess of the layers and symbols.

Export all of your symbols into the `sprites` folder in this directory and then run `grunt sprites:ranks` and `grunt sass:dist` to generate a new ranks images with your icons and update the CSS.