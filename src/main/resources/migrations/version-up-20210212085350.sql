ALTER TABLE customer ADD additional_name VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE customer ADD contact_person VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE invoice ADD customer_additional_name VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE invoice ADD customer_contact_person VARCHAR(255) NOT NULL DEFAULT '';
