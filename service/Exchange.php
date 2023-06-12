<?php
namespace service;

use service\database\DatabaseConnection;
use service\database\DatabaseManager;

class Exchange {

    public function exchangeCurrency(string $inputCurrency,string $outputCurrency,float $exchangeAmmout): array {
        $pdo = DatabaseConnection::getInstance()->getConnection();
        $databaseManager = new DatabaseManager($pdo);
        $exchangeRates = $databaseManager->select('currencies', 'code, bid, ask', 'code = ? OR code = ?', [$inputCurrency, $outputCurrency]);

        if (count($exchangeRates) !== 2) return [];

        foreach ($exchangeRates as $exchangeRate) {
            if ($exchangeRate['code'] === $inputCurrency) $inputCurrency = $exchangeRate;
            if ($exchangeRate['code'] === $outputCurrency) $outputCurrency = $exchangeRate;
        }

        $inputCurrency['amount'] = $exchangeAmmout;
        $value = $inputCurrency['bid'] * $exchangeAmmout;
        $outputCurrency['amount'] = $value / $outputCurrency['ask'];

        return [
            'inputCurrency' => $inputCurrency,
            'outputCurrency' => $outputCurrency,
        ];
    }

    public function insertExchangeIntoDatabase(array $data): bool
    {
        $pdo = DatabaseConnection::getInstance()->getConnection();
        $databaseManager = new DatabaseManager($pdo);
        return $databaseManager->insert('exchanges', $data);
    }

}


