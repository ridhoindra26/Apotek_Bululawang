<?php

namespace App\Services;

use App\Models\Suggestion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class SuggestionAttachmentService
{
    public function store(Suggestion $suggestion, array $files): array
    {
        $storedPaths = [];

        try {
            foreach ($files as $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $disk = config('suggestions.attachments.disk', 'local');
                $directory = trim(config('suggestions.attachments.directory', 'suggestions'), '/').'/'.$suggestion->id;
                $path = $file->store($directory, $disk);

                if (! $path) {
                    throw new RuntimeException('Lampiran gagal disimpan.');
                }

                $storedPaths[] = ['disk' => $disk, 'path' => $path];
                $suggestion->attachments()->create([
                    'disk' => $disk,
                    'file_path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'file_size' => $file->getSize(),
                ]);
            }
        } catch (Throwable $exception) {
            $this->deleteStoredFiles($storedPaths);
            throw $exception;
        }

        return $storedPaths;
    }

    public function deleteStoredFiles(array $files): void
    {
        foreach ($files as $file) {
            Storage::disk($file['disk'])->delete($file['path']);
        }
    }
}
