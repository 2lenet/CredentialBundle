const Encore = require('@symfony/webpack-encore');

Encore
    .addEntry('app', './assets/scripts/app.js')
    .setOutputPath('./src/Resources/public/credential')
    .setPublicPath('/')
    .setManifestKeyPrefix('bundles/credential')
    .cleanupOutputBeforeBuild()
    .enableSassLoader()
    .enableSourceMaps(false)
    .enableVersioning(false)
    .disableSingleRuntimeChunk()

const credential = Encore.getWebpackConfig();

module.exports = [credential];
