const { getDefaultConfig } = require('expo/metro-config');

const config = getDefaultConfig(__dirname);

// Soporte para CSS modules (requerido por @expo/log-box en SDK 57)
config.resolver.sourceExts = ['jsx', 'js', 'ts', 'tsx', 'json', 'css'];
config.resolver.assetExts = config.resolver.assetExts.filter(ext => ext !== 'css');

module.exports = config;
