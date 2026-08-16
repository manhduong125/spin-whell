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

    public static function get_setting_items( $type ) {
        $stored = get_option( 'wp_spin_wheel_setting_items', array() );
        if ( ! is_array( $stored ) ) {
            return array();
        }

        $group_key = self::normalize_setting_group( $type );
        if ( ! is_array( $stored[ $group_key ] ?? null ) ) {
            return array();
        }

        return $stored[ $group_key ];
    }

    private static function normalize_setting_group( $type ) {
        $type = is_string( $type ) ? sanitize_key( $type ) : '';

        if ( in_array( $type, array( 'audio_start', 'audios_start', 'spin_wheel_audio_start' ), true ) ) {
            return 'audios_start';
        }

        if ( in_array( $type, array( 'audio_end', 'audios_end', 'spin_wheel_audio_end' ), true ) ) {
            return 'audios_end';
        }

        return $type;
    }

    public static function get_setting_item_config( $type, $item_id ) {
        $item_id = (string) $item_id;
        if ( '' === $item_id ) {
            return array();
        }

        foreach ( self::get_setting_items( $type ) as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }

            if ( (string) ( $item['id'] ?? '' ) === $item_id ) {
                return is_array( $item['config'] ?? null ) ? $item['config'] : array();
            }
        }

        return array();
    }

    public static function get_wheel_settings( $post_id ) {
        $overrides = self::get_wheel_overrides( $post_id );
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
            'wheel'        => array( 'size' => 500, 'border' => 8, 'border_color' => '#ffffff', 'shadow' => true ),
            'button'       => array( 'text' => __( 'QUAY', 'wp-spin-wheel' ), 'color' => '#ff0000', 'text_color' => '#ffffff', 'radius' => 50, 'background_image' => '' ),
            'pointer'      => array( 'image' => '', 'size' => 80 ),
            'animation'    => array( 'duration' => 6, 'confetti' => true ),
            'audio'        => array( 'spin' => '', 'win' => '' ),
            'custom_css'   => '',
        );

        $settings = array_replace_recursive( $defaults, $overrides );

        if ( ! empty( $global_settings['wheel_title'] ) ) {
            $settings['title'] = sanitize_text_field( $global_settings['wheel_title'] );
        }
        if ( ! empty( $global_settings['wheel_description'] ) ) {
            $settings['description'] = sanitize_textarea_field( $global_settings['wheel_description'] );
        }

        $selected_background_item = self::get_setting_item_config( 'background', $overrides['selected_background_id'] ?? '' );
        if ( ! empty( $selected_background_item ) ) {
            $settings['background'] = array(
                'type'  => ! empty( $selected_background_item['image'] ) ? 'image' : ( ! empty( $selected_background_item['type'] ) ? $selected_background_item['type'] : 'color' ),
                'value' => ! empty( $selected_background_item['value'] ) ? sanitize_hex_color( $selected_background_item['value'] ) : ( isset( $settings['background']['value'] ) ? $settings['background']['value'] : '#ffffff' ),
                'image' => ! empty( $selected_background_item['image'] ) ? esc_url_raw( $selected_background_item['image'] ) : '',
            );
        } elseif ( ! empty( $global_settings['wheel_background_color'] ) ) {
            $settings['background'] = array(
                'type'  => ! empty( $global_settings['wheel_background_image'] ) ? 'image' : 'color',
                'value' => sanitize_hex_color( $global_settings['wheel_background_color'] ),
                'image' => ! empty( $global_settings['wheel_background_image'] ) ? esc_url_raw( $global_settings['wheel_background_image'] ) : '',
            );
        }

        $selected_button_item = self::get_setting_item_config( 'button', $overrides['selected_button_id'] ?? '' );
        if ( ! empty( $selected_button_item ) ) {
            $settings['button'] = array_replace_recursive( $settings['button'], array(
                'text'             => ! empty( $selected_button_item['text'] ) ? sanitize_text_field( $selected_button_item['text'] ) : $settings['button']['text'],
                'color'            => ! empty( $selected_button_item['color'] ) ? sanitize_hex_color( $selected_button_item['color'] ) : $settings['button']['color'],
                'text_color'       => ! empty( $selected_button_item['text_color'] ) ? sanitize_hex_color( $selected_button_item['text_color'] ) : $settings['button']['text_color'],
                'radius'           => ! empty( $selected_button_item['radius'] ) ? intval( $selected_button_item['radius'] ) : $settings['button']['radius'],
                'background_image' => ! empty( $selected_button_item['background_image'] ) ? esc_url_raw( $selected_button_item['background_image'] ) : $settings['button']['background_image'],
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

        $selected_pointer_item = self::get_setting_item_config( 'pointer', $overrides['selected_pointer_id'] ?? '' );
        if ( ! empty( $selected_pointer_item ) ) {
            $settings['pointer'] = array_replace_recursive( $settings['pointer'], array(
                'image' => ! empty( $selected_pointer_item['image'] ) ? esc_url_raw( $selected_pointer_item['image'] ) : $settings['pointer']['image'],
                'size'  => ! empty( $selected_pointer_item['size'] ) ? intval( $selected_pointer_item['size'] ) : $settings['pointer']['size'],
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

        return $settings;
    }
}
