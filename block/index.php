<?php
/**********************************************************************************
 * CUSTOM GUTENBERG BLOCK
 *********************************************************************************/

add_action('init', 'lmgps_register_block');

function lmgps_register_block() {
    if (!function_exists('register_block_type')) {
        // Gutenberg is not active
        return;
    }

    wp_register_script(
        'lmgps-block-editor-script',
        plugins_url('index.js', __FILE__),
        array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'),
        filemtime(plugin_dir_path(__FILE__).
            'index.js')
    );

    wp_register_style(
        'lmgps-block-editor-style',
        plugins_url('styles/editor.css', __FILE__),
        array('wp-edit-blocks'),
        filemtime(plugin_dir_path(__FILE__).
            'styles/editor.css')
    );

    wp_register_style(
        'lmgps-block-style',
        plugins_url('styles/style.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__).
            'styles/style.css')
    );

    register_block_type('lmgps/block', array(
        'style' => 'lmgps-block-style',
        'editor_style' => 'lmgps-block-editor-style',
        'editor_script' => 'lmgps-block-editor-script',
    ));
}