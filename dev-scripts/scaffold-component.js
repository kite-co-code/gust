#!/usr/bin/env node

/**
 * Component Scaffold
 *
 * Creates new component boilerplate: a class, a template and a Dev Kit
 * example (example.php, shown at /_dev/components/<name>/).
 *
 * Usage:
 *   node dev-scripts/scaffold-component.js my-component
 *   node dev-scripts/scaffold-component.js my-component --styles --scripts --block
 *
 * Options:
 *   --styles    Create styles.pcss
 *   --scripts   Create scripts.js
 *   --block     Create block.json for ACF block
 *   --all       Create all optional files
 */

import { execSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const COMPONENTS_DIR = path.join(__dirname, '../components');

/**
 * Convert kebab-case to PascalCase
 */
function toPascalCase(str) {
    return str
        .split('-')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join('');
}

/**
 * Convert PascalCase to a title ("HeroBanner" -> "Hero Banner")
 */
function toTitle(className) {
    return className.replace(/([A-Z])/g, ' $1').trim();
}

/**
 * Generate PHP class content
 */
function generateClass(name, className) {
    return `<?php

namespace Gust\\Components;

use Gust\\ComponentBase;

/**
 * ${className} Component
 *
 * Usage:
 *   use Gust\\Components\\${className};
 *
 *   echo ${className}::make(heading: 'Hello');
 */
class ${className} extends ComponentBase
{
    protected static string $name = '${name}';

    /**
     * Create a new ${className} component.
     *
     * @return static|null Returns null if component should not render.
     */
    public static function make(
        array $classes = [],
        string $heading = '',
        ...$others
    ): ?static {
        return static::createFromArgs(static::mergeArgs(get_defined_vars()));
    }

    /**
     * Transform args before rendering.
     */
    protected static function transform(array $args): array
    {
        return $args;
    }
}
`;
}

/**
 * Generate template content
 */
function generateTemplate(name, className) {
    return `<?php
/**
 * ${className} Template
 *
 * @var \\Gust\\Components\\${className} $this
 */
?>

<div class="<?= classes('${name}', $this->classes) ?>" <?= attributes($this->attributes) ?>>
    <?php if (! empty($this->heading)) { ?>
        <h2 class="${name}__heading"><?= esc_html($this->heading) ?></h2>
    <?php } ?>
</div>
`;
}

/**
 * Generate Dev Kit example content (shown at /_dev/components/<name>/)
 */
function generateExample(className) {
    return `<?php

/**
 * ${className} Component Examples
 */

use Gust\\Components\\${className};

?>

<section class="component-example-section">
    <h2 class="component-example-section__title">${toTitle(className)} - Default</h2>
    <p class="component-example-section__description">Basic usage.</p>
    <div class="component-example-section__preview">
        <?= ${className}::make(
            heading: 'Example heading',
        ); ?>
    </div>
</section>
`;
}

/**
 * Generate styles content
 */
function generateStyles(name) {
    return `.${name} {
    /* Component styles */
}
`;
}

/**
 * Generate scripts content
 */
function generateScripts(name) {
    return `import dynamicElements from '../../assets/scripts/helpers/dynamicElements.js';

// Runs once for each .${name} on the page, including ones added later.
dynamicElements.define('.${name}', (element) => {
    // Component logic
});
`;
}

/**
 * Generate block.json content
 */
function generateBlockJson(name, className) {
    const block = {
        $schema: 'https://schemas.wp.org/trunk/block.json',
        apiVersion: 3,
        name: `theme/${name}`,
        title: toTitle(className),
        description: '',
        category: 'theme-blocks',
        icon: 'admin-generic',
        acf: {
            blockVersion: 3,
            mode: 'auto',
            autoInlineEditing: true,
            renderCallback: `Gust\\Components\\${className}::renderBlock`,
        },
        supports: {
            anchor: true,
            align: false,
        },
    };

    return `${JSON.stringify(block, null, 4)}\n`;
}

/**
 * Regenerate Composer's classmap so the new component class autoloads.
 * Component assets need no step here: Vite picks up new styles.pcss and
 * scripts.js files on the next `pnpm build` or `pnpm dev` start.
 *
 * @returns {boolean} Whether the autoloader was regenerated.
 */
function dumpAutoload() {
    try {
        execSync('composer dump-autoload --quiet', {
            cwd: path.join(__dirname, '..'),
            stdio: 'ignore',
        });
        return true;
    } catch {
        return false;
    }
}

/**
 * Main
 */
function main() {
    const args = process.argv.slice(2);

    if (args.length === 0 || args[0] === '--help' || args[0] === '-h') {
        console.log(`
Usage: node dev-scripts/scaffold-component.js <name> [options]

Options:
  --styles    Create styles.pcss
  --scripts   Create scripts.js
  --block     Create block.json for ACF block
  --all       Create all optional files

Examples:
  node dev-scripts/scaffold-component.js hero-banner
  node dev-scripts/scaffold-component.js hero-banner --styles --block
  node dev-scripts/scaffold-component.js hero-banner --all
`);
        process.exit(0);
    }

    const name = args[0];
    const flags = args.slice(1);

    // Validate name
    if (!/^[a-z][a-z0-9-]*$/.test(name)) {
        console.error('Error: Component name must be lowercase with hyphens (e.g., my-component)');
        process.exit(1);
    }

    const className = toPascalCase(name);
    const componentDir = path.join(COMPONENTS_DIR, name);

    // Check if exists
    if (fs.existsSync(componentDir)) {
        console.error(`Error: Component '${name}' already exists`);
        process.exit(1);
    }

    const withStyles = flags.includes('--styles') || flags.includes('--all');
    const withScripts = flags.includes('--scripts') || flags.includes('--all');
    const withBlock = flags.includes('--block') || flags.includes('--all');

    const files = {
        [`${className}.php`]: generateClass(name, className),
        'template.php': generateTemplate(name, className),
        'example.php': generateExample(className),
    };

    if (withStyles) files['styles.pcss'] = generateStyles(name);
    if (withScripts) files['scripts.js'] = generateScripts(name);
    if (withBlock) files['block.json'] = generateBlockJson(name, className);

    fs.mkdirSync(componentDir, { recursive: true });
    console.log(`Created: components/${name}/`);

    for (const [file, content] of Object.entries(files)) {
        fs.writeFileSync(path.join(componentDir, file), content);
        console.log(`Created: ${file}`);
    }

    console.log(`\nComponent '${name}' scaffolded successfully.`);

    if (dumpAutoload()) {
        console.log('Regenerated the Composer autoloader.');
    } else {
        console.log('Could not run composer dump-autoload. Run it before using the component.');
    }

    // Usage hint
    console.log(`\nUsage:`);
    console.log(`  use Gust\\Components\\${className};`);
    console.log(`  echo ${className}::make(heading: 'Hello');`);
    console.log(`\nPreview: /_dev/components/${name}/`);

    if (withStyles || withScripts) {
        console.log(`\nRun pnpm build (or restart pnpm dev) to bundle the new styles/scripts.`);
    }
}

main();
