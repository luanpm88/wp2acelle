<?php
/**
 * @wordpress-plugin
 * Plugin Name:       WP2Acelle Plugin
 * Plugin URI:        https://acellemail.com/
 * Description:       A plugin.
 * Version:           1.0
 * Author:            Acelle Team @ Basic Technology
 * Author URI:        https://acellemail.com/
 */

if (!defined('WORDPRESS_MODE')) {
    define('WORDPRESS_MODE', microtime(true));
}

// Get laravel app response
function wp2acelle_getResponse($path=null)
{
    if (!defined('LARAVEL_START')) {
        define('LARAVEL_START', microtime(true));
    }
    require __DIR__.'/vendor/autoload.php';
    $app = require_once __DIR__.'/bootstrap/app.php';
    $wp2acelle_kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    if (!$path) {
        $path = isset($_REQUEST['path']) ? $_REQUEST['path'] : '/';
    }
    $response = $wp2acelle_kernel->handle(
        App\Wordpress\LaravelRequest::capture($path)
    );

    return $response;
}

// Main admin menu
function wp2acelle_menu()
{
    // add menu page
    $menu = add_menu_page(esc_html__('WP2Acelle', 'wp2acelle'), esc_html__('WP2Acelle', 'wp2acelle'), 'edit_pages', 'wp-wp2acelle-main', function () {
    }, null, 54);
}
add_action('admin_menu', 'wp2acelle_menu');

// Default sub menu
function wp2acelle_menu_main()
{
    $hook = add_submenu_page('wp-wp2acelle-main', esc_html__('Dashboard', 'wp2acelle'), esc_html__('Dashboard', 'wp2acelle'), 'edit_pages', 'wp-wp2acelle-main', function () {
        $response = wp2acelle_getResponse();

        // send response
        $response->sendHeaders();
        $response->sendContent();
    });
}
add_action('admin_menu', 'wp2acelle_menu_main');
