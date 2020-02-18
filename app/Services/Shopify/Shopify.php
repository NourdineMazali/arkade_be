<?php

namespace App\Services\Shopify;
/**
 * Class API
 * @package Shopify
 */

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;

class Shopify extends Facade
{
    protected $client;
    protected $base_uri = '';
    protected $shop;
    protected $api_key;
    protected $password;
    protected $access_token;
    protected $headers  = [];
    protected $calls_left = 1;
    protected $lastHttpCode;

    function __construct($options=[])
    {
        $this->shop = !isset($options['shop'])? env('SHOPIFY_STORE') :$options['shop'] ;

        $this->base_uri = $this->shop;

        $this->api_key = !isset($options['api_key'])? env('SHOPIFY_API_KEY') : $options['api_key'];

        $this->password = !isset($options['password'])? env('SHOPIFY_API_PASSWORD') : $options['password'];;

        if (isset($options['handlerStack'])) {
            $handlerStack = $options['handlerStack'];
        } else {
            $handlerStack = HandlerStack::create(new CurlHandler());
        }

        try {

            $this->client = new Client([
                'base_uri' => $this->getInstallURL(),
                'handler' => $handlerStack

            ]);
        } catch (Exception $e) {
            Log::notice('Curl error ' .  $e->getMessage());
        }

        $this->headers['Content-type'] = 'application/json';
        $this->headers['Accept']       = 'application/json';
    }


    public function call($options)
    {

        $url  = $this->getInstallURL() . $options['url'] . '.json';
        $body = null;

        $options['data'] = isset($options['data']) ? $options['data'] : [];

        if (strtoupper($options['method']) === 'GET') {
            $url = $url . '?' . http_build_query($options['data']);
            try {
                $response = $this->client->get($url, ['headers' => $this->headers]);
                $this->lastHttpCode = $response->getStatusCode();
            } catch (ClientException $e) {
                $request            = $e->getRequest();
                $response           =  $e->getResponse();
                $this->lastHttpCode = $response->getStatusCode();

                $errors = json_decode($response->getBody()->getContents());

                $temp = new \stdClass();
                $temp->statusCode = $response->getStatusCode();
                $temp->errors = isset($errors->errors) ? $errors->errors : [];
                $temp->message = $e->getMessage();

                switch ($response->getStatusCode()) {
                    case 422:
                        $temp->message = 'Validation failed';
                        break;
                    case 406:
                        $temp->message = 'Request not acceptable';
                        break;
                    case 404:
                        $temp->message = 'Could not be found';
                        break;
                    case 400:
                        $temp->message = 'Bad Request';
                        break;
                }
                return $temp;
            }

            $data = (string)$response->getBody()->getContents();
            $parsed_response = json_decode($data);
            return $parsed_response;
        } else {
            $body = json_encode($options['data']);
        }

        $request = new Request($options['method'], $url, $this->headers, $body);

        try {
            $response = $this->client->send($request);
            $this->lastHttpCode = $response->getStatusCode();
        } catch (ClientException $e) {
            $request            = $e->getRequest();

            $response           =  $e->getResponse();
            $this->lastHttpCode = $response->getStatusCode();


            $errors = json_decode($response->getBody()->getContents());

            $temp = new \stdClass();
            $temp->statusCode = $response->getStatusCode();
            $temp->errors = isset($errors->errors) ? $errors->errors : [];

            switch ($response->getStatusCode()) {
                case 422:
                    $temp->message = 'Validation failed';
                    break;
                case 406:
                    $temp->message = 'Request not acceptable';
                    break;
            }
            return $temp;
        }

        $data = (string)$response->getBody()->getContents();
        $parsed_response = json_decode($data);
        return $parsed_response;
    }


    /**
     * @return int $lastHttpCode
     */
    public function lastHttpCode()
    {
        return $this->lastHttpCode;
    }


    /**
     * Returns a string of the install URL for the app
     * @param array $data
     * @return string
     */
    public function getInstallURL($data = array())
    {
        return 'https://' .  $this->api_key . ':'. $this->password.'@' . $this->shop .'/admin/api/2020-01/';
    }

    /**
     * Returns an object with the Shopify shop's data
     * @return object
     */
    public function getShop()
    {
        return $this->call([
            'url'    => 'shop',
            'method' => 'GET'
        ]);
    }

    // Products

    /**
     * Retrieve all Products
     *
     * @param [type] $id
     * @return string
     */
    public function getAllProducts($id = null)
    {
        $options = [
            'url' => "products",
            'method' => 'GET',
        ];
        return $this->call($options);
    }

    /**
     * Returns an object with the Shopify product data
     * @param array $product
     * @return object
     */
    public function addProduct($product)
    {
        $options = [
            'url' => "products",
            'method' => 'POST',
            'data' =>  [
                'product' => $product
            ]
        ];
        return $this->call($options);
    }

    /**
     * Returns an object with the Shopify product data
     * @param $product_id
     * @param $fields // leave empty for all fields
     * @return object
     */
    public function getProduct($product_id, $fields = [])
    {
        $options = [
            'url'    => "products/{$product_id}",
            'method' => 'GET'
        ];
        if (!empty($fields)) {
            $options['data']['fields'] = implode(',', $fields);
        }

        return $this->call($options);
    }
    /**
     * Returns an object with the Shopify product data
     * @param array $product_ids - array of products to return from Shopify
     * @param array $fields // leave empty for all fields
     * @return object
     */
    public function getProducts($product_ids, $fields = [])
    {
        $options = [
            'url'    => "products",
            'method' => 'GET',
            'data'   => [
                'ids' => implode(',', $product_ids)
            ]
        ];
        if (!empty($fields)) {
            $options['data']['fields'] = implode(',', $fields);
        }

        return $this->call($options);
    }

    /**
     * Returns an object with the updated Shopify product
     * @param integer $id - id of product to be updated
     * @param array $product // leave empty for all fields
     * @return object
     */

    public function updateProduct($id, $product)
    {
        $options = [
            'url'    => "products/{$id}",
            'method' => 'PUT',
            'data'   => [
                'product' => $product
            ]
        ];

        return $this->call($options);
    }

    // Images

    /**
     * Returns an object with the Shopify product image data
     * @param int $id
     * @param array $image
     * @return object
     */
    public function addProductImage($id, $image) {
        $options = [
            'url' => "products/$id/images",
            'method' => 'POST',
            'data' => $image
        ];
        return $this->call($options);
    }


    // Variants

    /**
     * Returns an object with the Shopify product's variant data
     * @param $product_id
     * @param $variant
     * @param $fields // leave empty for all fields
     * @return object
     */
    public function addVariant($product_id, $variant = [])
    {
        $options = [
            'url'    => "products/{$product_id}/variants",
            'method' => 'POST',
            'data' =>  [
                'variant' => $variant
            ]
        ];

        return $this->call($options);
    }

    /**
     * Returns an object with the Shopify product's variant data
     * @param $product_id
     * @param $fields // leave empty for all fields
     * @return object
     */
    public function getVariants($product_id, $fields = [])
    {
        $options = [
            'url'    => "products/{$product_id}/variants",
            'method' => 'GET'
        ];
        if (!empty($fields)) {
            $options['data']['fields'] = implode(',', $fields);
        }

        return $this->call($options);
    }

}
