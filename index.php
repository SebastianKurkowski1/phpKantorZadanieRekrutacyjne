<?php 
session_start();
require_once 'autoloader.php';

use service\database\DatabaseConnection;
use service\database\DatabaseManager;
use service\ApiNbp;
use service\generators\HtmlTableGenerator;

if (isset($_SESSION['error'])) {
  $alert = "<script>alert('{$_SESSION['error']}')</script>";
  unset($_SESSION['error']);
}
$pdo = DatabaseConnection::getInstance()->getConnection();

try {
  $apiNBP = new ApiNbp();
  $currenciesC = $apiNBP->getCurrentExchangeRates('C');
} catch (\Exception $e) {
  $alert = '<script>alert("Nie udało się pobrać najnowszych kursów walut. Podane kursy zostały pobrane z naszej bazy danych.")</script>';
}

$databaseManager = new DatabaseManager($pdo);

if (isset($currenciesC))
{
  $databaseManager->insertOnDuplicateUpdate('currencies', $currenciesC[0]['rates']);
}

try {
  $currenciesFetched = $databaseManager->select('currencies', 'code, currency, bid, ask');
} catch (\Exception $e) {
  $alert = '<script>alert("Nie udało się pobrać kursów walut z bazy danych. Spróbuj ponownie później.")</script>';
}

try {
  $exchangesFetched = $databaseManager->select('exchanges', 'input_code, input_ask, input_amount, output_code, output_bid, output_amount, updated_at', '', [], 13, 'updated_at DESC');
} catch (\Exception $e) {
  $alert = '<script>alert("Nie udało się pobrać ostatnich przewalutowań z bazy danych. Spróbuj ponownie później.")</script>';
}

if (isset($currenciesFetched)) {
  $tableGenerator = new HtmlTableGenerator();
  $headerNames = [
    'code' => 'kod',
    'currency' => 'waluta',
    'ask' => 'kurs kupna',
    'bid' => 'kurs sprzedaży',
  ];
  $currenciesTable = $tableGenerator->generateTable($currenciesFetched, 'currencies', $headerNames);
}

if (isset($exchangesFetched)) {
  $tableGenerator = new HtmlTableGenerator();
  $headerNames = [
    'input_code' => 'waluta źródłowa',
    'input_ask' => 'kurs sprzedaży',
    'input_amount' => 'sprzedana ilość',
    'output_code' => 'waluta docelowa',
    'output_bid' => 'kurs kupna',
    'output_amount' => 'kupiona ilość',
    'updated_at' => 'data przewalutowania'
  ];

  $exchangesTable = $tableGenerator->generateTable($exchangesFetched, 'currencies', $headerNames);
}

?>

<!DOCTYPE html>
<html lang="pl">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kantor</title>
    <link rel="stylesheet" href="public/main.css">
  </head>
  <body>
    <main>
      <div id="tablesContainer">
        <div>
          <span>Kursy walut</span>
          <?php if (isset($currenciesTable)) echo $currenciesTable ?>
        </div>
        <div>
          <span>Ostatnie przewalutowania (ostatnie 13)</span>
          <?php if (isset($exchangesTable)) echo $exchangesTable ?>
        </div>
      </div>
        
      <div id="exchangeForm">
        <form method="POST" action="api/exchangeCurrency.php">
          <div>
            <label for="inputCurrency">Waluta źródłowa</label>
            <select name="inputCurrency">

            <?php foreach($currenciesFetched as $currency): ?>
              <option value="<?= $currency['code'] ?>"><?= $currency['currency'] ?></option>
            <?php endforeach; ?>

            </select>

            <label for="outputCurrency">Waluta docelowa</label>
            <select name="outputCurrency">

            <?php foreach($currenciesFetched as $currency): ?>
              <option value="<?= $currency['code'] ?>"><?= $currency['currency'] ?></option>
            <?php endforeach; ?>

            </select>
          </div>
          
          <div>
            <label for="amount">Ilość do przewalutowania</label>
            <input name="amount" step="0.01" type="number"/>
          </div>

          <button type="submit">
              Przewalutuj
          </button>

        </form>
      </div>
    </main>
  
  <?php if (isset($alert)) echo $alert ?>
  </body>
</html>