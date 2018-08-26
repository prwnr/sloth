<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{Permission, Role, Currency};
use Illuminate\Support\Facades\Storage;

/**
 * Class InstallationCommand
 * @package App\Console\Commands
 */
class InstallationCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sloth:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs application permissions (updates existing roles) and currencies';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->installPermissions();
        $this->installCurrencies();
    }

    /**
     * Install permissions and update existing roles
     */
    private function installPermissions(): void
    {
        $this->info('Installing application permissions');

        $fileContent = Storage::disk('data')->get('permissions.json');
        $permissions = json_decode($fileContent, true);

        foreach ((array)$permissions as $perm) {
            try {
                $model = Permission::where('name', $perm['name'])->first();
                if ($model) {
                    $model->update($perm);
                    continue;
                }

                $model = Permission::create($perm);
                $this->assignNewPermission($model);
            } catch (\Exception $ex) {
                $this->error("Failed installation of permission named '{$perm['name']}'. Reason: {$ex->getMessage()}");
            }
        }

        $this->info('- application permissions installed');
    }

    /**
     * Install or update currencies
     */
    private function installCurrencies(): void
    {
        $this->info('Installing application currencies');
        $fileContent = Storage::disk('data')->get('currencies.json');
        $currencies = json_decode($fileContent, true);

        foreach ((array)$currencies as $currency) {
            try {
                Currency::where('name', $currency['name'])->updateOrCreate($currency);
            } catch (\Exception $ex) {
                $this->error("Failed installation of currency named '{$currency['name']}'. Reason: {$ex->getMessage()}");
            }
        }

        $this->info('- application currencies installed');
    }

    /**
     * Assign new permission to existing roles if necessary
     * @param Permission $perm
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function assignNewPermission(Permission $perm): void
    {
        $fileContent = Storage::disk('data')->get('roles.json');
        $roles = json_decode($fileContent, true);

        $rolesToUpdate = [];
        foreach ($roles as $role) {
            if ($role['permissions'] === 'all') {
                $rolesToUpdate[] = $role['name'];
                continue;
            }

            if (\in_array($perm->name, $role['permissions'], true)) {
                $rolesToUpdate[] = $role['name'];
            }
        }

        if (empty($rolesToUpdate)) {
            return;
        }

        $this->info("Roles that will be updated with {$perm->name} permission: " . implode(', ', $rolesToUpdate));

        foreach ($rolesToUpdate as $roleName) {
            $roles = Role::where('name', $roleName)->get();
            /** @var Role $role */
            foreach ($roles as $role) {
                $role->attachPermission($perm);
            }
        }
    }
}