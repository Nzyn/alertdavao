const { getDefaultConfig } = require('expo/metro-config');

const config = getDefaultConfig(__dirname);

// Exclude react-native-maps from being bundled on web
const blockList = [
  // Ignore react-native-maps on web
  /node_modules\/react-native-maps\/.*/,
];

config.resolver.blockList = blockList;

// Tell metro to not resolve react-native-maps on web
config.resolver.sourceExts = ['web.js', 'web.ts', 'web.tsx', 'js', 'jsx', 'ts', 'tsx', 'json'];

module.exports = config;
