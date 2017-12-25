<?php

namespace FS;
/**
 * Инициализирует функции и классы плагина
 */
class FS_Init {
	public $config;

	public function __construct() {
		$this->config = new FS_Config();

		add_action( 'wp_enqueue_scripts', array( &$this, 'fast_shop_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'fast_shop_admin_scripts' ) );
		// Инициализация классов Fast Shop
		$GLOBALS['fs_option'] = get_option( 'fs_option' );
		new FS_Settings_Class;
		new FS_Ajax_Class;
		new FS_Shortcode;
		new FS_Rating_Class;
		new FS_Post_Type;
		new FS_Post_Types;
		new FS_Filters;
		new FS_Cart_Class;
		new FS_Orders_Class;
		new FS_Images_Class;
		new FS_Taxonomies_Class;
		new FS_Action_Class;
		new FS_Users_Class;
		new FS_Api_Class();
		new FS_Payment_Class();

		add_filter( "plugin_action_links_" . FS_BASENAME, array( $this, 'plugin_settings_link' ) );
		add_action( 'plugins_loaded', array( $this, 'true_load_plugin_textdomain' ) );
	} // END public function __construct

	function true_load_plugin_textdomain() {
		load_plugin_textdomain( 'fast-shop', false, FS_LANG_PATH );
	}

	// Add the settings link to the plugins page
	function plugin_settings_link( $links ) {
		$settings_link = '<a href="edit.php?post_type=product&page=fast-shop-settings">' . __( 'Settings' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	function fast_shop_scripts() {
		wp_enqueue_style( 'lightslider', $this->config->data['plugin_url'] . 'assets/lightslider/dist/css/lightslider.min.css', array(), $this->config->data['plugin_ver'], 'all' );
		wp_enqueue_style( 'font-awesome', $this->config->data['plugin_url'] . 'assets/fontawesome/css/font-awesome.min.css', array(), $this->config->data['plugin_ver'], 'all' );
		wp_enqueue_style( 'izi-toast', $this->config->data['plugin_url'] . 'assets/css/iziToast.min.css', array(), $this->config->data['plugin_ver'], 'all' );
		wp_enqueue_style( 'fs-style', $this->config->data['plugin_url'] . 'assets/css/f-shop.css', array(), $this->config->data['plugin_ver'], 'all' );

		wp_enqueue_script( "jquery-ui-core", array( 'jquery' ) );
		wp_enqueue_script( "jquery-ui-slider", array( 'jquery' ) );
		wp_enqueue_script( 'jquery-validate', $this->config->data['plugin_url'] . 'assets/js/jquery.validate.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'domurl', $this->config->data['plugin_url'] . 'assets/js/url.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'izi-toast', $this->config->data['plugin_url'] . 'assets/js/iziToast.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'lightslider', $this->config->data['plugin_url'] . 'assets/lightslider/dist/js/lightslider.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'f-shop', $this->config->data['plugin_url'] . 'assets/js/f-shop.js', array( 'jquery' ), $this->config->data['plugin_ver'], true );
		$price_max = (int) fs_price_max( false );
		$l10n      = array(
			'ajaxurl'           => admin_url( "admin-ajax.php" ),
			'fs_slider_max'     => $price_max,
			'fs_nonce'          => wp_create_nonce( 'fast-shop' ),
			'fs_currency'       => fs_currency(),
			'fs_slider_val_min' => ! empty( $_REQUEST['price_start'] ) ? (int) $_REQUEST['price_start'] : 0,
			'fs_slider_val_max' => ! empty( $_REQUEST['price_end'] ) ? (int) $_REQUEST['price_end'] : $price_max
		);
		wp_localize_script( 'f-shop', 'FastShopData', $l10n );
	}

	public function fast_shop_admin_scripts() {
		// необходимо для работы загрузчика изображений
		if ( ! did_action( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}
		wp_enqueue_script( "jquery-ui-core", array( 'jquery' ) );
		wp_enqueue_script( "jquery-ui-tabs", array( 'jquery' ) );
		wp_enqueue_style( 'spectrum', $this->config->data['plugin_url'] . 'assets/css/spectrum.css' );
		wp_enqueue_style( 'fs-admin', $this->config->data['plugin_url'] . 'assets/css/fs-admin.css', array(), $this->config->data['plugin_ver'], 'all' );

		wp_enqueue_script( 'spectrum', $this->config->data['plugin_url'] . 'assets/js/spectrum.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'js-cookie', $this->config->data['plugin_url'] . 'assets/js/js.cookie.js', array( 'jquery' ), null, true );
		$screen = get_current_screen();
		if ( $screen->id == 'edit-product' ) {
			wp_enqueue_script( 'fs-quick-edit', $this->config->data['plugin_url'] . 'assets/js/quick-edit.js', array( 'jquery' ), null, true );
		}

		wp_enqueue_script( 'fs-library', $this->config->data['plugin_url'] . 'assets/js/fs-library.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'fs-admin', $this->config->data['plugin_url'] . 'assets/js/fs-admin.js', array(
			'jquery',
			'js-cookie',
			'fs-library'
		), null, true );
		$l10n = array(
			'allowedImagesType' => fs_allowed_images_type( 'json' ),
			'mediaNonce'        => wp_create_nonce( 'media-form' )

		);
		wp_localize_script( 'fs-admin', 'fShop', $l10n );
	}
}
