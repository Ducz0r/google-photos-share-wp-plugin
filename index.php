<?php
/**
 * Plugin Name: Google Photos Share WP Plugin
 * Plugin URI:  https://github.com/Ducz0r/google-photos-share-wp-plugin
 * Description: Plugin to enable sharing Google Photos Shared images within WP (Gutenberg) posts.
 * Version:     0.0.1
 * Author:      Luka Murn <murn.luka@gmail.com>
 * Author URI:  https://github.com/Ducz0r
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 */
function lmgpsb_register_block() {
  if (!function_exists('register_block_type')) {
    // Gutenberg is not active
    return;
  }

  wp_register_script(
    'lmgpsb-block-editor-script',
    plugins_url('block.js', __FILE__),
    array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'),
    filemtime(plugin_dir_path(__FILE__) . 'block.js')
  );

  wp_register_style(
    'lmgpsb-block-editor-style',
    plugins_url('editor.css', __FILE__),
    array('wp-edit-blocks'),
    filemtime(plugin_dir_path(__FILE__) . 'editor.css')
  );

  wp_register_style(
    'lmgpsb-block-style',
    plugins_url('style.css', __FILE__),
    array(),
    filemtime(plugin_dir_path(__FILE__) . 'style.css')
  );

  register_block_type('lmgpsb/block', array(
    'style' => 'lmgpsb-block-style',
    'editor_style' => 'lmgpsb-block-editor-style',
    'editor_script' => 'lmgpsb-block-editor-script',
  ));
}

add_action('init', 'lmgpsb_register_block');
