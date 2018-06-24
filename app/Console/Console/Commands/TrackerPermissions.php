<?php

namespace App\Console\Commands;

use App\Models\{Channel, Permission};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Class TrackerPermissions
 * @package App\Console\Commands
 */
class TrackerPermissions extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracker:install:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install application permissions. Update existing ones if necessary';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Installing application permissions');
        $fileContent = Storage::disk('data')->get('permissions.json');
        $data = json_decode($fileContent, true);

        foreach ((array) $data as $values) {
            try {
                $model = Permission::where('name', $values['name'])->updateOrCreate($values);
            } catch (\Exception $ex) {
                $this->error("Failed installation of permission named '{$values['name']}'. Reason: {$ex->getMessage()}");
            }
        }

        $this->info('- application permissions installed');
    }
}
