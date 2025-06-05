<?php

namespace App\Controllers;

use App\Models\PlacementModel;
use CodeIgniter\HTTP\ResponseInterface;

class PlacementReport extends BaseController
{
    public function index()
    {
        return view('pages/placement_report_form');
    }

    private function exportCSV(string $method, string $filename)
    {
        $from = $this->request->getPost('from_date');
        $to = $this->request->getPost('to_date');

        if (!$from || !$to || strtotime($from) > strtotime($to)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['error' => 'Invalid date range.']);
        }

        $model = new PlacementModel();

        if (!method_exists($model, $method)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['error' => 'Invalid report method.']);
        }

        $data = $model->$method($from, $to);

        if (empty($data)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['error' => 'No records found.']);
        }

        // Generate CSV
        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, array_keys($data[0])); // Header
        foreach ($data as $row) {
            fputcsv($csv, $row);
        }
        rewind($csv);
        $csvContent = stream_get_contents($csv);
        fclose($csv);

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '.csv"')
            ->setBody($csvContent);
    }

    public function download()
    {
        $reportType = $this->request->getPost('report_type');

        // Map report types to model methods and filenames
        $reportMap = [
            'motor'   => ['method' => 'getMotorPlacementData',   'filename' => 'motor_placement'],
            'chc'     => ['method' => 'getCHCPlacementData',     'filename' => 'chc_placement'],
            'pa'      => ['method' => 'getPAPlacementData',      'filename' => 'pa_placement'],
            'travel'  => ['method' => 'getTravelPlacementData',  'filename' => 'travel_placement'],
            'fir'     => ['method' => 'getFIRPlacementData',     'filename' => 'fir_placement'],
            'car'     => ['method' => 'getCARPlacementData',     'filename' => 'car_placement'],
            'ihc'     => ['method' => 'getIHCPlacementData',     'filename' => 'ihc_placement'],
            'par'     => ['method' => 'getPARPlacementData',     'filename' => 'par_placement'],
            'pi'      => ['method' => 'getPIPlacementData',      'filename' => 'pi_placement'],
            'pl'      => ['method' => 'getPLPlacementData',      'filename' => 'pl_placement'],
            'wc'      => ['method' => 'getWCPlacementData',      'filename' => 'wc_placement'],
        ];

        if (!isset($reportMap[$reportType])) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['error' => 'Invalid report type.']);
        }

        $method = $reportMap[$reportType]['method'];
        $filename = $reportMap[$reportType]['filename'];

        return $this->exportCSV($method, $filename);
    }
}
