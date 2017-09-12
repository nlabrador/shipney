CREATE TABLE companies(
    id SERIAL PRIMARY KEY,
    name VARCHAR(200),
    address1 VARCHAR(255),
    address2 VARCHAR(200),
    phone VARCHAR(15),
    email VARCHAR(255),
    website VARCHAR(255),
    booksite VARCHAR(255)
);

CREATE TABLE town_cities(
    id SERIAL PRIMARY KEY,
    town_city VARCHAR(200) NOT NULL,
    province VARCHAR(150) NOT NULL
);

CREATE TABLE sea_ports(
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    town_city_id INTEGER REFERENCES town_cities(id) NOT NULL
);

CREATE TABLE company_vessels(
    id SERIAL PRIMARY KEY,
    company_id INTEGER REFERENCES companies(id) NOT NULL,
    vessel_type VARCHAR(30) NOT NULL,
    depart_port_id INTEGER REFERENCES sea_ports(id) NOT NULL,
    depart_time VARCHAR(35) NOT NULL,
    arrive_port_id INTEGER REFERENCES sea_ports(id) NOT NULL,
    arrive_time VARCHAR(35) NOT NULL,
    name VARCHAR(155) NOT NULL,
    sched_day VARCHAR(35) NOT NULL,
    pass_price_range VARCHAR(150),
    vehi_price_range VARCHAR(150)
);

CREATE TABLE vessel_accomodations(
    id SERIAL PRIMARY KEY,
    vessel_id INTEGER REFERENCES company_vessels(id) NOT NULL,
    accomodation VARCHAR(150) NOT NULL,
    price DECIMAL,
    features TEXT
);

CREATE TABLE distances(
    id SERIAL PRIMARY KEY,
    sea_port_id INTEGER REFERENCES sea_ports(id) NOT NULL,
    target_town_city_id INTEGER REFERENCES town_cities(id) NOT NULL,
    distance DECIMAL NOT NULL
);

CREATE TABLE company_bookings(
    id SERIAL PRIMARY KEY,
    company_id INTEGER REFERENCES companies(id) NOT NULL,
    address VARCHAR(255) NOT NULL,
    phone VARCHAR(150)
);
