<?php
/**
 * Pure-function tests runnable with plain `php` (no WordPress bootstrap).
 * Covers the sanitiser and the destination normaliser.
 */
define( 'ABSPATH', __DIR__ );
// minimal stubs for WP functions referenced by the included file
if ( ! function_exists( 'apply_filters' ) ) { function apply_filters( $t, $v ) { return $v; } }
if ( ! function_exists( 'wp_strip_all_tags' ) ) { function wp_strip_all_tags( $s ) { return trim( strip_tags( (string) $s ) ); } }

require __DIR__ . '/../includes/accessible-nav-destinations.php';

$fail = 0; $n = 0;
function check( $name, $cond ) { global $fail, $n; $n++; echo ( $cond ? "  ok  " : "  FAIL" ) . " $name\n"; if ( ! $cond ) $GLOBALS['fail']++; }

// sanitize_hex
check( '6-digit hex kept', accessible_nav_sanitize_hex( '#1A2B3C' ) === '#1A2B3C' );
check( '3-digit hex kept', accessible_nav_sanitize_hex( '#abc' ) === '#abc' );
check( 'missing hash -> fallback', accessible_nav_sanitize_hex( '1A2B3C' ) === '#9C2A24' );
check( 'garbage -> fallback', accessible_nav_sanitize_hex( 'red; }' ) === '#9C2A24' );
check( 'custom fallback honoured', accessible_nav_sanitize_hex( 'x', '#000000' ) === '#000000' );

// normalize_destinations
$in = array(
	array( 'label' => '  Home  ', 'href' => '/' ),
	array( 'label' => 'Home dup', 'href' => '/' ),         // dup href -> dropped
	array( 'label' => '', 'href' => '/empty' ),             // empty label -> dropped
	array( 'label' => 'About', 'href' => '' ),              // empty href -> dropped
	array( 'label' => '<b>Courses</b>', 'href' => '/courses/' ),
);
$out = accessible_nav_normalize_destinations( $in );
check( 'dedupes + drops invalid', count( $out ) === 2 );
check( 'trims label', $out[0]['label'] === 'Home' );
check( 'strips tags in label', $out[1]['label'] === 'Courses' );
check( 'clamps label to 60 chars', strlen( accessible_nav_normalize_destinations(
	array( array( 'label' => str_repeat( 'x', 80 ), 'href' => '/x' ) ) )[0]['label'] ) === 60 );

echo "\n$n checks, $fail failed\n";
exit( $fail ? 1 : 0 );
