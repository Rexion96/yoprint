<?php

namespace App\Jobs;

use RuntimeException;
use App\Models\Upload;
use League\Csv\Reader;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use App\Events\UploadStatusUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessCsvUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Upload $upload;
    public string $absolutePath;

    public function __construct(Upload $upload, string $absolutePath)
    {
        $this->upload = $upload;
        $this->absolutePath = $absolutePath;
    }

    public function handle(): void
    {
        try {
            Log::info('Job starting', [
                'upload_id' => $this->upload->id,
                'raw_path' => $this->absolutePath
            ]);

            $filePath = $this->absolutePath;

            if (! file_exists($filePath) || ! is_readable($filePath)) {
                throw new RuntimeException("CSV file does not exist or is not readable: {$filePath}");
            }

            $this->upload->update(['status' => 'processing']);

            broadcast(new UploadStatusUpdated($this->upload));

            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0);

            $processed = 0;
            $batch = [];
            $batchSize = 750;

            foreach ($csv->getRecords() as $record) {
                $processed++;
                $record = array_map(fn($v) => $v === null ? null : mb_convert_encoding(trim((string)$v), 'UTF-8', 'UTF-8'), $record);

                if (empty($record['UNIQUE_KEY'])) {
                    Log::warning("Skipping row {$processed} — missing UNIQUE_KEY", ['upload_id' => $this->upload->id]);
                    continue;
                }

                $batch[] = [
                    'unique_key' => $record['UNIQUE_KEY'],
                    'product_title' => $record['PRODUCT_TITLE'] ?? null,
                    'product_description' => $record['PRODUCT_DESCRIPTION'] ?? null,
                    'style_number' => $record['STYLE#'] ?? null,
                    'sanmar_mainframe_color' => $record['SANMAR_MAINFRAME_COLOR'] ?? null,
                    'size' => $record['SIZE'] ?? null,
                    'color_name' => $record['COLOR_NAME'] ?? null,
                    'piece_price' => $record['PIECE_PRICE'] ?? null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ];

                if (count($batch) >= $batchSize) {
                    Product::upsert(
                        $batch,
                        ['unique_key'],
                        [
                            'product_title',
                            'product_description',
                            'style_number',
                            'sanmar_mainframe_color',
                            'size',
                            'color_name',
                            'piece_price',
                            'updated_at'
                        ]
                    );
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                Product::upsert(
                    $batch,
                    ['unique_key'],
                    [
                        'product_title',
                        'product_description',
                        'style_number',
                        'sanmar_mainframe_color',
                        'size',
                        'color_name',
                        'piece_price',
                        'updated_at'
                    ]
                );
            }

            Log::info('Job processed file', [
                'upload_id' => $this->upload->id,
                'rows' => $processed
            ]);

            $this->upload->update([
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            broadcast(new UploadStatusUpdated($this->upload));
        } catch (\Throwable $e) {
            Log::error('Error processing upload', [
                'upload_id' => $this->upload->id ?? null,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->upload->update(['status' => 'failed']);
            broadcast(new UploadStatusUpdated($this->upload));
        }
    }
}
