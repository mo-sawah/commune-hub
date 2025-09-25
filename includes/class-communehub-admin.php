<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CommuneHub_Admin {

    const OPTION_KEY = 'commune_hub_options';

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'menu' ] );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
    }

    public static function defaults() {
        return [
            'items_per_page' => 20,
            'enable_voting'  => 1,
            'enable_tags'    => 1,
            'default_sort'   => 'hot',
            'allow_guests'   => 1,
        ];
    }

    public static function get_options() {
        $stored = get_option( self::OPTION_KEY, [] );
        return wp_parse_args( $stored, self::defaults() );
    }

    public static function menu() {
        add_menu_page(
            __( 'Commune Hub', 'commune-hub' ),
            __( 'Commune Hub', 'commune-hub' ),
            'manage_options',
            'commune-hub',
            [ __CLASS__, 'render_page' ],
            'dashicons-networking',
            57
        );
    }

    public static function register_settings() {
        register_setting( 'commune_hub_settings', self::OPTION_KEY, [
            'type' => 'array',
            'sanitize_callback' => [ __CLASS__, 'sanitize' ],
            'default' => self::defaults()
        ] );

        add_settings_section( 'ch_main', __( 'General Settings', 'commune-hub' ), '__return_false', 'commune_hub_settings' );

        add_settings_field(
            'items_per_page',
            __( 'Items Per Page', 'commune-hub' ),
            [ __CLASS__, 'field_number' ],
            'commune_hub_settings',
            'ch_main',
            [ 'label_for' => 'ch_items_per_page', 'key' => 'items_per_page', 'min' => 5, 'max' => 100 ]
        );

        add_settings_field(
            'enable_voting',
            __( 'Enable Voting', 'commune-hub' ),
            [ __CLASS__, 'field_checkbox' ],
            'commune_hub_settings',
            'ch_main',
            [ 'label_for' => 'ch_enable_voting', 'key' => 'enable_voting' ]
        );

        add_settings_field(
            'enable_tags',
            __( 'Enable Tags', 'commune-hub' ),
            [ __CLASS__, 'field_checkbox' ],
            'commune_hub_settings',
            'ch_main',
            [ 'label_for' => 'ch_enable_tags', 'key' => 'enable_tags' ]
        );

        add_settings_field(
            'default_sort',
            __( 'Default Sort', 'commune-hub' ),
            [ __CLASS__, 'field_select' ],
            'commune_hub_settings',
            'ch_main',
            [ 'label_for' => 'ch_default_sort', 'key' => 'default_sort', 'choices' => [ 'hot'=>'Hot','new'=>'New','top'=>'Top','rising'=>'Rising' ] ]
        );

        add_settings_field(
            'allow_guests',
            __( 'Allow Guest Viewing', 'commune-hub' ),
            [ __CLASS__, 'field_checkbox' ],
            'commune_hub_settings',
            'ch_main',
            [ 'label_for' => 'ch_allow_guests', 'key' => 'allow_guests' ]
        );
    }

    public static function sanitize( $input ) {
        $defaults = self::defaults();
        $out = [];
        $out['items_per_page'] = max(5, min(100, intval( $input['items_per_page'] ?? $defaults['items_per_page'] )));
        $out['enable_voting']  = empty( $input['enable_voting'] ) ? 0 : 1;
        $out['enable_tags']    = empty( $input['enable_tags'] ) ? 0 : 1;
        $out['default_sort']   = in_array( $input['default_sort'] ?? '', [ 'hot','new','top','rising' ], true ) ? $input['default_sort'] : $defaults['default_sort'];
        $out['allow_guests']   = empty( $input['allow_guests'] ) ? 0 : 1;
        return $out;
    }

    public static function field_number( $args ) {
        $opts = self::get_options();
        $key = $args['key'];
        $val = intval( $opts[$key] );
        printf(
            '<input type="number" id="%1$s" name="%2$s[%3$s]" value="%4$d" min="%5$d" max="%6$d" class="small-text" />',
            esc_attr( $args['label_for'] ),
            esc_attr( self::OPTION_KEY ),
            esc_attr( $key ),
            $val,
            intval( $args['min'] ),
            intval( $args['max'] )
        );
    }

    public static function field_checkbox( $args ) {
        $opts = self::get_options();
        $key = $args['key'];
        $checked = ! empty( $opts[$key] ) ? 'checked' : '';
        printf(
            '<label><input type="checkbox" id="%1$s" name="%2$s[%3$s]" value="1" %4$s /> %5$s</label>',
            esc_attr( $args['label_for'] ),
            esc_attr( self::OPTION_KEY ),
            esc_attr( $key ),
            $checked,
            esc_html__( 'Enabled', 'commune-hub' )
        );
    }

    public static function field_select( $args ) {
        $opts = self::get_options();
        $key = $args['key'];
        $val = $opts[$key];
        echo '<select id="'.esc_attr( $args['label_for'] ).'" name="'.esc_attr( self::OPTION_KEY ).'['.esc_attr( $key ).']">';
        foreach ( $args['choices'] as $k => $label ) {
            printf( '<option value="%s" %s>%s</option>',
                esc_attr( $k ),
                selected( $val, $k, false ),
                esc_html( $label )
            );
        }
        echo '</select>';
    }

    public static function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Commune Hub Settings', 'commune-hub' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'commune_hub_settings' );
                do_settings_sections( 'commune_hub_settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}