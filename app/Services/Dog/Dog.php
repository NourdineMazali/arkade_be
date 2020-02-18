<?php


namespace App\Services\Dog;

use App\Services\Shopify\API;
use App\Services\Shopify\ShopifyException;
use Exception;
use Faker\Factory;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;
use \App\Services\Shopify\Shopify;
use Faker\Generator as Faker;

class Dog extends Facade
{
    /**
     * Client
     */
    protected $client = null;

    /** @var array $options */
    protected $options = array();


    /**
     * Retrieve Guzzle Client
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->init();
        }
        return $this->client;
    }
    /**
     * Initialise Guzzle Client
     *
     * @param $store
     * @param $token
     */
    public function __construct($store = null, $token = null)
    {
        $baseUrl = "https://dog.ceo/api/breed";

        $config = [
            'base_url' => $baseUrl,
            'headers' => [
                'Accept' => '*/*',
                'Content-Type' => 'application/json'
            ]
        ];

        $this->options = $config;
        $this->baseUrl = $baseUrl;
        $this->client = new Client($config);

    }

    /**
     * Request get method
     *
     * @param [type] $uri
     * @param array $data
     * @return array
     */
    public function getResponse($uri, $data = array()) {

        try{
            $response = $this->getClient()->get($this->options['base_url']. $uri);
            $response = json_decode($response->getBody(), true);
            return $response;

        } catch (Exception $exception){
            throw new ShopifyException();
        }

    }


    /**
     * @return array
     */
    public function getDog($breed = null)
    {
        $promise = $this->getClient()->getAsync($breed."/images")->then(
            function ($response) {
                return $response->getBody();
            }, function ($exception) {
            return $exception->getMessage();
        }
        );
        $response = $promise->wait();
        return $response;

    }

    /**
     * @param Request $request
     * @return array
     */
    public function getAllBreeds()
    {
        $response = $this->getResponse('s/list/all');

        return $this->formatBreeds($response);
    }

    /**
     * @param Request $request
     * @param string $breed
     * @return array
     */
    public function getBreed($breed)
    {
        $response = $this->getResponse( '/' . $breed . '/images/random');

        return [
            'image' =>  $response['message'],
            'breed' => $breed,
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getAllSubBreeds($breed)
    {
        $response = $this->getResponse('/list/all');

        return [
            $breed => $this->formatBreeds($response)
        ];
    }

    /**
     * @param Request $request
     * @param string $breed
     * @param string $subBreed
     * @return array
     */
    public function getSubBreed($breed, $subBreed)
    {
        $response = $this->getResponse( '/' . $breed . '/' . $subBreed . '/images/random');
        return [
            'image' => $response['message'],
            'breed' => $breed,
            'subBreed' => $subBreed,
        ];
    }

    /**
     * @param array $response
     * @param string $breed
     * @return array
     */
    private function getImages(array $response, $breed)
    {
        $images = [];

        if ($response['status'] == 'success') {
            foreach ($response['message'] as $image) {
                $images[] = $image;
            }
        }

        return $images;
    }

    /**
     * @param array $response
     * @return array
     */
    private function formatBreeds($response)
    {
        $dogs = [];

        if ($response['status'] == 'success') {
            foreach ($response['message'] as $breed => $subBreed) {
                $dogs[$breed] = [];
                foreach ($subBreed as $key => $value) {
                    $dogs[$breed][] = $value;
                }
            }
        }

        return $dogs;
    }

}
