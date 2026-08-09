<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Random {
    public static function pick_prize( $prizes ) {
        $weighted = array();

        foreach ( $prizes as $prize ) {
            $weight = isset( $prize['weight'] ) ? max( 0, intval( $prize['weight'] ) ) : 0;
            $stock = isset( $prize['stock'] ) ? intval( $prize['stock'] ) : 0;
            $status = isset( $prize['status'] ) ? strtolower( trim( $prize['status'] ) ) : 'active';

            if ( $weight <= 0 || $stock <= 0 || ( $status !== '' && $status !== 'active' ) ) {
                continue;
            }

            for ( $i = 0; $i < $weight; $i++ ) {
                $weighted[] = $prize;
            }
        }

        if ( empty( $weighted ) ) {
            return false;
        }

        return $weighted[ array_rand( $weighted ) ];
    }
}
