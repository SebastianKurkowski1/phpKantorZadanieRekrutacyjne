<?php
session_start();
require_once __DIR__ . '/../autoloader.php';
$env =  require __DIR__ . '/../configuration/env.php';
require_once __DIR__ . '/../service/common.php';

use service\Exchange;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Nieprawidłowe zapytanie.';
    redirect($env['serverHost'] . '/phprekrutacja');
}

$inputCurrency = $_POST['inputCurrency'] ?? NULL;
$outputCurrency = $_POST['outputCurrency'] ?? NULL;
$amount = $_POST['amount'] ?? NULL;

if (is_null($inputCurrency)) {
    $_SESSION['error'] = 'Waluta źródłowa nie została podana.';
    redirect($env['serverHost'] . '/phprekrutacja');
}

if (is_null($outputCurrency)) {
    $_SESSION['error'] = 'Waluta docelowa nie została podana.';
    redirect($env['serverHost'] . '/phprekrutacja');
}

if (is_null($amount)) {
    $_SESSION['error'] = 'Ilość do przewalutowania nie została podana.';
    redirect($env['serverHost'] . '/phprekrutacja');
}

if (!is_numeric($amount)) {
    $_SESSION['error'] = 'Ilość do przewalutowania musi być liczbowa.';
    redirect($env['serverHost'] . '/phprekrutacja');
}


if ($inputCurrency === $outputCurrency) {
    $_SESSION['error'] = 'Wybrane waluty są takie same.';
    redirect($env['serverHost'] . '/phprekrutacja');
}

if ($amount < 0) {
    $_SESSION['error'] = 'Ilość do przewalutowania nie może być mniejsza od 0.';
    redirect($env['serverHost'] . '/phprekrutacja');
}


$exchange = new Exchange;
$exchangeOutput = $exchange->exchangeCurrency($inputCurrency, $outputCurrency, $amount);

if (count($exchangeOutput) === 0) {
    $_SESSION['error'] = 'Nie udało się wymienić walut. Spróbuj ponownie.';
    redirect($env['serverHost'] . '/phprekrutacja');
}

$convertedDataToDatabase = [];

foreach($exchangeOutput as $mainKey => $currencyData)
{
    if ($mainKey === 'inputCurrency') $prefix = 'input_';
    if ($mainKey === 'outputCurrency') $prefix = 'output_';

    foreach($currencyData as $key => $value)
    {
        $convertedDataToDatabase[$prefix . $key] = $value;
    }
}

$exchange->insertExchangeIntoDatabase($convertedDataToDatabase);

redirect($env['serverHost'] . '/phprekrutacja');
