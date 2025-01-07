<?php
/**
 * Mailchimp_Location_Detector class
 *
 * @package mailchimp
 */

declare(strict_types=1);

namespace Mailchimp\WordPress\Includes\Utility;

/**
 * Mailchimp_Location_Detector class
 *
 * Utility class to determine the location of the plugin in different directories.
 *
 * This class must be used early in the load process to set up important constants for the plugin.
 *
 * Argument for the $file_path parameter to allow setting the locations flexibility for testing.
 */
class Mailchimp_Location_Detector {
	/**
	 * Plugin locations.
	 *
	 * @var array
	 */
	private $locations;

	/**
	 * Base directory name.
	 *
	 * @var string
	 */
	private $dir_base;

	/**
	 * File path.
	 *
	 * @var string
	 */
	private $file_path;

	/**
	 * Directory path.
	 *
	 * @var string
	 */
	private $dir_path;

	/**
	 * Constructor.
	 *
	 * @param string      $file_path The file path.
	 * @param string|null $dir_base The base directory name. Defaults to null.
	 */
	public function __construct( $file_path, $dir_base = null ) {
		$this->file_path = $file_path;
		$this->dir_path  = dirname( $file_path );
		$this->dir_base  = $dir_base ?? trailingslashit( basename( $this->dir_path ) ); // Default: mailchimp/
		$this->initialize_locations();
	}

	/**
	 * Initializes the plugin locations.
	 *
	 * @return void
	 */
	private function initialize_locations() {
		$this->locations = array(
			'plugins'    => array(
				'dir' => plugin_dir_path( $this->file_path ),
				'url' => plugins_url(),
			),
			'mu_plugins' => array(
				'dir' => plugin_dir_path( $this->file_path ),
				'url' => plugins_url(),
			),
			'template'   => array(
				'dir' => trailingslashit( get_template_directory() ) . 'plugins/',
				'url' => trailingslashit( get_template_directory_uri() ) . 'plugins/',
			),
			'stylesheet' => array(
				'dir' => trailingslashit( get_stylesheet_directory() ) . 'plugins/',
				'url' => trailingslashit( get_stylesheet_directory_uri() ) . 'plugins/',
			),
		);
	}

	/**
	 * Detects the plugin location.
	 *
	 * @return array Contains 'dir' and 'url'.
	 */
	private function detect_location(): array {
		$dir_path = trailingslashit( plugin_dir_path( $this->file_path ) );
		$url_path = trailingslashit( plugins_url( '', $this->file_path ) );

		foreach ( $this->locations as $key => $loc ) {
			$dir = trailingslashit( $loc['dir'] ) . $this->dir_base;
			$url = trailingslashit( $loc['url'] ) . $this->dir_base;

			if ( is_file( $dir . basename( $this->file_path ) ) ) {
				$dir_path = $dir;
				$url_path = $url;
				break;
			}
		}

		return [
			'dir' => $dir_path,
			'url' => $url_path,
		];
	}

	/**
	 * Defines the necessary constants.
	 *
	 * @return void
	 */
	public function init() {
		$location = $this->detect_location();

		if ( ! defined( 'MCSF_DIR' ) ) {
			define( 'MCSF_DIR', $location['dir'] );
		}

		if ( ! defined( 'MCSF_URL' ) ) {
			define( 'MCSF_URL', $location['url'] );
		}
	}
}
