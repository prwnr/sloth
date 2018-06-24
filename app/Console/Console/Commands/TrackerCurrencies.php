<?php

namespace App\Console\Commands;

use App\Models\Currency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Class TrackerCurrencies
 * @package App\Console\Commands
 */
class TrackerCurrencies extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracker:install:currencies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install application currencies. Update existing ones if necessary';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Installing application currencies');
        $fileContent = Storage::disk('data')->get('currencies.json');
        $data = json_decode($fileContent, true);

        foreach ((array) $data as $values) {
            try {
                $model = Currency::where('name', $values['name'])->updateOrCreate($values);
            } catch (\Exception $ex) {
                $this->error("Failed installation of currency named '{$values['name']}'. Reason: {$ex->getMessage()}");
            }
        }

        $this->info('- application currencies installed');
    }
}
