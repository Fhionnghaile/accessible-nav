<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Minimal settings page using the Settings API. The only setting is the accent
 * colour used by the floating button and active states.
 */
class Accessible_Nav_Settings {

	const OPTION = 'accessible_nav_options';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
	}

	public static function defaults() {
		return array( 'accent_color' => '#9C2A24' );
	}

	public static function accent_color() {
		$opts = get_option( self::OPTION, self::defaults() );
		return accessible_nav_sanitize_hex( $opts['accent_color'] ?? '#9C2A24' );
	}

	public static function menu() {
		add_options_page(
			__( 'Accessible Nav', 'accessible-nav' ),
			__( 'Accessible Nav', 'accessible-nav' ),
			'manage_options',
			'accessible-nav',
			array( __CLASS__, 'render' )
		);
	}

	public static function register() {
		// register_setting wires in the Settings API nonce + capability handling.
		register_setting( 'accessible_nav', self::OPTION, array(
			'type'              => 'array',
			'sanitize_callback' => array( __CLASS__, 'sanitize' ),
			'default'           => self::defaults(),
		) );

		add_settings_section( 'accessible_nav_main', '', '__return_false', 'accessible-nav' );
		add_settings_field(
			'accent_color', __( 'Accent colour', 'accessible-nav' ),
			array( __CLASS__, 'field_accent' ), 'accessible-nav', 'accessible_nav_main'
		);
	}

	public static function sanitize( $input ) {
		return array(
			'accent_color' => accessible_nav_sanitize_hex( $input['accent_color'] ?? '#9C2A24' ),
		);
	}

	public static function field_accent() {
		$val = self::accent_color();
		printf(
			'<input type="text" name="%s[accent_color]" value="%s" class="regular-text" placeholder="#9C2A24">',
			esc_attr( self::OPTION ), esc_attr( $val )
		);
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		echo '<div class="wrap"><h1>' . esc_html__( 'Accessible Nav', 'accessible-nav' ) . '</h1>';
		echo '<form method="post" action="options.php">';
		settings_fields( 'accessible_nav' );          // nonce + referer
		do_settings_sections( 'accessible-nav' );
		submit_button();
		echo '</form></div>';
	}
}
