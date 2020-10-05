<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WcProductMetaLookup extends Model
{
    protected $connection = 'mysql_wp';
    protected $table = 'wc_product_meta_lookup';

    /**
     * Get abandonded carts.
     *
     * @var collect
     */
    public static function getProductOptions()
    {
        $products = self::select('posts.*')
            ->join('posts', 'posts.id', '=', 'wc_product_meta_lookup.product_id')
            ->get()
            ->map(function ($item, $key) {
                return ['text' => $item->post_title, 'value' => $item->ID];
            })->toArray();

        return $products;
    }

    /**
     * Products select2.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public static function select2($request)
    {
        $page = $request->page;
        $perPage = 15;
        $results = self::select('posts.*')
            ->join('posts', 'posts.id', '=', 'wc_product_meta_lookup.product_id');

        if ($request->q) {
            $results = $results->where('post_title', 'like', '%'.$request->q.'%');
        }
        
        $results = $results->skip(($page-1) * $perPage)->take($perPage)
            ->get()->map(function ($item, $key) {                
                return ['text' => $item->post_title, 'id' => $item->ID];
            })->toArray();

        $json = '{
            "items": ' .json_encode($results). ',
            "more": ' . (empty($results) ? 'false' : 'true') . '
        }';
        return $json;
    }
}
