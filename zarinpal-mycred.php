<?php
/*
Plugin Name: zarinpal myCRED
Version: 1.0
Description: افزونه درگاه پرداخت زرین پال برای افزونه myCred
Plugin URI: http://zarinpal.com
Author: erfan darvishnia
Author URI: http://github.com/erfanad1992
Text Domain: zarinpal-mycred
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Load plugin textdomain.
 *
 * @since 1.0
 */
function zarinpal_mycred_load_textdomain() {
    load_plugin_textdomain( 'zarinpal-mycred', FALSE, basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'zarinpal_mycred_load_textdomain' );

require_once( plugin_dir_path( __FILE__ ) . 'class-mycred-gateway-zarinpal.php' );
