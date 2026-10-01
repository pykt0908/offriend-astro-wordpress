<?php
/**
 * Plugin Name: Offriend Headless Core Loader (Must-Use)
 * Description: Automatically loads the Offriend Headless Core plugin and ensures initial data setup.
 * Version: 1.0.0
 * Author: Offriend Engineering
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$offriend_core_file = WP_CONTENT_DIR . '/plugins/offriend-core/offriend-core.php';
if ( file_exists( $offriend_core_file ) ) {
    require_once $offriend_core_file;

    // Automatic seed for initial run if not yet seeded
    add_action( 'init', function() {
        if ( ! get_option( 'offriend_initial_seeded_v1' ) ) {
            if ( class_exists( 'Offriend_Headless_Core' ) ) {
                Offriend_Headless_Core::get_instance()->seed_initial_content();
                update_option( 'offriend_initial_seeded_v1', 1 );
            }
        }
    }, 20 );
}
