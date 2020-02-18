<?php
namespace Tests;

use App\Services\Shopify\Shopify;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;

class ShopifyApiTest extends TestCase
{
    /** @test */
    public function test_api_gets_shop()
    {
        $shopResponse = file_get_contents(__DIR__ . '/responses/get_shop_response.json');

        $mock = new MockHandler([
            new Response(200, ['X-Shopify-Shop-Api-Call-Limit' => '1/40'], $shopResponse)
        ]);
        $handler = HandlerStack::create($mock);

        $shopifyApi = new Shopify([
            'shop' => 'myshopify.com',
            'api_key' => 'abcdefg',
            'handlerStack' => $handler
        ]);


        $response = $shopifyApi->getShop();
        $this->assertEquals("Apple Computers", $response->shop->name);
        $this->assertEquals("steve@apple.com", $response->shop->email);
    }


    /**
     * @test
     * @dataProvider getExceptionData
     */
    public function test_api_gets_4xx_defined_messages($statusCode, $message)
    {
        $mock = new MockHandler([
            new ClientException(
                "Oops. Error",
                new Request('GET', 'test'),
                new Response($statusCode, ['X-Shopify-Shop-Api-Call-Limit' => '1/40'])
            )
            ]);
        $handler = HandlerStack::create($mock);

        $shopifyApi = new Shopify([
            'shop' => 'myshopify.com',
            'token' => 'abcdefg',
            'handlerStack' => $handler
        ]);

        $response = $shopifyApi->getShop();
        $this->assertEquals($message, $response->message);
    }

    public function getExceptionData()
    {
        return [
            [422, 'Validation failed'],
            [406, 'Request not acceptable'],
            [404, 'Could not be found'],
            [400, 'Bad Request'],
            [401, 'Oops. Error']
        ];
    }

    /** @test */
    public function test_api_creates_a_new_product()
    {
        $productResponse = file_get_contents(__DIR__ . '/responses/create_new_product_response.json');

        $mock = new MockHandler([
            new Response(200, ['X-Shopify-Shop-Api-Call-Limit' => '1/40'], $productResponse)
        ]);
        $handler = HandlerStack::create($mock);

        $shopifyApi = new Shopify([
            'shop' => 'myshopify.com',
            'token' => 'abcdefg',
            'handlerStack' => $handler
        ]);


        $response = $shopifyApi->addProduct([
            'title' => 'Burton Custom Freestyle 151'
        ]);

        $this->assertEquals("Burton Custom Freestyle 151", $response->product->title);
        $this->assertEquals("Snowboard", $response->product->product_type);
    }
}
