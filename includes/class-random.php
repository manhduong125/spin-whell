<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Random {
    public static function pick_prize( $prizes ) {
        $weighted = array();
        foreach ( $prizes as $prize ) {
            $weight = isset( $prize['weight'] ) ? max( 1, intval( $prize['weight'] ) ) : 1;
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
