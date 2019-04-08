<?php
/**********************************************************************************
 * DB Initialization
 *********************************************************************************/

function lmgps_init_plugin_db() {
  global $wpdb;

  $charset_collate = $wpdb->get_charset_collate();
  // Status: 0 - Inactive, 1 - Active
  $sql = "CREATE TABLE " . LMGPS_TABLE_NAME . " (
    id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
    timestamp DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
    share_url TINYTEXT NOT NULL,
    photos_count SMALLINT DEFAULT 0 NOT NULL,
    photo_urls MEDIUMTEXT NOT NULL,
    status TINYINT DEFAULT 1 NOT NULL,
    PRIMARY KEY  (id)
    ) $charset_collate;";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}