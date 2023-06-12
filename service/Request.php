<?php
namespace service;

class Request {
    private string $baseUrl;
    
    public function __construct(string $baseUrl) {
        $this->baseUrl = $baseUrl;
    }
    
    public function get(string $url, array $headers = []): string {
        return $this->sendRequest('GET', $url, $headers);
    }
    
    public function post(string $url, array $data = [], array $headers = []): string {
        return $this->sendRequest('POST', $url, $headers, $data);
    }
    
    public function put(string $url, array $data = [], array $headers = []): string {
        return $this->sendRequest('PUT', $url, $headers, $data);
    }
    
    public function delete(string $url, array $headers = []): string {
        return $this->sendRequest('DELETE', $url, $headers);
    }
    
    public function patch(string $url, array $data = [], array $headers = []): string {
        return $this->sendRequest('PATCH', $url, $headers, $data);
    }
    
    private function sendRequest(string $method, string $url, array $headers = [], array $data = []): string {
        $ch = curl_init();
        
        $url = $this->baseUrl . $url;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($ch);
        
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception('cURL error: ' . $error);
        }
        
        curl_close($ch);
        
        return $response;
    }
}
