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
        $response = wp2acelle_getResponse('/acelle-connect');

        // send response
        $response->sendHeaders();
        $response->sendContent();
    });
}
add_action('admin_menu', 'wp2acelle_menu_main');

// Ajax page
function wp2acelle_ajax()
{
    $response = wp2acelle_getResponse($path);

    // Comment line below, do not send response
    $response->send();

    // Suppress the 0 character rendered in admin-ajax.php::wp_die(0)
    wp_die();
}
add_action('wp_ajax_wp2acelle_ajax', 'wp2acelle_ajax');

// Helpers
/**
 * WP action helper for laravel.
 */

function wp2acelle_public_url($path)
{
    return plugins_url('wp2acelle/public/' . $path);
}

/**
 * WP action helper for laravel.
 */
function wp2acelle_wp_action($name, $parameters = [], $absolute = true)
{
    $base = url('/');
    $full = app('url')->action($name, $parameters, $absolute);
    $path = str_replace($base, '', $full);

    return admin_url('admin.php?page=wp-wp2acelle-main&path=' . str_replace('?', '&', $path));
}

/**
 * WP action helper for laravel.
 */
function wp2acelle_lr_action($name, $parameters = [], $absolute = true)
{
    $base = url('/');
    $full = app('url')->action($name, $parameters, $absolute);
    $path = str_replace($base, '', $full);
    return admin_url('admin-ajax.php?action=wp2acelle_ajax&path=' . str_replace('?', '&', $path));
}

/**
 * WP url helper for laravel.
 */
function wp2acelle_wp_url($path = null, $parameters = [], $secure = null)
{
    if (is_null($path)) {
        $path = app(\Illuminate\Routing\UrlGenerator::class);
    }

    $base = url('/');
    $full = app(\Illuminate\Routing\UrlGenerator::class)->to($path, $parameters, $secure);
    $path = str_replace($base, '', $full);

    return admin_url('admin.php?page=wp-wp2acelle-main&path=' . str_replace('?', '&', $path));
}

/**
 * WP url helper for laravel.
 */
function wp2acelle_lr_url($path = null, $parameters = [], $secure = null)
{
    if (is_null($path)) {
        $path = app(\Illuminate\Routing\UrlGenerator::class);
    }

    $base = url('/');
    $full = app(\Illuminate\Routing\UrlGenerator::class)->to($path, $parameters, $secure);
    $path = str_replace($base, '', $full);

    return admin_url('admin-ajax.php?action=wp2acelle_ajax&path=' . str_replace('?', '&', $path));
}

// WordPress rest api connect
function wp2acelle_connect( $data ) {
    $response = wp2acelle_getResponse('/connect');
    // Comment line below, do not send response
    $response->send();
}
add_action( 'rest_api_init', function () {
    register_rest_route( '/acelle', '/connect', array(
        'methods' => 'GET',
        'callback' => 'wp2acelle_connect',
    ));
});