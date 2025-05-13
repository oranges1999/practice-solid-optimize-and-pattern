<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if(!function_exists('storeFile')){
    function storeFile($folder, $file)
    {   
        try {
            $randomString = Str::random(10);
            $dateTime = now()->format('Ymd-Hms');
            $fileOriginalName = $file->getClientOriginalName();
            $fileName = $randomString
                .'-'
                .pathinfo($fileOriginalName, PATHINFO_FILENAME)
                .'-'
                .$dateTime
                .'.'
                .pathinfo($fileOriginalName, PATHINFO_EXTENSION);
            return Storage::putFileAs($folder, $file, $fileName);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

if(!function_exists('getFileUrl')){
    function getFileUrl($path)
    {   
        return env('AWS_URL') . '/' . $path;
    }
}

if(!function_exists('deleteFile')){
    function deleteFile($path)
    {
        try {
            Storage::delete($path);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}