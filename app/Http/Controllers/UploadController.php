<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use App\Jobs\ProcessCsvUpload;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Upload\UploadStoreRequest;

class UploadController extends Controller
{
    public function index()
    {
        $uploads = Upload::latest()->get();
        return view('uploads.index', compact('uploads'));
    }

    public function store(UploadStoreRequest $request)
    {
        $file = $request->file('file');
        $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

        Storage::disk('local')->makeDirectory('uploads');

        $relativePath = Storage::disk('local')->putFileAs('uploads', $file, $filename);
        $absolutePath = Storage::disk('local')->path($relativePath);

        Log::info('Controller stored file', [
            'relative' => $relativePath,
            'absolute' => $absolutePath,
            'exists_after_store' => file_exists($absolutePath),
            'filesize' => file_exists($absolutePath) ? filesize($absolutePath) : null,
        ]);

        $upload = Upload::firstOrCreate(
            ['file_name' => $filename, 'status' => 'pending']
        );

        ProcessCsvUpload::dispatch($upload, $absolutePath);

        return redirect()->route('uploads.index')->with('success', 'File uploaded successfully!');
    }
}
