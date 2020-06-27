<?php
/**
 * Plugin Name: WPeMatico First Aids
 * Plugin URI: https://www.wpematico.com
 * Description: Activate this for *delete all campaings and options* from WPeMatico <b>without any ask</b>.
 * Version: 1.0
 * Author: etruel <esteban@netmdp.com>
 * Author URI: https://www.netmdp.com
 * Text Domain: wpematico-first-aids
 * Domain Path: /lang/
 * 
 * @package WPeMatico
 * @category Core
 * @author etruel <esteban@netmdp.com>
 */
# @charset utf-8
if ( ! function_exists( 'add_filter' ) )
	exit;
if (!class_exists('Main_WPeMatico_First_Aids') ) {

/**
 * Main_WPeMatico_First_Aids Class.
 */	
class Main_WPeMatico_First_Aids{
	private static $instance;

	private function setup_constants() {
		if(!defined( 'WPEMATICO_FIRST_AIDS_VERSION' ) ) define( 'WPEMATICO_FIRST_AIDS_VERSION', '1.0' );
		if(!defined( 'WPEMATICO_FIRST_AIDS_BASENAME' ) ) define( 'WPEMATICO_FIRST_AIDS_BASENAME', plugin_basename( __FILE__ ) );
		if(!defined( 'WPEMATICO_FIRST_AIDS_ROOTFILE' ) ) define( 'WPEMATICO_FIRST_AIDS_ROOTFILE', __FILE__ );
		if(!defined( 'WPEMATICO_FIRST_AIDS_PLUGIN_URL' ) ) define( 'WPEMATICO_FIRST_AIDS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		if(!defined( 'WPEMATICO_FIRST_AIDS_PLUGIN_DIR' ) ) define( 'WPEMATICO_FIRST_AIDS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
    }

	public static function required_php_notice(){
		$class = "error";
		$message = '<b>WPeMatico:</b> '.__('PHP 5.3.0 or higher needed!', 'wpematico-first-aids' ) . '<br />';
		echo"<div class=\"$class\"> <p>$message</p></div>"; 
	}
	
	
    public static function instance() {
		if (version_compare(phpversion(), '5.3.0', '<')) { // check PHP Version
			add_action( 'admin_notices', array(__CLASS__, 'required_php_notice') );
			return false; 
		}

        if( !self::$instance ) {
            self::$instance = new Main_WPeMatico_First_Aids();
            self::$instance->wpematico_uninstall_first_aids();
        }
        return self::$instance;
    }

	/**
	 * Internationalization
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function load_textdomain() {
		// Set filter for language directory
		$lang_dir = WPEMATICO_FIRST_AIDS_PLUGIN_DIR . '/lang/';
		//$lang_dir = apply_filters( 'wpematico_languages_directory', $lang_dir );

		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wpematico-first-aids' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'wpematico-first-aids', $locale );

		// Setup paths to current locale file
		$mofile_local   = $lang_dir . $mofile;
		$mofile_global  = WP_LANG_DIR . '/wpematico-first-aids/' . $mofile;
		
		/**
		 * Directory of language packs through translate.wordpress.org
		 * @var $mofile_global2 String.
		 * @since 1.6.2
		 */
		$mofile_global2  = WP_LANG_DIR . '/plugins/wpematico-first-aids/' . $mofile;

		if( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/wpematico/ folder
			load_textdomain( 'wpematico-first-aids', $mofile_global );
		} elseif( file_exists( $mofile_global2 ) ) {
			// Look in global /wp-content/languages/plugins/wpematico/ folder
			load_textdomain( 'wpematico-first-aids', $mofile_global2 );
		} elseif( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/wpematico/languages/ folder
			load_textdomain( 'wpematico-first-aids', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'wpematico-first-aids', false, $lang_dir );
		}
	}

	public static function wpematico_uninstall_first_aids() {
		global $wpdb, $blog_id;
		$global_option_key = 'WPeMatico_Options'; //ESTA CONSTANTE VIENE DE LA CLASE WPEMATICO, SE LLAMA OPTION_KEY
		$delete_options = true;
		$delete_campaigns = true;
		$delete_cron = true;

		if ( is_network_admin() && $delete_options ) {
			if ( isset ( $wpdb->blogs ) ) {
				$blogs = $wpdb->get_results(
					$wpdb->prepare(
						'SELECT blog_id ' .
						'FROM ' . $wpdb->blogs . ' ' .
						"WHERE blog_id <> '%s'",
						$blog_id
					)
				);
				foreach ( $blogs as $blog ) {
					delete_blog_option( $blog->blog_id, $global_option_key );
				}
			}
		}
		if ($delete_cron) {
			wp_clear_scheduled_hook('wpematico_cron');
		}
		if ($delete_options) {
			delete_option( $global_option_key );
			delete_option( 'wpematico_db_version' );
		}
		//delete campaigns
		if($delete_campaigns) {
			$args = array( 'post_type' => 'wpematico', 'orderby' => 'ID', 'order' => 'ASC' );
			$campaigns = get_posts( $args );
			foreach( $campaigns as $post ) {
				wp_delete_post( $post->ID, true);  // forces delete to avoid trash
			}
		}
	}

}  //class WPeMatico
}
$WPeMatico = Main_WPeMatico_First_Aids::instance();