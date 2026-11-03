import tailwindcss from '@tailwindcss/postcss';
import postcssFunctions from 'postcss-functions';
import postcssColorSystem from './dev-scripts/postcss-color-system.js';
import functionsConfig from './dev-scripts/postcss-functions-config.js';

const config = {
    plugins: [postcssColorSystem(), postcssFunctions(functionsConfig), tailwindcss()],
};

export default config;
