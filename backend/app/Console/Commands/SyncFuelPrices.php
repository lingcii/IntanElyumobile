<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncFuelPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-fuel-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $signature_description = 'Sync real-time local fuel prices in La Union from oil price tracker API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fuel price synchronization is disabled (fuel costing has been removed).');
        return Command::SUCCESS;
    }
}
