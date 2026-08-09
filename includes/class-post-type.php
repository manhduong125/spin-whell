<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Post_Type {
    public function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
    }

    public function register_post_type() {
        $labels = array(
            'name'               => __( 'Spin Wheel', 'wp-spin-wheel' ),
            'singular_name'      => __( 'Spin Wheel', 'wp-spin-wheel' ),
            'menu_name'          => __( 'Spin Wheel', 'wp-spin-wheel' ),
            'name_admin_bar'     => __( 'Spin Wheel', 'wp-spin-wheel' ),
            'add_new'            => __( 'Add New', 'wp-spin-wheel' ),
            'add_new_item'       => __( 'Add New Spin Wheel', 'wp-spin-wheel' ),
            'new_item'           => __( 'New Spin Wheel', 'wp-spin-wheel' ),
            'edit_item'          => __( 'Edit Spin Wheel', 'wp-spin-wheel' ),
            'view_item'          => __( 'View Spin Wheel', 'wp-spin-wheel' ),
            'all_items'          => __( 'All Spin Wheels', 'wp-spin-wheel' ),
            'search_items'       => __( 'Search Spin Wheels', 'wp-spin-wheel' ),
            'not_found'          => __( 'No Spin Wheels found.', 'wp-spin-wheel' ),
            'not_found_in_trash' => __( 'No Spin Wheels found in Trash.', 'wp-spin-wheel' ),
        );

        register_post_type( 'spin_wheel', array(
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_position'      => 25,
            'menu_icon'          => 'dashicons-chart-pie',
            'supports'           => array( 'title', 'editor', 'thumbnail' ),
            'has_archive'        => false,
            'rewrite'            => false,
            'capability_type'    => array( 'spin_wheel', 'spin_wheels' ),
            'map_meta_cap'       => true,
            'capabilities'       => array(
                'edit_post'          => 'edit_spin_wheel',
                'read_post'          => 'read_spin_wheel',
                'delete_post'        => 'delete_spin_wheel',
                'edit_posts'         => 'edit_spin_wheels',
                'edit_others_posts'  => 'edit_others_spin_wheels',
                'publish_posts'      => 'publish_spin_wheels',
                'read_private_posts' => 'read_private_spin_wheels',
                'create_posts'       => 'create_spin_wheels',
            ),
        ) );
    }
}
