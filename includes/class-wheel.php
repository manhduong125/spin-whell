<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Wheel {
    public function __construct() {
        add_action( 'wp_ajax_spin_wheel_spin', array( $this, 'ajax_spin' ) );
        add_action( 'wp_ajax_nopriv_spin_wheel_spin', array( $this, 'ajax_spin' ) );
    }

    public function ajax_spin() {
        check_ajax_referer( 'spin_wheel_nonce', 'nonce' );

        $wheel_id = absint( $_POST['wheel_id'] ?? 0 );
        $form_data = isset( $_POST['form'] ) ? wp_unslash( $_POST['form'] ) : array();

        $result = self::spin_wheel( $wheel_id, $form_data );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array( 'prize' => $result ) );
    }

    public static function spin_wheel( $wheel_id, $form_data = array() ) {
        $wheel_id = absint( $wheel_id );
        if ( ! $wheel_id || get_post_type( $wheel_id ) !== 'spin_wheel' ) {
            return new WP_Error( 'invalid_wheel', __( 'Invalid wheel.', 'wp-spin-wheel' ), array( 'status' => 404 ) );
        }

        $settings = WP_Spin_Wheel_Helper::get_wheel_settings( $wheel_id );
        $form_data = is_array( $form_data ) ? $form_data : array();

        $validation = self::validate_spin_request( $wheel_id, $settings, $form_data );
        if ( is_wp_error( $validation ) ) {
            return $validation;
        }

        $prizes = WP_Spin_Wheel_Prize::get_prizes( $wheel_id );
        if ( empty( $prizes ) ) {
            return new WP_Error( 'no_prizes', __( 'No prizes configured.', 'wp-spin-wheel' ), array( 'status' => 400 ) );
        }

        $winner = WP_Spin_Wheel_Random::pick_prize( $prizes );
        if ( ! $winner ) {
            return new WP_Error( 'no_winner', __( 'No prize selected.', 'wp-spin-wheel' ), array( 'status' => 400 ) );
        }

        if ( ! WP_Spin_Wheel_Prize::decrease_stock( $winner['id'] ?? 0 ) ) {
            return new WP_Error( 'stock_error', __( 'Unable to decrease prize stock.', 'wp-spin-wheel' ), array( 'status' => 500 ) );
        }

        $history = new WP_Spin_Wheel_History();
        $history->record_spin( $wheel_id, $winner['id'] ?? 0, $form_data );

        return $winner;
    }

    private static function validate_spin_request( $wheel_id, $settings, $form_data ) {
        $required_fields = is_array( $settings['form_fields'] ?? array() ) ? $settings['form_fields'] : array();
        foreach ( $required_fields as $field ) {
            $value = isset( $form_data[ $field ] ) ? trim( $form_data[ $field ] ) : '';
            if ( '' === $value ) {
                return new WP_Error( 'missing_field', sprintf( __( 'Field %s is required.', 'wp-spin-wheel' ), esc_html( $field ) ), array( 'status' => 400 ) );
            }
        }

        $limit = absint( $settings['spin_limit'] ?? 0 );
        $limit_type = $settings['spin_limit_type'] ?? 'none';
        if ( $limit > 0 && $limit_type !== 'none' ) {
            $limit_check = self::check_spin_limit( $wheel_id, $limit_type, $limit, $form_data );
            if ( is_wp_error( $limit_check ) ) {
                return $limit_check;
            }
        }

        return true;
    }

    private static function check_spin_limit( $wheel_id, $limit_type, $limit, $form_data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'spin_history';

        switch ( $limit_type ) {
            case 'per_email':
                $email = sanitize_email( $form_data['email'] ?? '' );
                if ( empty( $email ) ) {
                    return new WP_Error( 'missing_email', __( 'Email is required to spin.', 'wp-spin-wheel' ), array( 'status' => 400 ) );
                }
                $count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE wheel_id = %d AND email = %s", $wheel_id, $email ) );
                break;
            case 'per_phone':
                $phone = sanitize_text_field( $form_data['phone'] ?? '' );
                if ( empty( $phone ) ) {
                    return new WP_Error( 'missing_phone', __( 'Phone is required to spin.', 'wp-spin-wheel' ), array( 'status' => 400 ) );
                }
                $count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE wheel_id = %d AND phone = %s", $wheel_id, $phone ) );
                break;
            case 'per_cookie':
                $cookie_id = sanitize_text_field( $form_data['cookie_id'] ?? ( $_COOKIE['wp_spin_wheel_cookie'] ?? '' ) );
                if ( empty( $cookie_id ) ) {
                    return new WP_Error( 'missing_cookie', __( 'Cookie identifier is required to spin.', 'wp-spin-wheel' ), array( 'status' => 400 ) );
                }
                $count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE wheel_id = %d AND cookie = %s", $wheel_id, $cookie_id ) );
                break;
            case 'per_ip':
            default:
                $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
                if ( empty( $ip ) ) {
                    return new WP_Error( 'missing_ip', __( 'Unable to detect your IP address.', 'wp-spin-wheel' ), array( 'status' => 400 ) );
                }
                $count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE wheel_id = %d AND ip = %s", $wheel_id, $ip ) );
                break;
        }

        if ( $count >= $limit ) {
            return new WP_Error( 'spin_limit_reached', __( 'You have reached the spin limit for this wheel.', 'wp-spin-wheel' ), array( 'status' => 429 ) );
        }

        return true;
    }
}
