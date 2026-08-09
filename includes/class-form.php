<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Form {
    public static function validate( $fields, $form_data ) {
        $errors = array();
        foreach ( $fields as $field ) {
            if ( empty( $form_data[ $field ] ) ) {
                $errors[] = sprintf( __( '%s is required.', 'wp-spin-wheel' ), ucfirst( $field ) );
            }
        }
        return $errors;
    }
}
