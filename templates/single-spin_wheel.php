<?php
/**
 * Single template for Spin Wheel post type (No Header / No Footer - Flatsome style)
 *
 * @package WP_Spin_Wheel
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$main_classes = function_exists( 'flatsome_main_classes' ) ? flatsome_main_classes() : 'content-area';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="profile" href="http://gmpg.org/xfn/11" />
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

    <?php wp_head(); ?>
</head>

<body <?php body_class( 'spin-wheel-standalone-page' ); ?>>

<?php
if ( function_exists( 'wp_body_open' ) ) {
    wp_body_open();
}
do_action( 'flatsome_after_body_open' );
do_action( 'flatsome_before_page' );
do_action( 'flatsome_after_header' );
?>

<div id="wrapper">
    <div id="main" class="<?php echo esc_attr( $main_classes ); ?>">
        <?php
        while ( have_posts() ) :
            the_post();
            $wheel_id = get_the_ID();
            include WP_SPIN_WHEEL_PATH . 'templates/wheel-default.php';
        endwhile;
        ?>
    </div>
</div>

<?php
do_action( 'flatsome_after_page' );
wp_footer();
?>
</body>
</html>

