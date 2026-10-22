-- Doctrine migration Version20200315201311
ALTER TABLE customer ADD vat VARCHAR(255) DEFAULT NULL;
ALTER TABLE customer ADD phone VARCHAR(255) DEFAULT NULL;
ALTER TABLE invoice ADD customer_name VARCHAR(255) NOT NULL;
ALTER TABLE invoice ADD customer_street_name VARCHAR(255) NOT NULL;
ALTER TABLE invoice ADD customer_post_code VARCHAR(255) NOT NULL;
ALTER TABLE invoice ADD customer_city VARCHAR(255) NOT NULL;
ALTER TABLE invoice ADD customer_vat VARCHAR(255) DEFAULT NULL;
ALTER TABLE invoice ADD customer_email VARCHAR(255) NOT NULL;
ALTER TABLE invoice ADD customer_phone VARCHAR(255) DEFAULT NULL;
