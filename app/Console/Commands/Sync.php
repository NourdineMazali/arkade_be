<?php

namespace App\Console\Commands;

use Shopify;
use Dog;
use Exception;
use Faker\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Sync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Doggies from dog.ceo/dog-api to Shopify shop';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle() {

        $shopify = new Shopify();
        $dogApi = new Dog();
        $faker = Factory::create();

        // Get all the Doggies
        $breeds = $dogApi->getAllBreeds();
        foreach ($breeds as $breed => $sub_breed) {
            try {
                $breed_data = $dogApi->getBreed($breed);
                //Create Breed as the main Product
                $response = $shopify->addProduct([
                    "title" => $breed,
                    "body_html" => "<strong>$breed</strong>",
                    "vendor" => "DogApi",
                    "product_type" => "Dog"
                ]);

                #Add product image
                $shopify->addProductImage($response->product->id,
                    ['image' => [
                        "position" => 1,
                        "attachment" =>  base64_encode(file_get_contents($breed_data['image'])),
                        "filename" => $breed_data['image']
                    ]]);

                #If no sub breeds found, skip variants
                if (empty($sub_breed)) continue;

                #Create sub breeds as the product variants
                foreach ($sub_breed as $k => $sub_b) {

                    $_sub_breed = $dogApi->getSubBreed($breed, $sub_b);

                    #Add a new product image
                    $image_response = $shopify->addProductImage($response->product->id,
                        [
                            'image' => ['src' => $_sub_breed['image']]
                        ]
                    );
                    $sub_breed_data = [
                        "option1"    => $_sub_breed['subBreed'],
                        "price"         => $faker->randomFloat(2,0,50000),
                        "sku"           => $faker->unique()->numberBetween($min = 1000, $max = 9000),
                        #attach variant image to the variant
                        "image_id"      => isset($image_response->image->id) ? $image_response->image->id : null
                    ];
                    $shopify->addVariant($response->product->id, $sub_breed_data);
                }
            }catch (Exception $e) {
                Log::info("Error Syncing Doggy {$breed} :" . $e->getMessage());
                continue;
            }
        }

    }
}
