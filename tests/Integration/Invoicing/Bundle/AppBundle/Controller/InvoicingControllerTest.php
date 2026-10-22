<?php

namespace Invoicing\Tests\Integration\Invoicing\Bundle\AppBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Invoicing\Tests\ApiTestCase;

class InvoicingControllerTest extends ApiTestCase
{
    /**
     * Company B's invoice 1 gets invoice.id 1 and company A's invoice 1 gets invoice.id 2,
     * so the two companies' invoice numbers overlap and id differs from invoice_number.
     */
    private function createOverlappingInvoices()
    {
        $this->selectCompany($this->companyB);
        $this->createInvoice([
            'customer' => $this->customerInput(['name' => 'B Customer']),
            'items' => [['description' => 'B work', 'amount' => 10, 'price' => 1000, 'tax' => 0]],
        ]);

        $this->selectCompany($this->companyA);
        $this->createInvoice([
            'customer' => $this->customerInput(['name' => 'A Customer']),
            'items' => [
                ['description' => 'A work', 'amount' => 1, 'price' => 0.02, 'tax' => 25.5],
                ['description' => 'A work', 'amount' => 1, 'price' => 0.02, 'tax' => 25.5],
            ],
        ]);
    }

    /** @test */
    public function createsInvoiceAndCustomer()
    {
        $this->selectCompany($this->companyA);
        $this->assertEquals(1, $this->createInvoice());

        $invoice = $this->json($this->request('GET', '/api/invoice/1'));
        $this->assertEquals('Asiakas Oy', $invoice['customer']['name']);
        $this->assertEquals(1009, $invoice['referenceNumber']);
        $this->assertEquals(100, $invoice['items'][0]['price']);
    }

    /**
     * B1: the list joined items on invoice_number instead of invoice.id.
     * B7: the list total was the unrounded SQL sum truncated to cents; it now rounds each item's
     * tax like InvoiceCalculator (and the printed invoice): 2 × (2 + round(0.51)) = 6 cents.
     *
     * @test
     */
    public function listsTotalsOfTheInvoicesOwnItems()
    {
        $this->createOverlappingInvoices();

        $invoices = $this->json($this->request('GET', '/api/invoices'));

        $this->assertCount(1, $invoices);
        $this->assertEquals('A Customer', $invoices[0]['customer']);
        $this->assertEquals(0.06, $invoices[0]['total']);
    }

    /** @test B2 */
    public function getsTheSelectedCompanysInvoice()
    {
        $this->createOverlappingInvoices();

        $invoice = $this->json($this->request('GET', '/api/invoice/1'));
        $this->assertEquals('A Customer', $invoice['customer']['name']);

        $html = $this->request('GET', '/api/invoice/1/print_html')->getContent();
        $this->assertContains('A Customer', $html);
        $this->assertNotContains('B Customer', $html);
    }

    /** @test B2 */
    public function updatesOnlyTheSelectedCompanysInvoice()
    {
        $this->createOverlappingInvoices();

        $this->assertStatus(204, $this->request('PATCH', '/api/invoice/1', $this->invoiceInput([
            'customer' => $this->customerInput(['name' => 'A Customer']),
            'delivery' => 'Updated',
        ])));

        $deliveries = $this->connection()
            ->fetchAll('SELECT company, delivery FROM invoice ORDER BY company');
        $this->assertEquals([
            ['company' => $this->companyA, 'delivery' => 'Updated'],
            ['company' => $this->companyB, 'delivery' => null],
        ], $deliveries);
    }

    /** @test B2 */
    public function anotherCompanysInvoiceIsNotFound()
    {
        $this->selectCompany($this->companyB);
        $this->createInvoice();
        $this->selectCompany($this->companyA);

        $this->assertStatus(404, $this->request('GET', '/api/invoice/1'));
        $this->assertStatus(404, $this->request('GET', '/api/invoice/1/print_html'));
        $this->assertStatus(404, $this->request('PATCH', '/api/invoice/1', $this->invoiceInput()));
        $this->assertStatus(404, $this->request('DELETE', '/api/invoice/1'));
    }

    /** @test B3 */
    public function deletesInvoiceWithItemsOnlyFromTheSelectedCompany()
    {
        $this->createOverlappingInvoices();

        $this->assertStatus(200, $this->request('DELETE', '/api/invoice/1'));

        $this->assertEquals(
            [['company' => $this->companyB]],
            $this->connection()->fetchAll('SELECT company FROM invoice')
        );
        $this->assertEquals(1, $this->connection()->fetchColumn('SELECT count(*) FROM invoice_item'));
    }

    /** @test B4 */
    public function anotherCompanysCustomerIsNotFound()
    {
        $this->selectCompany($this->companyB);
        $this->createInvoice(['customer' => $this->customerInput(['name' => 'B Customer'])]);
        $customerB = (int) $this->connection()->fetchColumn('SELECT id FROM customer');

        $this->selectCompany($this->companyA);

        $this->assertStatus(404, $this->request('GET', "/api/customer/$customerB"));
        $this->assertStatus(404, $this->request(
            'PATCH',
            "/api/customer/$customerB",
            $this->customerInput(['id' => $customerB, 'name' => 'Hijacked'])
        ));
        $this->assertStatus(404, $this->request('POST', '/api/invoice', $this->invoiceInput([
            'customer' => $this->customerInput(['id' => $customerB, 'name' => 'Hijacked']),
        ])));

        $this->assertEquals('B Customer', $this->connection()->fetchColumn('SELECT name FROM customer'));
        $this->assertEquals(1, $this->connection()->fetchColumn('SELECT count(*) FROM invoice'));
    }

    /**
     * B5: the bank transfer slip printed the customer's current details instead of the ones
     * saved on the invoice.
     *
     * @test
     */
    public function printsTheCustomerDetailsSavedOnTheInvoice()
    {
        $this->selectCompany($this->companyA);
        $this->createInvoice(['template' => 'finnish']);
        $customer = (int) $this->connection()->fetchColumn('SELECT id FROM customer');

        $this->assertStatus(204, $this->request('PATCH', "/api/customer/$customer", $this->customerInput([
            'id' => $customer,
            'name' => 'Renamed Oy',
            'streetName' => 'Uusi katu 2',
        ])));

        foreach (['finnish', 'english'] as $template) {
            $this->connection()->executeUpdate('UPDATE invoice SET template = ?', [$template]);
            $html = $this->request('GET', '/api/invoice/1/print_html')->getContent();

            $this->assertContains('Asiakas Oy', $html, $template);
            $this->assertNotContains('Renamed Oy', $html, $template);
            $this->assertNotContains('Uusi katu 2', $html, $template);
        }
    }

    /** @test B6 */
    public function invoiceNumbersAreUniquePerCompany()
    {
        $this->selectCompany($this->companyA);
        $this->createInvoice();
        $customer = (int) $this->connection()->fetchColumn('SELECT id FROM customer');

        $this->expectException(UniqueConstraintViolationException::class);
        $this->connection()->executeUpdate(
            "INSERT INTO invoice (id, company, invoice_number, reference_number, created, due, status, customer,
                                  remarking_time, interest_on_arrears, template, customer_name, customer_street_name,
                                  customer_post_code, customer_city, customer_email)
             VALUES (nextval('invoice_id_seq'), ?, 1, 1001, now(), now(), 'PENDING', ?, 14, 18, 'finnish', '', '', '', '', '')",
            [$this->companyA, $customer]
        );
    }

    /** @test B8 */
    public function usesTheDefaultSettingsWithoutParameters()
    {
        $this->connection()->exec('DELETE FROM parameters');
        $this->selectCompany($this->companyA);

        $this->assertEquals(1, $this->createInvoice());
        $this->assertEquals(1009, $this->json($this->request('GET', '/api/invoice/1'))['referenceNumber']);
    }

    /** @test B9 */
    public function selectingAnUnknownCompanyIsNotFound()
    {
        $this->assertStatus(404, $this->request('POST', '/api/select-company/999'));
        $this->assertStatus(200, $this->request('GET', '/api/invoice/settings'));
    }
}
