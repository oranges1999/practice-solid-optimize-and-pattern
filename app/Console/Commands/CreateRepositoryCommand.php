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
        $name = Str::studly($rawName); // Ví dụ user → User
        $repositoriesRoot = app_path('Repositories');

        // 1. Tạo folder Repositories nếu chưa có
        if (!File::exists($repositoriesRoot)) {
            File::makeDirectory($repositoriesRoot, 0755, true);
            $this->info('Đã tạo folder: app/Repositories');
        }

        // 2. Tạo folder con
        $targetPath = $repositoriesRoot . '/' . $name;
        if (!File::exists($targetPath)) {
            File::makeDirectory($targetPath, 0755, true);
            $this->info("Đã tạo folder: app/Repositories/$name");
        }

        // 3. Chuẩn bị file
        $interfaceName = $name . 'RepositoryInterface';
        $repositoryName = $name . 'Repository';
        $interfacePath = $targetPath . '/' . $interfaceName . '.php';
        $repositoryPath = $targetPath . '/' . $repositoryName . '.php';

        // 4. Tạo file Interface nếu chưa có
        if (!File::exists($interfacePath)) {
            $interfaceContent = "<?php\n\nnamespace App\Repositories\\$name;\n\ninterface $interfaceName\n{\n    // \n}\n";
            File::put($interfacePath, $interfaceContent);
            $this->info("Đã tạo file Interface: $interfaceName.php");
        } else {
            $this->warn("$interfaceName.php đã tồn tại, bỏ qua.");
        }

        // 5. Tạo file Repository nếu chưa có
        if (!File::exists($repositoryPath)) {
            $repositoryContent = "<?php\n\nnamespace App\Repositories\\$name;\n\nclass $repositoryName implements $interfaceName\n{\n    // \n}\n";
            File::put($repositoryPath, $repositoryContent);
            $this->info("Đã tạo file Repository: $repositoryName.php");
        } else {
            $this->warn("$repositoryName.php đã tồn tại, bỏ qua.");
        }

        // 6. Tự động bind vào RepositoryServiceProvider 
        $providerPath = app_path('Providers/RepositoryServiceProvider.php');
        if (!File::exists($providerPath)) {
            $this->warn('Không tìm thấy RepositoryServiceProvider. Đang tạo...');
            $this->call('make:provider', ['name' => 'RepositoryServiceProvider']);
        }

        $interfaceFull = "App\\Repositories\\$name\\$interfaceName";
        $repositoryFull = "App\\Repositories\\$name\\$repositoryName";
        $bindCode = "        \$this->app->bind(\\$interfaceFull::class, \\$repositoryFull::class);";

        // Đọc nội dung provider
        $providerContent = File::get($providerPath);

        // Kiểm tra sự tồn tại của namespace Illuminate\Support\ServiceProvider
        if (!Str::contains($providerContent, 'use Illuminate\Support\ServiceProvider')) {
            $this->error('RepositoryServiceProvider phải extend từ Illuminate\Support\ServiceProvider');
            return 1;
        }

        // Nếu chưa có phương thức register(), thêm nguyên phương thức vào class
        if (!Str::contains($providerContent, 'public function register')) {
            $providerContent = preg_replace(
                '/class RepositoryServiceProvider extends ServiceProvider\s*\{/',
                "class RepositoryServiceProvider extends ServiceProvider\n{\n    /**\n     * Register services.\n     */\n    public function register(): void\n    {\n    }\n",
                $providerContent
            );
        }

        // Kiểm tra bind đã tồn tại chưa
        if (!Str::contains($providerContent, $bindCode)) {
            // Chèn bind vào trước dòng đóng của register()
            $pattern = '/(public function register\(\)(?:\s*:\s*void)?\s*\{)([\s\S]*?)(\n\s*\})/';
            $replacement = '$1$2' . "\n$bindCode$3";
            $providerContent = preg_replace($pattern, $replacement, $providerContent);
            
            if (File::put($providerPath, $providerContent)) {
                $this->info('Đã bind vào RepositoryServiceProvider.');
            } else {
                $this->error('Không thể ghi vào file RepositoryServiceProvider.');
                return 1;
            }
        } else {
            $this->warn('Bind đã tồn tại, bỏ qua.');
        }

        $this->info('Hoàn thành 🎉');
        return 0;
    }
}
