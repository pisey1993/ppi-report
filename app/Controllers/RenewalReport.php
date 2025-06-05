<?php

namespace App\Controllers;

use App\Models\RenewalReportModel;
use CodeIgniter\Controller;

class RenewalReport extends Controller
{
    public function index()
    {
        return view('pages/renewal_report_form');
    }

    public function download()
    {
        $from = $this->request->getGet('from_date');  // use getGet() for GET method
        $to = $this->request->getGet('to_date');
        $status = $this->request->getGet('status');

        if (!$from || !$to || strtotime($from) > strtotime($to)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Invalid date range.']);
        }

        $model = new RenewalReportModel();
        $results = $model->getReportData($from, $to, $status);

        if (empty($results)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'No records found.']);
        }

        // Open a temp memory stream for CSV
        $csv = fopen('php://temp', 'r+');

        // Convert first row object to array to get headers
        $firstRowArray = (array) $results[0];
        fputcsv($csv, array_keys($firstRowArray));

        // Write each row (convert object to array)
        foreach ($results as $row) {
            fputcsv($csv, (array) $row);
        }

        // Rewind and get CSV content
        rewind($csv);
        $csvContent = stream_get_contents($csv);
        fclose($csv);

        // Send CSV headers and content for download
        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="RenewalReport.csv"')
            ->setHeader('Content-Length', strlen($csvContent))
            ->setBody($csvContent);
    }
}
