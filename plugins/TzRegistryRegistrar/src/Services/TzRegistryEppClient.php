<?php

namespace Plugins\TzRegistryRegistrar\src\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Plugins\TzRegistryRegistrar\src\Support\EppResponse;
use Plugins\TzRegistryRegistrar\src\Support\TzRegistryException;

class TzRegistryEppClient
{
    protected mixed $socket = null;
    protected bool $connected = false;
    protected bool $loggedIn = false;
    protected float $lastRequestAt = 0.0;
    protected ?string $greeting = null;

    public function __construct(
        protected string $host,
        protected int $port,
        protected string $username,
        protected string $password,
        protected string $certificatePath,
        protected ?string $privateKeyPath = null,
        protected ?string $privateKeyPassphrase = null,
        protected bool $verifyPeer = true,
        protected int $timeout = 30,
        protected float $minimumInterval = 0.5,
        protected bool $logXml = false,
    ) {
    }

    public function connect(): string
    {
        if ($this->connected && is_resource($this->socket)) {
            return (string) $this->greeting;
        }

        if ($this->certificatePath === '' || !is_file($this->certificatePath)) {
            throw new TzRegistryException('Client certificate file was not found.');
        }

        if ($this->privateKeyPath && !is_file($this->privateKeyPath)) {
            throw new TzRegistryException('Private key file was not found.');
        }

        $ssl = [
            'local_cert' => $this->certificatePath,
            'verify_peer' => $this->verifyPeer,
            'verify_peer_name' => $this->verifyPeer,
            'allow_self_signed' => false,
            'SNI_enabled' => true,
            'peer_name' => $this->host,
            'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
        ];

        if ($this->privateKeyPath) {
            $ssl['local_pk'] = $this->privateKeyPath;
        }

        if ($this->privateKeyPassphrase) {
            $ssl['passphrase'] = $this->privateKeyPassphrase;
        }

        $context = stream_context_create(['ssl' => $ssl]);
        $remote = "tls://{$this->host}:{$this->port}";

        $this->socket = @stream_socket_client($remote, $errno, $errstr, $this->timeout, STREAM_CLIENT_CONNECT, $context);

        if (!is_resource($this->socket)) {
            throw new TzRegistryException("Could not connect to tzNIC EPP: {$errstr}", $errno);
        }

        stream_set_timeout($this->socket, $this->timeout);
        stream_set_blocking($this->socket, true);

        $this->greeting = $this->readFrame();
        $this->connected = true;

        return $this->greeting;
    }

    public function login(): EppResponse
    {
        if ($this->loggedIn) {
            return new EppResponse(true, 1000, 'Already logged in', null, null, '', []);
        }

        $this->connect();

        $xml = $this->envelope(
            '<login>'
            . '<clID>' . $this->xml($this->username) . '</clID>'
            . '<pw>' . $this->xml($this->password) . '</pw>'
            . '<options><version>1.0</version><lang>en</lang></options>'
            . '<svcs>'
            . '<objURI>urn:ietf:params:xml:ns:domain-1.0</objURI>'
            . '<objURI>urn:ietf:params:xml:ns:contact-1.0</objURI>'
            . '<objURI>urn:ietf:params:xml:ns:host-1.0</objURI>'
            . '</svcs>'
            . '</login>'
        );

        $response = $this->command($xml);
        $this->loggedIn = $response->success;

        if (!$response->success) {
            throw new TzRegistryException($response->message, $response->code, $response->toArray());
        }

        return $response;
    }

    public function logout(): void
    {
        if (!$this->loggedIn || !is_resource($this->socket)) {
            $this->disconnect();
            return;
        }

        try {
            $this->command($this->envelope('<logout/>'));
        } catch (\Throwable $e) {
            Log::warning('tzNIC EPP logout failed', ['message' => $e->getMessage()]);
        }

        $this->disconnect();
    }

    public function checkDomains(array $domains): array
    {
        $names = collect($domains)
            ->map(fn ($domain) => $this->normalizeDomain($domain))
            ->filter()
            ->unique()
            ->values();

        if ($names->isEmpty()) {
            return [];
        }

        $nameXml = $names->map(fn ($domain) => '<domain:name>' . $this->xml($domain) . '</domain:name>')->implode('');
        $response = $this->authenticatedCommand($this->envelope(
            '<check><domain:check xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">' . $nameXml . '</domain:check></check>'
        ));

        $xpath = $this->xpath($response->xml);
        $results = [];

        foreach ($xpath->query('//domain:chkData/domain:cd') as $node) {
            $name = $xpath->query('domain:name', $node)->item(0);
            if (!$name) {
                continue;
            }

            $reason = $xpath->query('domain:reason', $node)->item(0);
            $domain = trim($name->textContent);
            $results[$domain] = [
                'domain' => $domain,
                'available' => $name->getAttribute('avail') === '1',
                'message' => $reason ? trim($reason->textContent) : ($name->getAttribute('avail') === '1' ? 'Available' : 'Not available'),
                'response' => $response->toArray(),
            ];
        }

        return $results;
    }

    public function createContact(array $contact): EppResponse
    {
        $id = $contact['id'] ?? ('JV-' . strtoupper(Str::random(10)));
        $voice = $this->normalizePhone($contact['phone'] ?? '');

        $xml = $this->envelope(
            '<create><contact:create xmlns:contact="urn:ietf:params:xml:ns:contact-1.0">'
            . '<contact:id>' . $this->xml($id) . '</contact:id>'
            . '<contact:postalInfo type="loc">'
            . '<contact:name>' . $this->xml($contact['name'] ?? '') . '</contact:name>'
            . '<contact:org>' . $this->xml($contact['org'] ?? '') . '</contact:org>'
            . '<contact:addr>'
            . '<contact:street>' . $this->xml($contact['street1'] ?? 'N/A') . '</contact:street>'
            . '<contact:city>' . $this->xml($contact['city'] ?? 'Dar es Salaam') . '</contact:city>'
            . '<contact:sp>' . $this->xml($contact['province'] ?? '') . '</contact:sp>'
            . '<contact:pc>' . $this->xml($contact['postal'] ?? '00000') . '</contact:pc>'
            . '<contact:cc>' . $this->xml(strtoupper($contact['country'] ?? 'TZ')) . '</contact:cc>'
            . '</contact:addr></contact:postalInfo>'
            . '<contact:voice>' . $this->xml($voice) . '</contact:voice>'
            . '<contact:email>' . $this->xml($contact['email'] ?? '') . '</contact:email>'
            . '<contact:authInfo><contact:pw>' . $this->xml($contact['auth'] ?? Str::password(16)) . '</contact:pw></contact:authInfo>'
            . '</contact:create></create>'
        );

        $response = $this->authenticatedCommand($xml);
        $xpath = $this->xpath($response->xml);
        $createdId = $xpath->evaluate('string(//contact:creData/contact:id)') ?: $id;

        return new EppResponse($response->success, $response->code, $response->message, $response->clientTransactionId, $response->serverTransactionId, $response->xml, [
            'contact_id' => $createdId,
        ]);
    }

    public function createDomain(string $domain, int $years, array $nameservers, array $contacts, ?string $authCode = null): EppResponse
    {
        $nsXml = '';
        foreach ($this->cleanNameservers($nameservers) as $nameserver) {
            $nsXml .= '<domain:hostAttr><domain:hostName>' . $this->xml($nameserver) . '</domain:hostName></domain:hostAttr>';
        }

        $authCode ??= Str::password(16);
        $response = $this->authenticatedCommand($this->envelope(
            '<create><domain:create xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">'
            . '<domain:name>' . $this->xml($this->normalizeDomain($domain)) . '</domain:name>'
            . '<domain:period unit="y">' . max(1, min(10, $years)) . '</domain:period>'
            . '<domain:ns>' . $nsXml . '</domain:ns>'
            . '<domain:registrant>' . $this->xml($contacts['registrant']) . '</domain:registrant>'
            . '<domain:contact type="admin">' . $this->xml($contacts['admin']) . '</domain:contact>'
            . '<domain:contact type="tech">' . $this->xml($contacts['tech']) . '</domain:contact>'
            . '<domain:contact type="billing">' . $this->xml($contacts['billing']) . '</domain:contact>'
            . '<domain:authInfo><domain:pw>' . $this->xml($authCode) . '</domain:pw></domain:authInfo>'
            . '</domain:create></create>'
        ));

        $xpath = $this->xpath($response->xml);

        return new EppResponse($response->success, $response->code, $response->message, $response->clientTransactionId, $response->serverTransactionId, $response->xml, [
            'domain' => $xpath->evaluate('string(//domain:creData/domain:name)') ?: $domain,
            'created_date' => $xpath->evaluate('string(//domain:creData/domain:crDate)') ?: null,
            'expiry_date' => $xpath->evaluate('string(//domain:creData/domain:exDate)') ?: null,
            'auth_code' => $authCode,
        ]);
    }

    public function renewDomain(string $domain, int $years, ?string $currentExpiryDate): EppResponse
    {
        $expiryXml = $currentExpiryDate ? '<domain:curExpDate>' . $this->xml(substr($currentExpiryDate, 0, 10)) . '</domain:curExpDate>' : '';

        $response = $this->authenticatedCommand($this->envelope(
            '<renew><domain:renew xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">'
            . '<domain:name>' . $this->xml($this->normalizeDomain($domain)) . '</domain:name>'
            . $expiryXml
            . '<domain:period unit="y">' . max(1, min(10, $years)) . '</domain:period>'
            . '</domain:renew></renew>'
        ));

        $xpath = $this->xpath($response->xml);

        return new EppResponse($response->success, $response->code, $response->message, $response->clientTransactionId, $response->serverTransactionId, $response->xml, [
            'domain' => $xpath->evaluate('string(//domain:renData/domain:name)') ?: $domain,
            'expiry_date' => $xpath->evaluate('string(//domain:renData/domain:exDate)') ?: null,
        ]);
    }

    public function transferDomain(string $domain, string $authCode, int $years = 1, string $operation = 'request'): EppResponse
    {
        $response = $this->authenticatedCommand($this->envelope(
            '<transfer><domain:transfer xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" op="' . $this->xml($operation) . '">'
            . '<domain:name>' . $this->xml($this->normalizeDomain($domain)) . '</domain:name>'
            . '<domain:period unit="y">' . max(1, min(10, $years)) . '</domain:period>'
            . '<domain:authInfo><domain:pw>' . $this->xml($authCode) . '</domain:pw></domain:authInfo>'
            . '</domain:transfer></transfer>'
        ));

        return $response;
    }

    public function infoDomain(string $domain, ?string $authCode = null): array
    {
        $authXml = $authCode ? '<domain:authInfo><domain:pw>' . $this->xml($authCode) . '</domain:pw></domain:authInfo>' : '';
        $response = $this->authenticatedCommand($this->envelope(
            '<info><domain:info xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">'
            . '<domain:name>' . $this->xml($this->normalizeDomain($domain)) . '</domain:name>'
            . $authXml
            . '</domain:info></info>'
        ));

        $xpath = $this->xpath($response->xml);
        $nameservers = [];
        foreach ($xpath->query('//domain:infData/domain:ns/domain:hostObj | //domain:infData/domain:ns/domain:hostAttr/domain:hostName') as $node) {
            $nameservers[] = trim($node->textContent);
        }

        $statuses = [];
        foreach ($xpath->query('//domain:infData/domain:status') as $node) {
            $statuses[] = $node->getAttribute('s');
        }

        return [
            'domain' => $xpath->evaluate('string(//domain:infData/domain:name)') ?: $domain,
            'registrar_domain_id' => $xpath->evaluate('string(//domain:infData/domain:roid)') ?: null,
            'registrant' => $xpath->evaluate('string(//domain:infData/domain:registrant)') ?: null,
            'created_date' => $xpath->evaluate('string(//domain:infData/domain:crDate)') ?: null,
            'expiry_date' => $xpath->evaluate('string(//domain:infData/domain:exDate)') ?: null,
            'nameservers' => array_values(array_unique(array_filter($nameservers))),
            'statuses' => array_values(array_unique(array_filter($statuses))),
            'auth_code' => $xpath->evaluate('string(//domain:infData/domain:authInfo/domain:pw)') ?: null,
            'response' => $response->toArray(),
        ];
    }

    public function updateNameservers(string $domain, array $oldNameservers, array $newNameservers): EppResponse
    {
        $add = array_values(array_diff($this->cleanNameservers($newNameservers), $this->cleanNameservers($oldNameservers)));
        $remove = array_values(array_diff($this->cleanNameservers($oldNameservers), $this->cleanNameservers($newNameservers)));

        $addXml = $this->nameserverUpdateXml('add', $add);
        $removeXml = $this->nameserverUpdateXml('rem', $remove);

        return $this->authenticatedCommand($this->envelope(
            '<update><domain:update xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">'
            . '<domain:name>' . $this->xml($this->normalizeDomain($domain)) . '</domain:name>'
            . $addXml
            . $removeXml
            . '</domain:update></update>'
        ));
    }

    public function setTransferLock(string $domain, bool $locked): EppResponse
    {
        $operation = $locked ? 'add' : 'rem';

        return $this->authenticatedCommand($this->envelope(
            '<update><domain:update xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">'
            . '<domain:name>' . $this->xml($this->normalizeDomain($domain)) . '</domain:name>'
            . '<domain:' . $operation . '><domain:status s="clientTransferProhibited"/></domain:' . $operation . '>'
            . '</domain:update></update>'
        ));
    }

    protected function authenticatedCommand(string $xml): EppResponse
    {
        if (!$this->loggedIn) {
            $this->login();
        }

        $response = $this->command($xml);

        if (!$response->success) {
            throw new TzRegistryException($response->message, $response->code, $response->toArray());
        }

        return $response;
    }

    protected function command(string $xml): EppResponse
    {
        $responseXml = $this->sendFrame($xml);
        return $this->parseResponse($responseXml);
    }

    protected function sendFrame(string $xml): string
    {
        $this->connect();
        $this->throttle();

        $frame = pack('N', strlen($xml) + 4) . $xml;
        $written = fwrite($this->socket, $frame);

        if ($written === false || $written < strlen($frame)) {
            throw new TzRegistryException('Failed to write full EPP frame.');
        }

        if ($this->logXml) {
            Log::debug('tzNIC EPP request', ['xml' => $this->sanitizeXml($xml)]);
        }

        return $this->readFrame();
    }

    protected function readFrame(): string
    {
        $header = fread($this->socket, 4);

        if ($header === false || strlen($header) !== 4) {
            throw new TzRegistryException('Failed to read EPP frame header.');
        }

        $unpacked = unpack('Nlength', $header);
        $length = (int) ($unpacked['length'] ?? 0);

        if ($length < 5 || $length > 10485760) {
            throw new TzRegistryException('Invalid EPP frame length.');
        }

        $remaining = $length - 4;
        $xml = '';

        while (strlen($xml) < $remaining) {
            $chunk = fread($this->socket, $remaining - strlen($xml));
            if ($chunk === false || $chunk === '') {
                throw new TzRegistryException('Failed to read EPP frame payload.');
            }
            $xml .= $chunk;
        }

        if ($this->logXml) {
            Log::debug('tzNIC EPP response', ['xml' => $this->sanitizeXml($xml)]);
        }

        return $xml;
    }

    protected function parseResponse(string $xml): EppResponse
    {
        $xpath = $this->xpath($xml);
        $result = $xpath->query('//epp:response/epp:result')->item(0);

        if (!$result) {
            throw new TzRegistryException('Invalid EPP response: result element is missing.');
        }

        $code = (int) $result->getAttribute('code');
        $message = trim($xpath->evaluate('string(//epp:response/epp:result/epp:msg)')) ?: 'Unknown registry response';

        return new EppResponse(
            $code >= 1000 && $code < 2000,
            $code,
            $message,
            trim($xpath->evaluate('string(//epp:trID/epp:clTRID)')) ?: null,
            trim($xpath->evaluate('string(//epp:trID/epp:svTRID)')) ?: null,
            $xml
        );
    }

    protected function xpath(string $xml): DOMXPath
    {
        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);

        if (!$dom->loadXML($xml, LIBXML_NONET | LIBXML_NOBLANKS)) {
            $errors = collect(libxml_get_errors())->map(fn ($error) => trim($error->message))->implode('; ');
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            throw new TzRegistryException('Invalid XML from EPP server: ' . $errors);
        }

        libxml_use_internal_errors($previous);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('epp', 'urn:ietf:params:xml:ns:epp-1.0');
        $xpath->registerNamespace('domain', 'urn:ietf:params:xml:ns:domain-1.0');
        $xpath->registerNamespace('contact', 'urn:ietf:params:xml:ns:contact-1.0');
        $xpath->registerNamespace('host', 'urn:ietf:params:xml:ns:host-1.0');

        return $xpath;
    }

    protected function envelope(string $commandBody): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="no"?>'
            . '<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"><command>'
            . $commandBody
            . '<clTRID>' . $this->xml($this->transactionId()) . '</clTRID>'
            . '</command></epp>';
    }

    protected function transactionId(): string
    {
        return 'JAMVINI-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(8));
    }

    protected function throttle(): void
    {
        $delay = $this->lastRequestAt + $this->minimumInterval - microtime(true);

        if ($delay > 0) {
            usleep((int) ($delay * 1000000));
        }

        $this->lastRequestAt = microtime(true);
    }

    protected function nameserverUpdateXml(string $operation, array $nameservers): string
    {
        if (empty($nameservers)) {
            return '';
        }

        $items = '';
        foreach ($nameservers as $nameserver) {
            $items .= '<domain:hostAttr><domain:hostName>' . $this->xml($nameserver) . '</domain:hostName></domain:hostAttr>';
        }

        return '<domain:' . $operation . '><domain:ns>' . $items . '</domain:ns></domain:' . $operation . '>';
    }

    protected function cleanNameservers(array $nameservers): array
    {
        return collect($nameservers)
            ->map(fn ($nameserver) => strtolower(trim((string) $nameserver, ". \t\n\r\0\x0B")))
            ->filter(fn ($nameserver) => preg_match('/^[a-z0-9][a-z0-9.-]*[a-z0-9]$/', $nameserver))
            ->unique()
            ->values()
            ->all();
    }

    protected function normalizeDomain(string $domain): string
    {
        return strtolower(trim($domain, ". \t\n\r\0\x0B"));
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone) ?: '+255000000000';
        return str_starts_with($phone, '+') ? $phone : '+' . ltrim($phone, '0');
    }

    protected function xml(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    protected function sanitizeXml(string $xml): string
    {
        return preg_replace('/<(pw|domain:pw|contact:pw)>(.*?)<\/(pw|domain:pw|contact:pw)>/i', '<$1>***</$3>', $xml);
    }

    public function disconnect(): void
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }

        $this->socket = null;
        $this->connected = false;
        $this->loggedIn = false;
    }

    public function __destruct()
    {
        $this->logout();
    }
}
