<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CommuneHub_Membership {

    public static function init() {
        // Hooks for when membership changes (update counts)
    }

    public static function is_member( $community_id, $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'ch_memberships';
        $exists = $wpdb->get_var( $wpdb->prepare("SELECT id FROM $table WHERE community_id=%d AND user_id=%d", $community_id, $user_id ) );
        return (bool) $exists;
    }

    public static function join( $community_id, $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'ch_memberships';
        if ( self::is_member( $community_id, $user_id ) ) {
            return true;
        }
        $wpdb->insert( $table, [
            'community_id' => $community_id,
            'user_id' => $user_id,
            'created_at' => current_time( 'mysql' )
        ], [ '%d', '%d', '%s' ] );
        self::update_community_member_count( $community_id );
        return true;
    }

    public static function leave( $community_id, $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'ch_memberships';
        $wpdb->delete( $table, [ 'community_id' => $community_id, 'user_id' => $user_id ], [ '%d', '%d' ] );
        self::update_community_member_count( $community_id );
        return true;
    }

    public static function member_count( $community_id ) {
        $count = get_post_meta( $community_id, '_ch_member_count', true );
        if ( '' === $count ) {
            self::update_community_member_count( $community_id );
            $count = get_post_meta( $community_id, '_ch_member_count', true );
        }
        return intval( $count );
    }

    private static function update_community_member_count( $community_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'ch_memberships';
        $count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE community_id=%d", $community_id ) );
        update_post_meta( $community_id, '_ch_member_count', intval( $count ) );
    }
}