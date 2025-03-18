<?php
/**
 * Class responsible for Mailchimp List Subscribe Form blocks.
 *
 * @package Mailchimp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mailchimp_Admin
 *
 * @since 1.6.0
 */
class Mailchimp_List_Subscribe_Form_Blocks {

	/**
	 * Initialize the class.
	 */
	public function init() {
		// In line with conditional register of the widget.
		if ( ! mailchimp_sf_get_api() ) {
			return;
		}

		add_action( 'init', array( $this, 'register_blocks' ) );

		add_action( 'rest_api_init', array( $this, 'register_rest_endpoints' ) );
	}

	/**
	 * Register the block.
	 */
	public function register_blocks() {
		// Get the default visibility of merge fields.
		$merge_fields_visibility = array();
		$merge_fields            = get_option( 'mc_merge_vars', array() );
		if ( ! empty( $merge_fields ) ) {
			foreach ( $merge_fields as $field ) {
				$visible                                  = 'on' === get_option( 'mc_mv_' . $field['tag'], 'on' ) || $field['required'];
				$merge_fields_visibility[ $field['tag'] ] = $visible ? 'on' : 'off';
			}
		}

		// Get the default visibility of interest groups.
		$interest_groups_visibility = array();
		$interest_groups            = get_option( 'mc_interest_groups', array() );
		if ( ! empty( $interest_groups ) ) {
			foreach ( $interest_groups as $group ) {
				$visible                                    = 'on' === get_option( 'mc_show_interest_groups_' . $group['id'], 'on' ) && 'hidden' !== $group['type'];
				$interest_groups_visibility[ $group['id'] ] = $visible ? 'on' : 'off';
			}
		}

		$attributes = array(
			'header'                      => array(
				'type'    => 'string',
				'default' => get_option( 'mc_header_content', '' ),
			),
			'sub_header'                  => array(
				'type'    => 'string',
				'default' => get_option( 'mc_subheader_content', '' ),
			),
			'list_id'                     => array(
				'type'    => 'string',
			),
			'submit_text'                 => array(
				'type'    => 'string',
				'default' => esc_html__( 'Subscribe', 'mailchimp' ),
			),
			'interest_groups_visibility'  => array(
				'type'    => 'object',
			),
			'double_opt_in'               => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'update_existing_subscribers' => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'show_unsubscribe_link'       => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'unsubscribe_link_text'       => array(
				'type'    => 'string',
				'default' => esc_html__( 'unsubscribe from list', 'mailchimp' ),
			),
			'show_required_indicator'     => array(
				'type'    => 'boolean',
				'default' => true,
			),
		);

		// Register the Mailchimp List Subscribe Form block.
		$block_json_file = MCSF_DIR . 'dist/blocks/mailchimp/block.json';
		if ( file_exists( $block_json_file ) ) {
			$block_folder = dirname( $block_json_file );
			register_block_type(
				$block_folder,
				array(
					'attributes' => $attributes,
				)
			);
		}

		// Register the Mailchimp form field block.
		$form_field_block_json_file = MCSF_DIR . 'dist/blocks/mailchimp-form-field/block.json';
		if ( file_exists( $form_field_block_json_file ) ) {
			$form_field_block_folder = dirname( $form_field_block_json_file );
			register_block_type( $form_field_block_folder );
		}

		$data = array(
			'admin_settings_url'      => esc_url_raw( admin_url( 'admin.php?page=mailchimp_sf_options' ) ),
			'lists'                   => $this->get_lists(),
			'list_id'                 => get_option( 'mc_list_id', '' ),
			'merge_fields_visibility' => $merge_fields_visibility,
		);
		$data = 'window.mailchimp_sf_block_data = ' . wp_json_encode( $data );
		wp_add_inline_script( 'mailchimp-mailchimp-editor-script', $data, 'before' );

		ob_start();
		require_once MCSF_DIR . '/views/css/frontend.php';
		$data = ob_get_clean();
		wp_add_inline_style( 'mailchimp-mailchimp-editor-style', $data );
	}

	/**
	 * Get Mailchimp lists.
	 *
	 * @return array List of Mailchimp lists.
	 */
	public function get_lists() {
		$lists = get_option( 'mailchimp_sf_lists', array() );
		// If we have lists, return them.
		if ( ! empty( $lists ) ) {
			return $lists;
		}

		// If we don't have any lists, get them from the API.
		$api = mailchimp_sf_get_api();
		if ( ! $api ) {
			return array();
		}

		// we *could* support paging, but few users have that many lists (and shouldn't)
		$lists = $api->get( 'lists', 100, array( 'fields' => 'lists.id,lists.name,lists.email_type_option' ) );
		if ( is_wp_error( $lists ) ) {
			return array();
		}

		$lists = $lists['lists'] ?? array();

		// Update the option with the lists.
		update_option( 'mailchimp_sf_lists', $lists );

		return $lists;
	}

	/**
	 * Register REST API endpoints.
	 */
	public function register_rest_endpoints() {
		register_rest_route(
			'mailchimp/v1',
			'/list-data/(?P<list_id>[a-zA-Z0-9]+)/',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_list_data' ),
				'args'                => array(
					'list_id' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'description'       => esc_html__( 'Mailchimp list ID to get data', 'mailchimp' ),
					),
				),
				'permission_callback' => array( $this, 'get_list_data_permissions_check' ),
			)
		);
	}

	/**
	 * Get list data.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_list_data( $request ) {
		$list_id = $request->get_param( 'list_id' );

		$fields_key      = 'mailchimp_sf_merge_fields_' . $list_id;
		$merge_fields    = get_option( $fields_key, array() );
		$groups_key      = 'mailchimp_sf_interest_groups_' . $list_id;
		$interest_groups = get_option( $groups_key, array() );

		// If we don't have any merge fields, get them from the API.
		if ( empty( $merge_fields ) ) {
			$api      = mailchimp_sf_get_api();
			$response = $api->get( 'lists/' . $list_id . '/merge-fields', 80 );

			// if we get an error back from the api, return it.
			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$merge_fields = mailchimp_sf_add_email_field( $response['merge_fields'] );
			update_option( $fields_key, $merge_fields );
		}

		// If we don't have any interest groups, get them from the API.
		if ( empty( $interest_groups ) ) {
			$api      = mailchimp_sf_get_api();
			$response = $api->get( 'lists/' . $list_id . '/interest-categories', 60 );

			// if we get an error back from the api, return it.
			if ( is_wp_error( $response ) ) {
				return $response;
			}

			if ( is_array( $response ) ) {
				foreach ( $response['categories'] as $key => $ig ) {
					$groups                                   = $api->get( 'lists/' . $list_id . '/interest-categories/' . $ig['id'] . '/interests', 60 );
					$response['categories'][ $key ]['groups'] = $groups['interests'];
				}
			}

			$interest_groups = $response['categories'];
			update_option( $groups_key, $interest_groups );
		}

		$data = array(
			'merge_fields'    => $merge_fields,
			'interest_groups' => $interest_groups,
		);

		return rest_ensure_response( $data );
	}

	/**
	 * Check permissions for the list data.
	 *
	 * @return bool
	 */
	public function get_list_data_permissions_check() {
		return current_user_can( 'edit_posts' );
	}
}
