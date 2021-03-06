<?php
/**
 * Plugin Name: Google Photos Share WP Plugin
 * Plugin URI:  https://github.com/Ducz0r/google-photos-share-wp-plugin
 * Description: This plugin enables sharing of Google Photos Shared images within WP (Gutenberg) posts.
 * Version:     0.0.1
 * Author:      Luka Murn <murn.luka@gmail.com>
 * Author URI:  https://github.com/Ducz0r
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 */

global $wpdb;

/** Constants */
define('LMGPS_ROOT_DIR', WP_PLUGIN_DIR . '/google-photos-share');
define('LMGPS_TABLE_NAME', $wpdb->prefix . 'lm_google_photo_shares');
define('LMGPS_MENU_SLUG_SHARES', 'lm-google-photo-shares-menu-shares');
define('LMGPS_MENU_SLUG_SETTINGS', 'lm-google-photo-shares-menu-settings');

define('LMGPS_REGEX_NEWLINE', '/(\r\n|\n|\r)/m');
define('LMGPS_REGEX_PHOTOS', '/\["(https:\/\/.{3}\.googleusercontent\.com\/.{139})",\d{1,8},\d{1,8},null,null,null,null,null,null,\[\d{1,16}\]\]/m');

if(!defined('ABSPATH')) die();

/** Initialize plugin's DB */
include_once LMGPS_ROOT_DIR . '/init/db.php';
register_activation_hook(__FILE__, 'lmgps_init_plugin_db');

/** Require additional files */
require_once(LMGPS_ROOT_DIR . '/block/index.php');

if (is_admin()) {
  require_once(LMGPS_ROOT_DIR . '/admin/index.php');
  require_once(LMGPS_ROOT_DIR . '/admin/shares/index.php');
  require_once(LMGPS_ROOT_DIR . '/admin/shares/delete.php');
  require_once(LMGPS_ROOT_DIR . '/admin/shares/view_photos.php');
  require_once(LMGPS_ROOT_DIR . '/admin/shares/new_share.php');
  require_once(LMGPS_ROOT_DIR . '/admin/settings/index.php');
}