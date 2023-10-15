-- Drop existing tables (if they exist)
DROP TABLE IF EXISTS joined, events_tags, user_option, request_to_join, event_update, invite, event_notification, option, poll, file, comment, tags, location, event, "user" CASCADE;

-- Create tables for all relations
CREATE TABLE "user" (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    password VARCHAR(255) NOT NULL,
    blocked BOOLEAN DEFAULT FALSE,
    admin BOOLEAN DEFAULT FALSE
);

CREATE TABLE event (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    eventDate DATE CHECK (eventDate > current_date),
    description TEXT,
    creationDate DATE CHECK (creationDate > current_date),
    price NUMERIC CHECK (price >= 0),
    public BOOLEAN DEFAULT TRUE,
    openToJoin BOOLEAN DEFAULT TRUE,
    capacity INTEGER,
    id_user REFERENCES "user"(id),
    id_location REFERENCES location(id)
);

CREATE TABLE location (
    id SERIAL PRIMARY KEY,
    address VARCHAR(255) NOT NULL,
    coordinates VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE tags (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE comment (
    id SERIAL PRIMARY KEY,
    text TEXT NOT NULL,
    date DATE CHECK (date > current_date),
    id_event REFERENCES event(id),
    id_user REFERENCES "user"(id)
);

CREATE TABLE file (
    id SERIAL PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    file VARCHAR(255) NOT NULL,
    id_event REFERENCES event(id),
    id_user REFERENCES "user"(id)
);

CREATE TABLE poll (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    creationDate DATE CHECK (creationDate > current_date),
    id_event REFERENCES event(id),
    id_user REFERENCES "user"(id)
);

CREATE TABLE option (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    id_user REFERENCES "user"(id),
    id_poll REFERENCES poll(id)
);

CREATE TABLE event_notification (
    id SERIAL PRIMARY KEY,
    date DATE CHECK (date > current_date),
    text TEXT NOT NULL,
    link VARCHAR(255) NOT NULL,
    id_event REFERENCES event(id),
    id_user REFERENCES "user"(id)
);

CREATE TABLE invite (
    id_eventnotification REFERENCES event_notification(id),
    id_user REFERENCES "user"(id),
    PRIMARY KEY (id_eventnotification)
);

CREATE TABLE event_update (
    id_eventnotification REFERENCES event_notification(id),
    PRIMARY KEY (id_eventnotification)
);

CREATE TABLE request_to_join (
    id_eventnotification REFERENCES event_notification(id),
    response TEXT,
    id_user REFERENCES "user"(id),
    PRIMARY KEY (id_eventnotification, id_user)
);

CREATE TABLE joined (
    id_event REFERENCES event(id),
    id_user REFERENCES "user"(id),
    date DATE CHECK (date > current_date),
    ticket VARCHAR(255),
    PRIMARY KEY (id_event, id_user)
);

CREATE TABLE events_tags (
    id_tag REFERENCES tags(id),
    id_event REFERENCES event(id),
    PRIMARY KEY (id_tag, id_event)
);

CREATE TABLE user_option (
    id_user REFERENCES "user"(id),
    id_option REFERENCES option(id),
    PRIMARY KEY (id_user, id_option)
);
