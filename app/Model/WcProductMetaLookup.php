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
}
