<?php
/*
Plugin Name: Brochure Tools
Plugin URI: https://www.narnoo.com/
Description: A Brochure Tools plugin to interact with Narnoo.com webservice via PHP SDK 2.0.
Version: 2.0.0
Author: Narnoo Wordpress developer
Author URI: http://www.narnoo.com/
License: GPL2 or later
*/

/*  Copyright 2016  Narnoo.com  (email : info@narnoo.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// plugin definitions
define( 'NARNOO_BROCHURE_TOOLS_PLUGIN_NAME', 			'Brochure Tools' );
define( 'NARNOO_BROCHURE_TOOLS_CURRENT_VERSION', 		'2.0.0' );
define( 'NARNOO_BROCHURE_TOOLS_I18N_DOMAIN', 			'brochure-tools' );

define( 'NARNOO_BROCHURE_TOOLS_PLUGIN_URL', 			plugin_dir_url( __FILE__ ) );
define( 'NARNOO_BROCHURE_TOOLS_PLUGIN_PATH', 			plugin_dir_path( __FILE__ ) );
define( 'NARNOO_BROCHURE_TOOLS_SETTINGS_PAGE', 			'options-general.php?page=narnoo_distributor_api_settings' );
define( 'NARNOO_BROCHURE_TOOLS_PLUGIN_CACHE', 			'files' ); // auto, files, sqlite, apc, cookie, memcache, memcached, predis, redis, wincache, xcache


// REQUIRE_ONCE_ SCRIPTS
require_once( NARNOO_BROCHURE_TOOLS_PLUGIN_PATH . 'libs/class-brochure-tools-helper.php' );
require_once( NARNOO_BROCHURE_TOOLS_PLUGIN_PATH . 'libs/cache/phpfastcache.php' );

// NARNOO PHP SDK 2.0 //
require_once( NARNOO_BROCHURE_TOOLS_PLUGIN_PATH . 'libs/narnoo/http/WebClient.php' );
require_once( NARNOO_BROCHURE_TOOLS_PLUGIN_PATH . 'libs/narnoo/depgaconnect.php' );
require_once( NARNOO_BROCHURE_TOOLS_PLUGIN_PATH . 'libs/narnoo/distributor.php' );
require_once( NARNOO_BROCHURE_TOOLS_PLUGIN_PATH . 'libs/narnoo/operatorconnect.php' );

// begin!
new Brochure_tools();

class Brochure_tools {


	/**
	 * Plugin's main entry point.
	 **/
	function __construct() {

		register_uninstall_hook( __FILE__, 		array( 'BrochureTools', 'uninstall' ) );

		if ( is_admin() ) {

			add_action( 'plugins_loaded', 		array( &$this, 'load_language_file' ) );
			add_filter( 'plugin_action_links', 	array( &$this, 'plugin_action_links' ), 10, 2 );

			add_action( 'admin_notices', 		array( &$this, 'display_reminders' ) );
			add_action( 'admin_menu', 			array( &$this, 'create_menus' ), 9 );
			add_action( 'admin_init', 			array( &$this, 'admin_init' ) );


		}else{

			add_shortcode( 'brochure_tools_maps', 				array( &$this, 'brochure_tools_maps_shortcode' ) ); // return all maps for brochure tools based on Narnoo brochures
			add_shortcode( 'distributor_image_album', 			array( &$this, 'brochure_tools_image_album_shortcode' ) ); // return all TTNQ images for a nominated image album
			add_shortcode( 'product_information', 				array( &$this, 'brochure_tools_product_information_shortcode' ) ); // return all TTNQ images for a nominated image album
			add_shortcode( 'product_listings', 					array( &$this, 'brochure_tools_product_listings_shortcode' ) ); // return all TTNQ images for a nominated image album
			add_shortcode( 'download_product_image', 			array( &$this, 'brochure_tools_image_download_product_shortcode' ) ); // return download operator link to Hi Res Image
			add_shortcode( 'download_image', 					array( &$this, 'brochure_tools_image_download_shortcode' ) ); // return download link to Hi Res Image
			add_shortcode( 'download_map', 						array( &$this, 'brochure_tools_map_download_shortcode' ) ); // return download link to Hi Res Image

			//ADDED NEW REFERENCE TO SHORTCODE
			add_shortcode( 'all_maps',	 						array( &$this, 'brochure_tools_all_maps_shortcode' ) ); // return all maps
		}
	}


	/**
	 * Clean up upon plugin uninstall.
	 **/
	static function uninstall() {
		unregister_setting( 'brochure_tools_settings', 'brochure_tools_settings', 		array( &$this, 'settings_sanitize' ) );
	}


	/**
	 * Add settings link for this plugin to Wordpress 'Installed plugins' page.
	 **/
	function plugin_action_links( $links, $file ) {
		if ( $file == plugin_basename( dirname(__FILE__) . '/brochure-tools.php' ) ) {
			$links[] = '<a href="' . NARNOO_BROCHURE_TOOLS_SETTINGS_PAGE . '">' . __('Settings') . '</a>';
		}

		return $links;
	}

	/**
	 * Load language file upon plugin init (for future extension, if any)
	 **/
	function load_language_file() {
		load_plugin_textdomain( NARNOO_BROCHURE_TOOLS_I18N_DOMAIN, false, NARNOO_BROCHURE_TOOLS_PLUGIN_PATH . 'languages/' );
	}

	/**
	 * Display reminder to key in API keys in admin backend.
	 **/
	function display_reminders() {
		$options = get_option( 'narnoo_distributor_settings' );

		if ( empty( $options['access_key'] ) || empty( $options['secret_key'] ) || empty( $options['token_key'] ) ) {
			Brochure_Tools_Helper::show_notification(
				sprintf(
					__( '<strong>Reminder:</strong> Please key in your Narnoo API settings in the <strong><a href="%s">Settings->Narnoo API</a></strong> page.', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ),
					NARNOO_BROCHURE_TOOLS_SETTINGS_PAGE
				)
			);
		}
	}


	/**
	 * Add admin menus and submenus to backend.
	 **/
	function create_menus() {
		// add Narnoo API to settings menu
		add_options_page(
			__( 'Narnoo API Settings', 	NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ),
			__( 'Narnoo API', 			NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ),
			'manage_options',
			'narnoo-distributor-api-settings',
			array( &$this, 'api_settings_page' )
		);


	}

 	/*
 	* [SETTINGS MENU + API] --> Create the forms to add the Narnoo API into the WP database.
 	*/
	function settings_api_section() {
		echo '<p>' . __( 'You can edit your Narnoo API settings below.', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ) . '</p>';
	}
	/*
 	* [SETTINGS MENU + API] --> Access Key Field.
 	*/
	function settings_access_key() {
		$options = get_option( 'narnoo_distributor_settings' );
		echo "<input id='access_key' name='narnoo_distributor_settings[access_key]' size='40' type='text' value='" . esc_attr($options['access_key']). "' />";
	}
	/*
 	* [SETTINGS MENU + API] --> Secret Key Field.
 	*/
	function settings_secret_key() {
		$options = get_option( 'narnoo_distributor_settings' );
		echo "<input id='secret_key' name='narnoo_distributor_settings[secret_key]' size='40' type='text' value='" . esc_attr($options['secret_key']). "' />";
	}
	/*
 	* [SETTINGS MENU + API] --> token Key Field.
 	*/
    function settings_token_key() {
        $options = get_option('narnoo_distributor_settings');
        echo "<input id='token_key' name='narnoo_distributor_settings[token_key]' size='40' type='text' value='" . esc_attr($options['token_key']). "' />";
    }
	/**
	 * Sanitize input settings.
	 **/
	function settings_sanitize( $input ) {
		$new_input['access_key'] = trim( $input['access_key'] );
		$new_input['secret_key'] = trim( $input['secret_key'] );
        $new_input['token_key']  = trim( $input['token_key'] );
		return $new_input;
	}



	/**
	 * Display API settings page.
	 **/
	function api_settings_page() {
		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_BROCHURE_TOOLS_PLUGIN_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo API Settings', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ) ?></h2>
			<form action="options.php" method="post">
				<?php settings_fields( 		'narnoo_distributor_settings' 		); ?>
				<?php do_settings_sections( 'narnoo_distributor_api_settings' 	); ?>

				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>
			</form>

		</div>
		<?php
	}
	/**
	 * Upon admin init, register plugin settings and Narnoo shortcodes button, and define input fields for API settings.
	 **/
	function admin_init() {
		//Narnoo API settings page
		register_setting( 'narnoo_distributor_settings', 'narnoo_distributor_settings', array( &$this, 'settings_sanitize' ) );

		//Narnoo API setting page
		add_settings_section(
			'api_settings_section',
			__( 'API Settings',NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ),
			array( &$this, 'settings_api_section' ),
			'narnoo_distributor_api_settings'
		);

		//Narnoo API access key settings field
		add_settings_field(
			'access_key',
			__( 'Acesss key', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ),
			array( &$this, 'settings_access_key' ),
			'narnoo_distributor_api_settings',
			'api_settings_section'
		);

		//Narnoo API secret key settings field
		add_settings_field(
			'secret_key',
			__( 'Secret key', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ),
			array( &$this, 'settings_secret_key' ),
			'narnoo_distributor_api_settings',
			'api_settings_section'
		);

		//Narnoo API token key settings field
		add_settings_field(
			'token_key',
			__( 'Token key', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ),
			array( &$this, 'settings_token_key' ),
			'narnoo_distributor_api_settings',
			'api_settings_section'
		);

	}


	//[SHORTCODES - Register START]

	/**
	 * Display maps shortcode
	 **/
	function brochure_tools_maps_shortcode( $atts ) {
		ob_start();
		require( NARNOO_BROCHURE_TOOLS_PLUGIN_PATH . 'libs/maps/maps.php' );
		return ob_get_clean();
	}

	/**
	 * Display Distributor Images from album shortcode
	 **/
	function brochure_tools_image_album_shortcode( $atts ) {
		ob_start();
		require( NARNOO_BROCHURE_TOOLS_PLUGIN_PATH . 'libs/album/album.php' );
		return ob_get_clean();
	}

	/**
	 * Display Distributor Product information shortcode
	 **/
	function brochure_tools_product_information_shortcode( $atts ) {
		ob_start();
		require( NARNOO_BROCHURE_TOOLS_PLUGIN_PATH . 'libs/products/product.php' );
		return ob_get_clean();
	}

	/**
	 * Display Distributor Product information shortcode
	 **/
	function brochure_tools_product_listings_shortcode( $atts ) {
		ob_start();
		require( NARNOO_BROCHURE_TOOLS_PLUGIN_PATH . 'libs/listings/products.php' );
		return ob_get_clean();
	}

	/**
	 * Download Image shortcode
	 **/
	function brochure_tools_image_download_product_shortcode( $atts ) {
		ob_start();
		require( NARNOO_BROCHURE_TOOLS_PLUGIN_PATH . 'libs/downloadimage/operator_download.php' );
		return ob_get_clean();
	}

	/**
	 * Download Image shortcode
	 **/
	function brochure_tools_image_download_shortcode( $atts ) {
		ob_start();
		require( NARNOO_BROCHURE_TOOLS_PLUGIN_PATH . 'libs/downloadimage/download.php' );
		return ob_get_clean();
	}

	/**
	 * Download Map shortcode
	 **/
	function brochure_tools_map_download_shortcode( $atts ) {
		ob_start();
		require( NARNOO_BROCHURE_TOOLS_PLUGIN_PATH . 'libs/downloadmap/download.php' );
		return ob_get_clean();
	}

	// UPDATED SHORTCODE LIBRARY

	/**
	 * Get All Maps
	 **/
	function brochure_tools_all_maps_shortcode( $atts ) {
		ob_start();
		require( NARNOO_BROCHURE_TOOLS_PLUGIN_PATH . 'libs/maps/all_maps.php' );
		return ob_get_clean();
	}


}
