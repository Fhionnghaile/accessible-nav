<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Sanitize a hex colour, falling back to a safe default. Pure function (unit-tested).
 */
function accessible_nav_sanitize_hex( $value, $fallback = '#9C2A24' ) {
	$value = is_string( $value ) ? trim( $value ) : '';
	if ( preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value ) ) {
		return $value;
	}
	return $fallback;
}

/**
 * Build the palette destination list from the site's primary menu (or published
 * pages as a fallback). Generic — no hard-coded routes. Filterable.
 *
 * @return array<int, array{label:string, href:string, keys:string}>
 */
function accessible_nav_destinations() {
	$items = array();

	$locations = function_exists( 'get_nav_menu_locations' ) ? get_nav_menu_locations() : array();
	$menu_id   = $locations['primary'] ?? ( $locations ? reset( $locations ) : 0 );

	if ( $menu_id && function_exists( 'wp_get_nav_menu_items' ) ) {
		$menu_items = wp_get_nav_menu_items( $menu_id );
		if ( $menu_items ) {
			foreach ( $menu_items as $mi ) {
				$items[] = array( 'label' => $mi->title, 'href' => $mi->url, 'keys' => '' );
			}
		}
	}

	if ( empty( $items ) && function_exists( 'get_pages' ) ) {
		foreach ( (array) get_pages( array( 'sort_column' => 'menu_order,post_title', 'number' => 30 ) ) as $p ) {
			$items[] = array( 'label' => $p->post_title, 'href' => get_permalink( $p ), 'keys' => '' );
		}
	}

	/**
	 * Filter the command-palette destinations.
	 *
	 * @param array $items List of ['label'=>, 'href'=>, 'keys'=>].
	 */
	return apply_filters( 'accessible_nav_destinations', accessible_nav_normalize_destinations( $items ) );
}

/**
 * Normalise/clamp destination rows (pure function, unit-tested).
 */
function accessible_nav_normalize_destinations( array $items ) {
	$out  = array();
	$seen = array();
	foreach ( $items as $it ) {
		$label = isset( $it['label'] ) ? trim( wp_strip_all_tags_safe( $it['label'] ) ) : '';
		$href  = isset( $it['href'] ) ? trim( (string) $it['href'] ) : '';
		if ( '' === $label || '' === $href ) {
			continue;
		}
		$key = rtrim( $href, '/' );
		if ( isset( $seen[ $key ] ) ) {
			continue;
		}
		$seen[ $key ] = true;
		$out[] = array(
			'label' => mb_substr( $label, 0, 60 ),
			'href'  => $href,
			'keys'  => isset( $it['keys'] ) ? (string) $it['keys'] : '',
		);
	}
	return $out;
}

/** Tiny shim so the normaliser is testable without WordPress loaded. */
if ( ! function_exists( 'wp_strip_all_tags_safe' ) ) {
	function wp_strip_all_tags_safe( $s ) {
		return function_exists( 'wp_strip_all_tags' ) ? wp_strip_all_tags( $s ) : trim( strip_tags( (string) $s ) );
	}
}
