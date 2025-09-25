<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CommuneHub_Loader {

    public static function init() {
        CommuneHub_Post_Types::init();
        CommuneHub_Votes::init();
        CommuneHub_Membership::init();
        CommuneHub_Query::init();
        CommuneHub_REST::init();
        CommuneHub_Assets::init();
        CommuneHub_Shortcodes::init();
    }
}