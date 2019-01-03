<?php
/*
Plugin Name:  WordPress Primary Categories
Description:  Allows the user to select a primary category for a post.
Version:      0.1
Author:       Seagyn Davis
Author URI:   https://www.seagyndavis.com/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wordpress-primary-categories
Requires PHP: 5.4
*/

namespace SeagynDavis\WPC;

define( 'WPC_VERSION', '0.1' );

/**
 * We are adding our script to the admin post add / edit pages.
 *
 * @param string $hook The current admin page.
 */
function admin_enqueue_scripts( $hook ) {
	if ( 'edit.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

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
	wp_localize_script(
		'wordpress-primary-categories',
		'wpc_data',
		[
			'label' => __( 'Make Primary', 'wordpress-primary-categories' ),
		]
	);
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\admin_enqueue_scripts' );

