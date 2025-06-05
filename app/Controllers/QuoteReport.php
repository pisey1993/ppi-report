<?php

namespace App\Controllers;

use App\Models\QuoteModel;
use CodeIgniter\Controller;

class QuoteReport extends Controller
{
    public function index()
    {
        return view('pages/quotelisting');
    }

    public function download()
    {
        $from = $this->request->getGet('from_date');
        $to = $this->request->getGet('to_date');

        // Validate date range
        if (!$from || !$to || strtotime($from) > strtotime($to)) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => 'Invalid date range.']);
        }

        // Load model and fetch data
        $model = new QuoteModel();
        $results = $model->getData($from, $to);

        if (empty($results)) {
            return $this->response->setStatusCode(404)
                ->setJSON(['error' => 'No records found.']);
        }

        // Prepare CSV in memory
        $csvData = fopen('php://temp', 'r+');
        fputcsv($csvData, array_keys($results[0])); // headers

        foreach ($results as $row) {
            fputcsv($csvData, $row);
        }

        rewind($csvData);
        $csvOutput = stream_get_contents($csvData);
        fclose($csvData);

        // Output CSV
        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename=quotelisting.csv')
            ->setHeader('Content-Length', strlen($csvOutput))
            ->setBody($csvOutput);
    }
}
