<?php

namespace Plugins\ResellerClubRegistrar\src;

use Illuminate\Support\Facades\Http;
use App\Models\Setting;

class ResellerClubApi
{
    protected string $baseUrl;
    protected string $resellerId;
    protected string $apiKey;
    protected int $customerId;
    protected int $contactId;

    public function __construct()
    {
        $testMode = Setting::get('resellerclub_test_mode', '1') === '1';
        
        $this->baseUrl = $testMode
            ? 'https://test.httpapi.com/api/'
            : 'https://httpapi.com/api/';
        
        $this->resellerId = Setting::get('resellerclub_reseller_id', '');
        $this->apiKey = Setting::get('resellerclub_api_key', '');
        $this->customerId = (int) Setting::get('resellerclub_customer_id', '0');
        $this->contactId = (int) Setting::get('resellerclub_contact_id', '0');
    }

    /**
     * Check domain availability across multiple TLDs.
     */
    public function checkAvailability(string $sld, array $tlds): array
    {
        $params = ['domain-name' => $sld];
        foreach ($tlds as $tld) {
            $params['tlds'][] = ltrim($tld, '.');
        }
        
        return $this->get('domains/available.json', $params);
    }

    /**
     * Register a domain.
     */
    public function register(string $domain, int $years, array $nameservers): array
    {
        return $this->post('domains/register.json', [
            'domain-name' => $domain,
            'years' => $years,
            'ns' => array_slice($nameservers, 0, 4),
            'customer-id' => $this->customerId,
            'reg-contact-id' => $this->contactId,
            'admin-contact-id' => $this->contactId,
            'tech-contact-id' => $this->contactId,
            'billing-contact-id' => $this->contactId,
            'invoice-option' => 'NoInvoice',
            'purchase-privacy' => 'false',
            'protect-privacy' => 'false',
        ]);
    }

    /**
     * Renew a domain.
     */
    public function renew(string $domain, int $years, string $orderId): array
    {
        return $this->post('domains/renew.json', [
            'order-id' => $orderId,
            'years' => $years,
            'exp-date' => date('Y-m-d', strtotime("+{$years} years")),
            'invoice-option' => 'NoInvoice',
        ]);
    }

    /**
     * Transfer a domain.
     */
    public function transfer(string $domain, string $eppCode): array
    {
        return $this->post('domains/transfer.json', [
            'domain-name' => $domain,
            'auth-code' => $eppCode,
            'customer-id' => $this->customerId,
            'reg-contact-id' => $this->contactId,
            'invoice-option' => 'NoInvoice',
        ]);
    }

    /**
     * Get domain details by order ID.
     */
    public function getDomainDetails(string $orderId): array
    {
        return $this->get('domains/orderid.json', [
            'order-id' => $orderId,
            'options' => 'OrderDetails',
        ]);
    }

    /**
     * Update nameservers.
     */
    public function updateNameservers(string $orderId, array $nameservers): array
    {
        return $this->post('domains/modify-ns.json', [
            'order-id' => $orderId,
            'ns' => array_slice($nameservers, 0, 4),
        ]);
    }

    /**
     * Get customer details.
     */
    public function getCustomerDetails(): array
    {
        return $this->get('customers/details.json', [
            'customer-id' => $this->customerId,
        ]);
    }

    /**
     * Get available TLDs with pricing.
     */
    public function getTldPricing(): array
    {
        return $this->get('products/category-type.json', [
            'type' => 'domorder',
        ]);
    }

    /**
     * Search for a customer by email.
     */
    public function searchCustomer(string $email): array
    {
        return $this->get('customers/search.json', [
            'email' => $email,
        ]);
    }

    /**
     * Make a GET request.
     */
    protected function get(string $endpoint, array $params = []): array
    {
        $params['auth-userid'] = $this->resellerId;
        $params['auth-password'] = $this->apiKey;

        $url = $this->baseUrl . $endpoint . '?' . http_build_query($params);

        try {
            $response = Http::timeout(30)->get($url);
            return $response->json() ?: [];
        } catch (\Exception $e) {
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }

    /**
     * Make a POST request.
     */
    protected function post(string $endpoint, array $data = []): array
    {
        $data['auth-userid'] = $this->resellerId;
        $data['auth-password'] = $this->apiKey;

        try {
            $response = Http::timeout(30)->asForm()->post($this->baseUrl . $endpoint, $data);
            return $response->json() ?: [];
        } catch (\Exception $e) {
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }

    /**
     * Check if API is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->resellerId) && !empty($this->apiKey);
    }
}