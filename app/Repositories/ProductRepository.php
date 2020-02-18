<?php


namespace App\Repositories;


interface ProductRepository
{
    public function get();
    public function save($productData);
}
