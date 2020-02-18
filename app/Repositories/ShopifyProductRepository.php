<?php


namespace App\Repositories;

use App\Repositories\ProductRepository;
use Shopify;

class ShopifyProductRepository  implements ProductRepository
{
    public function get()
    {
        $shopifyApi = new Shopify();
        $products = $shopifyApi->getAllProducts()->products;
        return $products;
    }

    public function save($product)
    {
        $product['images'] = [
            ['src' => asset('storage/'.  $product['images'])]
        ];
        $product['variants'] = [
            [
                'option1' => 'Default',
                'price' => $product['price'],
                'sku' => $product['sku'],
            ]
        ];
        $product['body_html'] = "<p>{$product['description']}</p>";
        $product = Shopify::addProduct($product);
        return $product;
    }
}
