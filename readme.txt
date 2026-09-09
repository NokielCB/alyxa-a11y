=== Alyxa Accessibility ===
Tags: accessibility, a11y, wcag, contrast, dyslexia
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 0.7.0
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

The dyslexia-friendly font is served from your own site. Nothing is requested from Google Fonts or any other content network, so no visitor IP address is handed to a third party.

Reading aloud uses the speech engine already installed on the visitor's device, through the browser's own `speechSynthesis`. The text never passes through your server. Some browsers also offer network voices, which send the text they speak to the browser vendor - so the plugin prefers a voice that runs on the device, and falls back to a network one only when the device has no local voice for the page's language.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or install it through the Plugins screen.
2. Activate it.
3. The panel button appears in the corner of the site.

== Frequently Asked Questions ==

= Does my site need this to meet WCAG? =

No. WCAG applies to the page itself, not to add-ons placed on top of it. This panel is a convenience for visitors, not a compliance measure, and it is honest about that.

= Will it work with my theme? =

It reads the colour presets a block theme publishes from `theme.json`. With a classic theme that publishes none, it falls back to neutral colours that stay readable. Either way it does not require any change to the theme.

== Credits ==

Atkinson Hyperlegible Next was created by the Braille Institute of America, Inc. and is used here under the SIL Open Font License 1.1. The licence text ships with the plugin in `assets/fonts/OFL.txt`.

== Changelog ==

= 0.7.0 =
* New module: read aloud. It reads the main content of the page with a voice from the visitor's own device - start, pause and stop, with the reading stopped when they leave the page or change any other setting.
* The button does not appear at all when the device has no voice for the language the page declares. A Polish page read by an English voice sounds like a broken site, not like a missing voice.
* Only what is on the screen is read. Text hidden for screen readers is skipped, because a visitor who can see the page cannot see where it came from, and so are menus, headers and footers - the reading starts with the content.
* Modules can now be a set of buttons rather than a switch. A switch promises a state that stays; reading is something that happens and ends.

= 0.6.0 =
* New module: large cursor. A 48 px pointer, white with a black outline so it stays visible over photographs, with a matching hand over links and buttons. It ships as SVG and PNG drawn from one set of coordinates, so it looks the same whichever of the two your browser takes.
* New module: reading mask. A band follows the pointer and everything else is dimmed, which keeps the eye from sliding into the next line. It follows the keyboard too: tabbing to a link brings the band along, including while the page is still scrolling.
* The mask keeps working in high contrast. Its dimming is a shadow, and the high contrast module removes shadows, so the mask is now excluded from that rule by name.
* The panel moved above the rest of the page. It used to sit below sticky headers on purpose; the mask has to dim those, and the control that switches the mask off must never end up underneath it.
* Modules can now carry behaviour, not only a stylesheet. The core still knows no module by name - a behaviour declares which module it belongs to, and a module that is off attaches no listener and creates no element.

= 0.5.0 =
* New module: dyslexia-friendly font. Atkinson Hyperlegible Next, drawn so that letters which are easily confused cannot be: a capital I, a lowercase l and the digit 1 all look different, and b, d, p and q are not mirror images of each other.
* The font is served from your own site, not from Google Fonts, so no visitor IP address leaves it.
* Icon fonts and monospaced text keep their own typeface, so menus do not turn into squares and code samples stay aligned.
* A module stylesheet can now point at a file next to it with an ordinary relative `url()`; the paths are made absolute when the stylesheet is built.

= 0.4.0 =
* New module: high contrast — black background, white text, yellow links. Photographs, logos and diagrams stay visible.
* Themes can replace the module's stylesheet with their own through the `alyxa_moduly` filter, so a theme that knows its own colour tokens can do this properly instead of being flattened.
* The panel keeps its own colours in high contrast, so the control that switches the mode off never disappears into it.

= 0.3.0 =
* New module: larger text, in three steps up to 150%.
* Scaling follows the reader's own browser font size instead of replacing it.
* Themes with fluid type can read `--alyxa-skala-tekstu` so that headings written as `clamp()` grow by the same factor as body text.

= 0.2.0 =
* Three modules: more text spacing, underline links, stop animations.
* Stopping animations also removes hover movement on themes that expose a motion switch; on other themes movement becomes instant instead of animated.

= 0.1.0 =
* First release: module registry, panel, button, and per-visitor state. No modules yet.
