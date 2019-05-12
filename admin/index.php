<?php
/**********************************************************************************
 * CUSTOM ADMIN MENU SECTION
 *********************************************************************************/

add_action('admin_enqueue_scripts', 'lmgps_admin_enqueue_style_and_js');

function lmgps_admin_enqueue_style_and_js() {
  wp_register_script('lmgps-admin-js', plugins_url('index.js', __FILE__), array('jquery'), '2.5.1' );
  wp_register_style('lmgps-admin-style', plugins_url('index.css', __FILE__), false, filemtime(plugin_dir_path(__FILE__) . 'index.css'));
  wp_enqueue_script('lmgps-admin-js');
  wp_enqueue_style('lmgps-admin-style');
}

add_action('admin_menu', 'lmgps_plugin_menu');

function lmgps_plugin_menu() {
  add_menu_page('Google Photos Share', 'Google Photos Share', 'manage_options', LMGPS_MENU_SLUG_SHARES);
  add_submenu_page(LMGPS_MENU_SLUG_SHARES, 'Google Photos Share - Shares', 'View shares', 'manage_options', LMGPS_MENU_SLUG_SHARES, 'lmgps_plugin_menu_shares');
  add_submenu_page(LMGPS_MENU_SLUG_SHARES, 'Google Photos Share - Settings', 'Settings', 'manage_options', LMGPS_MENU_SLUG_SETTINGS, 'lmgps_plugin_menu_settings');
}