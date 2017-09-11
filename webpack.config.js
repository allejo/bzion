var Encore = require('@symfony/webpack-encore');

var ModernizrWebpackPlugin = require('modernizr-webpack-plugin');

Encore
    // directory where all compiled assets will be stored
    .setOutputPath('web/build/')

    // what's the public path to this directory (relative to your project's document root dir)
    .setPublicPath('/build')

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // will output as web/build/app.js
    .addEntry('app', './assets/js/main.js')

    // allow legacy applications to use $/jQuery as a global variable
    .autoProvidejQuery()

    // Modernizr configuration
    .addPlugin(new ModernizrWebpackPlugin({
        minify: true,
        options: [
            'setClasses'
        ],
        'feature-detects': [
            'test/canvas',
            'test/canvastext',
            'test/svg',
            'test/css/animations',
            'test/css/calc',
            'test/css/transforms',
            'test/css/transitions',
            'test/css/vhunit',
            'test/svg/inline'
        ]
    }))

    .enableSourceMaps(!Encore.isProduction())
;

// export the final configuration
module.exports = Encore.getWebpackConfig();
