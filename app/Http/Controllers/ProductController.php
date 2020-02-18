<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use App\Repositories\ShopifyProductRepository;

class ProductController extends Controller
{
    public function __construct(ShopifyProductRepository $products)
    {
        $this->products = $products;
    }

    public function index()
    {
        $products = $this->products->get();
        return view('products.index')->with(compact('products'));
    }

    public function create()
    {
        return;
    }

    public function store(ProductRequest $request)
    {
        return;
    }
}
