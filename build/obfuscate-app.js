const { obfuscate } = require('javascript-obfuscator');

module.exports = function obfuscateApp(source) {
    return obfuscate(source, {
        seed: 842,
        compact: true,
        identifierNamesGenerator: 'hexadecimal',
        stringArray: true,
        stringArrayThreshold: 1,
    }).getObfuscatedCode();
};
