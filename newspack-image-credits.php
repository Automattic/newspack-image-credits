<?php
/**
 * Plugin Name: Newspack Image Credits
 * Description: Add photo credit info to images. A modernization of Navis Media Credit.
 * Version: 1.0.1
 * Author: Automattic, INN Labs, Project Argo
 * Author URI: https://newspack.blog/
 * License: GPL2
 * Text Domain: newspack-image-credits
 * Domain Path: /languages/
 */

defined( 'ABSPATH' ) || exit;

// Define constants.
if ( ! defined( 'NEWSPACK_IMAGE_CREDITS_PLUGIN_FILE' ) ) {
	define( 'NEWSPACK_IMAGE_CREDITS_FILE', __FILE__ );
	define( 'NEWSPACK_IMAGE_CREDITS_PLUGIN_FILE', plugin_dir_path( NEWSPACK_IMAGE_CREDITS_FILE ) );
	define( 'NEWSPACK_IMAGE_CREDITS_URL', plugin_dir_url( NEWSPACK_IMAGE_CREDITS_FILE ) );
}

require_once NEWSPACK_IMAGE_CREDITS_PLUGIN_FILE . '/includes/class-newspack-image-credits-settings.php';
require_once NEWSPACK_IMAGE_CREDITS_PLUGIN_FILE . '/includes/class-newspack-image-credits.php';
