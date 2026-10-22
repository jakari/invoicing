<?php

namespace Invoicing\Tests;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs requests against the API with a logged in user (http_basic in the test firewall) and
 * fixtures for two companies. Uses the database in DATABASE_NAME (phpunit.xml.dist).
 */
abstract class ApiTestCase extends WebTestCase
{
    const USERNAME = 'tester';
    const PASSWORD = 'secret';

    /** @var Client */
    protected $client;

    /** @var int */
    protected $companyA;

    /** @var int */
    protected $companyB;

    protected function setUp()
    {
        $this->client = static::createClient([], [
            'PHP_AUTH_USER' => self::USERNAME,
            'PHP_AUTH_PW' => self::PASSWORD,
        ]);
        $this->resetDatabase();
    }

    protected function connection(): Connection
    {
        return static::$kernel->getContainer()->get('doctrine.dbal.default_connection');
    }

    private function resetDatabase()
    {
        $conn = $this->connection();
        $conn->exec('TRUNCATE invoice_item, invoice, customer, parameters, company, users RESTART IDENTITY CASCADE');
        $conn->insert('users', [
            'username' => self::USERNAME,
            'password' => password_hash(self::PASSWORD, PASSWORD_BCRYPT, ['cost' => 4]),
        ]);

        $this->companyA = $this->insertCompany('Company A');
        $this->companyB = $this->insertCompany('Company B');

        foreach ([$this->companyA, $this->companyB] as $company) {
            $conn->insert('parameters', ['company' => $company, 'key' => 'initial_invoice_number', 'value' => '1']);
            $conn->insert('parameters', ['company' => $company, 'key' => 'initial_reference_number', 'value' => '100']);
        }
    }

    protected function insertCompany(string $name): int
    {
        $this->connection()->insert('company', ['name' => $name]);

        return (int) $this->connection()->lastInsertId('company_id_seq');
    }

    protected function selectCompany(int $company)
    {
        $this->assertStatus(204, $this->request('POST', "/api/select-company/$company"));
    }

    protected function request(string $method, string $uri, $json = null): Response
    {
        $this->client->request(
            $method,
            $uri,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $json === null ? null : json_encode($json)
        );

        return $this->client->getResponse();
    }

    protected function json(Response $response)
    {
        return json_decode($response->getContent(), true);
    }

    protected function assertStatus(int $expected, Response $response)
    {
        $this->assertEquals($expected, $response->getStatusCode(), $response->getContent());
    }

    protected function customerInput(array $overrides = []): array
    {
        return array_merge([
            'id' => 0,
            'name' => 'Asiakas Oy',
            'additionalName' => '',
            'contactPerson' => '',
            'streetName' => 'Katu 1',
            'postCode' => '00100',
            'city' => 'Helsinki',
            'email' => 'asiakas@example.com',
            'vat' => '',
            'phone' => '',
        ], $overrides);
    }

    protected function invoiceInput(array $overrides = []): array
    {
        return array_merge([
            'created' => '2026-01-01',
            'due' => '2026-01-15',
            'customer' => $this->customerInput(),
            'items' => [
                ['description' => 'Työ', 'amount' => 1, 'price' => 100, 'tax' => 25.5],
            ],
            'remarkingTime' => 14,
            'interestOnArrears' => 18,
            'template' => 'finnish',
        ], $overrides);
    }

    /** Creates an invoice in the currently selected company and returns its invoice number. */
    protected function createInvoice(array $overrides = []): int
    {
        $response = $this->request('POST', '/api/invoice', $this->invoiceInput($overrides));
        $this->assertStatus(200, $response);

        return $this->json($response)['invoiceNumber'];
    }
}
