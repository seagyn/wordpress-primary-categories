<?php
/**
 * All admin related functions to handle primary categories.
 *
 * @package WPC
 */

namespace seagyn_davis\wpc\admin;

/**
 * Adding our script to the admin post add / edit pages.
 *
 * @param string $hook Which file is the enqueue do_action being called on.
 *
 * @return void
 */
function admin_enqueue_scripts( $hook ) {
	if ( ! in_array( $hook, [ 'edit.php', 'post.php' ], true ) ) {
		return;
	}

	global $post;

	\wp_enqueue_style(
		'wordpress-primary-categories',
		WPC_URL . 'css/wordpress-primary-categories.css',
		null,
		WPC_VERSION
	);

	\wp_enqueue_script(
		'wordpress-primary-categories',
		WPC_URL . 'js/wordpress-primary-categories.js',
		[ 'jquery' ],
		WPC_VERSION,
		true
	);

	$primary_category_id = \get_post_meta( $post->ID, '_primary_category_id', true );

	$localized_data = [
		'label'               => __( 'Make Primary', 'wordpress-primary-categories' ),
		'link_title'          => __( 'Set as the primary category.', 'wordpress-primary-categories' ),
		'nonce'               => \wp_create_nonce( 'wpc-nonce' ),
		'primary_category_id' => $primary_category_id,
	];
	\wp_localize_script(
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
	if ( ! \check_ajax_referer( 'wpc-nonce', 'nonce' ) ) {
		\wp_send_json_error(
			__( 'Invalid security token sent.', 'wpc-primary-categories' ),
			'403'
		); // Send Forbidden response.
		\wp_die();
	}

	if ( ! \current_user_can( 'edit_posts' ) ) {
		\wp_send_json_error( __( 'Unauthorised.', 'wpc-primary-categories' ), '401' ); // Send Unauthorized response.
		\wp_die();
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
		\wp_send_json_error(
			__( 'Missing parameters from request.', 'wpc-primary-categories' ),
			'422'
		); // Send Unprocessable Entity response.
		\wp_die();
	}

	/*
	 * Checking to see if the nonce was set and is correct. If you're sending an AJAX request, make sure you set the nonce in form data.
	 */
	if ( ! $category_id || ! $post_id ) {
		\wp_send_json_error(
			__( 'Invalid parameters passed in request.', 'wpc-primary-categories' ),
			'422'
		); // Send Unprocessable Entity response.
		\wp_die();
	}

	if ( \update_post_meta( $post_id, '_primary_category_id', $category_id, $old_category_id ) ) {
		\wp_send_json_success( __( 'Primary category updated.', 'wpc-primary-categories' ) );
	} else {
		\wp_send_json_error(
			__( 'Could not update primary category.', 'wpc-primary-categories' ),
			'500'
		); // Send Internal Server Error response.
	}

	\wp_die();
}
add_action( 'wp_ajax_set_primary_category', __NAMESPACE__ . '\set_primary_category' );

/**
 * Check to see if the current primary category is still selected.
 *
 * @param int      $post_ID ID of post being saved.
 * @param \WP_Post $post Post object of post being saved.
 * @param bool     $update If this was an update or create.
 *
 * @return void
 */
function check_primary_category( $post_ID, $post, $update ) {
	if ( $update && is_object_in_taxonomy( $post->post_type, 'category' ) ) {
		$primary_category_id = get_post_meta( $post_ID, '_primary_category_id', true );
		if ( $primary_category_id ) {
			$categories = wp_list_pluck( get_the_terms( $post, 'category' ), 'name', 'term_id' );

			if ( ! isset( $categories[ $primary_category_id ] ) ) {
				delete_post_meta( $post_ID, '_primary_category_id', $primary_category_id );
			}
		}
	}
}
add_action( 'save_post', __NAMESPACE__ . '\check_primary_category', 10, 3 );
