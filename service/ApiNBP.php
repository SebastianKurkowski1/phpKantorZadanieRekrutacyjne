<?php
namespace service;
use service\Request;

class ApiNbp {
    private Request $request;
    
    function __construct() {
        $this->request = new Request('http://api.nbp.pl');
    }

    public function getCurrentExchangeRates(string $table): array {
        return json_decode($this->request->get("/api/exchangerates/tables/$table/", ['Accept: application/json']), true);
    }
}