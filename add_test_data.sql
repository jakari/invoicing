INSERT INTO customer (id, name, street_name, post_code, city, email) VALUES (1, 'Yritys Oy', 'Teollisuuskatu 1', '00001', 'Helsinki', 'info@yritys.fi');
INSERT INTO customer (id, name, street_name, post_code, city, email) VALUES (2, 'Oy Firma Ab', 'Mekaanikonkatu 2', '00002', 'Rovaniemi', 'laskutus@firma.net');

INSERT INTO invoice (id, invoice_number, reference_number, created, due, customer, status) VALUES
  (1, 1000, 101023, '2016-04-04', '2016-04-30', 1, 'PENDING');
INSERT INTO invoice (id, invoice_number, reference_number, created, due, customer, status) VALUES
  (2, 1001, 101035, '2016-04-05', '2016-04-20', 2, 'PAID');

INSERT INTO item (invoice, description, amount, tax, price) VALUES (1, 'Vasaroita', 2, 24, 1000);
INSERT INTO item (invoice, description, amount, tax, price) VALUES (1, 'Nauloja', 1000, 24, 1);
INSERT INTO item (invoice, description, amount, tax, price) VALUES (2, 'Kakkosnelonen 1m', 2, 24, 1500);
INSERT INTO item (invoice, description, amount, tax, price) VALUES (2, 'Maalia 10L', 1, 24, 8500);
