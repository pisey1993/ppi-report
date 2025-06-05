<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class DbTest extends Controller
{
    public function index()
    {
        try {
            $db = Database::connect();
            $db->initialize();

            if ($db->connID) {
                echo "✅ Database connection successful!";
            } else {
                echo "❌ Database connection failed.";
            }
        } catch (\Throwable $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
}
