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

namespace seagyn_davis\wpc;

define( 'WPC_VERSION', '0.1' );
define( 'WPC_PATH', dirname( __FILE__ ) );
define( 'WPC_URL', plugin_dir_url( __FILE__ ) );

require_once WPC_PATH . '/includes/admin.php';
require_once WPC_PATH . '/includes/helpers.php';

do_action( 'wpc_loaded' );
