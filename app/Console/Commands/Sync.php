<?php

namespace App\Console\Commands;

use Shopify;
use Dog as DogApi;
use Sync as ShopifySync;
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
    public function handle(ShopifySync $sync) {
        $sync->up();
    }
}
