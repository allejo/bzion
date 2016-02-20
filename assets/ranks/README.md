# Ranking Sprites Process

The individual icons for each rank both themes, dark and light, are inside of the `rankings.ai` file. Each icon is defined as a symbol in Illustrator and have included a `Symbols to PNGs.jsx` script for Illustrator that will automatically export every symbol into an individual PNG.

When executing the script from Illustrator, you should export all of the PNGs into the "sprites" folder so grunt can then find all the images in that folder and make them into a sprite.

```
grunt sprites:rankings
```

## Design

If you would like to work with the design or make your own, I have included a `swatch.ai` file which you can import as a swatch and it includes all of the gradients and colors used in the current `rankings.ai`.