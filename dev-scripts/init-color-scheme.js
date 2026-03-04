#!/usr/bin/env node

/**
 * Colour Scheme Initialiser
 *
 * Generates a neutral, professional colour scheme from a single accent colour
 * and writes it to assets/theme-config.json.
 *
 * The neutral palette (charcoal → silver → light) is fixed cool-grey.
 * Only the accent and blue-100 derive from the input hex.
 *
 * Usage:
 *   node dev-scripts/init-color-scheme.js #0707a3
 *   node dev-scripts/init-color-scheme.js 0707a3
 *   npm run init-colors -- #0707a3
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const CONFIG_PATH = path.join(__dirname, '../assets/theme-config.json');

// ─── Colour helpers ──────────────────────────────────────────────────────────

function parseHex(hex) {
	const h = hex.replace(/^#/, '');
	if (!/^[0-9a-fA-F]{6}$/.test(h)) {
		throw new Error(`Invalid hex colour: "${hex}"`);
	}
	return {
		r: parseInt(h.slice(0, 2), 16),
		g: parseInt(h.slice(2, 4), 16),
		b: parseInt(h.slice(4, 6), 16),
	};
}

function toHex({ r, g, b }) {
	return '#' + [r, g, b].map((v) => Math.round(v).toString(16).padStart(2, '0')).join('');
}

/** Mix two rgb colours: 0 = all a, 1 = all b */
function mix(a, b, t) {
	return {
		r: a.r + (b.r - a.r) * t,
		g: a.g + (b.g - a.g) * t,
		b: a.b + (b.b - a.b) * t,
	};
}

/** Perceived luminance (0–1) */
function luminance({ r, g, b }) {
	const chan = (c) => {
		const s = c / 255;
		return s <= 0.03928 ? s / 12.92 : Math.pow((s + 0.055) / 1.055, 2.4);
	};
	return 0.2126 * chan(r) + 0.7152 * chan(g) + 0.0722 * chan(b);
}

function contrastRatio(a, b) {
	const la = luminance(a) + 0.05;
	const lb = luminance(b) + 0.05;
	return la > lb ? la / lb : lb / la;
}

const WHITE = { r: 255, g: 255, b: 255 };
const CHARCOAL = parseHex('#1a1a2a');

/** Choose white or charcoal foreground, whichever has better contrast */
function bestForeground(bg) {
	const onWhite = contrastRatio(bg, WHITE);
	const onDark = contrastRatio(bg, CHARCOAL);
	return onDark > onWhite ? 'var(--color-charcoal)' : 'var(--color-white)';
}

// ─── Palette builder ─────────────────────────────────────────────────────────

function buildPalette(accentHex) {
	const accent = parseHex(accentHex);
	const accentLight = mix(WHITE, accent, 0.08); // 8% accent, 92% white

	const accentFg = bestForeground(accent);
	const accentLightFg = bestForeground(accentLight);
	const accentLightLinkColor =
		accentLightFg === 'var(--color-charcoal)' ? 'var(--color-accent)' : 'var(--color-white)';

	return {
		colors: {
			base: {
				blue: {
					color: toHex(accent),
					name: 'Blue',
					block_editor: true,
					foreground: accentFg,
					properties: {
						'--link--color':
							accentFg === 'var(--color-white)' ? 'var(--color-white)' : 'var(--color-charcoal)',
						'--link--color-hover':
							accentFg === 'var(--color-white)' ? 'var(--color-white)' : 'var(--color-charcoal)',
					},
				},
				accent: { namedColor: 'blue' },
				'blue-100': {
					color: toHex(accentLight),
					name: 'Accent Light',
					block_editor: true,
					foreground: accentLightFg,
					properties: {
						'--link--color': accentLightLinkColor,
						'--link--color-hover': accentLightLinkColor,
					},
				},
				charcoal: {
					color: '#1a1a2a',
					name: 'Charcoal',
					block_editor: true,
					foreground: 'var(--color-white)',
					properties: {
						'--link--color': 'var(--color-blue-100)',
						'--link--color-hover': 'var(--color-white)',
					},
				},
				slate: {
					color: '#4a4a5c',
					name: 'Slate',
					block_editor: true,
					foreground: 'var(--color-white)',
					properties: {
						'--link--color': 'var(--color-blue-100)',
						'--link--color-hover': 'var(--color-white)',
					},
				},
				grey: {
					color: '#6b6b7b',
					name: 'Grey',
					foreground: 'var(--color-white)',
					properties: {
						'--link--color': 'var(--color-white)',
						'--link--color-hover': 'var(--color-white)',
					},
				},
				silver: {
					color: '#d0d0da',
					name: 'Silver',
					foreground: 'var(--color-charcoal)',
				},
				light: {
					color: '#f2f2f7',
					name: 'Light',
					block_editor: true,
					foreground: 'var(--color-charcoal)',
					properties: {
						'--link--color': 'var(--color-accent)',
						'--link--color-hover': 'var(--color-charcoal)',
					},
				},
				white: {
					color: '#ffffff',
					foreground: 'var(--color-charcoal)',
					properties: {
						'--link--color': 'var(--color-accent)',
						'--link--color-hover': 'var(--color-charcoal)',
					},
				},
				black: {
					color: '#111118',
					foreground: 'var(--color-white)',
					properties: {
						'--link--color': 'var(--color-blue-100)',
						'--link--color-hover': 'var(--color-white)',
					},
				},
				red: {
					color: '#c0392b',
					foreground: 'var(--color-white)',
					properties: {
						'--link--color': 'var(--color-white)',
						'--link--color-hover': 'var(--color-white)',
					},
				},
				'accent': { namedColor: 'accent' },
				'brand-2': { namedColor: 'blue-100' },
				foreground: { namedColor: 'charcoal' },
				background: { namedColor: 'white' },
				error: { namedColor: 'red' },
			},
		},
	};
}

// ─── Main ────────────────────────────────────────────────────────────────────

const arg = process.argv[2];

if (!arg || arg === '--help' || arg === '-h') {
	console.log(`
Usage:
  node dev-scripts/init-color-scheme.js <accent-hex>

Examples:
  node dev-scripts/init-color-scheme.js "#0707a3"
  node dev-scripts/init-color-scheme.js 0707a3

Writes a neutral professional colour scheme to assets/theme-config.json
using <accent-hex> as the primary brand colour.
`);
	process.exit(arg ? 0 : 1);
}

try {
	const palette = buildPalette(arg);
	fs.writeFileSync(CONFIG_PATH, JSON.stringify(palette, null, 4) + '\n');

	const accent = parseHex(arg);
	const accentLight = mix({ r: 255, g: 255, b: 255 }, accent, 0.08);

	console.log(`\n✓ Colour scheme written to assets/theme-config.json\n`);
	console.log(`  accent       ${toHex(accent)}`);
	console.log(`  blue-100 ${toHex(accentLight)}`);
	console.log(`  charcoal     #1a1a2a`);
	console.log(`  slate        #4a4a5c`);
	console.log(`  grey         #6b6b7b`);
	console.log(`  silver       #d0d0da`);
	console.log(`  light        #f2f2f7`);
	console.log(`  white        #ffffff`);
	console.log(`  black        #111118`);
	console.log(`  red          #c0392b`);
	console.log(`\nRun \`npm run dev\` to rebuild.\n`);
} catch (err) {
	console.error(`Error: ${err.message}`);
	process.exit(1);
}
