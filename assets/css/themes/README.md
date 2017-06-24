# BZiON Themes

Themes are defined as YAML files in the `themes/` folder of the Sass project. The file name is the name of the theme and cannot have spaces or special characters.

## Creating Your Own

The theme specification is still being built in parallel with the site's redesign. Until child themes are introduced, make a copy of `light.yml` or `dark.yml` and start changing the values.


## Registering Themes

In order to have BZiON build a new theme, you must register the theme by adding the name of the theme to the `$registered_themes` comma separated list located in `_themes/_registrar.scss`.

After registering the theme, rebuild BZiON's Sass file.

```bash
node_modules/.bin/gulp sass:dist
```

In addition to registering the theme with Sass, we need to register it with Symfony so we'll need to add the theme name to `app/config.yml` under `bzion.site.themes` and it'll be made available to users.
