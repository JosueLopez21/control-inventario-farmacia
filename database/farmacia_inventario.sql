--
-- PostgreSQL database dump
--

\restrict V9ZAhbYrW1KnOSYIQU9pbKsikNhYfYesKJfRefFfJUQ2iYJf8GjUAjhPnHEBjbk

-- Dumped from database version 18.6
-- Dumped by pg_dump version 18.6

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: categorias; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.categorias (
    id bigint NOT NULL,
    nombre character varying(255) NOT NULL,
    descripcion text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.categorias OWNER TO postgres;

--
-- Name: categorias_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.categorias_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.categorias_id_seq OWNER TO postgres;

--
-- Name: categorias_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.categorias_id_seq OWNED BY public.categorias.id;


--
-- Name: detalles_venta; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.detalles_venta (
    id bigint NOT NULL,
    venta_id bigint NOT NULL,
    lote_id bigint NOT NULL,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT chk_cantidad_positiva CHECK ((cantidad > 0)),
    CONSTRAINT chk_precio_unitario_positivo CHECK ((precio_unitario > (0)::numeric))
);


ALTER TABLE public.detalles_venta OWNER TO postgres;

--
-- Name: detalles_venta_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.detalles_venta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.detalles_venta_id_seq OWNER TO postgres;

--
-- Name: detalles_venta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.detalles_venta_id_seq OWNED BY public.detalles_venta.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection character varying(255) NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO postgres;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: laboratorios; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.laboratorios (
    id bigint NOT NULL,
    nombre character varying(255) NOT NULL,
    pais character varying(255) NOT NULL,
    direccion character varying(255),
    telefono character varying(255),
    email character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.laboratorios OWNER TO postgres;

--
-- Name: laboratorios_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.laboratorios_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.laboratorios_id_seq OWNER TO postgres;

--
-- Name: laboratorios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.laboratorios_id_seq OWNED BY public.laboratorios.id;


--
-- Name: lotes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lotes (
    id bigint NOT NULL,
    medicamento_id bigint NOT NULL,
    numero_lote character varying(255) NOT NULL,
    fecha_vencimiento date NOT NULL,
    cantidad_stock integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT chk_cantidad_stock_no_negativo CHECK ((cantidad_stock >= 0))
);


ALTER TABLE public.lotes OWNER TO postgres;

--
-- Name: lotes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lotes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.lotes_id_seq OWNER TO postgres;

--
-- Name: lotes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lotes_id_seq OWNED BY public.lotes.id;


--
-- Name: medicamentos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.medicamentos (
    id bigint NOT NULL,
    categoria_id bigint NOT NULL,
    laboratorio_id bigint NOT NULL,
    nombre_comercial character varying(255) NOT NULL,
    principio_activo character varying(255) NOT NULL,
    presentacion character varying(255) NOT NULL,
    precio_venta numeric(10,2) NOT NULL,
    stock_minimo integer DEFAULT 10 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT chk_precio_venta_positivo CHECK ((precio_venta > (0)::numeric)),
    CONSTRAINT chk_stock_minimo_no_negativo CHECK ((stock_minimo >= 0))
);


ALTER TABLE public.medicamentos OWNER TO postgres;

--
-- Name: medicamentos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.medicamentos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.medicamentos_id_seq OWNER TO postgres;

--
-- Name: medicamentos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.medicamentos_id_seq OWNED BY public.medicamentos.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: ventas; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ventas (
    id bigint NOT NULL,
    fecha_venta timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    monto_total numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT chk_monto_total_no_negativo CHECK ((monto_total >= (0)::numeric))
);


ALTER TABLE public.ventas OWNER TO postgres;

--
-- Name: ventas_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ventas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ventas_id_seq OWNER TO postgres;

--
-- Name: ventas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ventas_id_seq OWNED BY public.ventas.id;


--
-- Name: categorias id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categorias ALTER COLUMN id SET DEFAULT nextval('public.categorias_id_seq'::regclass);


--
-- Name: detalles_venta id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.detalles_venta ALTER COLUMN id SET DEFAULT nextval('public.detalles_venta_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: laboratorios id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.laboratorios ALTER COLUMN id SET DEFAULT nextval('public.laboratorios_id_seq'::regclass);


--
-- Name: lotes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lotes ALTER COLUMN id SET DEFAULT nextval('public.lotes_id_seq'::regclass);


--
-- Name: medicamentos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.medicamentos ALTER COLUMN id SET DEFAULT nextval('public.medicamentos_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: ventas id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ventas ALTER COLUMN id SET DEFAULT nextval('public.ventas_id_seq'::regclass);


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: categorias; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.categorias (id, nombre, descripcion, created_at, updated_at) FROM stdin;
4	Analgésicos	Medicamentos para aliviar el dolor, como paracetamol e ibuprofeno.	2026-10-18 21:30:06	2026-10-18 21:30:06
5	Antibióticos	Medicamentos para combatir infecciones bacterianas.	2026-10-18 21:30:06	2026-10-18 21:30:06
6	Antiinflamatorios	Medicamentos que reducen la inflamación y la hinchazón.	2026-10-18 21:30:06	2026-10-18 21:30:06
7	Antihipertensivos	Medicamentos para controlar la presión arterial alta.	2026-10-18 21:30:06	2026-10-18 21:30:06
8	Vitaminas y Suplementos	Suplementos nutricionales y complejos vitamínicos.	2026-10-18 21:30:06	2026-10-18 21:30:06
9	Antihistamínicos	Medicamentos para tratar alergias y reacciones alérgicas.	2026-10-18 21:30:06	2026-10-18 21:30:06
10	Antidiabéticos	Medicamentos para el control de la glucosa en sangre.	2026-10-18 21:30:06	2026-10-18 21:30:06
11	Gastrointestinales	Medicamentos para trastornos digestivos y estomacales.	2026-10-18 21:30:06	2026-10-18 21:30:06
12	Dermatológicos	Medicamentos de uso tópico para afecciones de la piel.	2026-10-18 21:30:06	2026-10-18 21:30:06
13	Broncodilatadores	Medicamentos para enfermedades respiratorias y asma.	2026-10-18 21:30:06	2026-10-18 21:30:06
\.


--
-- Data for Name: detalles_venta; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.detalles_venta (id, venta_id, lote_id, cantidad, precio_unitario, created_at, updated_at) FROM stdin;
3	2	111	3	9.00	2026-10-18 21:30:07	2026-10-18 21:30:07
4	2	20	3	25.00	2026-10-18 21:30:07	2026-10-18 21:30:07
5	3	12	1	12.00	2026-10-18 21:30:07	2026-10-18 21:30:07
6	3	95	3	8.50	2026-10-18 21:30:07	2026-10-18 21:30:07
7	3	120	1	16.00	2026-10-18 21:30:07	2026-10-18 21:30:07
8	3	64	2	45.00	2026-10-18 21:30:07	2026-10-18 21:30:07
9	4	11	2	12.00	2026-10-18 21:30:07	2026-10-18 21:30:07
10	4	116	4	28.00	2026-10-18 21:30:07	2026-10-18 21:30:07
11	4	61	3	10.00	2026-10-18 21:30:07	2026-10-18 21:30:07
12	5	29	1	22.00	2026-10-18 21:30:07	2026-10-18 21:30:07
13	5	108	3	11.00	2026-10-18 21:30:07	2026-10-18 21:30:07
14	6	13	1	12.00	2026-10-18 21:30:07	2026-10-18 21:30:07
15	6	95	2	8.50	2026-10-18 21:30:07	2026-10-18 21:30:07
16	6	43	3	11.00	2026-10-18 21:30:07	2026-10-18 21:30:07
17	7	111	3	9.00	2026-10-18 21:30:07	2026-10-18 21:30:07
18	7	76	5	6.00	2026-10-18 21:30:07	2026-10-18 21:30:07
19	7	103	4	35.00	2026-10-18 21:30:07	2026-10-18 21:30:07
20	8	68	2	12.00	2026-10-18 21:30:07	2026-10-18 21:30:07
21	8	32	4	35.00	2026-10-18 21:30:07	2026-10-18 21:30:07
22	8	47	5	15.00	2026-10-18 21:30:07	2026-10-18 21:30:07
23	9	59	5	10.00	2026-10-18 21:30:07	2026-10-18 21:30:07
24	9	117	1	28.00	2026-10-18 21:30:07	2026-10-18 21:30:07
25	9	68	5	12.00	2026-10-18 21:30:07	2026-10-18 21:30:07
26	9	91	2	6.50	2026-10-18 21:30:07	2026-10-18 21:30:07
27	9	40	2	9.00	2026-10-18 21:30:07	2026-10-18 21:30:07
28	10	51	1	12.50	2026-10-18 21:30:07	2026-10-18 21:30:07
29	11	7	1	8.50	2026-10-18 21:30:07	2026-10-18 21:30:07
30	11	51	5	12.50	2026-10-18 21:30:07	2026-10-18 21:30:07
31	11	123	3	14.00	2026-10-18 21:30:07	2026-10-18 21:30:07
32	12	105	2	35.00	2026-10-18 21:30:07	2026-10-18 21:30:07
33	12	32	4	35.00	2026-10-18 21:30:07	2026-10-18 21:30:07
34	12	31	5	35.00	2026-10-18 21:30:07	2026-10-18 21:30:07
35	13	115	4	28.00	2026-10-18 21:30:07	2026-10-18 21:30:07
36	13	63	2	45.00	2026-10-18 21:30:07	2026-10-18 21:30:07
37	13	72	5	5.50	2026-10-18 21:30:07	2026-10-18 21:30:07
38	13	125	5	14.00	2026-10-18 21:30:07	2026-10-18 21:30:07
39	13	49	1	15.00	2026-10-18 21:30:07	2026-10-18 21:30:07
40	14	12	1	12.00	2026-10-18 21:30:07	2026-10-18 21:30:07
41	14	48	5	15.00	2026-10-18 21:30:07	2026-10-18 21:30:07
42	15	49	2	15.00	2026-10-18 21:30:07	2026-10-18 21:30:07
43	16	91	1	6.50	2026-10-18 21:30:07	2026-10-18 21:30:07
44	16	107	1	11.00	2026-10-18 21:30:07	2026-10-18 21:30:07
45	16	52	3	12.50	2026-10-18 21:30:07	2026-10-18 21:30:07
46	17	125	2	14.00	2026-10-18 21:30:07	2026-10-18 21:30:07
47	18	60	5	10.00	2026-10-18 21:30:07	2026-10-18 21:30:07
48	19	48	5	15.00	2026-10-18 21:30:07	2026-10-18 21:30:07
49	19	67	4	12.00	2026-10-18 21:30:07	2026-10-18 21:30:07
50	20	91	4	6.50	2026-10-18 21:30:07	2026-10-18 21:30:07
51	20	92	5	6.50	2026-10-18 21:30:07	2026-10-18 21:30:07
52	20	123	2	14.00	2026-10-18 21:30:07	2026-10-18 21:30:07
53	20	97	1	8.50	2026-10-18 21:30:07	2026-10-18 21:30:07
54	20	79	3	8.00	2026-10-18 21:30:07	2026-10-18 21:30:07
55	21	120	5	16.00	2026-10-18 21:30:07	2026-10-18 21:30:07
56	21	123	3	14.00	2026-10-18 21:30:07	2026-10-18 21:30:07
57	21	40	2	9.00	2026-10-18 21:30:07	2026-10-18 21:30:07
58	21	63	1	45.00	2026-10-18 21:30:07	2026-10-18 21:30:07
59	21	17	2	6.50	2026-10-18 21:30:07	2026-10-18 21:30:07
60	22	15	2	6.50	2026-10-18 21:30:07	2026-10-18 21:30:07
61	22	41	3	9.00	2026-10-18 21:30:07	2026-10-18 21:30:07
62	23	51	5	12.50	2026-10-18 21:30:07	2026-10-18 21:30:07
63	23	120	1	16.00	2026-10-18 21:30:07	2026-10-18 21:30:07
64	24	41	3	9.00	2026-10-18 21:30:07	2026-10-18 21:30:07
65	25	120	5	16.00	2026-10-18 21:30:07	2026-10-18 21:30:07
66	25	24	1	18.50	2026-10-18 21:30:07	2026-10-18 21:30:07
67	25	21	3	25.00	2026-10-18 21:30:07	2026-10-18 21:30:07
68	25	49	3	15.00	2026-10-18 21:30:07	2026-10-18 21:30:07
69	25	100	4	10.00	2026-10-18 21:30:07	2026-10-18 21:30:07
70	26	39	3	9.00	2026-10-18 21:30:07	2026-10-18 21:30:07
71	26	100	5	10.00	2026-10-18 21:30:07	2026-10-18 21:30:07
72	26	31	2	35.00	2026-10-18 21:30:07	2026-10-18 21:30:07
73	26	55	5	14.00	2026-10-18 21:30:07	2026-10-18 21:30:07
74	26	73	4	5.50	2026-10-18 21:30:07	2026-10-18 21:30:07
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: laboratorios; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.laboratorios (id, nombre, pais, direccion, telefono, email, created_at, updated_at) FROM stdin;
4	Pfizer	Estados Unidos	\N	\N	contacto@pfizer.com	2026-10-18 21:30:06	2026-10-18 21:30:06
5	Bayer	Alemania	\N	\N	contacto@bayer.com	2026-10-18 21:30:06	2026-10-18 21:30:06
6	Roche	Suiza	\N	\N	contacto@roche.com	2026-10-18 21:30:06	2026-10-18 21:30:06
7	Novartis	Suiza	\N	\N	contacto@novartis.com	2026-10-18 21:30:06	2026-10-18 21:30:06
8	Sanofi	Francia	\N	\N	contacto@sanofi.com	2026-10-18 21:30:06	2026-10-18 21:30:06
9	Johnson & Johnson	Estados Unidos	\N	\N	contacto@jnj.com	2026-10-18 21:30:06	2026-10-18 21:30:06
10	AstraZeneca	Reino Unido	\N	\N	contacto@astrazeneca.com	2026-10-18 21:30:06	2026-10-18 21:30:06
11	GlaxoSmithKline	Reino Unido	\N	\N	contacto@gsk.com	2026-10-18 21:30:06	2026-10-18 21:30:06
12	Abbott Laboratories	Estados Unidos	\N	\N	contacto@abbott.com	2026-10-18 21:30:06	2026-10-18 21:30:06
13	Genfar	Colombia	\N	\N	contacto@genfar.com	2026-10-18 21:30:06	2026-10-18 21:30:06
14	MK (Tecnoquímicas)	Colombia	\N	\N	contacto@tecnoquimicas.com	2026-10-18 21:30:06	2026-10-18 21:30:06
15	Laboratorios Bagó	Argentina	\N	\N	contacto@bago.com	2026-10-18 21:30:06	2026-10-18 21:30:06
\.


--
-- Data for Name: lotes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.lotes (id, medicamento_id, numero_lote, fecha_vencimiento, cantidad_stock, created_at, updated_at) FROM stdin;
9	4	LOT-00003	2026-11-08	1	2026-10-18 21:30:06	2026-10-18 21:30:06
41	12	LOT-00035	2026-11-09	13	2026-10-18 21:30:06	2026-10-18 21:30:07
10	4	LOT-00004	2026-08-12	6	2026-10-18 21:30:06	2026-10-18 21:30:06
14	5	LOT-00008	2026-06-01	6	2026-10-18 21:30:06	2026-10-18 21:30:06
18	6	LOT-00012	2026-08-18	5	2026-10-18 21:30:06	2026-10-18 21:30:06
22	7	LOT-00016	2026-09-23	14	2026-10-18 21:30:06	2026-10-18 21:30:06
26	8	LOT-00020	2026-05-05	12	2026-10-18 21:30:06	2026-10-18 21:30:06
27	9	LOT-00021	2027-04-18	70	2026-10-18 21:30:06	2026-10-18 21:30:06
28	9	LOT-00022	2028-02-18	91	2026-10-18 21:30:06	2026-10-18 21:30:06
30	9	LOT-00024	2026-05-06	16	2026-10-18 21:30:06	2026-10-18 21:30:06
33	10	LOT-00027	2026-11-04	25	2026-10-18 21:30:06	2026-10-18 21:30:06
34	10	LOT-00028	2026-09-28	20	2026-10-18 21:30:06	2026-10-18 21:30:06
35	11	LOT-00029	2027-06-18	123	2026-10-18 21:30:06	2026-10-18 21:30:06
36	11	LOT-00030	2027-11-18	110	2026-10-18 21:30:06	2026-10-18 21:30:06
37	11	LOT-00031	2026-11-11	16	2026-10-18 21:30:06	2026-10-18 21:30:06
38	11	LOT-00032	2026-10-16	17	2026-10-18 21:30:06	2026-10-18 21:30:06
42	12	LOT-00036	2026-09-24	13	2026-10-18 21:30:06	2026-10-18 21:30:06
44	13	LOT-00038	2027-02-18	144	2026-10-18 21:30:06	2026-10-18 21:30:06
45	13	LOT-00039	2026-10-28	26	2026-10-18 21:30:06	2026-10-18 21:30:06
46	13	LOT-00040	2026-06-20	15	2026-10-18 21:30:06	2026-10-18 21:30:06
50	14	LOT-00044	2026-05-25	11	2026-10-18 21:30:06	2026-10-18 21:30:06
53	15	LOT-00047	2026-10-30	7	2026-10-18 21:30:06	2026-10-18 21:30:06
54	15	LOT-00048	2026-08-07	16	2026-10-18 21:30:06	2026-10-18 21:30:06
56	16	LOT-00050	2027-10-18	92	2026-10-18 21:30:06	2026-10-18 21:30:06
57	16	LOT-00051	2026-10-27	14	2026-10-18 21:30:06	2026-10-18 21:30:06
58	16	LOT-00052	2026-06-04	17	2026-10-18 21:30:06	2026-10-18 21:30:06
62	17	LOT-00056	2026-07-30	3	2026-10-18 21:30:06	2026-10-18 21:30:06
65	18	LOT-00059	2026-10-24	5	2026-10-18 21:30:06	2026-10-18 21:30:06
66	18	LOT-00060	2026-04-21	14	2026-10-18 21:30:06	2026-10-18 21:30:06
69	19	LOT-00063	2026-11-03	28	2026-10-18 21:30:06	2026-10-18 21:30:06
70	19	LOT-00064	2026-07-08	18	2026-10-18 21:30:06	2026-10-18 21:30:06
71	20	LOT-00065	2027-06-18	40	2026-10-18 21:30:06	2026-10-18 21:30:06
74	20	LOT-00068	2026-07-28	17	2026-10-18 21:30:06	2026-10-18 21:30:06
75	21	LOT-00069	2027-11-18	40	2026-10-18 21:30:06	2026-10-18 21:30:06
77	21	LOT-00071	2026-11-07	22	2026-10-18 21:30:06	2026-10-18 21:30:06
78	21	LOT-00072	2026-08-31	18	2026-10-18 21:30:06	2026-10-18 21:30:06
80	22	LOT-00074	2028-01-18	116	2026-10-18 21:30:06	2026-10-18 21:30:06
81	22	LOT-00075	2026-11-16	29	2026-10-18 21:30:06	2026-10-18 21:30:06
82	22	LOT-00076	2026-04-21	1	2026-10-18 21:30:06	2026-10-18 21:30:06
83	23	LOT-00077	2028-04-18	75	2026-10-18 21:30:06	2026-10-18 21:30:06
84	23	LOT-00078	2027-03-18	117	2026-10-18 21:30:06	2026-10-18 21:30:06
85	23	LOT-00079	2026-11-05	11	2026-10-18 21:30:06	2026-10-18 21:30:06
86	23	LOT-00080	2026-05-22	10	2026-10-18 21:30:06	2026-10-18 21:30:06
87	24	LOT-00081	2027-11-18	38	2026-10-18 21:30:06	2026-10-18 21:30:06
88	24	LOT-00082	2028-04-18	71	2026-10-18 21:30:06	2026-10-18 21:30:06
89	24	LOT-00083	2026-11-07	29	2026-10-18 21:30:06	2026-10-18 21:30:06
90	24	LOT-00084	2026-05-15	8	2026-10-18 21:30:06	2026-10-18 21:30:06
93	25	LOT-00087	2026-11-10	11	2026-10-18 21:30:06	2026-10-18 21:30:06
94	25	LOT-00088	2026-05-16	3	2026-10-18 21:30:06	2026-10-18 21:30:06
8	4	LOT-00002	2027-06-18	1	2026-10-18 21:30:06	2026-10-18 21:30:06
95	26	LOT-00089	2027-12-18	94	2026-10-18 21:30:06	2026-10-18 21:30:07
61	17	LOT-00055	2026-11-10	15	2026-10-18 21:30:06	2026-10-18 21:30:07
43	13	LOT-00037	2028-03-18	67	2026-10-18 21:30:06	2026-10-18 21:30:07
15	6	LOT-00009	2028-02-18	0	2026-10-18 21:30:06	2026-10-18 21:30:07
16	6	LOT-00010	2027-05-18	2	2026-10-18 21:30:06	2026-10-18 21:30:06
24	8	LOT-00018	2027-01-18	1	2026-10-18 21:30:06	2026-10-18 21:30:07
49	14	LOT-00043	2026-11-08	5	2026-10-18 21:30:06	2026-10-18 21:30:07
67	19	LOT-00061	2028-03-18	31	2026-10-18 21:30:06	2026-10-18 21:30:07
19	7	LOT-00013	2028-04-18	3	2026-10-18 21:30:06	2026-10-18 21:30:06
25	8	LOT-00019	2026-10-26	2	2026-10-18 21:30:06	2026-10-18 21:30:06
21	7	LOT-00015	2026-11-07	0	2026-10-18 21:30:06	2026-10-18 21:30:07
23	8	LOT-00017	2027-10-18	2	2026-10-18 21:30:06	2026-10-18 21:30:06
20	7	LOT-00014	2027-11-18	0	2026-10-18 21:30:06	2026-10-18 21:30:07
64	18	LOT-00058	2027-06-18	64	2026-10-18 21:30:06	2026-10-18 21:30:07
11	5	LOT-00005	2027-10-18	0	2026-10-18 21:30:06	2026-10-18 21:30:07
29	9	LOT-00023	2026-11-13	6	2026-10-18 21:30:06	2026-10-18 21:30:07
13	5	LOT-00007	2026-10-25	1	2026-10-18 21:30:06	2026-10-18 21:30:07
76	21	LOT-00070	2027-01-18	136	2026-10-18 21:30:06	2026-10-18 21:30:07
52	15	LOT-00046	2027-07-18	81	2026-10-18 21:30:06	2026-10-18 21:30:07
55	16	LOT-00049	2027-12-18	46	2026-10-18 21:30:06	2026-10-18 21:30:07
47	14	LOT-00041	2027-12-18	53	2026-10-18 21:30:06	2026-10-18 21:30:07
59	17	LOT-00053	2028-01-18	96	2026-10-18 21:30:06	2026-10-18 21:30:07
68	19	LOT-00062	2028-01-18	86	2026-10-18 21:30:06	2026-10-18 21:30:07
63	18	LOT-00057	2027-02-18	135	2026-10-18 21:30:06	2026-10-18 21:30:07
32	10	LOT-00026	2028-02-18	75	2026-10-18 21:30:06	2026-10-18 21:30:07
7	4	LOT-00001	2027-03-18	0	2026-10-18 21:30:06	2026-10-18 21:30:07
17	6	LOT-00011	2026-10-25	0	2026-10-18 21:30:06	2026-10-18 21:30:07
72	20	LOT-00066	2027-06-18	58	2026-10-18 21:30:06	2026-10-18 21:30:07
92	25	LOT-00086	2028-03-18	116	2026-10-18 21:30:06	2026-10-18 21:30:07
12	5	LOT-00006	2028-02-18	0	2026-10-18 21:30:06	2026-10-18 21:30:07
39	12	LOT-00033	2027-11-18	64	2026-10-18 21:30:06	2026-10-18 21:30:07
60	17	LOT-00054	2027-07-18	128	2026-10-18 21:30:06	2026-10-18 21:30:07
48	14	LOT-00042	2027-11-18	76	2026-10-18 21:30:06	2026-10-18 21:30:07
91	25	LOT-00085	2027-06-18	53	2026-10-18 21:30:06	2026-10-18 21:30:07
79	22	LOT-00073	2027-04-18	59	2026-10-18 21:30:06	2026-10-18 21:30:07
40	12	LOT-00034	2027-11-18	46	2026-10-18 21:30:06	2026-10-18 21:30:07
51	15	LOT-00045	2027-02-18	81	2026-10-18 21:30:06	2026-10-18 21:30:07
31	10	LOT-00025	2028-03-18	32	2026-10-18 21:30:06	2026-10-18 21:30:07
73	20	LOT-00067	2026-10-28	1	2026-10-18 21:30:06	2026-10-18 21:30:07
96	26	LOT-00090	2027-08-18	74	2026-10-18 21:30:06	2026-10-18 21:30:06
98	26	LOT-00092	2026-06-07	4	2026-10-18 21:30:06	2026-10-18 21:30:06
99	27	LOT-00093	2028-01-18	143	2026-10-18 21:30:06	2026-10-18 21:30:06
101	27	LOT-00095	2026-10-22	15	2026-10-18 21:30:06	2026-10-18 21:30:06
102	27	LOT-00096	2026-07-19	14	2026-10-18 21:30:06	2026-10-18 21:30:06
104	28	LOT-00098	2027-02-18	51	2026-10-18 21:30:06	2026-10-18 21:30:06
106	28	LOT-00100	2026-10-06	20	2026-10-18 21:30:06	2026-10-18 21:30:06
109	29	LOT-00103	2026-10-21	18	2026-10-18 21:30:06	2026-10-18 21:30:06
110	29	LOT-00104	2026-08-25	7	2026-10-18 21:30:06	2026-10-18 21:30:06
112	30	LOT-00106	2028-01-18	146	2026-10-18 21:30:06	2026-10-18 21:30:06
113	30	LOT-00107	2026-11-08	5	2026-10-18 21:30:06	2026-10-18 21:30:06
114	30	LOT-00108	2026-08-17	2	2026-10-18 21:30:06	2026-10-18 21:30:06
118	31	LOT-00112	2026-05-03	5	2026-10-18 21:30:06	2026-10-18 21:30:06
119	32	LOT-00113	2027-05-18	116	2026-10-18 21:30:06	2026-10-18 21:30:06
121	32	LOT-00115	2026-10-20	28	2026-10-18 21:30:06	2026-10-18 21:30:06
122	32	LOT-00116	2026-09-25	15	2026-10-18 21:30:06	2026-10-18 21:30:06
124	33	LOT-00118	2028-03-18	94	2026-10-18 21:30:06	2026-10-18 21:30:06
126	33	LOT-00120	2026-07-19	1	2026-10-18 21:30:06	2026-10-18 21:30:06
116	31	LOT-00110	2027-03-18	81	2026-10-18 21:30:06	2026-10-18 21:30:07
108	29	LOT-00102	2028-03-18	134	2026-10-18 21:30:06	2026-10-18 21:30:07
111	30	LOT-00105	2028-03-18	89	2026-10-18 21:30:06	2026-10-18 21:30:07
103	28	LOT-00097	2028-03-18	134	2026-10-18 21:30:06	2026-10-18 21:30:07
117	31	LOT-00111	2026-11-08	8	2026-10-18 21:30:06	2026-10-18 21:30:07
105	28	LOT-00099	2026-11-09	18	2026-10-18 21:30:06	2026-10-18 21:30:07
115	31	LOT-00109	2027-03-18	44	2026-10-18 21:30:06	2026-10-18 21:30:07
107	29	LOT-00101	2027-02-18	43	2026-10-18 21:30:06	2026-10-18 21:30:07
125	33	LOT-00119	2026-11-06	7	2026-10-18 21:30:06	2026-10-18 21:30:07
97	26	LOT-00091	2026-10-20	26	2026-10-18 21:30:06	2026-10-18 21:30:07
123	33	LOT-00117	2027-06-18	112	2026-10-18 21:30:06	2026-10-18 21:30:07
120	32	LOT-00114	2027-04-18	68	2026-10-18 21:30:06	2026-10-18 21:30:07
100	27	LOT-00094	2027-09-18	68	2026-10-18 21:30:06	2026-10-18 21:30:07
\.


--
-- Data for Name: medicamentos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.medicamentos (id, categoria_id, laboratorio_id, nombre_comercial, principio_activo, presentacion, precio_venta, stock_minimo, created_at, updated_at) FROM stdin;
4	4	9	Panadol	Paracetamol	Tabletas 500mg x 20	8.50	15	2026-10-18 21:30:06	2026-10-18 21:30:06
5	4	4	Advil	Ibuprofeno	Cápsulas 400mg x 10	12.00	10	2026-10-18 21:30:06	2026-10-18 21:30:06
6	4	5	Aspirina	Ácido acetilsalicílico	Tabletas 500mg x 20	6.50	20	2026-10-18 21:30:06	2026-10-18 21:30:06
7	5	11	Amoxil	Amoxicilina	Cápsulas 500mg x 21	25.00	10	2026-10-18 21:30:06	2026-10-18 21:30:06
8	5	14	Azitromicina MK	Azitromicina	Tabletas 500mg x 3	18.50	8	2026-10-18 21:30:06	2026-10-18 21:30:06
9	5	13	Ciprofloxacino Genfar	Ciprofloxacino	Tabletas 500mg x 10	22.00	10	2026-10-18 21:30:06	2026-10-18 21:30:06
10	5	14	Claritromicina MK	Claritromicina	Tabletas 500mg x 14	35.00	8	2026-10-18 21:30:06	2026-10-18 21:30:06
11	6	13	Diclofenaco Genfar	Diclofenaco sódico	Tabletas 50mg x 20	7.50	15	2026-10-18 21:30:06	2026-10-18 21:30:06
12	6	14	Naproxeno MK	Naproxeno sódico	Tabletas 275mg x 10	9.00	12	2026-10-18 21:30:06	2026-10-18 21:30:06
13	6	13	Dexametasona Genfar	Dexametasona	Tabletas 0.5mg x 30	11.00	10	2026-10-18 21:30:06	2026-10-18 21:30:06
14	7	14	Losartán MK	Losartán potásico	Tabletas 50mg x 30	15.00	20	2026-10-18 21:30:06	2026-10-18 21:30:06
15	7	13	Enalapril Genfar	Enalapril maleato	Tabletas 20mg x 30	12.50	20	2026-10-18 21:30:06	2026-10-18 21:30:06
16	7	14	Amlodipino MK	Amlodipino besilato	Tabletas 5mg x 30	14.00	15	2026-10-18 21:30:06	2026-10-18 21:30:06
17	8	5	Vitamina C Bayer	Ácido ascórbico	Tabletas efervescentes 1g x 10	10.00	15	2026-10-18 21:30:06	2026-10-18 21:30:06
18	8	4	Centrum	Multivitamínico	Tabletas x 30	45.00	10	2026-10-18 21:30:06	2026-10-18 21:30:06
19	8	14	Complejo B MK	Complejo vitamínico B	Tabletas x 30	12.00	15	2026-10-18 21:30:06	2026-10-18 21:30:06
20	9	13	Loratadina Genfar	Loratadina	Tabletas 10mg x 10	5.50	10	2026-10-18 21:30:06	2026-10-18 21:30:06
21	9	14	Cetirizina MK	Cetirizina	Tabletas 10mg x 10	6.00	10	2026-10-18 21:30:06	2026-10-18 21:30:06
22	10	13	Metformina Genfar	Metformina clorhidrato	Tabletas 850mg x 30	8.00	25	2026-10-18 21:30:06	2026-10-18 21:30:06
23	11	14	Omeprazol MK	Omeprazol	Cápsulas 20mg x 14	9.50	15	2026-10-18 21:30:06	2026-10-18 21:30:06
24	11	14	Ranitidina MK	Ranitidina	Tabletas 150mg x 20	7.00	12	2026-10-18 21:30:06	2026-10-18 21:30:06
25	11	14	Metoclopramida MK	Metoclopramida	Tabletas 10mg x 20	6.50	10	2026-10-18 21:30:06	2026-10-18 21:30:06
26	12	13	Clotrimazol Genfar	Clotrimazol	Crema 1% x 20g	8.50	8	2026-10-18 21:30:06	2026-10-18 21:30:06
27	12	13	Hidrocortisona Genfar	Hidrocortisona	Crema 1% x 15g	10.00	8	2026-10-18 21:30:06	2026-10-18 21:30:06
28	13	14	Salbutamol MK	Salbutamol	Inhalador 100mcg x 200 dosis	35.00	10	2026-10-18 21:30:06	2026-10-18 21:30:06
29	4	13	Ibuprofeno Genfar	Ibuprofeno	Suspensión 100mg/5mL x 120mL	11.00	12	2026-10-18 21:30:06	2026-10-18 21:30:06
30	4	13	Acetaminofén Genfar	Acetaminofén	Jarabe 150mg/5mL x 120mL	9.00	15	2026-10-18 21:30:06	2026-10-18 21:30:06
31	8	15	Glucosamina Bagó	Glucosamina sulfato	Sobres 1500mg x 15	28.00	8	2026-10-18 21:30:06	2026-10-18 21:30:06
32	7	13	Simvastatina Genfar	Simvastatina	Tabletas 20mg x 30	16.00	15	2026-10-18 21:30:06	2026-10-18 21:30:06
33	11	13	Fluoxetina Genfar	Fluoxetina	Cápsulas 20mg x 14	14.00	10	2026-10-18 21:30:06	2026-10-18 21:30:06
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2026_10_18_000001_create_categorias_table	1
5	2026_10_18_000002_create_laboratorios_table	1
6	2026_10_18_000003_create_medicamentos_table	1
7	2026_10_18_000004_create_lotes_table	1
8	2026_10_18_000005_create_ventas_table	1
9	2026_10_18_000006_create_detalles_venta_table	1
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: ventas; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ventas (id, fecha_venta, monto_total, created_at, updated_at) FROM stdin;
2	2026-10-06 21:01:40	102.00	2026-10-18 21:30:07	2026-10-18 21:30:07
3	2026-10-03 06:47:51	143.50	2026-10-18 21:30:07	2026-10-18 21:30:07
4	2026-08-10 13:05:44	166.00	2026-10-18 21:30:07	2026-10-18 21:30:07
5	2026-09-28 03:43:42	55.00	2026-10-18 21:30:07	2026-10-18 21:30:07
6	2026-08-14 01:49:41	62.00	2026-10-18 21:30:07	2026-10-18 21:30:07
7	2026-10-11 01:42:54	197.00	2026-10-18 21:30:07	2026-10-18 21:30:07
8	2026-07-25 23:51:46	239.00	2026-10-18 21:30:07	2026-10-18 21:30:07
9	2026-09-03 04:14:55	169.00	2026-10-18 21:30:07	2026-10-18 21:30:07
10	2026-08-13 18:11:12	12.50	2026-10-18 21:30:07	2026-10-18 21:30:07
11	2026-10-03 09:04:36	113.00	2026-10-18 21:30:07	2026-10-18 21:30:07
12	2026-09-24 21:22:06	385.00	2026-10-18 21:30:07	2026-10-18 21:30:07
13	2026-07-27 05:32:18	314.50	2026-10-18 21:30:07	2026-10-18 21:30:07
14	2026-09-15 09:52:08	87.00	2026-10-18 21:30:07	2026-10-18 21:30:07
15	2026-09-07 06:58:20	30.00	2026-10-18 21:30:07	2026-10-18 21:30:07
16	2026-08-18 10:40:46	55.00	2026-10-18 21:30:07	2026-10-18 21:30:07
17	2026-08-29 22:16:49	28.00	2026-10-18 21:30:07	2026-10-18 21:30:07
18	2026-10-02 02:54:39	50.00	2026-10-18 21:30:07	2026-10-18 21:30:07
19	2026-08-13 14:38:30	123.00	2026-10-18 21:30:07	2026-10-18 21:30:07
20	2026-08-23 19:40:22	119.00	2026-10-18 21:30:07	2026-10-18 21:30:07
21	2026-10-06 04:17:31	198.00	2026-10-18 21:30:07	2026-10-18 21:30:07
22	2026-08-18 19:55:01	40.00	2026-10-18 21:30:07	2026-10-18 21:30:07
23	2026-10-07 21:46:49	78.50	2026-10-18 21:30:07	2026-10-18 21:30:07
24	2026-10-06 09:57:34	27.00	2026-10-18 21:30:07	2026-10-18 21:30:07
25	2026-10-11 18:24:45	258.50	2026-10-18 21:30:07	2026-10-18 21:30:07
26	2026-09-07 11:35:00	239.00	2026-10-18 21:30:07	2026-10-18 21:30:07
\.


--
-- Name: categorias_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.categorias_id_seq', 13, true);


--
-- Name: detalles_venta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.detalles_venta_id_seq', 74, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: laboratorios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.laboratorios_id_seq', 15, true);


--
-- Name: lotes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.lotes_id_seq', 126, true);


--
-- Name: medicamentos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.medicamentos_id_seq', 33, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 9, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 1, false);


--
-- Name: ventas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ventas_id_seq', 26, true);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: categorias categorias_nombre_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categorias
    ADD CONSTRAINT categorias_nombre_unique UNIQUE (nombre);


--
-- Name: categorias categorias_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categorias
    ADD CONSTRAINT categorias_pkey PRIMARY KEY (id);


--
-- Name: detalles_venta detalles_venta_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.detalles_venta
    ADD CONSTRAINT detalles_venta_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: laboratorios laboratorios_nombre_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.laboratorios
    ADD CONSTRAINT laboratorios_nombre_unique UNIQUE (nombre);


--
-- Name: laboratorios laboratorios_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.laboratorios
    ADD CONSTRAINT laboratorios_pkey PRIMARY KEY (id);


--
-- Name: lotes lotes_numero_lote_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lotes
    ADD CONSTRAINT lotes_numero_lote_unique UNIQUE (numero_lote);


--
-- Name: lotes lotes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lotes
    ADD CONSTRAINT lotes_pkey PRIMARY KEY (id);


--
-- Name: medicamentos medicamentos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.medicamentos
    ADD CONSTRAINT medicamentos_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: ventas ventas_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ventas
    ADD CONSTRAINT ventas_pkey PRIMARY KEY (id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: categorias_nombre_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX categorias_nombre_index ON public.categorias USING btree (nombre);


--
-- Name: detalles_venta_venta_id_lote_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX detalles_venta_venta_id_lote_id_index ON public.detalles_venta USING btree (venta_id, lote_id);


--
-- Name: failed_jobs_connection_queue_failed_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX failed_jobs_connection_queue_failed_at_index ON public.failed_jobs USING btree (connection, queue, failed_at);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: laboratorios_nombre_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX laboratorios_nombre_index ON public.laboratorios USING btree (nombre);


--
-- Name: laboratorios_pais_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX laboratorios_pais_index ON public.laboratorios USING btree (pais);


--
-- Name: lotes_fecha_vencimiento_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX lotes_fecha_vencimiento_index ON public.lotes USING btree (fecha_vencimiento);


--
-- Name: lotes_medicamento_id_fecha_vencimiento_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX lotes_medicamento_id_fecha_vencimiento_index ON public.lotes USING btree (medicamento_id, fecha_vencimiento);


--
-- Name: medicamentos_categoria_id_laboratorio_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX medicamentos_categoria_id_laboratorio_id_index ON public.medicamentos USING btree (categoria_id, laboratorio_id);


--
-- Name: medicamentos_nombre_comercial_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX medicamentos_nombre_comercial_index ON public.medicamentos USING btree (nombre_comercial);


--
-- Name: medicamentos_principio_activo_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX medicamentos_principio_activo_index ON public.medicamentos USING btree (principio_activo);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: ventas_fecha_venta_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX ventas_fecha_venta_index ON public.ventas USING btree (fecha_venta);


--
-- Name: detalles_venta detalles_venta_lote_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.detalles_venta
    ADD CONSTRAINT detalles_venta_lote_id_foreign FOREIGN KEY (lote_id) REFERENCES public.lotes(id) ON DELETE RESTRICT;


--
-- Name: detalles_venta detalles_venta_venta_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.detalles_venta
    ADD CONSTRAINT detalles_venta_venta_id_foreign FOREIGN KEY (venta_id) REFERENCES public.ventas(id) ON DELETE CASCADE;


--
-- Name: lotes lotes_medicamento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lotes
    ADD CONSTRAINT lotes_medicamento_id_foreign FOREIGN KEY (medicamento_id) REFERENCES public.medicamentos(id) ON DELETE RESTRICT;


--
-- Name: medicamentos medicamentos_categoria_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.medicamentos
    ADD CONSTRAINT medicamentos_categoria_id_foreign FOREIGN KEY (categoria_id) REFERENCES public.categorias(id) ON DELETE RESTRICT;


--
-- Name: medicamentos medicamentos_laboratorio_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.medicamentos
    ADD CONSTRAINT medicamentos_laboratorio_id_foreign FOREIGN KEY (laboratorio_id) REFERENCES public.laboratorios(id) ON DELETE RESTRICT;


--
-- PostgreSQL database dump complete
--

\unrestrict V9ZAhbYrW1KnOSYIQU9pbKsikNhYfYesKJfRefFfJUQ2iYJf8GjUAjhPnHEBjbk

