<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Spatie\DbDumper\Databases\MySql;


class BackupController extends Controller
{
    public function index(){
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        $files = $disk->files(config('backup.backup.name'));
        $backups = [];
        foreach ($files as $k => $f) {
            if (substr($f, -4) == '.zip' && $disk->exists($f)) {
                $backups[] = [
                    'file_path' => $f,
                    'file_name' => str_replace(config('backup.backup.name') . '/', '', $f),
                    'file_size' => $this->human_filesize($disk->size($f)),
                    'last_modified' => $this->getDate($disk->lastModified($f)),
                ];
            }
        }
        $backups = array_reverse($backups);
        return view("backup.index")->with(compact('backups'));
    }

    public function create()
    {
        try {
            // start the backup process
            Artisan::call('backup:run',['--only-db'=>true]);
            Artisan::output();
            //Log::info("Backpack\BackupManager -- new backup started from admin interface \r\n" . $output);
            // return the results as a response to the ajax call
            //Alert::success('New backup created');
            /*Spatie\DbDumper\Databases\MySql::create()
            ->setDbName($databaseName)
            ->setUserName($userName)
            ->setPassword($password)
            ->dumpToFile('dump.sql');*/
            return redirect()->back();
        } catch (Exception $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function download($file_name)
    {
        $file = config('backup.backup.name') . '/' . $file_name;
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        if ($disk->exists($file)) {
            $fs = Storage::disk(config('backup.backup.destination.disks')[0])->getDriver();
            $stream = $fs->readStream($file);
            return \Response::stream(function () use ($stream) {
                fpassthru($stream);
            }, 200, [
                "Content-Type" => $fs->getMimetype($file),
                "Content-Length" => $fs->getSize($file),
                "Content-disposition" => "attachment; filename=\"" . basename($file) . "\"",
            ]);
        } else {
            abort(404, "The backup file doesn't exist.");
        }
    }

    public function delete($file_name)
    {
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        if ($disk->exists(config('backup.backup.name') . '/' . $file_name)) {
            $disk->delete(config('backup.backup.name') . '/' . $file_name);
            return redirect()->back();
        } else {
            abort(404, "The backup file doesn't exist.");
        }
    }

    function getDate($date_modify){
        return Carbon::createFromTimestamp($date_modify)->formatLocalized('%d %B %Y %H:%M');
    }

    public function human_filesize($bytes,$decimals = 2){
        if ($bytes < 1024){
            return $bytes . ' B';
        }
        $factor = floor(log($bytes,1024));
        return sprintf("%.{$decimals}f ", $bytes / pow(1024,$factor)) . ['B','KB','MB','GB','TB','PB'][$factor];
    }
}
