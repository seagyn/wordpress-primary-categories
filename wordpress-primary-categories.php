<?php
/**
 * Plugin Name:  WordPress Primary Categories
 * Description:  Allows the user to select a primary category for a post.
 * Version:      0.1
 * Author:       Seagyn Davis
 * Author URI:   https://www.seagyndavis.com/
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  wordpress-primary-categories
 * Requires PHP: 5.6
 *
 * @package WPC
 */

namespace SeagynDavis\WPC;

define( 'WPC_VERSION', '0.1' );

/**
 * Adding our script to the admin post add / edit pages.
 *
 * @param string $hook The current admin page.
 */
function admin_enqueue_scripts( $hook ) {
	if ( ! in_array( $hook, [ 'edit.php', 'post.php' ], true ) ) {
		return;
	}

	global $post;

	wp_enqueue_style(
		'wordpress-primary-categories',
		plugins_url( '/css/wordpress-primary-categories.css', __FILE__ ),
		null,
		WPC_VERSION
	);

	wp_enqueue_script(
		'wordpress-primary-categories',
		plugins_url( '/js/wordpress-primary-categories.js', __FILE__ ),
		[ 'jquery' ],
		WPC_VERSION,
		true
	);

	$primary_category_id = get_post_meta( $post->ID, '_primary_category_id', true );

	$localized_data = [
		'label'               => __( 'Make Primary', 'wordpress-primary-categories' ),
		'link_title'          => __( 'Set as the primary category.', 'wordpress-primary-categories' ),
		'nonce'               => wp_create_nonce( 'wpc-nonce' ),
		'primary_category_id' => $primary_category_id,
	];
	wp_localize_script(
		'wordpress-primary-categories',
		'wpc_data',
		$localized_data
	);
}

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\admin_enqueue_scripts' );

/**
 * Handles the admin ajax request.
 *
 * @return void
 */
function set_primary_category() {
	/*
	 * Checking to see if the nonce was set and is correct. If you're sending an AJAX request, make sure you set the nonce in form data.
	 */
	if ( ! check_ajax_referer( 'wpc-nonce', 'nonce' ) ) {
		wp_send_json_error(
			__( 'Invalid security token sent.', 'wpc-primary-categories' ),
			'403'
		); // Send Forbidden response.
		wp_die();
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( __( 'Unauthorised.', 'wpc-primary-categories' ), '401' ); // Send Unauthorized response.
		wp_die();
	}

	$category_id     = null;
	$post_id         = null;
	$old_category_id = null;

	/*
	 * Checking to see if the nonce was set and is correct. If you're sending an AJAX request, make sure you set the nonce in form data.
	 */
	if ( isset( $_POST['category_id'] ) && isset( $_POST['post_id'] ) && isset( $_POST['old_category_id'] ) ) {
		$category_id     = intval( $_POST['category_id'] );
		$post_id         = intval( $_POST['post_id'] );
		$old_category_id = intval( $_POST['old_category_id'] );
	} else {
		wp_send_json_error(
			__( 'Missing parameters from request.', 'wpc-primary-categories' ),
			'422'
		); // Send Unprocessable Entity response.
		wp_die();
	}

	/*
	 * Checking to see if the nonce was set and is correct. If you're sending an AJAX request, make sure you set the nonce in form data.
	 */
	if ( ! $category_id || ! $post_id ) {
		wp_send_json_error(
			__( 'Invalid parameters passed in request.', 'wpc-primary-categories' ),
			'422'
		); // Send Unprocessable Entity response.
		wp_die();
	}

	if ( update_post_meta( $post_id, '_primary_category_id', $category_id, $old_category_id ) ) {
		wp_send_json_success( __( 'Primary category updated.', 'wpc-primary-categories' ) );
	} else {
		wp_send_json_error(
			__( 'Could not update primary category.', 'wpc-primary-categories' ),
			'500'
		); // Send Internal Server Error response.
	}

	wp_die();
}

add_action( 'wp_ajax_set_primary_category', __NAMESPACE__ . '\set_primary_category' );

/**
 * Get posts types which use the built-in categories taxonomy for a certain primary category.
 *
 * @param int   $category_id The category ID you are wanting to get posts for.
 * @param mixed $post_type The post type or types you want to search for. Use an array for multiple post types.
 *
 * @return \WP_Query
 */
function get_posts_from_primary_category( $category_id, $post_type = 'post' ) {
	$args = [
		'post_type'      => $post_type,
		'meta_key'       => '_primary_category_id',
		'meta_value_num' => $category_id,
	];

	return new \WP_Query( $args );
}
