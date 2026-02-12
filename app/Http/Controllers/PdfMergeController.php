<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Webklex\PDFMerger\Facades\PDFMergerFacade;

class PdfMergeController extends Controller
{
    // merge
    public function merge(Request $request): JsonResponse
    {
        $request->validate([
            'pdfs' => 'required|array|min:2',
            'pdfs.*' => 'required|mimes:pdf|max:20480'
        ]);

        $pdf = PDFMergerFacade::init();

        $files = $request->file('pdfs');

        foreach ($files as $file) {

            $path = $file->store('temp', 'private');

            $fullPath = Storage::disk('private')->path($path);

            if (!file_exists($fullPath)) {
                abort(500, "File missing: $fullPath");
            }

            $pdf->addPDF($fullPath, 'all');
        }

        $pdf->merge();

        $time = time();
        $filename = "merged_{$time}.pdf";
        $outputPath = "temp/{$filename}";
        $pdf->save(Storage::disk('private')->path($outputPath));

        Cache::put($filename, $outputPath, now()->addMinutes(10));

        return response()->json([
            'id' => $filename
        ]);
    }
    
    // preview
    public function preview(Request $request): BinaryFileResponse
    {
        $path = Cache::get($request->query('id'));
    
        if (!$path) {
            abort(404);
        }
    
        return response()->file(Storage::disk('local')->path($path));
    }
    
    // download
    public function download(Request $request): BinaryFileResponse
    {
        $path = Cache::get($request->query('id'));

        if (!$path) {
            abort(404);
        }
    
        return response()
            ->download(Storage::disk('local')
            ->path($path))
            ->deleteFileAfterSend(true);
    }
}
