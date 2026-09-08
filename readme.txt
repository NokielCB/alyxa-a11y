=== Alyxa Accessibility ===
Tags: accessibility, a11y, wcag, contrast, dyslexia
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 0.3.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

An accessibility panel that borrows your theme's own colours, and where every feature is a module you can switch off.

== Description ==

Alyxa Accessibility adds a small button to the corner of your site. Behind it sits a panel where visitors can adjust how the page is presented: text size, spacing, contrast, and so on.

Two things make it different from the usual accessibility widget.

**It does not paint over your theme.** Colours come from the palette in your theme's `theme.json`, so the panel looks like it belongs to the site rather than bolted on. Nothing is loaded from an external server, so no visitor IP address leaves your site.

**Every feature is a module, and a module that is off leaves nothing behind.** No hidden button, no CSS rule waiting under another one, no event listener. You pick what your site needs; the rest is simply not on the page.

= What this plugin does not do =

It does not claim to make an inaccessible site compliant. Accessibility belongs to the page: the headings, the alternative text, the colour contrast, the keyboard order. A panel cannot repair those, and any plugin that says it can is selling you a problem, not a solution.

So there are no "profiles" promising a blind mode or an ADHD mode, no JavaScript that rewrites your alternative text or your ARIA at page load, and no screen reader of our own. Visitors who use a screen reader already have one, and it is better than anything a web page can offer.

= Privacy =

The visitor's choices are stored in their own browser, in `localStorage`. They are never sent to the server, so the plugin sets no cookies, collects no personal data, and needs no consent banner.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or install it through the Plugins screen.
2. Activate it.
3. The panel button appears in the corner of the site.

== Frequently Asked Questions ==

= Does my site need this to meet WCAG? =

No. WCAG applies to the page itself, not to add-ons placed on top of it. This panel is a convenience for visitors, not a compliance measure, and it is honest about that.

= Will it work with my theme? =

It reads the colour presets a block theme publishes from `theme.json`. With a classic theme that publishes none, it falls back to neutral colours that stay readable. Either way it does not require any change to the theme.

== Changelog ==

= 0.3.0 =
* New module: larger text, in three steps up to 150%.
* Scaling follows the reader's own browser font size instead of replacing it.
* Themes with fluid type can read `--alyxa-skala-tekstu` so that headings written as `clamp()` grow by the same factor as body text.

= 0.2.0 =
* Three modules: more text spacing, underline links, stop animations.
* Stopping animations also removes hover movement on themes that expose a motion switch; on other themes movement becomes instant instead of animated.

= 0.1.0 =
* First release: module registry, panel, button, and per-visitor state. No modules yet.
