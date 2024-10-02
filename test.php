<?php
require 'vendor/autoload.php';
use App\VaGateService;

$virtual_account = (object)[
    'nama' => 'John Doe',
    'nominal' => 100000,
    'expired' => date('Y-m-d H:i:s', strtotime('+1 day')),
];

$vGateService = new VaGateService;

$response = $vGateService->requestVa('create', $virtual_account);

var_dump($response);

