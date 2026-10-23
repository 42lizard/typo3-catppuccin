import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { palette, states, build, contrast, accessiblePair } from '../Build/build.mjs';

test('all four flavours use their own fixed colour scheme and complete palette', () => {
    const css = build();
    assert.deepEqual(Object.keys(palette.flavours), ['latte', 'frappe', 'macchiato', 'mocha']);
    for (const [id, flavour] of Object.entries(palette.flavours)) {
        assert.equal(Object.keys(flavour.colors).length, 26);
        assert.ok(css.includes(`:root[data-theme="catppuccin-${id}"] {\n    color-scheme: only ${flavour.dark ? 'dark' : 'light'};`));
        for (const hex of Object.values(flavour.colors)) assert.match(hex, /^#[a-f0-9]{6}$/);
    }
    // No unconditional :root / body selector may recolour Core themes or frontend.
    for (const rule of css.replace(/\/\*[\s\S]*?\*\//g, '').split('}').filter((part) => part.trim())) {
        for (const selector of rule.split('{')[0].trim().split(',\n')) {
            assert.match(selector.trim(), /^:root\[data-theme="catppuccin-(latte|frappe|macchiato|mocha)"\]$/);
        }
    }
});

test('semantic text and solid buttons meet 4.5:1 contrast in every flavour', () => {
    for (const flavour of Object.values(palette.flavours)) {
        for (const color of Object.values(states)) {
            const pair = accessiblePair(flavour.colors, color);
            assert.ok(contrast(pair.on, pair.bg) >= 4.5, `${flavour.name}: ${color} button`);
            assert.ok(contrast(pair.readable, flavour.colors.base) >= 4.5, `${flavour.name}: ${color} text`);
        }
    }
});

test('every Catppuccin variable reference is defined in every flavour', () => {
    const css = build();
    const references = [...css.matchAll(/var\((--ctp-[\w-]+)\)/g)].map((match) => match[1]);
    const blocks = css.split('}\n\n').slice(0, 4);
    for (const block of blocks) {
        const definitions = new Set([...block.matchAll(/(--ctp-[\w-]+):/g)].map((match) => match[1]));
        for (const reference of references) assert.ok(definitions.has(reference), reference);
    }
});

// Resolve the generated declarations, so these checks also catch incorrect
// connections between palette values and TYPO3's actual component variables.
function themeColor(flavour, property) {
    const declarations = new Map();
    for (const [, selectors, body] of build().matchAll(/([^{}]+)\{([^{}]+)\}/g)) {
        if (!selectors.includes(`data-theme="catppuccin-${flavour}"`)) continue;
        for (const [, name, value] of body.matchAll(/(--[\w-]+):\s*([^;]+);/g)) {
            declarations.set(name, value.trim());
        }
    }
    const visited = new Set();
    let value = property;
    while (value.startsWith('--')) {
        assert.ok(!visited.has(value), `Circular variable: ${value}`);
        visited.add(value);
        assert.ok(declarations.has(value), `Missing variable: ${value}`);
        value = declarations.get(value).replace(/^var\((--[\w-]+)\)$/, '$1');
    }
    assert.match(value, /^#[a-f0-9]{6}$/);
    return value;
}

test('tree and inherited-text search highlights remain readable in every flavour', () => {
    for (const [id, flavour] of Object.entries(palette.flavours)) {
        const bg = themeColor(id, '--typo3-component-match-highlight-bg');
        const foreground = themeColor(id, '--typo3-component-match-highlight-color');
        assert.ok(contrast(foreground, bg) >= 4.5, `${id}: tree highlight`);
        assert.ok(contrast(flavour.colors.text, bg) >= 4.5, `${id}: inherited-text highlight`);
    }
});

test('generated button colours retain contrast on hover and focus', () => {
    for (const id of Object.keys(palette.flavours)) {
        for (const state of Object.keys(states)) {
            for (const interaction of ['hover', 'focus']) {
                const color = themeColor(id, `--typo3-state-${state}-${interaction}-color`);
                const bg = themeColor(id, `--typo3-state-${state}-${interaction}-bg`);
                const normalBg = themeColor(id, `--typo3-state-${state}-bg`);
                assert.notEqual(bg, normalBg, `${id}/${state}: ${interaction} feedback`);
                assert.ok(contrast(color, bg) >= 4.5, `${id}/${state}: ${interaction} contrast`);
            }
        }
    }
});

test('distributed CSS matches the generator', () => {
    assert.equal(readFileSync(new URL('../Resources/Public/Css/catppuccin.css', import.meta.url), 'utf8'), build());
});
