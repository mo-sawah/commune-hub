<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CommuneHub_Install {

    public static function activate() {
        self::create_tables();
        flush_rewrite_rules();
    }

    private static function create_tables() {
        global $wpdb;

        $charset = $wpdb->get_charset_collate();

        $votes_table = $wpdb->prefix . 'ch_votes';
        $members_table = $wpdb->prefix . 'ch_memberships';

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql_votes = "CREATE TABLE $votes_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            post_id BIGINT UNSIGNED NOT NULL,
            vote TINYINT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_post (user_id, post_id),
            KEY post_idx (post_id),
            KEY user_idx (user_id)
        ) $charset;";

        $sql_memberships = "CREATE TABLE $members_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            community_id BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_community (user_id, community_id),
            KEY community_idx (community_id),
            KEY user_idx (user_id)
        ) $charset;";

        dbDelta( $sql_votes );
        dbDelta( $sql_memberships );
    }
}