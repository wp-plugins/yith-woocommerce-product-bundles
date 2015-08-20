<?php
/**
 * Main class
 *
 * @author Yithemes
 * @package YITH WooCommerce Product Bundles
 * @version 1.0.0
 */


if ( ! defined( 'YITH_WCPB' ) ) {
    exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCPB' ) ) {
    /**
     * YITH WooCommerce Product Bundles
     *
     * @since 1.0.0
     */
    class YITH_WCPB {

        /**
         * Single instance of the class
         *
         * @var \YITH_WCPB
         * @since 1.0.0
         */
        protected static $instance;

        /**
         * Plugin version
         *
         * @var string
         * @since 1.0.0
         */
        public $version = YITH_WCPB_VERSION;

        /**
         * Plugin object
         *
         * @var string
         * @since 1.0.0
         */
        public $obj = null;

        /**
         * Returns single instance of the class
         *
         * @return \YITH_WCPB
         * @since 1.0.0
         */
        public static function get_instance(){
            if( is_null( self::$instance ) ){
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Constructor
         *
         * @return mixed| YITH_WCPB_Admin | YITH_WCPB_Frontend
         * @since 1.0.0
         */
        public function __construct() {

            // Load Plugin Framework
            add_action( 'after_setup_theme', array( $this, 'plugin_fw_loader' ), 1 );

                // Class admin
                if ( is_admin() ) {
                    YITH_WCPB_Admin();
                }
                // Class frontend
                //else{
                    YITH_WCPB_Frontend();
                //}
        }


        /**
         * Load Plugin Framework
         *
         * @since  1.0
         * @access public
         * @return void
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function plugin_fw_loader() {

            if ( ! defined( 'YIT' ) || ! defined( 'YIT_CORE_PLUGIN' ) ) {
                require_once( 'plugin-fw/yit-plugin.php' );
            }

        }
    }
}

/**
 * Unique access to instance of YITH_WCPB class
 *
 * @return \YITH_WCPB
 * @since 1.0.0
 */
function YITH_WCPB(){
    return YITH_WCPB::get_instance();
}
?>