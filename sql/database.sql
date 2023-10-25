CREATE SCHEMA lbaw2354;
SET search_path TO lbaw2354;

DROP TABLE IF EXISTS joined CASCADE;
DROP TABLE IF EXISTS events_tags CASCADE;
DROP TABLE IF EXISTS user_option CASCADE;
DROP TABLE IF EXISTS request_to_join CASCADE;
DROP TABLE IF EXISTS event_update CASCADE;
DROP TABLE IF EXISTS invite CASCADE;
DROP TABLE IF EXISTS event_notification CASCADE; 
DROP TABLE IF EXISTS option CASCADE;
DROP TABLE IF EXISTS poll CASCADE;
DROP TABLE IF EXISTS file CASCADE;
DROP TABLE IF EXISTS comment CASCADE;
DROP TABLE IF EXISTS tags CASCADE;
DROP TABLE IF EXISTS location CASCADE;
DROP TABLE IF EXISTS event CASCADE;
DROP TABLE IF EXISTS users CASCADE;


CREATE TABLE users (
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
    id_user REFERENCES users(id),
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
    id_user REFERENCES users(id)
);

CREATE TABLE file (
    id SERIAL PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    file VARCHAR(255) NOT NULL,
    id_event REFERENCES event(id),
    id_user REFERENCES users(id)
);

CREATE TABLE poll (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    creationDate DATE CHECK (creationDate > current_date),
    id_event REFERENCES event(id),
    id_user REFERENCES users(id)
);

CREATE TABLE option (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    id_poll REFERENCES poll(id)
);

CREATE TABLE event_notification (
    id SERIAL PRIMARY KEY,
    date DATE CHECK (date > current_date),
    text TEXT NOT NULL,
    link VARCHAR(255) NOT NULL,
    id_event REFERENCES event(id),
    id_user REFERENCES users(id)
);

CREATE TABLE invite (
    id_eventnotification REFERENCES event_notification(id),
    id_user REFERENCES users(id),
    PRIMARY KEY (id_eventnotification)
);

CREATE TABLE event_update (
    id_eventnotification REFERENCES event_notification(id),
    PRIMARY KEY (id_eventnotification)
);

CREATE TABLE request_to_join (
    id_eventnotification REFERENCES event_notification(id),
    response TEXT,
    id_user REFERENCES users(id),
    PRIMARY KEY (id_eventnotification, id_user)
);

CREATE TABLE joined (
    id_event REFERENCES event(id),
    id_user REFERENCES users(id),
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
    id_user REFERENCES users(id),
    id_option REFERENCES option(id),
    PRIMARY KEY (id_user, id_option)
);

--Indexes
--01
CREATE INDEX idx_joined_events ON joined USING btree (id_user);
--02
CREATE INDEX idx_user_email ON users USING HASH (email);
--03
CREATE INDEX idx_event_owner ON event USING BTREE (id_user);
--04

ALTER TABLE event
ADD COLUMN tsvectors TSVECTOR;
CREATE FUNCTION events_search_update() RETURNS TRIGGER AS $$
BEGIN
 IF TG_OP = 'INSERT' THEN
    NEW.tsvectors = (
        setweight(to_tsvector('english', NEW.name), 'A') ||
        setweight(to_tsvector('english', NEW.description), 'B')
    );
 END IF;
 IF TG_OP = 'UPDATE' THEN
     IF (NEW.name <> OLD.name OR NEW.description <> OLD.description) THEN
       NEW.tsvectors = (
         setweight(to_tsvector('english', NEW.name), 'A') ||
         setweight(to_tsvector('english', NEW.description), 'B')
       );
     END IF;
 END IF;
 RETURN NEW;
END $$
LANGUAGE plpgsql;
CREATE TRIGGER events_search_update
 BEFORE INSERT OR UPDATE ON event
 FOR EACH ROW
 EXECUTE PROCEDURE events_search_update();
CREATE INDEX search_idx ON event USING GIN (tsvectors);

--Triggers

--01

CREATE OR REPLACE FUNCTION check_event_capacity()
RETURNS TRIGGER AS $$
BEGIN
  DECLARE event_capacity INT;
  DECLARE event_participants INT;
  SELECT capacity INTO event_capacity FROM Event WHERE id = NEW.id_event;
  SELECT COUNT(*) INTO event_participants FROM Joined WHERE id_event = NEW.id_event;
  IF event_participants >= event_capacity THEN
    RAISE EXCEPTION 'Event has reached its capacity. You cannot join this event.';
  END IF;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER event_capacity_check
BEFORE INSERT ON Joined
FOR EACH ROW
EXECUTE FUNCTION check_event_capacity();

--02

CREATE OR REPLACE FUNCTION delete_user_trigger()
RETURNS TRIGGER AS $$
BEGIN
  DELETE FROM User WHERE id = OLD.id;
  UPDATE Event SET id_User = NULL WHERE id_User = OLD.id;
  UPDATE Comment SET id_User = NULL WHERE id_User = OLD.id;
  UPDATE File SET id_User = NULL WHERE id_User = OLD.id;
  UPDATE Poll SET id_User = NULL WHERE id_User = OLD.id;
  UPDATE Option SET id_User = NULL WHERE id_User = OLD.id;
  UPDATE EventNotification SET id_User = NULL WHERE id_User = OLD.id;
  UPDATE Invite SET id_User = NULL WHERE id_User = OLD.id;
  UPDATE EventUpdate SET id_User = NULL WHERE id_User = OLD.id;
  UPDATE RequestToJoin SET id_User = NULL WHERE id_User = OLD.id;
  UPDATE Joined SET id_User = NULL WHERE id_User = OLD.id;
  UPDATE UserOption SET id_User = NULL WHERE id_User = OLD.id;
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER user_deletion_trigger
BEFORE DELETE ON User
FOR EACH ROW
EXECUTE FUNCTION delete_user_trigger();

--03

CREATE OR REPLACE FUNCTION check_event_happened()
RETURNS TRIGGER AS $$
BEGIN
  DECLARE event_date DATE;
  SELECT eventDate INTO event_date FROM Event WHERE id = NEW.id_event;
  IF event_date < CURRENT_DATE THEN
    RAISE EXCEPTION 'This event has already happened. You cannot join it.';
  END IF;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER event_happened_check
BEFORE INSERT ON Joined
FOR EACH ROW
EXECUTE FUNCTION check_event_happened()

--Transactions

--01

BEGIN TRANSACTION;
DECLARE pollEventID INT;
DECLARE userID INT;
SELECT id INTO pollEventID FROM poll WHERE id_event = eventID LIMIT 1;
IF pollEventId IS NOT NULL THEN
    FOR userId IN (SELECT id_user FROM joined WHERE id_event = eventId) LOOP
        INSERT INTO event_notification (date, text, link, id_event, id_user)
        VALUES ($date, $text, $link, $id_event, $id_user);
    END LOOP;
ENDF;
END TRANSACTION;

--02

BEGIN TRANSACTION;
SET TRANSACTION ISOLATION LEVEL REPEATABLE READINSERT INTO poll (title, creationDate, id_Event, id_User)  VALUES ( $title , $creationDate , $id_Event, $id_User);
INSERT INTO option (name, id_Poll)  VALUES (currval(‘id_poll_seq’), $name, $id_Poll);
END TRANSACTION;

--03

BEGIN TRANSACTION;
DECLARE eventCapacity INT;
DECLARE joinedUsersCount INT;
DECLARE requestExists BOOLEAN;
SELECT TRUE INTO requestExists
FROM request_to_join
WHERE id_eventnotification = eventId AND id_user = userId;
SELECT capacity INTO eventCapacity FROM event WHERE id = eventId;
IF requestExists AND (eventCapacity IS NULL OR eventCapacity > 0) THEN
    INSERT INTO joined (id_event, id_user, date, ticket)
    VALUES ($id_event, $id_user, $date, $ticket);
    IF eventCapacity IS NOT NULL THEN
        UPDATE event SET capacity = capacity - 1 WHERE id = eventId;
    END IF;
    DELETE FROM request_to_join WHERE id_eventnotification = eventId AND id_user = userId;
    INSERT INTO event_notification (date, text, link, id_event, id_user)
    VALUES ($date, $text, $link, $id_event, $id_user);
ELSE
    RAISE EXCEPTION 'Invalid request or event is full.';
END IF;
END TRANSACTION;
