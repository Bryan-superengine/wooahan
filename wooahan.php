<?php
/**
 * Plugin Name: Wooahan
 * Description: 우커머스의 상품 등록부터 택배발송, 주문처리까지 보다 직관적인 UI 와 한국 실정에 맞는 다양한 기능이 들어 있습니다.
 * Plugin URI: http://superengine.io/wooahan/
 * Version: 0.0.3
 * Author : Mr. Bryan Lee
 * Author URI: http://superengine.io
 *
 *
 * Text Domain: wooahan
 *
 * @package Wooahan
 * @category Core
 */

define( 'WOOAHAN__FILE__', __FILE__ );
define( 'WOOAHAN_PLUGIN_BASE', plugin_basename( WOOAHAN__FILE__ ) );
define( 'WOOAHAN_PATH', plugin_dir_path( WOOAHAN__FILE__ ) );

require_once( WOOAHAN_PATH . 'class/initialize.php' );
require_once( WOOAHAN_PATH . 'class/class.wooahanUpdater.php' );
require_once( WOOAHAN_PATH . 'class/class.shopOrder.php' );
require_once( WOOAHAN_PATH . 'class/class.orderList.php' );
require_once( WOOAHAN_PATH . 'class/class.orderTracking.php' );
require_once( WOOAHAN_PATH . 'class/class.shipping.php' );
require_once( WOOAHAN_PATH . 'functions.php' );
require_once( WOOAHAN_PATH . 'class/class.badge.php' );
require_once( WOOAHAN_PATH . 'class/class-tgm-plugin-activation.php' );