<?php
/*
Plugin Name: F-Shop
Plugin URI: https://f-shop.top/
Description:  Плагин интернет магазина для Wordpress.
Version: 1.2
Author: Vitaliy Karakushan
Author URI: https://f-shop.top/
License: GPL2
Domain: fast-shop
*/
/*
Copyright 2016 Vitaliy Karakushan  (email : karakushan@gmail.com)

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

/* Выходим если кто-то пытается получить прямой доступ к файлам плагина */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Подключаем composer (автозагрузка классов, подробнее: https://getcomposer.org/)*/
require_once __DIR__ . '/vendor/autoload.php';

/* Основные константы для упрощения режим разработки, сокращения написания путей и пр. */
define( 'FS_PLUGIN_VER', '1.2' ); // версия плагина
define( 'FS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) ); // абсолютный системный путь
define( 'FS_PLUGIN_URL', plugin_dir_url( __FILE__ ) ); // абсолютный путь относительно сайта
define( 'FS_BASENAME', plugin_basename( __FILE__ ) ); // относительный путь типа my-plugin/my-plugin.php
define( 'FS_LANG_PATH', dirname( plugin_basename( __FILE__ ) ) . '/languages' ); // относительный путь типа my-plugin/my-plugin.php

define( 'FS_DEBUG', false ); // режим разработки, по умолчанию выключен

/* Включаем режим разработки если константа FS_DEBUG === true */
if ( FS_DEBUG === true ) {
	ini_set( 'error_reporting', E_ALL );
	ini_set( 'display_errors', 1 );
	ini_set( 'display_startup_errors', 1 );
}

/* Активируем класс инициализации плагина */
if ( ! class_exists( '\FS\FS_Init', false ) ) {
	$fs_init              = new \FS\FS_Init;
	$GLOBALS['fs_config'] = new FS\FS_Config();
}

function fs_activate_callback() {
	// Добавляем роль клиента
	add_role(
		'client',
		__( 'Client', 'fast-shop' ),
		array(
			'read'    => true,
			'level_0' => true
		)
	);
	$pages = array(
		'cart'      => array(
			'name'    => __( 'Basket', 'fast-shop' ),
			'content' => '[fs_cart]'
		),
		'payment'   => array(
			'name'    => __( 'Payment', 'fast-shop' ),
			'content' => '[fs_payment]'
		),
		'thanks'    => array(
			'name'    => __( 'Thank you', 'fast-shop' ),
			'content' => '[fs_order_info]'
		),
		'wishlist'  => array(
			'name'    => __( 'Wishlist', 'fast-shop' ),
			'content' => '[fs_fishlist]'
		),
		'dashboard' => array(
			'name'    => __( 'Dashboard', 'fast-shop' ),
			'content' => '[fs_user_cabinet]'
		),
		'auth'      => array(
			'name'    => __( 'Authorization', 'fast-shop' ),
			'content' => '[fs_login]'
		)
	);
	foreach ( $pages as $key => $page ) {
		wp_insert_post( array(
			'post_type'    => 'page',
			'post_name'    => $key,
			'post_title'   => $page['name'],
			'post_content' => $page['content'],
			'post_status'  => 'publish'
		) );
	}
}

register_activation_hook( __FILE__, 'fs_activate_callback' );