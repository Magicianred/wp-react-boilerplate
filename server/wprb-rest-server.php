<?php

/**
 * Class WPRB_Rest_Server
 *
 * Example rest server that allows for CRUD operations on the wp_options table
 *
 */
class WPRB_Rest_Server extends WP_Rest_Controller {

	public $namespace = 'wprb/';
	public $version = 'v1';


	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		$namespace = $this->namespace . $this->version;


		register_rest_route( $namespace, '/options', array(
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_options' ),
				//				'permission_callback' => function () {
				//					return current_user_can( 'edit_others_posts' );
				//				},
			),
		) );

		register_rest_route( $namespace, '/option/(?P<slug>(.*)+)', array(
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_option' ),
				//				'permission_callback' => array( $this, 'get_options_permission' ),
			),
			array(
				'methods'  => WP_REST_Server::EDITABLE,
				'callback' => array( $this, 'edit_option' ),
				//				'permission_callback' => array( $this, 'get_options_permission' ),
			),
		) );
	}

	public function get_options( WP_REST_Request $request ) {
		return wp_load_alloptions();
	}

	public function get_options_permission() {
		if ( ! current_user_can( 'install_themes' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'You do not have permissions to manage options.', 'wp-react-boilerplate' ), array( 'status' => 401 ) );
		}

		return true;
	}

	public function get_option( WP_REST_Request $request ) {

		$params = $request->get_params();

		if ( ! isset( $params['slug'] ) || empty( $params['slug'] ) ) {
			return new WP_Error( 'no-param', __( 'No slug param' ) );
		}

		$converted_slug = $this->_convert_slug( $params['slug'] );

		$opt_value = get_site_option( $converted_slug );

		if ( ! $opt_value ) {
			return new WP_Error( 'option-not-found', __( 'Option not found' ) );
		}

		return $opt_value;
	}

	public function edit_option( WP_REST_Request $request ) {
		$params = $request->get_params();

		if ( ! isset( $params['slug'] ) || empty( $params['slug'] ) ) {
			return new WP_Error( 'no-param', __( 'No slug param' ) );
		}

		$converted_slug = $this->_convert_slug( $params['slug'] );
		$new_value      = $request->get_param( 'newvalue' );

		if ( ! empty( $new_value ) ) {
			return update_option( $converted_slug, $new_value );
		}

		return false;
	}

	protected function _convert_slug( $value ) {
		return str_replace( "-", "_", urldecode( $value ) );
	}

}