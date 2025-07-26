<?php
function registrar_bloque_grafica_bar() {
    wp_register_script(
        'cdb-grafica-bar',
        plugins_url('build/index.js', __FILE__),
        include(plugin_dir_path(__FILE__) . 'build/index.asset.php'),
        null,
        true
    );

    register_block_type('cdb/grafica-bar', array(
        'editor_script' => 'cdb-grafica-bar',
    ));
}
add_action('init', 'registrar_bloque_grafica_bar');
