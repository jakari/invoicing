--
-- PostgreSQL database dump
--

-- Dumped from database version 10.3 (Debian 10.3-1.pgdg90+1)
-- Dumped by pg_dump version 10.3 (Debian 10.3-1.pgdg90+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- Name: invoice_status; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.invoice_status AS ENUM (
    'PENDING',
    'PAID'
);


SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: company; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.company (
    id integer NOT NULL,
    name character varying(255) NOT NULL
);


--
-- Name: company_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.company_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: company_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.company_id_seq OWNED BY public.company.id;


--
-- Name: customer; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.customer (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    street_name character varying(255) NOT NULL,
    post_code character varying(10) NOT NULL,
    city character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    company integer NOT NULL,
    vat character varying(255) DEFAULT NULL::character varying,
    phone character varying(255) DEFAULT NULL::character varying,
    additional_name character varying(255) DEFAULT ''::character varying NOT NULL,
    contact_person character varying(255) DEFAULT ''::character varying NOT NULL
);


--
-- Name: customer_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.customer_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: customer_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.customer_id_seq OWNED BY public.customer.id;


--
-- Name: invoice; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.invoice (
    id integer NOT NULL,
    customer integer NOT NULL,
    reference_number integer NOT NULL,
    created date NOT NULL,
    due date NOT NULL,
    status public.invoice_status NOT NULL,
    remarking_time integer NOT NULL,
    interest_on_arrears integer NOT NULL,
    customer_reference character varying(255) DEFAULT NULL::character varying,
    delivery character varying(255) DEFAULT NULL::character varying,
    conditions_of_payment character varying(255) DEFAULT NULL::character varying,
    template character varying(255) DEFAULT NULL::character varying NOT NULL,
    invoice_number integer NOT NULL,
    company integer NOT NULL,
    customer_name character varying(255) NOT NULL,
    customer_street_name character varying(255) NOT NULL,
    customer_post_code character varying(255) NOT NULL,
    customer_city character varying(255) NOT NULL,
    customer_vat character varying(255) DEFAULT NULL::character varying,
    customer_email character varying(255) NOT NULL,
    customer_phone character varying(255) DEFAULT NULL::character varying,
    customer_additional_name character varying(255) DEFAULT ''::character varying NOT NULL,
    customer_contact_person character varying(255) DEFAULT ''::character varying NOT NULL
);


--
-- Name: COLUMN invoice.status; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.invoice.status IS '(DC2Type:invoice_status)';


--
-- Name: invoice_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.invoice_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: invoice_item; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.invoice_item (
    id integer NOT NULL,
    invoice integer NOT NULL,
    description character varying(255) NOT NULL,
    amount integer NOT NULL,
    tax numeric(4,2) NOT NULL,
    price integer NOT NULL
);


--
-- Name: invoice_item_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.invoice_item_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: invoice_item_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.invoice_item_id_seq OWNED BY public.invoice_item.id;


--
-- Name: migration_versions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migration_versions (
    version character varying(255) NOT NULL
);


--
-- Name: parameters; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.parameters (
    id integer NOT NULL,
    company integer NOT NULL,
    key character varying(255) NOT NULL,
    value text
);


--
-- Name: parameters_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.parameters_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: parameters_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.parameters_id_seq OWNED BY public.parameters.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id integer NOT NULL,
    username character varying(255) NOT NULL,
    password character varying(255) NOT NULL
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: company id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.company ALTER COLUMN id SET DEFAULT nextval('public.company_id_seq'::regclass);


--
-- Name: customer id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.customer ALTER COLUMN id SET DEFAULT nextval('public.customer_id_seq'::regclass);


--
-- Name: invoice_item id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invoice_item ALTER COLUMN id SET DEFAULT nextval('public.invoice_item_id_seq'::regclass);


--
-- Name: parameters id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.parameters ALTER COLUMN id SET DEFAULT nextval('public.parameters_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: company company_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.company
    ADD CONSTRAINT company_pkey PRIMARY KEY (id);


--
-- Name: customer customer_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.customer
    ADD CONSTRAINT customer_pkey PRIMARY KEY (id);


--
-- Name: invoice_item invoice_item_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invoice_item
    ADD CONSTRAINT invoice_item_pkey PRIMARY KEY (id);


--
-- Name: invoice invoice_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invoice
    ADD CONSTRAINT invoice_pkey PRIMARY KEY (id);


--
-- Name: migration_versions migration_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migration_versions
    ADD CONSTRAINT migration_versions_pkey PRIMARY KEY (version);


--
-- Name: parameters parameters_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.parameters
    ADD CONSTRAINT parameters_pkey PRIMARY KEY (id);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: idx_1dde477b90651744; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_1dde477b90651744 ON public.invoice_item USING btree (invoice);


--
-- Name: idx_69348fe4fbf094f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_69348fe4fbf094f ON public.parameters USING btree (company);


--
-- Name: idx_81398e094fbf094f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_81398e094fbf094f ON public.customer USING btree (company);


--
-- Name: idx_906517444fbf094f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_906517444fbf094f ON public.invoice USING btree (company);


--
-- Name: idx_9065174481398e09; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_9065174481398e09 ON public.invoice USING btree (customer);


--
-- Name: invoice_company_invoice_number; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX invoice_company_invoice_number ON public.invoice USING btree (company, invoice_number);


--
-- Name: uniq_1483a5e9f85e0677; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uniq_1483a5e9f85e0677 ON public.users USING btree (username);


--
-- Name: invoice_item fk_1dde477b90651744; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invoice_item
    ADD CONSTRAINT fk_1dde477b90651744 FOREIGN KEY (invoice) REFERENCES public.invoice(id);


--
-- Name: parameters fk_69348fe4fbf094f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.parameters
    ADD CONSTRAINT fk_69348fe4fbf094f FOREIGN KEY (company) REFERENCES public.company(id);


--
-- Name: customer fk_81398e094fbf094f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.customer
    ADD CONSTRAINT fk_81398e094fbf094f FOREIGN KEY (company) REFERENCES public.company(id);


--
-- Name: invoice fk_906517444fbf094f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invoice
    ADD CONSTRAINT fk_906517444fbf094f FOREIGN KEY (company) REFERENCES public.company(id);


--
-- Name: invoice fk_9065174481398e09; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invoice
    ADD CONSTRAINT fk_9065174481398e09 FOREIGN KEY (customer) REFERENCES public.customer(id);


--
-- PostgreSQL database dump complete
--

