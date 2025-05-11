<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

use Brevo\Client\Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use GuzzleHttp\Client;

try {
    // Configure the HTTP client with SSL verification disabled (for testing only)
    $httpClient = new Client([
        'verify' => false // Disables SSL verification
    ]);
    
    $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', BREVO_API_KEY);
    $apiInstance = new TransactionalEmailsApi($httpClient, $config);
    
    $result = $apiInstance->getSmtpTemplate(1);
    echo "Connection to Brevo successful!";
} catch (Exception $e) {
    echo "Brevo Connection Failed: " . $e->getMessage();
}
?>