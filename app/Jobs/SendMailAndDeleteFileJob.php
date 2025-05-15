<?php

namespace App\Jobs;

use App\Enums\MailTypeEnum;
use App\Mail\ExportMail;
use App\Mail\ImportMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendMailAndDeleteFileJob implements ShouldQueue
{
    use Queueable;

    private $type;
    private $email;
    private $path;
    /**
     * Create a new job instance.
     */
    public function __construct($email, $type, $path)
    {
        $this->email = $email;
        $this->path = $path;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $template = $this->type == MailTypeEnum::ExportMail->value?
            new ExportMail(storage_path('app/public/'.$this->path)):
            new ImportMail(storage_path('app/public/'.$this->path));
            
        Mail::to($this->email)->send($template);

        if(Storage::disk('public')->exists($this->path)){
            deleteFile('public', $this->path);
        }
    }
}
