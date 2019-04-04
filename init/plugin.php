<?php
/**********************************************************************************
 * INIT PLUGIN: this call mainly initializes the custom database table.
 *********************************************************************************/

register_activation_hook(__FILE__, 'lmgps_init_plugin');

function lmgps_init_plugin() {
  $charset_collate = '';
  if (!empty($wpdb->charset)) {
    $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
  }
  if (!empty($wpdb->collate)) {
    $charset_collate .= " COLLATE {$wpdb->collate}";
  }
  // Status: 0 - Inactive, 1 - Active
  $sql = "CREATE TABLE " . LMGPS_TABLE_NAME . " (
    id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
    timestamp DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
    share_url TINYTEXT NOT NULL,
    photos_count SMALLINT DEFAULT 0 NOT NULL,
    photo_urls MEDIUMTEXT DEFAULT 'a:0:{}' NOT NULL,
    status TINYINT DEFAULT 1 NOT NULL,
    UNIQUE KEY id (id)
    ) $charset_collate;";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}