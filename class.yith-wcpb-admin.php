<?php
/**
 * Admin class
 *
 * @author Yithemes
 * @package YITH WooCommerce Product Bundles
 * @version 1.0.0
 */

if ( !defined( 'YITH_WCPB' ) ) { exit; } // Exit if accessed directly

if( !class_exists( 'YITH_WCPB_Admin' ) ) {
    /**
     * Admin class.
	 * The class manage all the admin behaviors.
     *
     * @since 1.0.0
     * @author   Leanza Francesco <leanzafrancesco@gmail.com>
     */
    class YITH_WCPB_Admin {
		
        /**
         * Single instance of the class
         *
         * @var \YITH_WCQV_Admin
         * @since 1.0.0
         */
        protected static $instance;

        /**
         * Plugin options
         *
         * @var array
         * @access public
         * @since 1.0.0
         */
        public $options = array();

        /**
         * Plugin version
         *
         * @var string
         * @since 1.0.0
         */
        public $version = YITH_WCPB_VERSION;

        /**
         * @var $_panel Panel Object
         */
        protected $_panel;

        /**
         * @var string Premium version landing link
         */
        protected $_premium_landing = 'https://yithemes.com/themes/plugins/yith-woocommerce-product-bundles';

        /**
         * @var string Quick View panel page
         */
        protected $_panel_page = 'yith_wcpb_panel';

        /**
         * Various links
         *
         * @var string
         * @access public
         * @since 1.0.0
         */
        public $doc_url = 'http://yithemes.com/docs-plugins/yith-woocommerce-product-bundle/';

        public $templates = array();

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
		 * @access public
		 * @since 1.0.0
		 */
		public function __construct() {

            add_action( 'admin_menu', array( $this, 'register_panel' ), 5) ;

            //Add action links
            add_filter( 'plugin_action_links_' . plugin_basename( YITH_WCPB_DIR . '/' . basename( YITH_WCPB_FILE ) ), array( $this, 'action_links') );
            add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

            add_filter( 'woocommerce_product_data_tabs', array( $this, 'woocommerce_product_data_tabs' ) );
            add_action( 'woocommerce_product_data_panels', array( $this, 'woocommerce_product_data_panels' ) );
            add_action( 'wp_ajax_yith_wcpb_add_product_in_bundle', array( $this, 'add_product_in_bundle' ) );
            add_action( 'woocommerce_process_product_meta', array( $this, 'woocommerce_process_product_meta' ) );
            add_action( 'yith_wcpb_admin_product_bundle_data', array( $this, 'yith_wcpb_admin_product_bundle_data' ), 10, 4 );
            add_filter( 'product_type_selector', array( $this, 'product_type_selector' ) );

            // Admin ORDER
            add_filter( 'woocommerce_admin_html_order_item_class',  array( $this, 'woocommerce_admin_html_order_item_class' ), 10, 2 );
            add_filter( 'woocommerce_admin_order_item_class',  array( $this, 'woocommerce_admin_html_order_item_class' ), 10, 2 );
            add_filter( 'woocommerce_admin_order_item_count',  array( $this, 'woocommerce_admin_order_item_count' ), 10, 2 );
            add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'woocommerce_hidden_order_itemmeta'));

            // Premium Tabs
            add_action( 'yith_wcpb_premium_tab', array( $this, 'show_premium_tab' ) );
		}

        /**
         * Hide bundled_by meta in admin order
         *
         * @access public
         * @since 1.0.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function woocommerce_hidden_order_itemmeta( $hidden ){
            return array_merge( $hidden, array('_bundled_by', '_cartstamp') );
        }

        /**
         * add CSS class in admin order bundled items
         *
         * @access public
         * @since 1.0.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function woocommerce_admin_html_order_item_class( $class, $item ) {
            if ( isset( $item[ 'bundled_by' ] ) )
                return $class . ' yith-wcpb-admin-bundled-item';
            return $class;
        }

        /**
         * Filter item count in admin orders page
         *
         * @access public
         * @since 1.0.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function woocommerce_admin_order_item_count( $count, $order ) {
            $counter = 0;
            foreach ( $order->get_items() as $item ) {
                if ( isset( $item[ 'bundled_by' ] ) )
                    $counter += $item[ 'qty' ];
            }
            if ( $counter > 0 ){
                $non_bundled_count = $count - $counter;
                return sprintf( _n( '%1$s item [%2$s bundled elements]', '%1$s items [%2$s bundled elements]', $non_bundled_count, 'yith-wcpb' ), $non_bundled_count, $counter );
            }
            return $count;
        }

		/**
		 * add Product Bundle type in product type selector [in product wc-metabox]
		 *
		 * @access public
		 * @since 1.0.0
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 */
        public function product_type_selector( $types ){
            $types[ 'yith_bundle' ] = _x( 'Product Bundle', 'Admin: type of product', 'yith-wcpb' );
            return $types;
        }

        /**
		 * bundle items data form
		 *
		 * @access public
		 * @since 1.0.0
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 */
        public function yith_wcpb_admin_product_bundle_data( $metabox_id, $product_id, $item_data, $post_id ){
            $b_prod = wc_get_product( $product_id );

            $bp_quantity = isset( $item_data[ 'bp_quantity' ] ) ? $item_data[ 'bp_quantity' ] : 1;
            ?>
            <table>
                <tr>
                    <td><?php echo _ex('Quantity','Admin: quantity of the bundled product.', 'yith-wcpb'); ?></td>
                    <td><input type="number" size="4" value="<?php echo $bp_quantity ?>" name="_yith_wcpb_bundle_data[<?php echo $metabox_id; ?>][bp_quantity]" class="yith-wcpb-bp-quantity short"></td>
                </tr>
            </table>
            <?php
        }

        /**
		 * Ajax Called in bundle_options_metabox.js
		 * return the empty form for the item
		 *
		 * @access public
		 * @since 1.0.0
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 */
        public function add_product_in_bundle(){
            $metabox_id     = intval( $_POST[ 'id' ] );
            $post_id        = intval( $_POST[ 'post_id' ] );
            $product_id     = intval( $_POST[ 'product_id' ] );

            $title = get_the_title( $product_id );

            $p = wc_get_product( $product_id );
            if( $p->product_type != 'simple'){
                echo "notsimple";
                die();
            }

            ob_start();
            include YITH_WCPB_TEMPLATE_PATH . '/admin/admin-bundled-product-item.php';
            echo ob_get_clean();

            die();
        }

        /**
		 * add Bundle Options Tab [in product wc-metabox]
		 *
		 * @access public
		 * @since 1.0.0
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 */
        public function woocommerce_product_data_tabs( $product_data_tabs ){
            $product_data_tabs['yith_bundled_options'] = array(
                            'label'  => __( 'Bundle Options', 'yith-wcpb' ),
                            'target' => 'yith_bundled_product_data',
                            'class'  => array('show_if_bundle'),
                        );
            
            return $product_data_tabs;
        }
        
        /**
		 * add panel for Bundle Options Tab [in product wc-metabox]
		 *
		 * @access public
		 * @since 1.0.0
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 */
        public function woocommerce_product_data_panels(){
            global $post;

            $bundle_data = get_post_meta( $post->ID, '_yith_wcpb_bundle_data', true );

            ?><div id="yith_bundled_product_data" class="panel woocommerce_options_panel wc-metaboxes-wrapper">

                <div class="options_group yith-wcpb-bundle-metaboxes-wrapper">

                    <div id="yith-wcpb-bundle-metaboxes-wrapper-inner">

                        <p class="toolbar">
                            <a href="#" class="close_all"><?php _e('Close all', 'woocommerce'); ?></a>
                            <a href="#" class="expand_all"><?php _e('Expand all', 'woocommerce'); ?></a>
                        </p>

                        <div class="yith-wcpb-bundled-items wc-metaboxes ui-sortable">
                            <?php
                            if ( ! empty( $bundle_data ) ) {
                            	$i = 0;
                                foreach ($bundle_data as $item_id => $item_data) {
                                    //$metabox_id     = $item_data[ 'bundle_order' ];
                                    $i++;
                                    $metabox_id = $i;
                                    $post_id        = $post->ID;
                                    $product_id     = $item_data[ 'product_id' ];

                                    $title = get_the_title( $product_id );
                                    $open_closed = 'closed';
                                    ob_start();
                                    include YITH_WCPB_TEMPLATE_PATH . '/admin/admin-bundled-product-item.php';
                                    echo ob_get_clean();
                                }
                            }
                            ?>
                        </div>
                        <p class="yith-wcpb-bundled-prod-toolbar toolbar">
                            <span class="yith-wcpb-bundled-prod-toolbar-wrapper">
                                <span class="yith-wcpb-bundled-prod-selector">
                                    <input type="hidden" class="wc-product-search" style="width: 250px;" id="yith-wcpb-bundled-product" name="yith_wcpb_bundled_product" data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products" data-multiple="false" data-selected="" value="" />
                                </span>
                                <button type="button" id="yith-wcpb-add-bundled-product" class="button button-primary"><?php _e( 'Add Product', 'yith-wcpb' ); ?></button>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <?php
        }

        /**
		 * Save Product Bandle Data
		 *
		 * @access public
		 * @since 1.0.0
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 */
        public function woocommerce_process_product_meta(  $post_id  ){
            $bundle_data = isset( $_POST[ '_yith_wcpb_bundle_data' ] ) ? $_POST[ '_yith_wcpb_bundle_data' ] : false;
            //var_dump($bundle_data); die();
            update_post_meta( $post_id, '_yith_wcpb_bundle_data', $bundle_data );
        }

        /**
         * Action Links
         *
         * add the action links to plugin admin page
         *
         * @param $links | links plugin array
         *
         * @return   mixed Array
         * @since    1.0
         * @author   Leanza Francesco <leanzafrancesco@gmail.com>
         * @return mixed
         * @use plugin_action_links_{$plugin_file_name}
         */
        public function action_links( $links ) {

            $links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'yith-wcpb' ) . '</a>';

            return $links;
        }

        /**
         * plugin_row_meta
         *
         * add the action links to plugin admin page
         *
         * @param $plugin_meta
         * @param $plugin_file
         * @param $plugin_data
         * @param $status
         *
         * @return   Array
         * @since    1.0
         * @use plugin_row_meta
         */
        public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {

            if ( defined( 'YITH_WCPB_FREE_INIT' ) && YITH_WCPB_FREE_INIT == $plugin_file ) {
                $plugin_meta[] = '<a href="' . $this->doc_url . '" target="_blank">' . __( 'Plugin Documentation', 'yith-wcpb' ) . '</a>';
            }
            return $plugin_meta;
        }

        /**
         * Add a panel under YITH Plugins tab
         *
         * @return   void
         * @since    1.0
         * @author   Leanza Francesco <leanzafrancesco@gmail.com>
         * @use     /Yit_Plugin_Panel class
         * @see      plugin-fw/lib/yit-plugin-panel.php
         */
        public function register_panel() {

            if ( ! empty( $this->_panel ) ) {
                return;
            }

            $admin_tabs_free = array(
                //'settings'      => __( 'Settings', 'yith-wcpb' ),
                'premium'       => __( 'Premium Version', 'yith-wcpb' )
                );

            $admin_tabs = apply_filters('yith_wcpb_settings_admin_tabs', $admin_tabs_free);

            $args = array(
                'create_menu_page' => true,
                'parent_slug'      => '',
                'page_title'       => __( 'Product Bundles', 'yith-wcpb' ),
                'menu_title'       => __( 'Product Bundles', 'yith-wcpb' ),
                'capability'       => 'manage_options',
                'parent'           => '',
                'parent_page'      => 'yit_plugin_panel',
                'page'             => $this->_panel_page,
                'admin-tabs'       => $admin_tabs,
                'options-path'     => YITH_WCPB_DIR . '/plugin-options'
            );

            /* === Fixed: not updated theme  === */
            if( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
                require_once( 'plugin-fw/lib/yit-plugin-panel-wc.php' );
            }

            $this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );
            
            add_action( 'woocommerce_admin_field_yith_wcpb_upload', array( $this->_panel, 'yit_upload' ), 10, 1 );
            add_action( 'woocommerce_update_option_yith_wcpb_upload', array( $this->_panel, 'yit_upload_update' ), 10, 1 );
        }

        public function admin_enqueue_scripts() {
            wp_enqueue_style( 'yith-wcpb-admin-styles', YITH_WCPB_ASSETS_URL . '/css/admin.css');
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_script('jquery-ui-tabs');
            wp_enqueue_style('jquery-ui-style-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/themes/smoothness/jquery-ui.css');
            wp_enqueue_style('googleFontsOpenSans', 'http://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800,300');
            
            $screen     = get_current_screen();
            $metabox_js = defined( 'YITH_WCPB_PREMIUM' ) ? 'bundle_options_metabox_premium.js' : 'bundle_options_metabox.js';

            if( 'product' == $screen->id  ) {
                wp_enqueue_script( 'yith_wcpb_bundle_options_metabox', YITH_WCPB_ASSETS_URL .'/js/' . $metabox_js, array('jquery'), '1.0.0', true );
                wp_localize_script( 'yith_wcpb_bundle_options_metabox', 'ajax_object', array( 
                    'free_not_simple' => __('You can add only simple products with the FREE version of YITH WooCommerce Product Bundles', 'yith-wcpb') ) );
            }
        }

         /**
         * Show premium landing tab
         *
         * @return   void
         * @since    1.0
         * @author  Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function show_premium_tab(){
            $landing = YITH_WCPB_TEMPLATE_PATH . '/premium.php';
            file_exists( $landing ) && require( $landing );
        }

        /**
         * Get the premium landing uri
         *
         * @since   1.0.0
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  string The premium landing link
         */
        public function get_premium_landing_uri() {
            return defined( 'YITH_REFER_ID' ) ? $this->_premium_landing . '?refer_id=' . YITH_REFER_ID : $this->_premium_landing . '?refer_id=1030585';
        }
    }
}

/**
 * Unique access to instance of YITH_WCPB_Admin class
 *
 * @return \YITH_WCPB_Admin
 * @since 1.0.0
 */
function YITH_WCPB_Admin(){
    return YITH_WCPB_Admin::get_instance();
}
?>
