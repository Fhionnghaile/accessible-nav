=== Accessible Nav ===
Contributors: fhionnghaile
Tags: accessibility, a11y, navigation, command palette, wcag
Requires at least: 6.2
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A self-contained accessibility toolbar plus a keyboard/touch command-palette for fast site navigation. Additive and reversible.

== Description ==

Accessible Nav adds a floating accessibility control to every page — text size, boosted
contrast, reduced motion, a readable font, and underlined links — with each preference
remembered per visitor. It also adds a command palette (⌘K / Ctrl-K, or the "Search & go"
button) that lets keyboard and touch users jump to any page in your primary menu.

* No theme edits. It attaches via `wp_head`/`wp_footer` and enhances landmarks and skip
  links by attribute only — nothing in your markup is restructured.
* Destinations come from your own primary menu (or published pages) — there are no
  hard-coded routes. Customise them with the `accessible_nav_destinations` filter.
* One setting (accent colour) via the Settings API, sanitised and capability-checked.
* Respects `prefers-reduced-motion` and `prefers-color-scheme`.

== Installation ==

1. Upload the `accessible-nav` folder to `/wp-content/plugins/`.
2. Activate it in **Plugins**.
3. Optionally set an accent colour under **Settings → Accessible Nav**.

== Frequently Asked Questions ==

= Does it store any personal data? =
No. Preferences live in the visitor's own browser (localStorage); nothing is sent to the
server or to third parties.

= How do I change the command-palette destinations? =
Use the `accessible_nav_destinations` filter to add, remove, or reorder entries.

== Changelog ==

= 1.0.0 =
* Initial public release. Distilled and generalised from a production module.
