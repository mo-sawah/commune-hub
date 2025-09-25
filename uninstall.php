<?php
// Uninstall logic (optional).
// If you want to remove tables & data when user deletes plugin, uncomment & implement:

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/*
global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ch_votes" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ch_memberships" );
*/