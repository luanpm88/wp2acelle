<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Check if WooCommerce is active
     **/
    public function is_woocommerce_installed() {
        return in_array( 
            'woocommerce/woocommerce.php', 
            apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) 
        );
    }

    /**
     * Connect to Acelle.
     *
     * @return \Illuminate\Http\Response
     */
    public function connect(Request $request)
    {
        header("Access-Control-Allow-Origin: *");

        if (!$this->is_woocommerce_installed()) {
            return response('WooCommerce is not available in the target WordPress instance', 404)
                ->header('Content-Type', 'text/plain');
        }

        if ($request->product_id) {
            $post = \App\Model\Post::find($request->product_id);

            $product   = wc_get_product( $post->ID );
            $image_id  = $product->get_image_id();
            $image_url = wp_get_attachment_image_url( $image_id, 'full' );

            return response()->json([
                'id' => $post->ID,
                'name' => $post->post_title,
                'price' => wc_price($product->get_price()),
                'image' => $image_url,
                'description' => substr(strip_tags($post->post_content), 0, 100),
                'link' => get_permalink( $post->ID ),
            ]);
        }

        elseif ($request->action == 'shop_info') {
            $custom_logo_id = get_theme_mod( 'custom_logo' );
            $image = wp_get_attachment_image_src( $custom_logo_id , 'full' );

            return response()->json([
                'name' => get_bloginfo('name'),
                'url' => get_site_url(),
                'logo' => isset($image[0]) ? $image[0] : '',
                'products_count' => wp_count_posts( 'product' )->publish,
                'orders_count' => wc_orders_count('wc-completed'),
                'total_sales' => 0,
            ]);
        }

        if ($request->action == 'list') {
            return response()->json(\App\Model\WcProductMetaLookup::search($request));
        }

        return \App\Model\WcProductMetaLookup::select2($request);
    }
}
