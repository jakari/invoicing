-- Doctrine migration Version20261022120000
CREATE UNIQUE INDEX invoice_company_invoice_number ON invoice (company, invoice_number);
