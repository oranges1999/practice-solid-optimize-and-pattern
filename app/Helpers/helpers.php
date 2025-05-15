<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

if(!function_exists('getFilePath')){
    function getFilePath($url)
    {
        $pathArray = explode('/', $url);
        return implode('/',array_splice($pathArray, 4));
    }
}

if(!function_exists('deleteFile')){
    function deleteFile($disk, $path)
    {
        try {
            Storage::disk($disk)->delete($path);
        } catch (\Throwable $th) {
            Log::info($th);
            throw $th;
        }
    }
}

if(!function_exists('exportFile')){
    function exportFile($fileContent, $sheet, $spreadsheet, $currentUser)
    {
        $directory = 'temp/'.$currentUser->id;
        if(!Storage::disk('public')->exists($directory)){
            Storage::disk('public')->makeDirectory($directory);
        }
        $fullPath = storage_path('app/public/'.$directory.'/error_file.xlsx');
        $row = 1;
        foreach ($fileContent as $record) {
            $sheet->fromArray($record, NULL, 'A' . $row++);
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save($fullPath);
        
        return $directory.'/error_file.xlsx';
}
}