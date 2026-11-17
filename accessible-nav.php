<?php
/**
 * Plugin Name:       Accessible Nav
 * Plugin URI:        https://example.com/plugins/accessible-nav
 * Description:       A self-contained accessibility layer (text size, contrast, reduce-motion, readable font, underline links) plus a keyboard/touch command-palette for fast navigation. Additive and reversible — no theme edits required.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Pádraig Fhionnghaile
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       accessible-nav
 *
 * Distilled and generalised from a production module for public release.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ACCESSIBLE_NAV_VERSION', '1.0.0' );

require_once __DIR__ . '/includes/class-accessible-nav-settings.php';
require_once __DIR__ . '/includes/accessible-nav-destinations.php';

add_action( 'plugins_loaded', function () {
	load_plugin_textdomain( 'accessible-nav', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

add_action( 'init', array( 'Accessible_Nav_Settings', 'init' ) );

/**
 * Inject the stylesheet (accent colour is the only themeable value, from settings).
 */
add_action( 'wp_head', function () {
	$accent = Accessible_Nav_Settings::accent_color();
	$css    = file_get_contents( __DIR__ . '/assets/accessible-nav.css' );
	echo '<style id="accessible-nav-css">:root{--an-accent:' . esc_attr( $accent ) . '}' . "\n" . $css . "</style>\n";
}, 99 );

/**
 * Inject the panel + command-palette markup and behaviour.
 * The destination list is harvested from the site's own nav menus and pages, with a
 * filter for customisation — no hard-coded, site-specific routes.
 */
add_action( 'wp_footer', function () {
	$destinations = accessible_nav_destinations();
	$config       = array(
		'destinations' => $destinations,
		'strings'      => array(
			'open'     => __( 'Navigation open. Type to search.', 'accessible-nav' ),
			'jump'     => __( 'Press a key to jump', 'accessible-nav' ),
			'reset'    => __( 'Settings reset', 'accessible-nav' ),
		),
	);
	$markup = file_get_contents( __DIR__ . '/assets/accessible-nav.html' );
	$js     = file_get_contents( __DIR__ . '/assets/accessible-nav.js' );

	echo $markup;
	echo '<script id="accessible-nav-config" type="application/json">'
		. wp_json_encode( $config ) . "</script>\n";
	echo '<script id="accessible-nav-js">' . $js . "</script>\n";
}, 99 );
