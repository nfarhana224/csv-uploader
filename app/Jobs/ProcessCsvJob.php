<?php

namespace App\Jobs;

use App\Models\FileUpload;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;
use Illuminate\Support\Facades\DB;

class ProcessCsvJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public FileUpload $fileUpload;
    public $timeout = 1200; // 20 minutes timeout untuk queue job

    public function __construct(FileUpload $fileUpload)
    {
        $this->fileUpload = $fileUpload;
    }

    public function handle(): void
    {
        // Set higher limits untuk large files
        ini_set('memory_limit', '1024M'); // 1GB memory
        set_time_limit(1200); // 20 minutes

        $this->fileUpload->update([
            'status' => 'processing', 
            'processed_rows' => 0
        ]);

        $path = storage_path('app/' . $this->fileUpload->filepath);

        if (!file_exists($path)) {
            $this->fileUpload->update([
                'status' => 'failed', 
                'error_message' => 'File not found'
            ]);
            return;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            $this->fileUpload->update([
                'status' => 'failed', 
                'error_message' => 'Unable to open file'
            ]);
            return;
        }

        $header = null;
        $rowCount = 0;
        $processed = 0;
        $batch = [];
        $batchSize = 100; // Process 100 rows sekaligus

        try {
            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                if ($header === null) {
                    $header = array_map(fn($h) => trim(strtoupper($h)), $row);
                    continue;
                }

                $data = array_combine($header, $row);
                $rowCount++;

                $uniqueKey = $data['UNIQUE_KEY'] ?? null;
                if (!$uniqueKey) {
                    $this->fileUpload->increment('processed_rows');
                    continue;
                }

                // Add to batch
                $batch[] = [
                    'unique_key' => $uniqueKey,
                    'file_upload_id' => $this->fileUpload->id,
                    'product_title' => $this->cleanUtf8($data['PRODUCT_TITLE'] ?? null),
                    'product_description' => $this->cleanUtf8($data['PRODUCT_DESCRIPTION'] ?? null),
                    'style' => $this->cleanUtf8($data['STYLE#'] ?? $data['STYLE'] ?? null),
                    'sanmar_mainframe_color' => $this->cleanUtf8($data['SANMAR_MAINFRAME_COLOR'] ?? null),
                    'size' => $this->cleanUtf8($data['SIZE'] ?? null),
                    'color_name' => $this->cleanUtf8($data['COLOR_NAME'] ?? null),
                    'piece_price' => is_numeric($data['PIECE_PRICE'] ?? null) ? floatval($data['PIECE_PRICE']) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Process batch ketika mencapai batch size
                if (count($batch) >= $batchSize) {
                    $this->processBatch($batch);
                    $processed += count($batch);
                    $batch = [];
                    
                    // Update progress
                    $this->fileUpload->update(['processed_rows' => $processed]);
                    
                    // Reset execution time untuk setiap batch
                    set_time_limit(1200);
                }
            }

            // Process remaining records in batch
            if (!empty($batch)) {
                $this->processBatch($batch);
                $processed += count($batch);
            }

            fclose($handle);

            // Update final status
            $this->fileUpload->update([
                'status' => 'completed',
                'total_rows' => $rowCount,
                'processed_rows' => $rowCount,
            ]);

        } catch (\Exception $e) {
            fclose($handle);
            $this->fileUpload->update([
                'status' => 'failed',
                'error_message' => 'Processing error: ' . $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Process batch of records dengan UPSERT
     */
    private function processBatch(array $batch): void
    {
        if (empty($batch)) return;

        try {
            // Gunakan UPSERT untuk batch processing
            Product::upsert(
                $batch,
                ['unique_key'], // Unique key untuk conflict detection
                [
                    'product_title',
                    'product_description', 
                    'style',
                    'sanmar_mainframe_color',
                    'size',
                    'color_name',
                    'piece_price',
                    'file_upload_id',
                    'updated_at'
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Batch processing error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function cleanUtf8($value)
    {
        if (!is_string($value)) return $value;
        
        // Remove non-UTF-8 characters
        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        
        // Remove any remaining invalid characters
        return preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $value);
    }

    public function failed(Throwable $exception): void
    {
        $this->fileUpload->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);
        
        \Log::error('ProcessCsvJob failed: ' . $exception->getMessage());
    }
}