<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CommuneHub_Votes {

    public static function init() {
        // Placeholder for hooks if needed later
    }

    public static function get_user_vote( $post_id, $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'ch_votes';
        $vote  = $wpdb->get_var( $wpdb->prepare("SELECT vote FROM $table WHERE post_id=%d AND user_id=%d", $post_id, $user_id ) );
        return $vote ? intval( $vote ) : 0;
    }

    public static function record_vote( $post_id, $user_id, $direction ) {
        global $wpdb;
        $table = $wpdb->prefix . 'ch_votes';
        $dir   = $direction === 'up' ? 1 : ( $direction === 'down' ? -1 : 0 );

        if ( 0 === $dir ) {
            // Remove vote
            $wpdb->delete( $table, [ 'post_id' => $post_id, 'user_id' => $user_id ], [ '%d', '%d' ] );
            return 0;
        }

        $exists = self::get_user_vote( $post_id, $user_id );
        if ( $exists === $dir ) {
            // toggle off
            $wpdb->delete( $table, [ 'post_id' => $post_id, 'user_id' => $user_id ], [ '%d', '%d' ] );
            return 0;
        }

        if ( $exists ) {
            $wpdb->update( $table, [ 'vote' => $dir ], [ 'post_id' => $post_id, 'user_id' => $user_id ], [ '%d' ], [ '%d', '%d' ] );
        } else {
            $wpdb->insert( $table, [
                'post_id' => $post_id,
                'user_id' => $user_id,
                'vote'    => $dir,
                'created_at' => current_time( 'mysql' ),
            ], [ '%d', '%d', '%d', '%s' ] );
        }
        return $dir;
    }

    public static function aggregate_votes( $post_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'ch_votes';
        $row = $wpdb->get_row( $wpdb->prepare("
            SELECT 
                SUM(CASE WHEN vote=1 THEN 1 ELSE 0 END) as ups,
                SUM(CASE WHEN vote=-1 THEN 1 ELSE 0 END) as downs
            FROM $table WHERE post_id=%d
        ", $post_id ), ARRAY_A );
        $ups = intval( $row['ups'] ?? 0 );
        $downs = intval( $row['downs'] ?? 0 );
        return [
            'ups' => $ups,
            'downs' => $downs,
            'score' => $ups - $downs
        ];
    }

    public static function hot_score( $post_id, $created_gmt ) {
        $votes = self::aggregate_votes( $post_id );
        $net   = $votes['score'];
        $t     = ( time() - strtotime( $created_gmt ) ) / 3600; // hours
        $score = $net / pow( ( $t + 2 ), 1.5 );
        $score = apply_filters( 'commune_hub_hot_score_modifier', $score, $post_id );
        return $score;
    }

    public static function rising_score( $post_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'ch_votes';
        $since = gmdate( 'Y-m-d H:i:s', time() - 6 * HOUR_IN_SECONDS );
        $count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE post_id=%d AND created_at >= %s", $post_id, $since ) );
        return intval( $count );
    }
}