import tailwindcss from '@tailwindcss/postcss';
import postcssFunctions from 'postcss-functions';
import postcssSimpleVars from 'postcss-simple-vars';
import postcssColorSystem from './dev-scripts/postcss-color-system.js';
import functionsConfig from './dev-scripts/postcss-functions-config.js';
import postcssGlobImport from './dev-scripts/postcss-glob-import.js';

const config = {
    plugins: [
        postcssGlobImport(),
        postcssColorSystem(),
        postcssFunctions(functionsConfig),
        postcssSimpleVars(),
        tailwindcss(),
    ],
};

export default config;
