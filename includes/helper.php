<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Helper {
    public static function sanitize_text( $text ) {
        return sanitize_text_field( wp_unslash( $text ) );
    }

    public static function get_post_meta( $post_id, $meta_key, $single = true ) {
        return get_post_meta( $post_id, $meta_key, $single );
    }

    public static function get_preset_config( $post_id ) {
        $config = self::get_post_meta( $post_id, '_spin_wheel_preset_config', true );
        if ( is_string( $config ) ) {
            $config = json_decode( $config, true );
        }
        return is_array( $config ) ? $config : array();
    }

    public static function get_wheel_preset_id( $post_id ) {
        return absint( self::get_post_meta( $post_id, '_spin_wheel_preset_id', true ) );
    }

    public static function get_wheel_overrides( $post_id ) {
        $overrides = self::get_post_meta( $post_id, '_spin_wheel_overrides', true );
        if ( is_string( $overrides ) ) {
            $overrides = json_decode( $overrides, true );
        }
        if ( ! is_array( $overrides ) ) {
            $overrides = array();
        }

        if ( empty( $overrides ) ) {
            $legacy_design = self::get_post_meta( $post_id, '_spin_wheel_design', true );
            if ( is_string( $legacy_design ) ) {
                $legacy_design = json_decode( $legacy_design, true );
            }
            if ( is_array( $legacy_design ) ) {
                $overrides = $legacy_design;
            }
        }

        return $overrides;
    }

    public static function get_wheel_settings( $post_id ) {
        $preset_id = self::get_wheel_preset_id( $post_id );
        $preset_config = array();
        if ( $preset_id && get_post_type( $preset_id ) === 'spin_wheel_preset' ) {
            $preset_config = self::get_preset_config( $preset_id );
        }

        $overrides = self::get_wheel_overrides( $post_id );
        $preset_title = '';
        $preset_thumbnail = '';

        if ( $preset_id && get_post_type( $preset_id ) === 'spin_wheel_preset' ) {
            $preset_title = get_the_title( $preset_id );
            $preset_thumbnail = get_the_post_thumbnail_url( $preset_id, 'full' );
        }

        $defaults = array(
            'title'        => get_the_title( $post_id ),
            'description'  => get_post_field( 'post_content', $post_id ),
            'background'   => array( 'type' => 'color', 'value' => '#ffffff', 'image' => '' ),
            'logo'         => '',
            'music'        => '',
            'sound'        => '',
            'effect'       => '',
            'spin_limit'   => 0,
            'spin_limit_type' => 'none',
            'form_fields'  => array(),
            'preset_id'    => $preset_id,
            'preset_title' => $preset_title,
            'preset_thumbnail' => $preset_thumbnail,
            'wheel'        => array( 'size' => 500, 'border' => 8, 'border_color' => '#ffffff', 'shadow' => true ),
            'button'       => array( 'text' => __( 'QUAY', 'wp-spin-wheel' ), 'color' => '#ff0000', 'text_color' => '#ffffff', 'radius' => 50, 'background_image' => '' ),
            'pointer'      => array( 'image' => '', 'size' => 80 ),
            'font'         => array( 'family' => 'Arial', 'size' => 20 ),
            'animation'    => array( 'duration' => 6, 'confetti' => true ),
            'audio'        => array( 'spin' => '', 'win' => '' ),
            'custom_css'   => '',
        );

        $settings = array_replace_recursive( $defaults, $preset_config, $overrides );
        $settings['preset_id'] = $preset_id;
        $settings['preset_title'] = $preset_title;
        $settings['preset_thumbnail'] = $preset_thumbnail;

        return $settings;
    }
}
