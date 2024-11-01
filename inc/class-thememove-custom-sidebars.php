<?php
/**
 * ThemeMove Custom Sidebars Class
 *
 * @package ThemeMove_Custom_Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * It should be a final class
 */
final class ThemeMove_Custom_Sidebars {

	/**
	 * Instance
	 *
	 * @var ThemeMove_Custom_Sidebars The single instance of the class.
	 */
	private static $instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return ThemeMove_Custom_Sidebars An instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'plugins_loaded', array( $this, 'load_language_file' ) );
	}

	/**
	 * Initialize the plugin
	 */
	public function init() {
		if ( current_user_can( 'manage_options' ) ) {
			add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 15 );
		}

		// Load scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );

		// Register custom sidebars.
		add_action( 'widgets_init', array( $this, 'register_custom_sidebars' ), 15 );

		// Add a new sidebar.
		add_action( 'wp_ajax_add_custom_sidebar', array( $this, 'add_custom_sidebar' ) );
		add_action( 'wp_ajax_nopriv_add_custom_sidebar', array( $this, 'add_custom_sidebar' ) );

		// Remove a custom sidebar.
		add_action( 'wp_ajax_remove_custom_sidebar', array( $this, 'remove_custom_sidebar' ) );
		add_action( 'wp_ajax_nopriv_remove_custom_sidebar', array( $this, 'remove_custom_sidebar' ) );

		// Export sidebars.
		add_action( 'admin_post_export_custom_sidebars', array( $this, 'export_custom_sidebars' ) );

		// Import custom sidebars w/o ThemeMove Core.
		if ( ! class_exists( 'ThemeMove_Core' ) ) {
			add_action( 'admin_post_import_custom_sidebars_wo_tmc', array( $this, 'import_custom_sidebars_wo_tmc' ) );
		}

		if ( class_exists( 'ThemeMove_Core' ) ) {

			// Add export item to Tools > Export.
			add_filter( 'tmc_export_items', array( $this, 'add_export_item' ), 15, 1 );

			// Add import step.
			add_filter( 'tmc_demo_steps', array( $this, 'add_import_step' ), 15, 2 );

			// Import custom sidebars.
			add_action( 'wp_ajax_import_custom_sidebars', array( $this, 'import_custom_sidebars' ) );
			add_action( 'wp_ajax_nopriv_import_custom_sidebars', array( $this, 'import_custom_sidebars' ) );
		}
	}

	/**
	 * Loads textdomain
	 */
	public function load_language_file() {
		$lang_dir = TMCS_DIR . '/languages/';
		load_plugin_textdomain( 'tm-custom_sidebars', false, $lang_dir );
	}

	/**
	 * Adds menu item to admin menu
	 */
	public function add_admin_menu() {
		if ( class_exists( 'ThemeMove_Core' ) ) {
			add_submenu_page(
				'thememove-core',
				esc_html__( 'Custom Sidebars - ThemeMove Core', 'tm-custom-sidebars' ),
				esc_html__( 'Custom Sidebars', 'tm-custom-sidebars' ),
				'manage_options',
				'thememove-custom-sidebars',
				array( $this, 'render_custom_sidebars_page' )
			);
		} else {
			add_menu_page(
				esc_html__( 'Custom Sidebars', 'tm-custom-sidebars' ),
				esc_html__( 'Custom Sidebars', 'tm-custom-sidebars' ),
				'manage_options',
				'thememove-custom-sidebars',
				array( $this, 'render_custom_sidebars_page' ),
				'dashicons-align-right',
				7
			);
		}
	}

	/**
	 * Render welcome page
	 */
	public function render_custom_sidebars_page() {
		$sidebars = $this->get_custom_sidebars();

		include_once TMCS_DIR . 'views/page-custom-sidebars.php';
	}

	/**
	 * Load scripts
	 *
	 * @param string $hook Hook.
	 */
	public function load_admin_scripts( $hook ) {

		if ( strpos( $hook, 'thememove-custom-sidebars' ) ) {

			// Font Awesome.
			wp_enqueue_script( 'font-awesome', 'https://kit.fontawesome.com/8bbdf860ba.js', array(), 'latest' ); //phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter

			// ThemeMove Core style & script.
			if ( defined( 'TMC_URL' ) && defined( 'TMC_VER' ) ) {
				wp_enqueue_style( 'thememove-core', TMC_URL . 'assets/css/thememove-core.css', array(), TMC_VER );
			}

			// ThemeMove Custom Sidebars style & script.
			wp_enqueue_style( 'thememove-custom-sidebars', TMCS_URL . 'assets/css/thememove-custom-sidebars.css', array(), TMCS_VER );
			wp_enqueue_script( 'thememove-custom-sidebars', TMCS_URL . 'assets/js/thememove-custom-sidebars.js', array(), TMCS_VER, true );

			wp_localize_script(
				'thememove-custom-sidebars',
				'tmcsVars',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				)
			);
		}
	}

	/**
	 * Add custom sidebar
	 *
	 * @return void
	 */
	public function add_custom_sidebar() {

		$nonce = '';
		if ( isset( $_POST['_wpnonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) );
		}

		if ( ! wp_verify_nonce( $nonce, 'add_custom_sidebar' ) ) {
			wp_send_json_error( esc_html__( 'Invalid nonce', 'tm-custom-sidebars' ) );
		}

		$sidebars     = $this->get_custom_sidebars();
		$name         = isset( $_POST['sidebar_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sidebar_name'] ) ) : '';
		$sidebar_slug = sanitize_title( $name );
		$response     = array();

		if ( isset( $sidebars[ $sidebar_slug ] ) ) {
			wp_send_json_error( esc_html__( 'Sidebar already exists, please use a different name.', 'tm-custom-sidebars' ) );
		} else {
			$sidebars[ $sidebar_slug ] = $name;
			update_option( 'tm_custom_sidebars', $sidebars );
			$response['slug']  = $sidebar_slug;
			$response['nonce'] = wp_create_nonce( "remove_custom_sidebar_{$sidebar_slug}" );
			wp_send_json_success( $response );
		}
	}

	/**
	 * Remove custom sidebar
	 *
	 * @return void
	 */
	public function remove_custom_sidebar() {
		$sidebar_slug = isset( $_POST['sidebar_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['sidebar_slug'] ) ) : '';
		$nonce        = '';

		if ( isset( $_POST['_wpnonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) );
		}

		if ( ! wp_verify_nonce( $nonce, "remove_custom_sidebar_{$sidebar_slug}" ) ) {
			wp_send_json_error( esc_html__( 'Invalid nonce', 'tm-custom-sidebars' ) );
		}

		$sidebars = $this->get_custom_sidebars();
		$response = array();

		if ( ! isset( $sidebars[ $sidebar_slug ] ) ) {
			wp_send_json_error( esc_html__( 'Sidebar does not exist.', 'tm-custom-sidebars' ) );
		} else {
			unset( $sidebars[ $sidebar_slug ] );
			update_option( 'tm_custom_sidebars', $sidebars );
			wp_send_json_success();
		}
	}

	/**
	 * Get all custom sidebars
	 *
	 * @return array
	 */
	public function get_custom_sidebars() {
		$sidebars = get_option( 'tm_custom_sidebars', array() );

		return apply_filters( 'tm_custom_sidebars', $sidebars );
	}

	/**
	 * Register custom sidebars
	 */
	public function register_custom_sidebars() {

		$sidebars = $this->get_custom_sidebars();

		if ( ! empty( $sidebars ) ) {
			foreach ( $sidebars as $slug => $sidebar ) {
				register_sidebar(
					array(
						'name'          => $sidebar,
						'id'            => strtolower( $slug ),
						'before_widget' => apply_filters( 'tm_custom_sidebars_before_widget', '<div id="%1$s" class="widget %2$s">' ),
						'after_widget'  => apply_filters( 'tm_custom_sidebars_after_widget', '</div>' ),
						'before_title'  => apply_filters( 'tm_custom_sidebars_before_title', '<h5 class="widget-title">' ),
						'after_title'   => apply_filters( 'tm_custom_sidebars_after_title', '</h5>' ),
					)
				);
			}
		}
	}

	/**
	 * Add export item to the ThemeMove > Tools > Export
	 *
	 * @param array $export_items Export items array.
	 * @return array
	 */
	public function add_export_item( $export_items ) {
		$export_items[] = array(
			'name'        => esc_html__( 'Sidebars', 'tm-custom-sidebars' ),
			'action'      => 'export_custom_sidebars',
			'icon'        => 'far fa-columns',
			'description' => esc_html__( 'Create an text file containing all custom sidebars', 'tm-custom-sidebars' ),
		);

		return $export_items;
	}

	/**
	 * Save export file
	 *
	 * @param string $file_name File Name.
	 * @param string $file_content File Content.
	 */
	public function save_file( $file_name, $file_content ) {
		header( 'Content-Type: application/text', true, 200 );
		header( "Content-Disposition: attachment; filename={$file_name}" );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// Output file contents.
		echo $file_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Export custom sidebars
	 */
	public function export_custom_sidebars() {

		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

		if ( wp_verify_nonce( $nonce, 'export_custom_sidebars' ) ) {
			$data = wp_json_encode( get_option( 'tm_custom_sidebars', '' ) );
		} else {
			wp_die( esc_html__( 'Invalid nonce', 'tm-custom-sidebars' ) );
		}

		$this->save_file( apply_filters( 'tm_custom_sidebars_file_name', 'sidebars.json' ), $data );
	}

	/**
	 * Add new import step
	 *
	 * @param array  $demo_steps Demo steps array.
	 * @param string $demo_slug Demo slug.
	 * @return array
	 */
	public function add_import_step( $demo_steps, $demo_slug ) {
		$new_steps     = array();
		$import_dir    = TMC_THEME_DIR . '/assets/import/' . $demo_slug;
		$sidebars_json = "{$import_dir}/sidebars.json";

		if ( file_exists( $sidebars_json ) ) {

			foreach ( $demo_steps as $key => $step ) {
				$new_steps[ $key ] = $step;
				if ( 'content_xml' === $key ) {
					$new_steps['custom_sidebars'] = esc_html__( 'Custom Sidebars', 'tm-custom-sidebars' );
				}
			}
		}

		return $new_steps;
	}

	/**
	 * Import custom sidebars w/o ThemeMove Core
	 */
	public function import_custom_sidebars_wo_tmc() {

		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

		if ( wp_verify_nonce( $nonce, 'import_custom_sidebars_wo_tmc' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';

			if ( ! empty( $_FILES ) && isset( $_FILES['import_file'] ) ) {
				$upload_overrides = array(
					'test_form' => false,
					'test_type' => false,
				);

				$import_file = wp_unslash( $_FILES['import_file'] );
				$data        = wp_handle_upload( $import_file, $upload_overrides );

				if ( $data && ! isset( $data['error'] ) ) {
					$file = $data['file'];

					if ( ! file_exists( $file ) ) {
						// translators: %s: File path.
						wp_die( sprintf( esc_html__( 'The %s file does not exist.', 'tm-custom-sidebars' ), esc_html( $file ) ) );
					}

					require_once ABSPATH . 'wp-admin/includes/file.php';
					WP_Filesystem();
					global $wp_filesystem;

					$data = $wp_filesystem->get_contents( $file );

					if ( ! is_wp_error( $data ) ) {
						$data = json_decode( $data, true );

						if ( ! is_array( $data ) ) {
							wp_die( esc_html__( 'Error: Custom Sidebars data could not be read. Please try a different file.', 'tm-custom-sidebars' ) );
						}

						update_option( 'tm_custom_sidebars', $data );

						wp_safe_redirect( admin_url( '/admin.php?page=thememove-custom-sidebars' ) );
					}
				} else {
					wp_die( esc_html( $data['error'] ) );
				}
			}
		} else {
			wp_die( esc_html__( 'Invalid nonce', 'tm-custom-sidebars' ) );
		}
	}

	/**
	 * Import custom sidebars
	 */
	public function import_custom_sidebars() {

		$importer = ThemeMove_Importer::instance();

		$importer->verify_before_call_ajax( 'import_custom_sidebars' );

		$import_content_steps = array();

		if ( isset( $_POST['import_content_steps'] ) && ! empty( $_POST['import_content_steps'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Remove all empty item in steps.
			$import_content_steps = explode( ',', sanitize_text_field( wp_unslash( $_POST['import_content_steps'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		// Is this a new AJAX call to continue the previous import?
		$use_existing_importer_data = $importer->use_existing_importer_data();

		if ( ! $use_existing_importer_data ) {
			// Create a date and time string to use for demo and log file names.
			ThemeMove_Import_Logger::set_demo_import_start_time();

			// Define log file path.
			$importer->log_file_path = ThemeMove_Import_Logger::get_log_path();
		}

		set_transient( 'tmc_importer_data', $importer->get_current_importer_data(), 0.1 * HOUR_IN_SECONDS );

		$data = $importer->read_import_file( 'sidebars.json' );

		if ( ! is_wp_error( $data ) && ! empty( $data ) ) {
			$data = json_decode( $data, true );

			// Have valid data? If no data or could not decode.
			if ( ! is_array( $data ) ) {
				ThemeMove_Import_Logger::append_to_file(
					esc_html__( 'Error: Custom Sidebars data could not be read. Please try a different file.', 'tm-custom-sidebars' ),
					$importer->log_file_path,
					esc_html__( 'Importing Custom Sidebars', 'tm-custom-sidebars' )
				);

				wp_send_json_error( esc_html__( 'Error: Custom Sidebars data could not be read. Please try a different file.', 'tm-custom-sidebars' ) );
			}

			$message  = '';
			$sidebars = array();

			foreach ( $data as $slug => $name ) {

				$sidebars[ $slug ] = $name;

				register_sidebar(
					array(
						'name'          => $name,
						'id'            => strtolower( $slug ),
						'before_widget' => apply_filters( 'tm_custom_sidebars_before_widget', '<div id="%1$s" class="widget %2$s">' ),
						'after_widget'  => apply_filters( 'tm_custom_sidebars_after_widget', '</div>' ),
						'before_title'  => apply_filters( 'tm_custom_sidebars_before_title', '<h5 class="widget-title">' ),
						'after_title'   => apply_filters( 'tm_custom_sidebars_after_title', '</h5>' ),
					)
				);

				$message .= "{$name} - {$slug}" . esc_html__( ' - Imported', 'tm-custom-sidebars' ) . PHP_EOL;
			}

			update_option( 'tm_custom_sidebars', $sidebars );

			ThemeMove_Import_Logger::append_to_file(
				$message,
				$importer->log_file_path,
				esc_html__( 'Importing Custom Sidebars', 'tm-custom-sidebars' )
			);

			// Finish or go to next steps?
			if ( ! empty( $import_content_steps ) ) {
				$next_step = $importer->get_next_step( 'custom_sidebars', $import_content_steps );

				if ( $next_step ) {
					wp_send_json(
						array(
							'next_step' => $next_step,
							'_wpnonce'  => wp_create_nonce( 'import_' . $next_step ),
						)
					);
				}
			}

			$importer->send_final_response();
		} else {
			$error_message = $data->get_error_message();
			ThemeMove_Import_Logger::append_to_file( $error_message, $this->log_file_path, esc_html__( 'Importing menu', 'tm-custom-sidebars' ) );
			wp_send_json_error( $error_message );
		}
	}
}
