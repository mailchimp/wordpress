<?php
/**
 * Class responsible for the Analytics admin page.
 *
 * @package Mailchimp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mailchimp_Analytics
 */
class Mailchimp_Analytics {

	/**
	 * Initialize the class.
	 */
	public function init() {
		if ( ! $this->is_connected() ) {
			return;
		}

		add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Check if a Mailchimp account is connected.
	 *
	 * @return bool
	 */
	private function is_connected() {
		$user = get_option( 'mc_user' );
		return $user && ( get_option( 'mc_api_key' ) || mailchimp_sf_get_access_token() );
	}

	/**
	 * Register the Analytics submenu page under Mailchimp.
	 */
	public function register_admin_page() {
		add_submenu_page(
			'mailchimp_sf_options',
			esc_html__( 'Analytics', 'mailchimp' ),
			esc_html__( 'Analytics', 'mailchimp' ),
			MCSF_CAP_THRESHOLD,
			'mailchimp_sf_analytics',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render the Analytics page.
	 */
	public function render_page() {
		include_once MCSF_DIR . 'includes/admin/templates/analytics.php';
	}

	/**
	 * Enqueue scripts and styles only on the analytics page.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( 'mailchimp_page_mailchimp_sf_analytics' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'mailchimp_sf_admin_css',
			MCSF_URL . 'assets/css/admin.css',
			array(),
			MCSF_VER
		);

		wp_enqueue_style(
			'mailchimp_sf_analytics_css',
			MCSF_URL . 'dist/css/analytics.css',
			array( 'mailchimp_sf_admin_css' ),
			MCSF_VER
		);

		wp_enqueue_script(
			'mailchimp_sf_chartjs',
			MCSF_URL . 'assets/js/chart.umd.min.js',
			array(),
			'4.4.7',
			true
		);

		$dependencies = array( 'mailchimp_sf_chartjs', 'wp-i18n' );
		$version      = MCSF_VER;
		if ( file_exists( MCSF_DIR . '/dist/js/analytics.asset.php' ) ) {
			$asset        = require MCSF_DIR . '/dist/js/analytics.asset.php';
			$dependencies = $asset['dependencies'] ?? array();
			$dependencies = array_merge( $dependencies, array( 'mailchimp_sf_chartjs' ) );
			$version      = $asset['version'] ?? MCSF_VER;
		}

		wp_enqueue_script(
			'mailchimp_sf_analytics_js',
			MCSF_URL . 'dist/js/analytics.js',
			$dependencies,
			$version,
			true
		);

		wp_localize_script(
			'mailchimp_sf_analytics_js',
			'mailchimpSFAnalytics',
			array(
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'mailchimp_sf_analytics_admin_nonce' ),
				'dateFormat'  => get_option( 'date_format', 'Y-m-d' ),
				'startOfWeek' => (int) get_option( 'start_of_week', 0 ),
				'settingsUrl' => admin_url( 'admin.php?page=mailchimp_sf_options' ),
			)
		);
	}
}
