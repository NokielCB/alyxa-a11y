=== Alyxa Accessibility ===
Tags: accessibility, a11y, wcag, contrast, dyslexia
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.3.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

An accessibility panel that borrows your theme's own colours, and where every feature is a module you can switch off.

== Description ==

Alyxa Accessibility adds a small button to the corner of your site. Behind it sits a panel where visitors can adjust how the page is presented: text size, spacing, contrast, and so on.

Two things make it different from the usual accessibility widget.

**It does not paint over your theme.** Colours come from the palette in your theme's `theme.json`, so the panel looks like it belongs to the site rather than bolted on. Nothing is loaded from an external server, so no visitor IP address leaves your site.

**Every feature is a module, and a module that is off leaves nothing behind.** No hidden button, no CSS rule waiting under another one, no event listener. You pick what your site needs; the rest is simply not on the page.

= The modules =

Ten modules are on when you install the plugin: larger text, a dyslexia-friendly font, more spacing, high contrast, underlined links, stopped animations, a large cursor, a reading mask, reading aloud, and reading whatever you click.

Fourteen more sit in the catalogue, switched off, waiting for the site that needs them: your own colours, dark mode, fewer colours, invert colours, a colour blindness filter, a colour overlay, dimming, text alignment, bolder text, hide pictures, mute sound, a reading line, keyboard shortcuts, and a link to your accessibility statement. Switch on the ones that fit your site and leave the rest out of it - they cost nothing while they are off.

= Every module says how it relates to WCAG =

The plugin is not limited to what the guidelines recommend, but it does not hide where it steps outside them. Each module carries one of three marks, shown next to it on the settings screen:

* **Supports** - it meets the success criterion named on it, for example 1.4.8 for your own colours.
* **Outside WCAG** - the guidelines do not cover it, and it makes nothing worse.
* **Risk** - it can make something WCAG measures worse. The reason is written next to it in one sentence, the tile in the panel carries a warning sign, and a note at the bottom of the panel lists every such module with its reason.

Seven modules are marked as a risk: fewer colours, invert colours, the colour blindness filter, the colour overlay, dimming, text alignment (for its centred and line-end settings) and hide pictures. A theme or plugin adding its own module declares its mark the same way.

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
3. The panel button appears in the corner of the site, with the standard set of modules already on.
4. Optional: go to **Settings - Accessibility** to choose which modules the site offers, which corner the button sits in, and where reading aloud should start from.

== Frequently Asked Questions ==

= Does my site need this to meet WCAG? =

No. WCAG applies to the page itself, not to add-ons placed on top of it. This panel is a convenience for visitors, not a compliance measure, and it is honest about that.

= Who can change the settings? =

Only users who can manage options - an administrator on a normal site. An editor writing content does not decide which accessibility features the whole site offers; that decision is made once, for the site.

= Can I switch a module off for good? =

Yes, and that is the point. A module that is off is not hidden behind a rule or greyed out - its tile is not rendered, its CSS never reaches the stylesheet the site serves, and its JavaScript never attaches a listener. Turning modules off makes the page smaller, not larger.

= What are the keyboard shortcuts? =

Alt+Shift and the letter shown in the corner of each tile; Alt+Shift+A opens and closes the panel. They work only when the keyboard shortcuts module is on and the visitor has switched it on for themselves, and they stay quiet while the visitor is typing in a field. A theme can change any letter through the module register, without forking the plugin.

= Some modules are marked as a risk. Why ship them at all? =

Because they genuinely help some people, and can genuinely take something away. Fewer colours flattens hues that differ only in tone; a colour overlay and dimming lower the contrast of the whole page; research has not shown that coloured overlays help with dyslexia, and some people still read more comfortably with one. Every such module is off by default, says what it costs in its own description, and carries a warning sign in the panel. The visitor decides. That is a different thing from a plugin that quietly does it to everyone.

= Do visitor's own colours break WCAG if they pick a poor pair? =

No. WCAG judges the page as the site presents it, not a combination a visitor chose for themselves - someone with light sensitivity may want grey on black on purpose. The panel does not forbid it; it shows the contrast of the chosen pair while it is being chosen, and says so plainly when it falls below 4.5:1. The four ready palettes all stay above 8:1, links included. The panel itself keeps its own colours, so a visitor can always find the way back.

= What does the plugin store about visitors? =

Whatever they switch on, in their own browser's local storage, under one key. Nothing is sent to the server, nothing is a cookie, no consent banner is needed and the site owner cannot read it. The plugin adds a paragraph to the privacy policy wizard under Settings - Privacy saying exactly that, ready to paste. Read-aloud uses the speech engine already installed on the visitor's device; the text being read never leaves the browser.

= Will it work with my theme? =

It reads the colour presets a block theme publishes from `theme.json`. With a classic theme that publishes none, it falls back to neutral colours that stay readable. Either way it does not require any change to the theme.

== Credits ==

Atkinson Hyperlegible Next was created by the Braille Institute of America, Inc. and is used here under the SIL Open Font License 1.1. The licence text ships with the plugin in `assets/fonts/OFL.txt`.

== Changelog ==

= 1.3.0 =
* Every module now says how it relates to WCAG: supports, outside WCAG, or a risk with the reason written out. The settings screen shows the mark next to each module; risky tiles carry a warning sign in the panel, and a note at the bottom of the panel explains each one.
* New module: your own colours. Text, background and links, four ready palettes or any colour you like, with the contrast of your choice shown while you choose. Wins over high contrast and dark mode. The panel keeps its own colours, so a poor pair never hides the way back.
* New modules marked as a risk: invert colours (photographs are turned back), a colour blindness filter for protanopia, deuteranopia and tritanopia, a colour overlay in four tints, and dimming in three steps.
* Fewer colours, invert colours and the colour blindness filter now share one filter chain, so they can be on together instead of the last one silently winning.
* The panel header no longer spills out of the panel at 320 pixels with text at 150%: its two icon buttons are a fixed 44 pixels instead of growing with the text.

= 1.2.0 =
* Text alignment is one tile that cycles, instead of a minus and a plus. The drawing and the label change with the setting, so the tile answers "what is set right now" without being read out. Stepped modules with the obieg flag get this control; a scale of more and less keeps the pair of buttons, because a minus that means "less alignment" promises something that does not exist.
* New register key: ikony - one drawing per step, for modules whose steps are equal alternatives rather than a scale.

= 1.1.0 =
* The panel header is now sticky, and the reset button sits in it. With nineteen tiles the way out of a mistake was at the bottom of a long scroll; now it is always one click away. There is only one reset button, not two.
* Align text left became text alignment, in three settings: line start, centred, line end, and then back to whatever the page does on its own. Centring and right alignment do not help anyone read a long text, and the module says so in its own description - they are there for people who want them, and the first setting in the cycle is still the one with the evidence behind it.
* New register key for stepped modules: obieg. With it, the plus button on the last step returns to zero instead of standing still. It is off for the text size module on purpose - the way back through an even larger size is worse than a button that does nothing.

= 1.0.0 =
* First stable release. Nineteen modules, ten of them on out of the box.
* A paragraph for the privacy policy wizard, in the site's own language: the plugin keeps the visitor's choices in their browser and sends nothing anywhere.
* Verified with axe-core on the whole page in four states - plain, high contrast, dark mode, and dark mode with the text and spacing modules on top: no violations in any of them.
* Contrast measured across every pair of high contrast or dark mode with each of the other seventeen modules: the worst pair sits at 10.5:1, well past the AAA threshold of 7:1.
* Checked at 360 CSS pixels and at 640, the width a 1280 window gives you at 200% browser zoom: no horizontal scrolling in any module combination, and the panel always fits inside the viewport.
* The shortcut badge went from 11 to 12 pixels. It was the smallest text in the panel, and a badge you have to guess at is not a badge.

= 0.11.0 =
* The optional catalogue: nine more modules, all off by default. Dark mode, fewer colours, align text left, bolder text, hide pictures, mute sound, a reading line, keyboard shortcuts, and a link to the accessibility statement.
* The panel is now divided into sections. With nineteen possible tiles, one flat grid was a list you had to walk to the end of; a section heading is a jump for a screen reader and a place to stop looking for everyone else.
* Keyboard shortcuts: Alt+Shift and the letter shown on each tile. Each module declares its own letter in the register, so a module added by a theme gets one the same way.
* Two modules show their tile only where they have something to do: hide pictures on a page with pictures, mute sound on a page with a recording.
* A link to your accessibility statement can now sit in the panel. The address is a field on the settings screen; with no address the module draws nothing, and the screen says so.
* The reading mask and the new reading line share one mechanism for following the pointer and the keyboard focus, instead of two listening separately to every mouse move.

= 0.10.0 =
* A settings screen, under Settings - Accessibility, for users who can manage options. Modules are grouped into sections; each one says what it does.
* Starter sets: minimal, public institution, everything. A set switches every module at once, including the ones it leaves out, so it is a starting point rather than an addition.
* The area that "Read the page" starts from is now a field on that screen, instead of a filter only a developer could reach. The filter is still there and still wins.
* The module stylesheet is rebuilt whenever the settings option is written, no matter who writes it - the screen, WP-CLI or a deployment script. Before, only the screen rebuilt it.
* A status box says which modules are on and whether the stylesheet is a file or had to be written into the page, which is what happens on hosting that cannot write to the uploads folder.

= 0.9.0 =
* New module: read what you click. With it on, the voice reads a single paragraph, heading, table cell or link - the one you click, or the one you reach with the Tab key - instead of the whole page.
* A link you click is cut short, because the click takes you to another page and leaving a page silences the voice. Reach the link with the Tab key to hear all of it. Holding up navigation so a link could finish reading would break the site to make the feature work.
* Both reading modules now share one speech engine, so starting one stops the other and the buttons of the module that was interrupted go back to their resting state.
* Like reading the whole page, the new module is not shown at all unless the device has a voice for the language of the page.

= 0.8.0 =
* The panel is now a grid of tiles instead of a list of rows. Each tile carries a line drawing, and a tile that is on shows a check badge, the accent colour and a thicker border - three signals, so the state survives greyscale, the system high contrast mode and print.
* Text size moved from one button cycling through the steps to a minus, a percentage and a plus. The way back used to run through a size even larger than the one that was already in the way.
* The number of columns is not written down anywhere. Columns are sized in relative units, so they grow with the text and drop out on their own when the panel meets the edge of the window - at 320 px with text at 150% a single column is left, with nothing scrolling sideways.
* Colours still come from the theme. The layout is borrowed; the palette is not.

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
