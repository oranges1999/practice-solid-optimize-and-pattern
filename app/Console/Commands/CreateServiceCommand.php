<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rawName = $this->argument('name');
        $name = Str::studly($rawName);
        $servicesRoot = app_path('Services');

        // 1. Tạo folder Services nếu chưa có
        if (!File::exists($servicesRoot)) {
            File::makeDirectory($servicesRoot, 0755, true);
            $this->info('Đã tạo folder: app/Services');
        }

        // 2. Tạo file
        $fileName = $name . 'Service';
        $filePath = $servicesRoot . '/' . $fileName . '.' . 'php';
        if(!File::exists($filePath)){
            $content = "<?php\n\nnamespace App\\Services;\n\nclass $fileName\n{\n    // \n}\n";
            File::put($filePath, $content);
            $this->info("Đã tạo file Interface: $fileName.php");
        }
    }
}
