# Alyxa Accessibility

An accessibility panel for WordPress, built on the active theme's own design tokens.

Every feature is a module that can be switched off site by site — and a module that
is off leaves no button, no CSS rule and no event listener behind. Nineteen modules
ship with the plugin; ten of them are on when you install it.

No dependencies. No jQuery, no npm, no build step. Nothing is loaded from a CDN,
nothing is sent anywhere, and the visitor's choices live in their own browser.

## What it does

**On by default** — larger text (three steps, up to 150%), a dyslexia-friendly font
(Atkinson Hyperlegible Next, bundled), larger spacing, high contrast, underlined
links, stopped animations, a large cursor, a reading mask, reading the page aloud,
and reading aloud whatever you click.

**In the catalogue, off by default** — dark mode, fewer colours, text alignment,
bolder text, hide pictures, mute sound, a reading line, keyboard shortcuts, and a
link to your accessibility statement.

Switch on what fits your site and leave the rest out of it.

## What it is not

It is not a substitute for an accessible site. A panel cannot write an alt text,
cannot pull the words out of a scanned PDF and cannot turn a timetable published as
a photograph into a table. If a plugin promises that, it is promising something no
plugin can do — and in several countries a site that relies on one is still in
breach of the law. This one is a set of controls for people who need something on
top of a site that is already built properly.

## Requirements

WordPress 6.5 or newer, PHP 8.1 or newer. Works with any theme; a theme can replace
any module's stylesheet with one of its own through the `alyxa_moduly` filter, which
is how it fits a design system it was never told about.

## Installing

Copy the `alyxa-a11y` directory into `wp-content/plugins/` and switch it on. The
settings screen is at **Settings → Accessibility** and needs `manage_options`.

## Extending

A module is one entry in the register plus, usually, one CSS file:

```php
add_filter( 'alyxa_moduly', function ( $moduly ) {
	$moduly[] = array(
		'slug'      => 'moj-modul',
		'nazwa'     => __( 'My module', 'my-textdomain' ),
		'opis'      => __( 'What it does, in one sentence.', 'my-textdomain' ),
		'typ'       => 'przelacznik',
		'css'       => __DIR__ . '/moj-modul.css',
		'domyslnie' => false,
	);

	return $moduly;
}, 30 );
```

The stylesheet is merged into one file with the others and served from `uploads/`.
The class `alyxa-moj-modul` lands on `<html>` when the visitor switches the module
on, and nothing at all is emitted while it is off.

Modules needing JavaScript register a behaviour in `assets/js/panel.js`; the core
never knows a single module by name.

## Accessibility of the panel itself

Verified with axe-core on the whole page in four states — plain, high contrast, dark
mode, and dark mode with the text and spacing modules on top: no violations under
WCAG 2.0, 2.1 and 2.2 at levels A and AA. Contrast measured for every pair of a
colour module with each of the other modules: the worst pair sits at 10.5:1, past
the AAA threshold of 7:1. Checked at 320, 360 and 640 CSS pixels with every layout
module on at once: no horizontal scrolling, and the panel always fits the viewport.

Every control is a native `<button>` or a link. There is no `tabindex` anywhere in
the panel, and no keyboard handling of its own beyond the optional shortcuts module.

## Translating

Source strings are English; a Polish catalogue ships in `languages/`. Comments in
the code are Polish, because the reviewer does not grade them and translating code
comments helps nobody.

## Licence

GPL-2.0-or-later. See `LICENSE`.

The bundled font, Atkinson Hyperlegible Next, is under the SIL Open Font License 1.1
— see `assets/fonts/OFL.txt`.
