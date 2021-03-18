<?php
/**
 * @wordpress-plugin
 * Plugin Name:       WordPress Plugin for Acelle Plugin
 * Plugin URI:        https://acellemail.com/
 * Description:       A plugin.
 * Version:           1.0
 * Author:            Acelle Team @ Basic Technology
 * Author URI:        https://acellemail.com/
 */

// Get laravel app response
function acellesync_getResponse($path=null)
{
    if (!defined('LARAVEL_START')) {
        define('LARAVEL_START', microtime(true));
    }
    require __DIR__.'/vendor/autoload.php';
    $app = require_once __DIR__.'/bootstrap/app.php';
    $acellesync_kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    if (!$path) {
        $path = isset($_REQUEST['path']) ? $_REQUEST['path'] : '/';
    }
    $response = $acellesync_kernel->handle(
        App\Wordpress\LaravelRequest::capture($path)
    );

    return $response;
}

function acellesync_activate() {
    acellesync_getResponse('/');

    // actually call the Artisan command
    \Artisan::call('config:cache');
}
  
register_activation_hook( __FILE__, 'acellesync_activate' );

// Main admin menu
function acellesync_menu()
{
    // add menu page
    $menu = add_menu_page(esc_html__('Acelle Connect', 'acellesync'), esc_html__('Acelle Connect', 'acellesync'), 'edit_pages', 'wp-acellesync-main', function () {
    }, null, 54);
}
add_action('admin_menu', 'acellesync_menu');

// Default sub menu
function acellesync_menu_main()
{
    $hook = add_submenu_page('wp-acellesync-main', esc_html__('Dashboard', 'acellesync'), esc_html__('Dashboard', 'acellesync'), 'edit_pages', 'wp-acellesync-main', function () {
        $response = acellesync_getResponse('/acelle-connect');

        // send response
        $response->sendHeaders();
        $response->sendContent();
    });
}
add_action('admin_menu', 'acellesync_menu_main');

// Ajax page
function acellesync_ajax()
{
    $response = acellesync_getResponse($path);

    // Comment line below, do not send response
    $response->send();

    // Do not use wp_die() here, it will produce WP default layout, use die() instead;
    die();
}
add_action('wp_ajax_acellesync_ajax', 'acellesync_ajax');

// Helpers
/**
 * WP action helper for laravel.
 */

function acellesync_public_url($path)
{
    return plugins_url('acellesync/public/' . $path);
}

/**
 * WP action helper for laravel.
 */
function acellesync_wp_action($name, $parameters = [], $absolute = true)
{
    $base = url('/');
    $full = app('url')->action($name, $parameters, $absolute);
    $path = str_replace($base, '', $full);

    return admin_url('admin.php?page=wp-acellesync-main&path=' . str_replace('?', '&', $path));
}

/**
 * WP action helper for laravel.
 */
function acellesync_lr_action($name, $parameters = [], $absolute = true)
{
    $base = url('/');
    $full = app('url')->action($name, $parameters, $absolute);
    $path = str_replace($base, '', $full);
    return admin_url('admin-ajax.php?action=acellesync_ajax&path=' . str_replace('?', '&', $path));
}

/**
 * WP url helper for laravel.
 */
function acellesync_wp_url($path = null, $parameters = [], $secure = null)
{
    if (is_null($path)) {
        $path = app(\Illuminate\Routing\UrlGenerator::class);
    }

    $base = url('/');
    $full = app(\Illuminate\Routing\UrlGenerator::class)->to($path, $parameters, $secure);
    $path = str_replace($base, '', $full);

    return admin_url('admin.php?page=wp-acellesync-main&path=' . str_replace('?', '&', $path));
}

/**
 * WP url helper for laravel.
 */
function acellesync_lr_url($path = null, $parameters = [], $secure = null)
{
    if (is_null($path)) {
        $path = app(\Illuminate\Routing\UrlGenerator::class);
    }

    $base = url('/');
    $full = app(\Illuminate\Routing\UrlGenerator::class)->to($path, $parameters, $secure);
    $path = str_replace($base, '', $full);

    return admin_url('admin-ajax.php?action=acellesync_ajax&path=' . str_replace('?', '&', $path));
}

// WordPress rest api connect
function acellesync_connect( $data ) {
    $response = acellesync_getResponse('/connect');
    // Comment line below, do not send response
    $response->send();

    die();
}
add_action( 'rest_api_init', function () {
    register_rest_route( '/acelle', '/connect', array(
        'methods' => 'GET',
        'callback' => 'acellesync_connect',
    ));
});

// add beemail css to WordPress admin area
function acellesync_add_theme_scripts()
{
    wp_enqueue_style('acellesync', plugin_dir_url(__FILE__) . 'public/css/wp-admin.css');
}
add_action('admin_enqueue_scripts', 'acellesync_add_theme_scripts');
