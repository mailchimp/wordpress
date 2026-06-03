<?php
/**
 * Mailchimp admin notices class.
 *
 * Registers notices and renders them on the admin_notices hook in the same request.
 *
 * @since 2.1.0
 *
 * @package Mailchimp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mailchimp_Admin_Notices
 *
 * @since 2.1.0
 */
class Mailchimp_Admin_Notices {

	/**
	 * Singleton instance.
	 *
	 * @var Mailchimp_Admin_Notices|null
	 */
	private static $instance = null;

	/**
	 * Queued notices for the current request.
	 *
	 * @var array<int, array{message: string, type: string}>
	 */
	private $notices = array();

	/**
	 * Get the singleton instance.
	 *
	 * @return Mailchimp_Admin_Notices
	 */
	public static function instance(): Mailchimp_Admin_Notices {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register the admin_notices hook.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_notices', array( $this, 'render' ) );
	}

	/**
	 * Queue an admin notice.
	 *
	 * @param string $message Notice message (already escaped/translated by caller).
	 * @param string $type    Notice type: success or error.
	 * @return void
	 */
	public function add( string $message, string $type ) {
		if ( ! is_admin() ) {
			return;
		}

		if ( did_action( 'admin_notices' ) ) {
			$this->print_notice( $message, $type );
			return;
		}

		$this->notices[] = array(
			'message' => $message,
			'type'    => $type,
		);
	}

	/**
	 * Render all queued notices.
	 *
	 * @return void
	 */
	public function render() {
		foreach ( $this->notices as $notice ) {
			$this->print_notice( $notice['message'], $notice['type'] );
		}

		$this->notices = array();
	}

	/**
	 * Print a single admin notice.
	 *
	 * @param string $message Notice message.
	 * @param string $type    Notice type: success or error.
	 * @return void
	 */
	private function print_notice( string $message, string $type ) {
		$classes = array( 'notice', 'notice-' . sanitize_html_class( $type ) );

		if ( 'success' === $type ) {
			$classes[] = 'is-dismissible';
		}

		$allowed_html = array(
			'a'      => array(
				'href'   => array(),
				'title'  => array(),
				'target' => array(),
			),
			'strong' => array(),
			'em'     => array(),
			'br'     => array(),
		);

		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<p>
				<?php echo wp_kses( $message, $allowed_html ); ?>
			</p>
		</div>
		<?php
	}
}
