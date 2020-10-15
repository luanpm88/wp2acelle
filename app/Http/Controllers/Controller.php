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
     * Connect to Acelle.
     *
     * @return \Illuminate\Http\Response
     */
    public function connect(Request $request)
    {
        header("Access-Control-Allow-Origin: *");

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

        if ($request->action == 'list') {
            return response()->json(\App\Model\WcProductMetaLookup::search($request));
        }

        return \App\Model\WcProductMetaLookup::select2($request);
    }
}
