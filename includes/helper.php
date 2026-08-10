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

    public static function get_global_settings() {
        $settings = get_option( 'wp_spin_wheel_settings', array() );
        return is_array( $settings ) ? $settings : array();
    }

    public static function resolve_preset_by_id( $presets, $selected_id ) {
        if ( ! is_array( $presets ) ) {
            return array();
        }

        foreach ( $presets as $preset ) {
            if ( is_array( $preset ) && ! empty( $preset['id'] ) && $preset['id'] === $selected_id ) {
                return $preset;
            }
        }

        return array();
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

        $global_settings = self::get_global_settings();

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

        if ( ! empty( $global_settings['wheel_title'] ) ) {
            $settings['title'] = sanitize_text_field( $global_settings['wheel_title'] );
        }
        if ( ! empty( $global_settings['wheel_description'] ) ) {
            $settings['description'] = sanitize_textarea_field( $global_settings['wheel_description'] );
        }

        $background_preset = self::resolve_preset_by_id( ! empty( $global_settings['wheel_background_presets'] ) ? $global_settings['wheel_background_presets'] : array(), ! empty( $global_settings['wheel_selected_background_preset'] ) ? $global_settings['wheel_selected_background_preset'] : '' );
        if ( ! empty( $background_preset ) ) {
            $settings['background'] = array(
                'type'  => ! empty( $background_preset['image'] ) ? 'image' : 'color',
                'value' => ! empty( $background_preset['color'] ) ? sanitize_hex_color( $background_preset['color'] ) : ( isset( $settings['background']['value'] ) ? $settings['background']['value'] : '#ffffff' ),
                'image' => ! empty( $background_preset['image'] ) ? esc_url_raw( $background_preset['image'] ) : '',
            );
        } elseif ( ! empty( $global_settings['wheel_background_color'] ) ) {
            $settings['background'] = array(
                'type'  => ! empty( $global_settings['wheel_background_image'] ) ? 'image' : 'color',
                'value' => sanitize_hex_color( $global_settings['wheel_background_color'] ),
                'image' => ! empty( $global_settings['wheel_background_image'] ) ? esc_url_raw( $global_settings['wheel_background_image'] ) : '',
            );
        }

        $button_preset = self::resolve_preset_by_id( ! empty( $global_settings['wheel_button_presets'] ) ? $global_settings['wheel_button_presets'] : array(), ! empty( $global_settings['wheel_selected_button_preset'] ) ? $global_settings['wheel_selected_button_preset'] : '' );
        if ( ! empty( $button_preset ) ) {
            $settings['button'] = array_replace_recursive( $settings['button'], array(
                'text'       => ! empty( $button_preset['text'] ) ? sanitize_text_field( $button_preset['text'] ) : $settings['button']['text'],
                'color'      => ! empty( $button_preset['color'] ) ? sanitize_hex_color( $button_preset['color'] ) : $settings['button']['color'],
                'text_color' => ! empty( $button_preset['text_color'] ) ? sanitize_hex_color( $button_preset['text_color'] ) : $settings['button']['text_color'],
                'radius'     => ! empty( $button_preset['radius'] ) ? intval( $button_preset['radius'] ) : $settings['button']['radius'],
            ) );
        } elseif ( ! empty( $global_settings['wheel_button_text'] ) ) {
            $settings['button']['text'] = sanitize_text_field( $global_settings['wheel_button_text'] );
            if ( ! empty( $global_settings['wheel_button_color'] ) ) {
                $settings['button']['color'] = sanitize_hex_color( $global_settings['wheel_button_color'] );
            }
            if ( ! empty( $global_settings['wheel_button_text_color'] ) ) {
                $settings['button']['text_color'] = sanitize_hex_color( $global_settings['wheel_button_text_color'] );
            }
        }

        $font_preset = self::resolve_preset_by_id( ! empty( $global_settings['wheel_font_presets'] ) ? $global_settings['wheel_font_presets'] : array(), ! empty( $global_settings['wheel_selected_font_preset'] ) ? $global_settings['wheel_selected_font_preset'] : '' );
        if ( ! empty( $font_preset ) ) {
            $settings['font'] = array_replace_recursive( $settings['font'], array(
                'family' => ! empty( $font_preset['family'] ) ? sanitize_text_field( $font_preset['family'] ) : $settings['font']['family'],
                'size'   => ! empty( $font_preset['size'] ) ? intval( $font_preset['size'] ) : $settings['font']['size'],
            ) );
        } elseif ( ! empty( $global_settings['wheel_font_family'] ) ) {
            $settings['font']['family'] = sanitize_text_field( $global_settings['wheel_font_family'] );
            if ( ! empty( $global_settings['wheel_font_size'] ) ) {
                $settings['font']['size'] = intval( $global_settings['wheel_font_size'] );
            }
        }

        $pointer_preset = self::resolve_preset_by_id( ! empty( $global_settings['wheel_pointer_presets'] ) ? $global_settings['wheel_pointer_presets'] : array(), ! empty( $global_settings['wheel_selected_pointer_preset'] ) ? $global_settings['wheel_selected_pointer_preset'] : '' );
        if ( ! empty( $pointer_preset ) ) {
            $settings['pointer'] = array_replace_recursive( $settings['pointer'], array(
                'image' => ! empty( $pointer_preset['image'] ) ? esc_url_raw( $pointer_preset['image'] ) : $settings['pointer']['image'],
                'size'  => ! empty( $pointer_preset['size'] ) ? intval( $pointer_preset['size'] ) : $settings['pointer']['size'],
            ) );
        } elseif ( ! empty( $global_settings['wheel_pointer_image'] ) ) {
            $settings['pointer']['image'] = esc_url_raw( $global_settings['wheel_pointer_image'] );
            if ( ! empty( $global_settings['wheel_pointer_size'] ) ) {
                $settings['pointer']['size'] = intval( $global_settings['wheel_pointer_size'] );
            }
        }

        if ( isset( $global_settings['wheel_animation_duration'] ) ) {
            $settings['animation']['duration'] = floatval( $global_settings['wheel_animation_duration'] );
        }
        if ( isset( $global_settings['wheel_confetti'] ) ) {
            $settings['animation']['confetti'] = ! empty( $global_settings['wheel_confetti'] );
        }

        $settings['preset_id'] = $preset_id;
        $settings['preset_title'] = $preset_title;
        $settings['preset_thumbnail'] = $preset_thumbnail;

        return $settings;
    }
}
