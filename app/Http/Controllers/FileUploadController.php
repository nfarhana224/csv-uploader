<?php

namespace App\Http\Controllers;

use App\Http\Resources\FileUploadResource;
use App\Jobs\ProcessCsvJob;
use App\Models\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FileUploadController extends Controller
{
    public function index()
    {
        // Return blade with initial data (for server-side render)
        $uploads = FileUpload::latest()->take(50)->get();
        return view('uploads.index', compact('uploads'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:512000' // up to 500MB in KB
        ]);

        $file = $request->file('csv_file');

        // DEBUG: Cek file info
        \Log::info('File upload attempt:', [
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType()
        ]);

        // Validation for Idempotency
        // cek file content hash
        $fileContent = file_get_contents($file->getRealPath());
        $fileHash = md5($fileContent);
        $existingUpload = FileUpload::where('filename', $file->getClientOriginalName())
            ->where('status', 'completed')
            ->first();

        if ($existingUpload) {
            return redirect()->back()->with('error', 'This file has already been processed.');
        }

        // Debug
        if ($existingUpload) {
        \Log::info('File already processed:', ['filename' => $file->getClientOriginalName()]);
        return redirect()->back()->with('error', 'This file has already been processed.');
        }

        $path = $file->store('uploads');
        // DEBUG: Cek storage path
        \Log::info('File stored at:', ['path' => $path]);

        $upload = FileUpload::create([
            'user_id' => null, // no login yet
            'filename' => $file->getClientOriginalName(),
            'filepath' => $path,
            'status' => 'pending',
        ]);

        // Debug
        \Log::info('FileUpload record created:', ['id' => $upload->id]);

        // Dispatch job (use queue)
         dispatch(new ProcessCsvJob($upload));
        //ProcessCsvJob::dispatch($upload);

        return redirect()->back()->with('success', 'File uploaded, processing started.');
    }

    // API: return all uploads (for polling)
    public function apiIndex()
    {
        $uploads = FileUpload::latest()->get();
        return FileUploadResource::collection($uploads);
    }

    // API: single upload (for polling specific)
    public function apiShow(FileUpload $fileUpload)
    {
        return FileUploadResource::make($fileUpload);
    }
}