<?php


namespace App\Services\Sync;


use App\Services\Dog\Dog;
use App\Services\Shopify\Shopify;
use Exception;
use Faker\Factory;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;

/**
 * Class Sync
 * @package App\Services\Sync
 */
class Sync extends Facade
{

    private $shopify;
    private $dogApi;
    private $faker;

    /**
     * Sync constructor.
     * @param Shopify $shopify
     * @param Dog $dogApi
     */
    public function __construct(Shopify $shopify, Dog $dogApi)
    {
        $this->dogApi = $dogApi;
        $this->shopify = $shopify;
        $this->faker = Factory::create();

    }

    /**
     * Syncing the dog breeds from Dog Api to our Shopify store
     * @return void
     */
    public function up() {

        // Get all the Doggies
        $breeds = $this->dogApi->getAllBreeds();
        foreach ($breeds as $breed => $sub_breed) {
            try {
                $breed_data = $this->dogApi->getBreed($breed);
                //Create Breed as the main Product
                $response = $this->shopify->addProduct([
                    "title" => $breed,
                    "body_html" => "<strong>$breed</strong>",
                    "vendor" => "DogApi",
                    "product_type" => "Dog"
                ]);

                #Add product image
                $this->shopify->addProductImage($response->product->id,
                    ['image' => [
                        "position" => 1,
                        "attachment" =>  base64_encode(file_get_contents($breed_data['image'])),
                        "filename" => $breed_data['image']
                    ]]);

                #If no sub breeds found, skip variants
                if (empty($sub_breed)) continue;

                #Create sub breeds as the product variants
                foreach ($sub_breed as $k => $sub_b) {

                    $_sub_breed = $this->dogApi->getSubBreed($breed, $sub_b);

                    #Add a new product image
                    $image_response = $this->shopify->addProductImage($response->product->id,
                        [
                            'image' => ['src' => $_sub_breed['image']]
                        ]
                    );
                    $sub_breed_data = [
                        "option1"    => $_sub_breed['subBreed'],
                        "price"         => $this->faker->randomFloat(2,0,50000),
                        "sku"           => $this->faker->unique()->numberBetween($min = 1000, $max = 9000),
                        #attach variant image to the variant
                        "image_id"      => isset($image_response->image->id) ? $image_response->image->id : null
                    ];
                    $this->shopify->addVariant($response->product->id, $sub_breed_data);
                }
            }catch (Exception $e) {
                Log::info("Error Syncing Doggy {$breed} :" . $e->getMessage());
                continue;
            }
        }
    }

}
