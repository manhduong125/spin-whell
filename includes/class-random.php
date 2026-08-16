<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Random {
    public static function pick_prize( $prizes ) {
        if ( empty( $prizes ) || ! is_array( $prizes ) ) {
            return false;
        }

        $total_weight = 0;
        $eligible = array();

        foreach ( $prizes as $prize ) {
            $weight = isset( $prize['weight'] ) ? max( 0, intval( $prize['weight'] ) ) : 0;
            $stock  = isset( $prize['stock'] ) ? intval( $prize['stock'] ) : 0;
            $status = isset( $prize['status'] ) ? strtolower( trim( $prize['status'] ) ) : 'active';

            if ( $weight <= 0 || $stock <= 0 || ( $status !== '' && $status !== 'active' ) ) {
                continue;
            }

            $total_weight += $weight;
            $eligible[] = array(
                'prize'  => $prize,
                'weight' => $weight,
            );
        }

        if ( empty( $eligible ) || $total_weight <= 0 ) {
            return false;
        }

        $rand = mt_rand( 1, $total_weight );
        $cumulative = 0;

        foreach ( $eligible as $item ) {
            $cumulative += $item['weight'];
            if ( $rand <= $cumulative ) {
                return $item['prize'];
            }
        }

        return $eligible[0]['prize'];
    }
}
