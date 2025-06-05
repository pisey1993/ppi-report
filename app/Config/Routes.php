<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('quote-report', 'QuoteReport::index');
$routes->get('quote-report/download', 'QuoteReport::download');

$routes->get('renewalreport', 'RenewalReport::index');
$routes->get('renewalreport/download', 'RenewalReport::download');

$routes->get('placement', 'PlacementReport::index');
$routes->post('placement/download', 'PlacementReport::download');


$routes->get('db-test', 'DbTest::index');

