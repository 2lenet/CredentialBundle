const Encore = require('@symfony/webpack-encore');

Encore
    .addEntry('app', './assets/scripts/app.js')
    .addStyleEntry('css', './assets/styles/app.scss')
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
