<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use setasign\Fpdi\Tcpdf\Fpdi;

class DocumentController extends Controller
{
    public function upload(Request $request)
    {

        $request->validate([
            'file' => 'required|mimes:pdf,doc,docx,txt|max:1000',
            'title' => 'nullable|string'
        ]);

        $file = $request->file('file');


        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $path = $file->store('documents' , 'public');



        $title = $request->title ?? pathinfo($originalName, PATHINFO_FILENAME);

        // Handle different file types
        $pageCount = 1;
        $pdfPath = $path;

        if ($extension === 'pdf') {
            // Get PDF page count
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile(storage_path('app/public/' . $path));
        } else if (in_array($extension, ['doc', 'docx'])) {

            // Convert Word to PDF
            $pdfPath = $this->convertWordToPdf($path);

            // Get page count of the converted PDF
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile(storage_path('app/private/' . $pdfPath));
        } else if ($extension === 'txt') {
            // Convert text file to PDF
            $pdfPath = $this->convertTextToPdf($path);

            // Get page count
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile(storage_path('app/private/' . $pdfPath));
        }

        $document = Document::create([
            'title' => $title,
            'file_path' => $path,
            'pdf_path' => $pdfPath,
            'page_count' => $pageCount,
            'file_type' => $extension,
            'company_id' => 1
        ]);

        return response()->json($document, 201);
    }


    public function show()
    {


        return Inertia::render('Company/Pages/Documents/DocumentPreview');
    }



    public function replacePdf(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf',
            'filepath' => 'required|string'
        ]);

        // Get the file path
        $filepath = $request->input('filepath');

        // Store the new file, replacing the old one
        if ($request->hasFile('file')) {
            // Delete the existing file if it exists
            if (Storage::exists($filepath)) {
                Storage::delete($filepath);
            }

            // Store the new file at the same path
            Storage::putFileAs(
                dirname($filepath),
                $request->file('file'),
                basename($filepath)
            );

            // Return success response with the file URL
            return response()->json([
                'success' => true,
                'message' => 'File replaced successfully',
                'file_url' => Storage::url($filepath)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No file was uploaded'
        ], 400);
    }


    private function convertWordToPdf($wordPath)
    {
        // You would need to install phpoffice/phpword
        // composer require phpoffice/phpword

        $phpWord = \PhpOffice\PhpWord\IOFactory::load(storage_path('app/' . $wordPath));
        $pdfWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');

        $pdfPath = 'documents/' . basename($wordPath, '.' . pathinfo($wordPath, PATHINFO_EXTENSION)) . '.pdf';
        $pdfWriter->save(storage_path('app/' . $pdfPath));

        return $pdfPath;
    }

    private function convertTextToPdf($textPath)
    {
        // Using TCPDF to convert text file to PDF
        // composer require tecnickcom/tcpdf

        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle(basename($textPath));
        $pdf->SetHeaderData('', 0, basename($textPath), '');
        $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf->AddPage();

        $textContent = file_get_contents(storage_path('app/' . $textPath));
        $pdf->writeHTML(nl2br(htmlspecialchars($textContent)), true, false, true, false, '');

        $pdfPath = 'documents/' . basename($textPath, '.txt') . '.pdf';
        $pdf->Output(storage_path('app/' . $pdfPath), 'F');

        return $pdfPath;
    }
}
