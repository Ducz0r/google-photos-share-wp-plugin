<?php
/**********************************************************************************
 * CUSTOM ADMIN MENU SECTION
 *********************************************************************************/

add_action('admin_menu', 'lmgps_plugin_menu');

function lmgps_plugin_menu() {
  add_menu_page('Google Photos Share', 'Google Photos Share', 'manage_options', LMGPS_MENU_SLUG_SHARES);
  add_submenu_page(LMGPS_MENU_SLUG_SHARES, 'Google Photos Share - Shares', 'View shares', 'manage_options', LMGPS_MENU_SLUG_SHARES, 'lmgps_plugin_menu_shares');
  add_submenu_page(LMGPS_MENU_SLUG_SHARES, 'Google Photos Share - Settings', 'Settings', 'manage_options', LMGPS_MENU_SLUG_SETTINGS, 'lmgps_plugin_menu_settings');
}