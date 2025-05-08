<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class CreateRepositoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for creating necessary file based on repository pattern';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rawName = $this->argument('name');
        $name = Str::studly($rawName);
        $repositoriesRoot = app_path('Repositories');

        // 1. Create Repositories folder if there's none
        if (!File::exists($repositoriesRoot)) {
            File::makeDirectory($repositoriesRoot, 0755, true);
            $this->info('app/Repositories created');
        }

        // 2. Create sub-folder
        $targetPath = $repositoriesRoot . '/' . $name;
        if (!File::exists($targetPath)) {
            File::makeDirectory($targetPath, 0755, true);
            $this->info("app/Repositories/$name created");
        }

        // 3. Prepare the file
        $interfaceName = $name . 'RepositoryInterface';
        $repositoryName = $name . 'Repository';
        $interfacePath = $targetPath . '/' . $interfaceName . '.php';
        $repositoryPath = $targetPath . '/' . $repositoryName . '.php';

        // 4. Create Interface
        if (!File::exists($interfacePath)) {
            $interfaceContent = "<?php\n\nnamespace App\Repositories\\$name;\n\ninterface $interfaceName\n{\n    // \n}\n";
            File::put($interfacePath, $interfaceContent);
            $this->info("$interfaceName.php created");
        } else {
            $this->warn("$interfaceName.php is existed, skipping");
        }

        // 5. Create repository
        if (!File::exists($repositoryPath)) {
            $repositoryContent = "<?php\n\nnamespace App\Repositories\\$name;\n\nclass $repositoryName implements $interfaceName\n{\n    // \n}\n";
            File::put($repositoryPath, $repositoryContent);
            $this->info("$repositoryName.php created");
        } else {
            $this->warn("$repositoryName.php is existed, skipping");
        }

        // 6. Automatic binding 2 files
        $providerPath = app_path('Providers/RepositoryServiceProvider.php');
        if (!File::exists($providerPath)) {
            $this->warn('RepositoryServiceProvider is not found. Creating...');
            $this->call('make:provider', ['name' => 'RepositoryServiceProvider']);
        }

        $interfaceFull = "App\\Repositories\\$name\\$interfaceName";
        $repositoryFull = "App\\Repositories\\$name\\$repositoryName";
        $bindCode = "        \$this->app->bind(\\$interfaceFull::class, \\$repositoryFull::class);";

        // Read provider content
        $providerContent = File::get($providerPath);

        // Checking namespace
        if (!Str::contains($providerContent, 'use Illuminate\Support\ServiceProvider')) {
            $this->error('RepositoryServiceProvider must extend from Illuminate\Support\ServiceProvider');
            return 1;
        }

        // Add register() method if thers's none
        if (!Str::contains($providerContent, 'public function register')) {
            $providerContent = preg_replace(
                '/class RepositoryServiceProvider extends ServiceProvider\s*\{/',
                "class RepositoryServiceProvider extends ServiceProvider\n{\n    /**\n     * Register services.\n     */\n    public function register(): void\n    {\n    }\n",
                $providerContent
            );
        }

        // Checking if binding's existed
        if (!Str::contains($providerContent, $bindCode)) {
            // Inserting bind
            $pattern = '/(public function register\(\)(?:\s*:\s*void)?\s*\{)([\s\S]*?)(\n\s*\})/';
            $replacement = '$1$2' . "\n$bindCode$3";
            $providerContent = preg_replace($pattern, $replacement, $providerContent);
            
            if (File::put($providerPath, $providerContent)) {
                $this->info('Repository binded.');
            } else {
                $this->error('RepositoryServiceProvider can not be written.');
                return 1;
            }
        } else {
            $this->warn('Binding exists, skipping...');
        }

        $this->info('Finish🎉');
        return 0;
    }
}
