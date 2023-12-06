create schema if not exists lbaw2354;

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
    admin BOOLEAN DEFAULT FALSE,
    remember_token VARCHAR(100)
);

CREATE TABLE location (
    id SERIAL PRIMARY KEY,
    address VARCHAR(255) NOT NULL,
    coordinates VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE event (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    eventDate TIMESTAMP CHECK (eventDate > current_date),
    description TEXT,
    creationDate TIMESTAMP CHECK (creationDate > current_date),
    price NUMERIC CHECK (price >= 0),
    public BOOLEAN DEFAULT TRUE,
    openToJoin BOOLEAN DEFAULT TRUE,  
    capacity INTEGER,
    id_owner INTEGER REFERENCES users(id),
    id_location INTEGER REFERENCES location(id),
    highlight_owner BOOLEAN DEFAULT FALSE,
    hide_owner BOOLEAN DEFAULT FALSE
);

CREATE TABLE tags (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE comment (
    id SERIAL PRIMARY KEY,
    text TEXT NOT NULL,
    date TIMESTAMP CHECK (date > current_date),
    id_event INTEGER REFERENCES event(id),
    id_user INTEGER REFERENCES users(id)
);

CREATE TABLE file (
    id SERIAL PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    file VARCHAR(255) NOT NULL,
    id_event INTEGER REFERENCES event(id),
    id_user INTEGER REFERENCES users(id)
);

CREATE TABLE poll (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    creationDate TIMESTAMP CHECK (creationDate > current_date),
    id_event INTEGER REFERENCES event(id),
    id_user INTEGER REFERENCES users(id)
);

CREATE TABLE option (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    id_poll INTEGER REFERENCES poll(id)
);

CREATE TABLE event_notification (
    id SERIAL PRIMARY KEY,
    date TIMESTAMP CHECK (date >= current_date),
    text TEXT NOT NULL,
    link VARCHAR(255) NOT NULL,
    id_event INTEGER REFERENCES event(id),
    id_user INTEGER REFERENCES users(id)
);

CREATE TABLE invite (
    id_eventnotification INTEGER  REFERENCES event_notification(id),
    id_user INTEGER REFERENCES users(id),
    PRIMARY KEY (id_eventnotification)
);

CREATE TABLE event_update (
    id_eventnotification INTEGER REFERENCES event_notification(id),
    PRIMARY KEY (id_eventnotification)
);

CREATE TABLE request_to_join (
    id_eventnotification INTEGER REFERENCES event_notification(id),
    response TEXT,
    id_user INTEGER REFERENCES users(id),
    PRIMARY KEY (id_eventnotification, id_user)
);

CREATE TABLE joined (
    id_event INTEGER REFERENCES event(id),
    id_user INTEGER REFERENCES users(id),
    date TIMESTAMP CHECK (date > current_date),
    ticket VARCHAR(255),
    highlighted BOOLEAN DEFAULT false,
    hidden BOOLEAN DEFAULT false,
    PRIMARY KEY (id_event, id_user)
);

CREATE TABLE events_tags (
    id_tag INTEGER REFERENCES tags(id),
    id_event INTEGER REFERENCES event(id),
    PRIMARY KEY (id_tag, id_event)
);

CREATE TABLE user_option (
    id_user INTEGER REFERENCES users(id),
    id_option INTEGER REFERENCES option(id),
    PRIMARY KEY (id_user, id_option)
);

CREATE TABLE password_recovers
(
    token VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    date TIMESTAMP,
    PRIMARY KEY (token)
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
CREATE OR REPLACE FUNCTION events_search_update() RETURNS TRIGGER AS $$
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
    DECLARE event_capacity INT;
    DECLARE event_participants INT;
BEGIN
    SELECT capacity INTO event_capacity FROM Event WHERE id = NEW.id_event;
    SELECT COUNT(*) INTO event_participants FROM Joined WHERE id_event = NEW.id_event;
    IF event_participants >= event_capacity AND event_capacity != 0 THEN
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
    UPDATE event SET id_owner = NULL WHERE id_owner = OLD.id;
    UPDATE comment SET id_user = NULL WHERE id_user = OLD.id;
    UPDATE file SET id_user = NULL WHERE id_user = OLD.id;
    UPDATE poll SET id_user = NULL WHERE id_user = OLD.id;
    UPDATE user_option SET id_user = NULL WHERE id_user = OLD.id;
    DELETE FROM joined WHERE id_user = OLD.id;
    DELETE FROM event_notification WHERE id_user = OLD.id;
    DELETE FROM invite WHERE id_user = OLD.id;
    DELETE FROM request_to_join WHERE id_user = OLD.id;
    return OLD;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER user_deletion_trigger
BEFORE DELETE ON users
FOR EACH ROW
EXECUTE FUNCTION delete_user_trigger();

--03

CREATE OR REPLACE FUNCTION check_event_happened()
RETURNS TRIGGER AS $$
DECLARE event_date DATE;
BEGIN
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
EXECUTE FUNCTION check_event_happened();


INSERT INTO users (email, username, name, description, password)
VALUES
('john.doe@email.com', 'john_doe', 'John Doe', 'Software developer and technology enthusiast.', '$2a$12$2yH1/juufHeEMJ/5XqROg.zx8LwAZG4a5FEA22VoH3lW7H11I/Kn2'),
('jane.smith@email.com', 'jane_smith', 'Jane Smith', 'Marketing professional and content creator.', '$2a$12$9tAPiXldp0d9FRULhVG4FOsMycLPiZGiCN1a0YPXp./rSmjZ6LCRq'),
('michael.jones@email.com', 'michael_jones', 'Michael Jones', 'Finance expert and investment advisor.', '$2a$12$RZfqyZI6A/.UfH90nN1YxewXkjjjYxV/kWezAVFV.6JF0azxQJqVu'),
('emily.brown@email.com', 'emily_brown', 'Emily Brown', 'Graphic designer and creative artist.', '$2a$12$Y26BK6Twff25AY/Zc2Y6FucWeXXwDghd8.T0lRb6.ywZZSb7W74m6'),
('david.wilson@email.com', 'david_wilson', 'David Wilson', 'Health and fitness trainer, passionate about well-being.', '$2a$12$HoKYsa05pdtBMCJGZaSm9.I7hqGDrAR.OQbEqokVdPp2s3NzgLcAa'),
('olivia.taylor@email.com', 'olivia_taylor', 'Olivia Taylor', 'Travel blogger and adventure seeker.', '$2a$12$.O3Ddcq3Tnbd.lgP6WsEK.D/WgVJ3jKCbVj8I9Jwmkg7.GjOvXGw6'),
('william.johnson@email.com', 'william_johnson', 'William Johnson', 'Photographer and nature lover.', '$2a$12$0hLbBtQY4P.0Bldy1OM1Ee/r1b4f3PMuQ0SPf2mvjF2ICutEaaPd2'),
('sophia.miller@email.com', 'sophia_miller', 'Sophia Miller', 'Fashion blogger and style influencer.', '$2a$12$82twyb1xFL5rX7MfrEjSS.3aT8QDOnT0/B.E0Dxc5qBj0s5WvFLKy'),
('noah.davis@email.com', 'noah_davis', 'Noah Davis', 'Technology journalist and gadget enthusiast.', '$2a$12$AbCxQQ1rE4hrpvNdqkwMv.PXWsKSrDfT2QKbG3dkp5SnnTwa4v.uG'),
('emma.anderson@email.com', 'emma_anderson', 'Emma Anderson', 'Art curator and gallery owner.', '$2a$12$ytkKQymG/SNcmi4f7H5oBeZWaQ5y3uPM2trkIV5IayU.t4Fs7ouHK'),
('liam.miller@email.com', 'liam_miller', 'Liam Miller', 'Musician and songwriter, passionate about melodies.', '$2a$12$hnpGqXAFrMOY.s4.vw1.ZeWGdcsyfJpmqC/TB6Za/4MkC5GW.9iL6'),
('ava.jones@email.com', 'ava_jones', 'Ava Jones', 'Environmental activist and sustainability advocate.', '$2a$12$wYntwWq8yIw5mQsbqIML/e.QfD/i6p8.IJ.OMH/AR0ALmeABpseka'),
('logan.brown@email.com', 'logan_brown', 'Logan Brown', 'Sports enthusiast and fitness coach.', '$2a$12$EKBCJpRs9OhzVAv6k0yCROEg2ZRMMhJpdUHTFTS2K7WDC5w5wIA5O'),
('mia.wilson@email.com', 'mia_wilson', 'Mia Wilson', 'Travel photographer and adventure seeker.', '$2a$12$1q0Pmd2UW7jB8vM3FNS1IuI5HxU9NXkKwWhZIB.z6EAP9HZleXoDm'),
('ethan.smith@email.com', 'ethan_smith', 'Ethan Smith', 'Tech entrepreneur and startup enthusiast.', '$2a$12$2KZ3E6u8O.ZHWT.I5x/W2.I6YNkTVeghVhNGc0V.XtpQ7rUBiWqK6'),
('zoe.wilson@email.com', 'zoe_wilson', 'Zoe Wilson', 'Bookworm and literature lover.', '$2a$12$fpe3gQwzQu69W4EAvHgY4.PpFdkArFD1HugzY57Q2csIn6.L2KmVe'),
('mason.jones@email.com', 'mason_jones', 'Mason Jones', 'Science researcher and astronomy enthusiast.', '$2a$12$xwCu9ExrGw6CSB5D6mVcm.Ykk9X0KL0lO8a8qk9eCWplWAWLwOXeq'),
('ava.davis@email.com', 'ava_davis', 'Ava Davis', 'Animal rights activist and vegan advocate.', '$2a$12$QsLjOl4SnA2dVxIwRm.kw./DN2NhWji6nxy/omfr0dE6u5tq3alpK'),
('liam.johnson@email.com', 'liam_johnson', 'Liam Johnson', 'Outdoor adventurer and nature lover.', '$2a$12$3g.lEp2SN1gBPof1UMqGXenreMlxYpWbhTtI91h16cDYcK3dcLq/K'),
('olivia.miller@email.com', 'olivia_miller', 'Olivia Miller', 'Fashion designer and trendsetter.', '$2a$12$6QFJwrl8Xb97eLOmBEdGz.G8IUWfvx8SEufvDutRGiN3P/cOmNuTa'),
('owen.wilson@email.com', 'owen_wilson', 'Owen Wilson', 'Film director and movie enthusiast.', '$2a$12$wiWYRc02SXYHLzF.LyNzMeGKFFY9v/I7JgT/qIlHFE36a3YvqL2iC'),
('mia.jones@email.com', 'mia_jones', 'Mia Jones', 'Fitness trainer and wellness coach.', '$2a$12$fIY9/i8qzGsYvqS9HFIwXet0vJmsWdBTOZ/.C4TnM2iAse6UmeOgC'),
('william.anderson@email.com', 'william_anderson', 'William Anderson', 'Tech geek and software engineer.', '$2a$12$5IqB3yY9L9DOFCFolGRzE.M5vl0j5ZcugnJUTg24w4w64xMChnqae'),
('sophia.davis@email.com', 'sophia_davis', 'Sophia Davis', 'Fashion blogger and style icon.', '$2a$12$JS0dA4As58RrkFpAvLHiWupLZ6oFtLAsy27gRkiI0KodD7OqFS05O'),
('noah.brown@email.com', 'noah_brown', 'Noah Brown', 'Web developer and coding enthusiast.', '$2a$12$9lkGvjCJYwx6XsgbpvWdR.uSJDKCpeQpPzIsdC7vZREg3WSl2iHbS'),
('emma.wilson@email.com', 'emma_wilson', 'Emma Wilson', 'Graphic designer and artist.', '$2a$12$v6w26vF4meK3ifqrvyHCL./le2OACShEWfKegIxxJMQar4nDB6l.e'),
('liam.davis@email.com', 'liam_davis', 'Liam Davis', 'Musician and songwriter.', '$2a$12$HT/z9Ktu6lLL9Q4fg7mV8.LSWlN9uG2c4isVVEiXeAIS6vIiaIlSC'),
('ava.miller@email.com', 'ava_miller', 'Ava Miller', 'Travel blogger and adventure seeker.', '$2a$12$PvXzCXziK0z5TMLFYTwkAemusRct4f5pMG/yf2av8WMf.HTtBGYsS'),
('owen.johnson@email.com', 'owen_johnson', 'Owen Johnson', 'Film director and cinephile.', '$2a$12$49p60BvSHj6Bj7Ocq7/7E.jTIdhNBN/rz/VDHbA7aVRBKRlLFEsFq'),
('mia.davis@email.com', 'mia_davis', 'Mia Davis', 'Fashion designer and style influencer.', '$2a$12$KwNUFDXwhXbsm4.3LX.OmeytdMiO14J.kn0aGzTF78TX2VxkG6Y76'),
('logan.smith@email.com', 'logan_smith', 'Logan Smith', 'Sports journalist and fitness enthusiast.', '$2a$12$DXxiDgCKYDTv0y.G7gHPsuynfG.Dx7hTmCV2a2ezdeBqDgo9zH2kC'),
('olivia.jones@email.com', 'olivia_jones', 'Olivia Jones', 'Social media influencer and content creator.', '$2a$12$T/MvH16LMoC9k0VGXuPn7.2XvLm7ZLu4onfou6SR7IFfytzpHrNzq'),
('ethan.brown@email.com', 'ethan_brown', 'Ethan Brown', 'Tech entrepreneur and startup enthusiast.', '$2a$12$FZPxJ63oMf.NgKsZQwDKLulhGh82pUds7VCY.WW6WsgRZuTSo23Ba'),
('zoe.davis@email.com', 'zoe_davis', 'Zoe Davis', 'Bookworm and literature enthusiast.', '$2a$12$ZldNRkHvh1sgN1iUT.RKQe8rK7lZgRQQ9tpA4oP7RQcXpIb4QTnAe'),
('mason.miller@email.com', 'mason_miller', 'Mason Miller', 'Science researcher and aspiring scientist.', '$2a$12$z0NHyFokR6o.F6yZnQbuT.yGsgLOcHy7xZ17l8iDlU.Ccv2Zr5opu'),
('ava.wilson@email.com', 'ava_wilson', 'Ava Wilson', 'Environmental activist and nature lover.', '$2a$12$AgHWp.NFysmuud0tfbi6huZ6aWztRrOZw7wGzZkHnsN7Mxghs.xD2'),
('liam.smith@email.com', 'liam_smith', 'Liam Smith', 'Photographer and visual artist.', '$2a$12$NQ4e./8.AfQ/e3v6SCHu7.Y0vhHKO2rCKVGAXPiC.0/iNpBjIqW2G'),
('olivia.brown@email.com', 'olivia_brown', 'Olivia Brown', 'Health and wellness enthusiast.', '$2a$12$Jjy./FjSzVzv1v9qrq.QgOxHh2sQY1HRlWdAT1eLV3RO3Rg9.gqjS'),
('ana.silva@email.com', 'ana_silva', 'Ana Silva', 'Graphic designer and art enthusiast.', '$2a$12$KeJNV6cNwivH.Ucx3eCpVeYeeyEOfPvWUo7kmiTqCVImaW8WTnV82'),
('joao.pereira@email.com', 'joao_pereira', 'João Pereira', 'Software developer and coding enthusiast.', '$2a$12$fqSiM2AnU3CB7VJg78ct6u7unYfCwbpqDnc6g98zmvGFrV8qkQc.e'),
('maria.santos@email.com', 'maria_santos', 'Maria Santos', 'Bookworm and literature lover.', '$2a$12$hElNJuU5l2uYomoyQQmltOnNK9aTQ/.ImP1eATRPeQfr.qOlSo4/e'),
('manuel.ferreira@email.com', 'manuel_ferreira', 'Manuel Ferreira', 'Adventure seeker and travel enthusiast.', '$2a$12$gzh.jvRQz0svvBYwnN6JdOVwR1Iu8uqHkht9T7TEQvdo4fDvVEeHa'),
('carla.oliveira@email.com', 'carla_oliveira', 'Carla Oliveira', 'Yoga instructor and wellness coach.', '$2a$12$VQ6SyRV3az05gHbBj5CmLOf.KjwMnQ6Fv7Mm0mHkoyZOp9npwJ0ga'),
('ricardo.martins@email.com', 'ricardo_martins', 'Ricardo Martins', 'Tech enthusiast and startup lover.', '$2a$12$RHbI5owfYwbp1GIl5Vy2/eufBtGS7YJ/l4UoK5eUZUZt5g0R7CbXu'),
('ana.rodrigues@email.com', 'ana_rodrigues', 'Ana Rodrigues', 'Art curator and museum lover.', '$2a$12$kOhOnF4qRMKpXQdWe.4W1u7RZ0ycVZwWc7EzQwR47W6GJpA7fWumC'),
('joaquim.fernandes@email.com', 'joaquim_fernandes', 'Joaquim Fernandes', 'Nature photographer and environmentalist.', '$2a$12$OnI5kRYV0R1h8pV6D/uf/OzX51yjykUjQJ8c2VTARy8N7rfCnQFze'),
('luisa.gomes@email.com', 'luisa_gomes', 'Luísa Gomes', 'Fashion designer and style influencer.', '$2a$12$1D7LFzHC7ZxB6jzjepfqj.nTDkIz52vP1hyP3zhPjGMU5IbeoF3je'),
('antonio.silveira@email.com', 'antonio_silveira', 'António Silveira', 'Film director and movie buff.', '$2a$12$qP2bjcMc61GfSm57pYun0u/mqaUhrnqDSV.LvrKXvmpUBzKJgK0NW'),
('sara.pinto@email.com', 'sara_pinto', 'Sara Pinto', 'Fitness trainer and wellness coach.', '$2a$12$e8Z/Vg4xu2WTMlMLqmv0Su0cVSrKvg7tjRm0e3T3V1hxBl8.DlNt.'),
('hugo.oliveira@email.com', 'hugo_oliveira', 'Hugo Oliveira', 'Musician and music lover.', '$2a$12$NxNn0mQsP/vNYPwmWrj.jOZvOJ0eVuy/JvM/JOYDzIt50Jtya5nN6'),
('patricia.carvalho@email.com', 'patricia_carvalho', 'Patrícia Carvalho', 'Tech geek and software engineer.', '$2a$12$4D0E1sCNo64J26jYOVf7BuMvZnmNWUbTd3CMb9uOmmiCX/cP3VmOe'),
('miguel.pereira@email.com', 'miguel_pereira', 'Miguel Pereira', 'Artificial intelligence researcher and tech enthusiast.', '$2a$12$SkbNhQzkVPHNRxZXjtb6EuMtybA.kRRfRjHFQSTZLUqHsYN0Vi8Hu'),
('claudia.rocha@email.com', 'claudia_rocha', 'Cláudia Rocha', 'Science enthusiast and astronomy lover.', '$2a$12$X8xNVyFGBrlO5q1eie3k6.3rfBNd.V7H9b7o/hQCD6ES3Rq4YPxye'),
('pedro.almeida@email.com', 'pedro_almeida', 'Pedro Almeida', 'Foodie and culinary explorer.', '$2a$12$qEGcnzrcntqob24vjbK5KeR6Ry7uHZJFchWgeDcCDTX9nF/VszQDe'),
('ana.mendes@email.com', 'ana_mendes', 'Ana Mendes', 'Fashion blogger and style icon.', '$2a$12$uOLK7UKRn2f/LvD6rDZ10ueZn5ckU.BbNL3RSiXIk1gUp4Aqkiktm'),
('carlos.silva@email.com', 'carlos_silva', 'Carlos Silva', 'Travel blogger and adventure seeker.', '$2a$12$Ljv2kFVfFkK3EQXXWrc6OOqQxCTwX7Z7GOw/9zP.2NNXIp6T5/TU.'),
('ines.ferreira@email.com', 'ines_ferreira', 'Inês Ferreira', 'Digital marketer and social media strategist.', '$2a$12$Nps.gKrK8TZ.NU57r2eQRuZSdHFwVlWwZ0LX0X4.h3qXO/1fDg57W'),
('ricardo.santos@email.com', 'ricardo_santos', 'Ricardo Santos', 'Fitness enthusiast and health coach.', '$2a$12$pfIPTiYlRWYLrjzRxEjoFuCDUJj4uMzOTZ.dOORss5tQwqTYZ05Y.'),
('marta.rodrigues@email.com', 'marta_rodrigues', 'Marta Rodrigues', 'Art curator and museum lover.', '$2a$12$RKc7O0eAmDcUpxviMbUMNeVaXwUlr6hDaWvtmRzadW9Bv0vE5FzAC'),
('tiago.martins@email.com', 'tiago_martins', 'Tiago Martins', 'Web developer and coding enthusiast.', '$2a$12$BTLh8hkiGfFNoJb5JHlZhehi4C1zDvPZvFjXuGwNzOgN2sj4rrUlm'),
('ana.sousa@email.com', 'ana_sousa', 'Ana Sousa', 'Art and culture enthusiast.', '$2a$12$w1C2a5lR7z7A3Er8txI.w.qUoQtv6N91eH1UghdFqIfrTPvRsUgtW'),
('jose.oliveira@email.com', 'jose_oliveira', 'José Oliveira', 'Nature lover and environmental activist.', '$2a$12$LmOQOWll36T6nlZ7C.kv5uZ3.UmJz76VRiMziLm9syIQ5ZnC0oHhC'),
('rita.silva@email.com', 'rita_silva', 'Rita Silva', 'Tech geek and software engineer.', '$2a$12$rTVpgjB2WZcXLqzqP.05NO63oaaKJ9FVmi9z4DWakZ7Abg.CndbzK'),
('andre.santos@email.com', 'andre_santos', 'André Santos', 'Fitness trainer and health coach.', '$2a$12$4q4gkkLrfF7I./R4CQJzTObcW/d4ItSjiq4eDxxc86gKYtTnd2smG'),
('susana.mendes@email.com', 'susana_mendes', 'Susana Mendes', 'Tech enthusiast and startup lover.', '$2a$12$yWbJfw3KQUvz1lNrJwnqyOC9I1Myx.5.ztKf9KzbdN9R06M1RFMJu'),
('ricardo.rodrigues@email.com', 'ricardo_rodrigues', 'Ricardo Rodrigues', 'Science researcher and astronomy lover.', '$2a$12$PZZ2ek3QIKMuW6VOKKTRa.WS7KF1fB4gOWD9.K2CV0P9VTg4gsfoC'),
('oliver.jones@email.com', 'oliver_jones', 'Oliver Jones', 'Software developer and coding enthusiast.', '$2a$12$NHqWsFT2XjWc2tJ.uh5Qv.zx5y2gzp9hQJjRo/zAbuMVljjYnAdra'),
('amelia.smith@email.com', 'amelia_smith', 'Amelia Smith', 'Art curator and museum lover.', '$2a$12$JWwOelGKsv7Uz/uxZl1hhu7Q2fvX6QPIe.L0f.s5hzRQvZchJrDrq'),
('harry.wilson@email.com', 'harry_wilson', 'Harry Wilson', 'Fitness trainer and wellness coach.', '$2a$12$7ZZ9twTCSceQ3czqYqZ3.ezyPq2rQV9SckLKDprXYLLOvOzOD6nA2'),
('jack.taylor@email.com', 'jack_taylor', 'Jack Taylor', 'Tech geek and software engineer.', '$2a$12$7A4I/Jn7vKv7LnxH1Xa9L.0WTr/huJ4mX2A5gTT5Nk0R5T.yndd/a'),
('olivia.martin@email.com', 'olivia_martin', 'Olivia Martin', 'Fashion blogger and style icon.', '$2a$12$UgG2ZVLbhN.zA9gGH1DgVusMQoUdJp9tsNmsQ7n5nrnQgImZiz54W'),
('charlie.evans@email.com', 'charlie_evans', 'Charlie Evans', 'Digital marketer and social media strategist.', '$2a$12$5Lqq5sGg1OZz4g.1w3zpV.uPFKprqonvam3AG5SDRWI2MrjehvvK6'),
('ava.clark@email.com', 'ava_clark', 'Ava Clark', 'Yoga instructor and wellness coach.', '$2a$12$41fdIbEiysbYwsO.6DpoCuEPfr5Lzt4G2mR1ZmyKEsGSfY3HvjK6K'),
('archie.johnson@email.com', 'archie_johnson', 'Archie Johnson', 'Web developer and coding enthusiast.', '$2a$12$ay/EGiRrML3F2gLRdH6oYOhE9BOMrJ1tOaYKpBpN/MJspRnRwLvlK'),
('grace.davies@email.com', 'grace_davies', 'Grace Davies', 'Fashion designer and style influencer.', '$2a$12$v.qQr2vsbMjy2ujEXzyOfeDl76qRVZSOGPlv1Dv8Y6zSoG9nlxtlu'),
('finley.murray@email.com', 'finley_murray', 'Finley Murray', 'Musician and music lover.', '$2a$12$.yWQj7ENhDE3TCX96Rs9pe6L5CnNJESB5eLZc5WjB5G0Bfsj5sJLG'),
('sienna.hall@email.com', 'sienna_hall', 'Sienna Hall', 'Tech enthusiast and startup lover.', '$2a$12$X6ABl/.WRxEpQBYKYXQRauMd5It3Vg2ept4l2FsZgO4H8/YpTjRJG'),
('jackson.russell@email.com', 'jackson_russell', 'Jackson Russell', 'Fitness enthusiast and health coach.', '$2a$12$0HrjOTrS5nQFowOcnAT/KuK6vJ2D/5yIpe2cCKzgUDMtfNKunFz0C'),
('ivy.mitchell@email.com', 'ivy_mitchell', 'Ivy Mitchell', 'Bookworm and literature lover.', '$2a$12$G9LNDGhdQkAmXKv9/wOHc.EYmMybuoZ1kpdbNs.c.3YQbGM6ScFiG'),
('dylan.walker@email.com', 'dylan_walker', 'Dylan Walker', 'Adventure seeker and travel enthusiast.', '$2a$12$MlX0l7rTg3fj5ocGxGGnUexyZJ3LJn1xBZ5uozAYxVXMaGnTumPpy'),
('ruby.cooper@email.com', 'ruby_cooper', 'Ruby Cooper', 'Science researcher and astronomy lover.', '$2a$12$G39V29mUcZI6sXRSbh/lzeW9bEZyZysY/PLAt8RiCm9t2Gnlr5GzG'),
('max.harrison@email.com', 'max_harrison', 'Max Harrison', 'Film director and movie buff.', '$2a$12$CI1sjrXbQsgKT6QUmHC8i.ACP4eGW2D6NfsLBiYUKxLWnHh4LWUBi'),
('poppy.hughes@email.com', 'poppy_hughes', 'Poppy Hughes', 'Graphic designer and art enthusiast.', '$2a$12$3LxH2McxszVqkZ3lqq4U3uIjlxV7/ZDLu5SMpZKnQpIjveDNSEelO'),
('leo.bennett@email.com', 'leo_bennett', 'Leo Bennett', 'Nature photographer and environmentalist.', '$2a$12$Ldm27/9z4zzB3AqPtYnBn.Peg4EihU8UWuFeHxMXA9P3bX8tDwSUu'),
('lily.burton@email.com', 'lily_burton', 'Lily Burton', 'Fashion blogger and style icon.', '$2a$12$H9Gfs4Rk.Q6Mh3J8OPJuy.QZzFtfBq76O.B5RJYRYWTjV87XwecY.'),
('henry.cooper@email.com', 'henry_cooper', 'Henry Cooper', 'Artificial intelligence researcher and tech enthusiast.', '$2a$12$ptNNvE.AoNY.Q8elVmLl3ukYURANizZzM7X3EzDMUN0M3tpCnWnCq'),
('amelie.davies@email.com', 'amelie_davies', 'Amelie Davies', 'Foodie and culinary explorer.', '$2a$12$y9Rr7ZrmtBIM0F14dNcS3eVg7edR66ue0o0pgD.n5kNiIPOvOyndK'),
('samuel.fisher@email.com', 'samuel_fisher', 'Samuel Fisher', 'Musician and music lover.', '$2a$12$XfyuTl7Aur5xUteGRxLhRuJnXq6L96v4z26J.B7QcIT1u2pswVVim'),
('ella.robinson@email.com', 'ella_robinson', 'Ella Robinson', 'Travel blogger and adventure seeker.', '$2a$12$yv2ZxuBDTkdxm3r6v.9Fi.H7IQHM7eh7NYJm0AsRRD9tY2MQZCZWm'),
('sebastian.kelly@email.com', 'sebastian_kelly', 'Sebastian Kelly', 'Science enthusiast and astronomy lover.', '$2a$12$fwb/K1O8K8dLZ/G02U1/v.BGpLV5QaDoyCfMlFtLBwvH6m.yR7i3u'),
('maya.collins@email.com', 'maya_collins', 'Maya Collins', 'Digital marketer and social media strategist.', '$2a$12$eZZktWqfPMuXWUbfuTg1H.Oh1F.3M/vQ2OK1iKyZlGqQ.17KFd/Bu'),
('milo.stewart@email.com', 'milo_stewart', 'Milo Stewart', 'Nature lover and environmental activist.', '$2a$12$QucI07G1pPiy8oj57i1wUuQkVvvoyBL8ujltdm8UnPOAzZsX2YJ4e'),
('bella.phillips@email.com', 'bella_phillips', 'Bella Phillips', 'Tech geek and software engineer.', '$2a$12$tUpz6/6Rq0UBl6Wd6CSZ5OoZ.t2MQPMJ0qzhIh/0RouKHLHK/tEiq'),
('luca.jones@email.com', 'luca_jones', 'Luca Jones', 'Fashion designer and style influencer.', '$2a$12$kF0S1wGbJ9EN9eQY73ldMujqNNikd98ms2zTgOV2XGSmDMDh/ehaW'),
('isla.harrison@email.com', 'isla_harrison', 'Isla Harrison', 'Adventure seeker and travel enthusiast.', '$2a$12$dMeNxHvLhZ0b3fgQ5sEzleUz5A2P2W6bNEJ1cADRC5Y.uEzpSefSq');


INSERT INTO location (address, coordinates, name)
VALUES
('123 Tech Street, Silicon Valley', '37.7749° N, 122.4194° W', 'Tech Hub Convention Center'),
('456 Film Avenue, Hollywood', '34.0522° N, 118.2437° W', 'Starlight Studios'),
('789 Art Lane, Bohemia', '40.7891° N, 73.1350° W', 'Bohemia Art Gallery'),
('321 Food Plaza, Gourmet City', '41.8781° N, 87.6298° W', 'Gourmet Food Park'),
('555 Fitness Road, Workoutville', '34.0522° N, 118.2437° W', 'FitLife Gym'),
('999 Craft Street, Artisan District', '40.7128° N, 74.0060° W', 'Crafty Creations Center'),
('777 Greenery Avenue, Eco Town', '34.0522° N, 118.2437° W', 'Green Paradise Park'),
('111 Book Street, Literary City', '41.8781° N, 87.6298° W', 'Book Haven'),
('234 Science Boulevard, Innovation City', '37.7749° N, 122.4194° W', 'Science Central'),
('567 Fashion Lane, Trendsville', '34.0522° N, 118.2437° W', 'Fashion Plaza'),
('987 Charity Circle, Philanthropy Heights', '40.7128° N, 74.0060° W', 'Generosity Hall'),
('876 Film Workshop Lane, Cinematown', '34.0522° N, 118.2437° W', 'CineCraft Studios'),
('345 Outdoor Yoga Street, Zen Valley', '41.8781° N, 87.6298° W', 'Nature Yoga Retreat'),
('654 Classic Car Boulevard, Nostalgia City', '37.7749° N, 122.4194° W', 'Vintage Auto Showground'),
('210 Digital Avenue, Cyber City', '34.0522° N, 118.2437° W', 'Digital Innovation Hub'),
('777 Farmers Market Plaza, Freshville', '40.7128° N, 74.0060° W', 'Local Harvest Market'),
('888 Photography Lane, Shutter City', '34.0522° N, 118.2437° W', 'Photo Gallery'),
('456 Startup Street, Tech Town', '37.7749° N, 122.4194° W', 'Startup Central'),
('123 Dance Boulevard, Rhythmtown', '34.0522° N, 118.2437° W', 'Dance Pavilion'),
('789 Environment Street, Greenfield', '41.8781° N, 87.6298° W', 'EcoAware Center'),
('321 Mobile App Lane, Code City', '37.7749° N, 122.4194° W', 'AppDev Institute'),
('555 Orchestra Avenue, Melodyville', '34.0522° N, 118.2437° W', 'Symphony Hall'),
('999 Historical Walk Street, Heritage City', '40.7128° N, 74.0060° W', 'History Trail'),
('777 Health Plaza, Wellness Town', '34.0522° N, 118.2437° W', 'WellBeing Center'),
('111 VR Gaming Street, Tech Oasis', '37.7749° N, 122.4194° W', 'VR Playground'),
('234 Stargazing Lane, Celestial City', '34.0522° N, 118.2437° W', 'Stellar Observatory'),
('567 Hiking Trail, Nature Valley', '41.8781° N, 87.6298° W', 'Serene Hikes Park'),
('987 Sustainable Living Street, Greenberg', '37.7749° N, 122.4194° W', 'EcoLiving Hub'),
('876 Comedy Corner, Laughter Land', '34.0522° N, 118.2437° W', 'Joke Junction'),
('345 Craft Beer Street, Brewsville', '40.7128° N, 74.0060° W', 'CraftBrew Haven'),
('654 Coding Lane, TechHub City', '37.7749° N, 122.4194° W', 'CodeCraft Academy'),
('210 Pet Adoption Plaza, Furry Haven', '34.0522° N, 118.2437° W', 'Paw Palace'),
('777 Culinary Avenue, Flavorville', '40.7128° N, 74.0060° W', 'Culinary Studio'),
('111 Board Game Street, Playtown', '37.7749° N, 122.4194° W', 'BoardGame Café'),
('234 Robotics Lane, Innovation Plaza', '34.0522° N, 118.2437° W', 'RoboTech Center'),
('567 Caribbean Music Boulevard, Island Groove', '41.8781° N, 87.6298° W', 'Reggae Rhythms Stage'),
('987 Investment Plaza, Finance District', '37.7749° N, 122.4194° W', 'Finance Forum'),
('876 Art Workshop Street, Canvas City', '34.0522° N, 118.2437° W', 'Artistry Workshop'),
('345 Rock Climbing Lane, Adventure Heights', '40.7128° N, 74.0060° W', 'RockPeak Challenge'),
('654 Street Photography Boulevard, Snapshot City', '37.7749° N, 122.4194° W', 'UrbanSnap Studio'),
('210 Networking Plaza, Connect City', '34.0522° N, 118.2437° W', 'BizConnect Lounge'),
('777 Meditation Street, Zen Zenith', '40.7128° N, 74.0060° W', 'Mindful Oasis'),
('111 Indie Film Avenue, Cinematic Center', '37.7749° N, 122.4194° W', 'IndieFlix Theater'),
('234 Urban Gardening Lane, EcoMetropolis', '34.0522° N, 118.2437° W', 'GreenThumb Garden'),
('567 Esports Arena, GameZone City', '41.8781° N, 87.6298° W', 'eSports Stadium'),
('987 Travel Photography Street, Wanderlust Town', '37.7749° N, 122.4194° W', 'WanderLens Gallery'),
('876 Aerial Yoga Plaza, Sky Sanctuary', '34.0522° N, 118.2437° W', 'AeroZen Studio'),
('345 Classic Movie Lane, Retroville', '40.7128° N, 74.0060° W', 'RetroCinema Theater'),
('654 Choir Avenue, Harmony Heights', '37.7749° N, 122.4194° W', 'Chorus Hall'),
('210 Beer Tasting Boulevard, Hop Haven', '34.0522° N, 118.2437° W', 'HopHarbor Brewery');


INSERT INTO event (name, eventDate, description, creationDate, price, public, openToJoin, capacity, id_user, id_location)
VALUES
('Tech Expo', '2024-11-15 12:30:00', 'Explore the latest in technology and innovation.', '2024-10-15 08:45:00', 0.00, true, true, 500, 1, 1),
('International Film Festival', '2024-12-01 18:15:00', 'Celebrate global cinema with a diverse selection of films.', '2024-10-16 14:22:00', 15.00, true, true, 300, 2, 2),
('Gastronomy Tour', '2024-11-20 15:45:00', 'Embark on a culinary journey with local chefs and food artisans.', '2024-10-17 09:53:00', 25.00, true, false, 200, 3, 3),
('Community Cleanup Day', '2024-11-25 09:10:00', 'Join hands for a cleaner and greener neighborhood.', '2024-10-18 11:27:00', 0.00, true, true, 400, 4, 4),
('Music Marathon', '2024-11-10 07:30:00', 'A day-long music festival featuring diverse genres and local talent.', '2024-10-19 16:40:00', 10.00, true, false, 100, 5, 5),
('Art and Craft Fair', '2024-11-30 10:15:00', 'Discover handmade artworks and crafts by local artisans.', '2024-10-20 20:05:00', 5.00, true, false, 150, 6, 6),
('Fitness Challenge', '2024-12-05 12:45:00', 'Participate in a fitness challenge with various activities and competitions.', '2024-10-21 14:18:00', 8.00, true, true, 250, 7, 7),
('Book Club Meeting', '2024-11-18 11:20:00', 'Join fellow book enthusiasts for a discussion on the latest bestseller.', '2024-10-22 18:32:00', 0.00, true, true, 300, 8, 8),
('Science and Technology Symposium', '2024-11-22 14:55:00', 'Explore cutting-edge developments in science and technology.', '2024-10-23 22:47:00', 15.00, true, true, 200, 9, 9),
('Fashion Show', '2024-12-10 20:30:00', 'Experience the latest trends and designs in the world of fashion.', '2024-10-24 07:11:00', 20.00, true, false, 350, 10, 10),
('Charity Gala', '2024-11-17 19:10:00', 'Support local charities and causes at this elegant fundraising event.', '2024-10-25 09:26:00', 50.00, true, false, 120, 11, 11),
('Film Making Workshop', '2024-11-29 22:25:00', 'Learn the art and techniques of film making from industry experts.', '2024-10-26 13:53:00', 30.00, true, false, 180, 12, 12),
('Outdoor Yoga Retreat', '2024-12-08 16:40:00', 'Reconnect with nature and rejuvenate through yoga and meditation.', '2024-10-27 18:17:00', 40.00, true, true, 100, 13, 13),
('Classic Car Show', '2024-11-19 13:20:00', 'Admire vintage and classic cars from different eras at this show.', '2024-10-28 22:32:00', 10.00, true, false, 250, 14, 14),
('Digital Marketing Conference', '2024-11-28 18:45:00', 'Stay updated on the latest trends and strategies in digital marketing.', '2024-10-29 05:45:00', 25.00, true, true, 300, 15, 15),
('Local Farmers Market', '2024-12-02 09:30:00', 'Support local farmers and vendors by shopping for fresh produce and goods.', '2024-10-30 15:00:00', 0.00, true, false, 400, 16, 16),
('Photography Exhibition', '2024-11-21 08:05:00', 'View captivating photographs by local and international photographers.', '2024-11-01 19:15:00', 5.00, true, true, 200, 17, 17),
('Startup Pitch Competition', '2024-12-07 14:35:00', 'Witness innovative startup pitches and ideas at this competition.', '2024-11-02 21:30:00', 15.00, true, false, 150, 18, 18),
('Dance Performance Showcase', '2024-11-16 11:45:00', 'Enjoy mesmerizing dance performances by local and international troupes.', '2024-11-03 06:58:00', 12.00, true, true, 300, 19, 19),
('Environmental Conservation Workshop', '2024-11-27 09:00:00', 'Learn about environmental conservation and sustainable practices.', '2024-11-04 11:22:00', 0.00, true, false, 200, 20, 20),
('Mobile App Development Seminar', '2024-12-04 14:10:00', 'Get insights into the latest trends and tools in mobile app development.', '2024-11-05 13:45:00', 10.00, true, false, 250, 21, 21),
('Community Orchestra Concert', '2024-11-23 13:30:00', 'Experience the harmonious melodies of a community orchestra.', '2024-11-06 17:10:00', 8.00, true, true, 180, 22, 22),
('Historical Walking Tour', '2024-12-03 10:15:00', 'Explore the history of the city with a guided walking tour.', '2024-11-07 22:25:00', 5.00, true, false, 100, 23, 23),
('Health and Wellness Expo', '2024-11-26 13:45:00', 'Discover products and practices for a healthier and happier lifestyle.', '2024-11-08 08:48:00', 0.00, true, false, 350, 24, 24),
('Virtual Reality Gaming Tournament', '2024-12-09 16:20:00', 'Compete in a virtual reality gaming tournament with cutting-edge VR technology.', '2024-11-09 10:03:00', 20.00, true, true, 150, 25, 25),
('Astronomy Night', '2024-11-24 20:50:00', 'Stargazing and astronomy presentations for enthusiasts and curious minds.', '2024-11-10 15:16:00', 0.00, true, false, 200, 26, 26),
('Hiking Adventure', '2024-11-29 18:25:00', 'Embark on a scenic hiking adventure with fellow nature enthusiasts.', '2024-11-11 19:30:00', 0.00, true, false, 120, 27, 27),
('Sustainable Living Workshop', '2024-12-06 11:50:00', 'Learn practical tips for sustainable living and eco-friendly practices.', '2024-11-12 23:45:00', 15.00, true, true, 250, 28, 28),
('Live Comedy Show', '2024-11-18 18:00:00', 'Enjoy an evening of laughter with a live comedy performance.', '2024-11-13 17:15:00', 18.00, true, true, 180, 29, 30),
('Craft Beer Festival', '2024-11-30 05:35:00', 'Sample a variety of craft beers from local and regional breweries.', '2024-11-14 21:30:00', 25.00, true, false, 300, 30, 30),
('Coding Bootcamp', '2024-12-07 15:30:00', 'Intensive coding sessions and workshops for aspiring programmers.', '2024-11-15 14:45:00', 30.00, true, false, 200, 31, 31),
('Pet Adoption Fair', '2024-11-22 09:45:00', 'Find your new furry friend at this pet adoption fair.', '2024-11-16 05:00:00', 0.00, true, true, 150, 32, 32),
('Culinary Masterclass', '2024-12-08 14:10:00', 'Learn culinary skills from renowned chefs in an interactive masterclass.', '2024-11-17 09:15:00', 40.00, true, false, 250, 33, 33),
('Board Game Night', '2024-11-25 21:25:00', 'Join a night of fun and strategy with a variety of board games.', '2024-11-18 13:30:00', 0.00, true, true, 100, 34, 34),
('Robotics Workshop for Kids', '2024-12-05 14:50:00', 'Engaging and educational robotics workshop for young minds.', '2024-11-19 18:45:00', 10.00, true, false, 120, 35, 35),
('Caribbean Music Festival', '2024-11-20 12:00:00', 'Groove to the beats of Caribbean music at this lively festival.', '2024-11-20 22:15:00', 12.00, true, true, 300, 36, 36),
('Investment and Finance Seminar', '2024-12-02 15:15:00', 'Get insights into smart investing and financial planning.', '2024-11-21 07:30:00', 0.00, true, true, 200, 37, 37),
('Interactive Art Workshop', '2024-11-17 18:00:00', 'Create your own art with guidance from experienced artists.', '2024-11-22 11:15:00', 18.00, true, false, 180, 38, 38),
('Rock Climbing Challenge', '2024-12-09 15:25:00', 'Test your strength and skills in an exciting rock climbing challenge.', '2024-11-23 16:30:00', 15.00, true, true, 150, 39, 39),
('Street Photography Walk', '2024-11-24 10:50:00', 'Capture the essence of the city streets through photography.', '2024-11-24 20:05:00', 0.00, true, false, 100, 40, 40),
('Networking Mixer', '2024-12-06 08:15:00', 'Connect with professionals and expand your professional network.', '2024-11-25 14:20:00', 8.00, true, true, 250, 41, 41),
('Mindfulness Meditation Session', '2024-11-29 17:40:00', 'Experience peace and relaxation through guided mindfulness meditation.', '2024-11-26 09:55:00', 0.00, true, true, 120, 42, 42),
('Indie Film Premiere', '2024-12-03 12:15:00', 'Be among the first to watch a premiere of an independent film.', '2024-11-27 18:10:00', 12.00, true, true, 200, 43, 43),
('Urban Gardening Workshop', '2024-11-18 10:00:00', 'Learn how to create and maintain a garden in an urban setting.', '2024-11-28 15:15:00', 10.00, true, true, 150, 44, 44),
('Esports Tournament', '2024-12-01 20:35:00', 'Compete in a thrilling esports tournament with various gaming titles.', '2024-11-29 09:30:00', 20.00, true, false, 180, 45, 45),
('Travel Photography Exhibition', '2024-11-23 14:00:00', 'Journey through stunning travel photographs from around the world.', '2024-11-30 23:45:00', 0.00, true, true, 300, 46, 46),
('Aerial Yoga Workshop', '2024-12-05 15:25:00', 'Experience yoga in the air with an aerial yoga workshop.', '2024-12-01 17:30:00', 25.00, true, true, 100, 47, 47),
('Classic Movie Marathon', '2024-11-28', 'Relive the golden age of cinema with a marathon of classic films.', '2024-12-02 17:30:00 ', 0.00, true, false, 250, 48, 48),
('Community Choir Performance', '2024-12-07 17:30:00', 'Enjoy harmonious melodies from a community choir.', '2024-12-03 17:30:00', 5.00, true, false, 200, 49, 49),
('Local Beer Tasting', '2024-11-21 17:30:00', 'Sip and savor a selection of local craft beers.', '2024-12-04 17:30:00', 8.00, true, true, 150, 50, 50);


INSERT INTO tags (name) VALUES
('Music'),
('Art'),
('Technology'),
('Fitness'),
('Fashion'),
('Travel'),
('Science'),
('Food'),
('Literature'),
('Adventure'),
('Yoga'),
('Wellness'),
('Coding'),
('Startup'),
('Photography'),
('Nature'),
('Movies'),
('Digital Marketing'),
('AI'),
('Culinary'),
('Blogging'),
('Social Media'),
('Health'),
('Environmentalism'),
('Museum'),
('Astronomy'),
('Design'),
('Sports'),
('Education'),
('Entertainment'),
('Events'),
('Networking'),
('Gaming'),
('Crafts'),
('Theater'),
('History'),
('Reading'),
('Writing'),
('Comedy'),
('Politics'),
('Technology Trends'),
('DIY'),
('Music Festivals'),
('Fashion Shows'),
('Tech Conferences'),
('Food Festivals'),
('Art Exhibitions'),
('Literary Events'),
('Fitness Challenges'),
('Nature Walks'),
('Adventure Tours'),
('Startup Meetups'),
('Film Festivals'),
('Science Fairs'),
('Culinary Workshops'),
('Yoga Retreats'),
('Wellness Retreats'),
('Coding Bootcamps'),
('Photography Workshops'),
('Book Clubs'),
('Social Media Seminars'),
('Health Conferences'),
('Environmental Cleanup'),
('Museum Tours'),
('Astronomy Observations'),
('Design Workshops'),
('Sports Competitions'),
('Educational Seminars'),
('Entertainment Shows'),
('Networking Events'),
('Gaming Tournaments'),
('Crafting Workshops'),
('Theater Performances'),
('Historical Tours'),
('Reading Circles'),
('Writing Workshops'),
('Comedy Nights'),
('Political Debates'),
('Tech Talks'),
('DIY Workshops');


INSERT INTO events_tags (id_event, id_tag) VALUES (1, 3),
(2, 2),
(3, 8),
(4, 7),
(5, 1),
(6, 2),
(7, 4),
(8, 8),
(9, 7),
(10, 5),
(11, 11),
(12, 2),
(13, 9),
(14, 6),
(15, 15),
(16, 3),
(17, 2),
(18, 9),
(19, 1),
(20, 7),
(21, 15),
(22, 1),
(23, 6),
(24, 4),
(25, 9),
(26, 7),
(27, 16),
(28, 17),
(29, 5),
(30, 8),
(31, 7),
(32, 20),
(33, 8),
(34, 17),
(35, 9),
(36, 1),
(37, 15),
(38, 2),
(39, 4),
(40, 2),
(41, 15),
(42, 9),
(43, 2),
(44, 15),
(45, 9),
(46, 3),
(47, 9),
(48, 2),
(49, 1),
(50, 8);


INSERT INTO comment (text, date, id_event, id_user) VALUES
  ('Great event!', '2024-11-15', 1, 1),
  ('I enjoyed every moment!', '2024-11-16', 2, 2),
  ('The food was amazing!', '2024-11-17', 3, 3),
  ('Well organized event!', '2024-11-18', 4, 4),
  ('Awesome music selection!', '2024-11-19', 5, 5),
  ('Loved the art and crafts!', '2024-11-20', 6, 6),
  ('Challenging but fun!', '2024-11-21', 7, 7),
  ('Great book discussions!', '2024-11-22', 8, 8),
  ('Incredible tech innovations!', '2024-11-23', 9, 9),
  ('Fashion show was fantastic!', '2024-11-24', 10, 10),
  ('Amazing charity work!', '2024-11-25', 11, 11),
  ('Learned a lot about filmmaking!', '2024-11-26', 12, 12),
  ('Relaxing yoga retreat!', '2024-11-27', 13, 13),
  ('Vintage cars were a hit!', '2024-11-28', 14, 14),
  ('Informative digital marketing talks!', '2024-11-29', 15, 15),
  ('Fresh produce at the market!', '2024-11-30', 16, 16),
  ('Stunning photography exhibit!', '2024-12-01', 17, 17),
  ('Impressive startup pitches!', '2024-12-02', 18, 18),
  ('Mesmerizing dance performances!', '2024-12-03', 19, 19),
  ('Eco-friendly workshop!', '2024-12-04', 20, 20),
  ('Insights into app development!', '2024-12-05', 21, 21),
  ('Harmonious orchestra concert!', '2024-12-06', 22, 22),
  ('Fascinating historical tour!', '2024-12-07', 23, 23),
  ('Healthy living expo!', '2024-12-08', 24, 24),
  ('Exciting VR gaming tournament!', '2024-12-09', 25, 25),
  ('Starry night was magical!', '2024-12-10', 26, 26),
  ('Scenic hike with great people!', '2024-11-11', 27, 27),
  ('Practical sustainability tips!', '2024-11-12', 28, 28),
  ('Comedy show had me in stitches!', '2024-11-13', 29, 29),
  ('Craft beer variety was impressive!', '2024-11-14', 30, 30),
  ('Coding bootcamp changed my life!', '2024-11-15', 31, 31),
  ('Found my new pet at the fair!', '2024-11-16', 32, 32),
  ('Mastering culinary skills!', '2024-11-17', 33, 33),
  ('Board game night was a blast!', '2024-11-18', 34, 34),
  ('Kids loved the robotics workshop!', '2024-11-19', 35, 35),
  ('Caribbean music festival was lively!', '2024-11-20', 36, 36),
  ('Insightful finance seminar!', '2024-11-21', 37, 37),
  ('Art workshop unleashed creativity!', '2024-11-22', 38, 38),
  ('Rock climbing was a challenge!', '2024-11-23', 39, 39),
  ('Capturing city streets with photos!', '2024-11-24', 40, 40),
  ('Networking event expanded contacts!', '2024-11-25', 41, 41),
  ('Mindfulness meditation was calming!', '2024-11-26', 42, 42),
  ('Indie film premiere was fantastic!', '2024-11-27', 43, 43),
  ('Urban gardening in the city!', '2024-11-28', 44, 44),
  ('Esports tournament was thrilling!', '2024-11-29', 45, 45),
  ('Travel photography exhibition!', '2024-11-30', 46, 46),
  ('Aerial yoga was an adventure!', '2024-12-01', 47, 47),
  ('Classic movie marathon nostalgia!', '2024-12-02', 48, 48),
  ('Community choir harmonies!', '2024-12-03', 49, 49),
  ('Tasting local craft beers!', '2024-12-04', 50, 50);

INSERT INTO poll (title, creationDate, id_event, id_user) VALUES
  ('Music Preferences for Tech Expo', '2024-11-15', 1, 1),
  ('Film Favorites for Film Festival', '2024-11-16', 2, 2),
  ('Favorite Cuisine for Gastronomy Tour', '2024-11-17', 3, 3),
  ('Feedback for Community Cleanup Day', '2024-11-18', 4, 4),
  ('Preferred Music for Music Marathon', '2024-11-19', 5, 5),
  ('Favorite Art Forms for Art and Craft Fair', '2024-11-20', 6, 6),
  ('Fitness Preferences for Fitness Challenge', '2024-11-21', 7, 7),
  ('Book Choices for Book Club Meeting', '2024-11-22', 8, 8),
  ('Tech Trends Poll for Tech Symposium', '2024-11-23', 9, 9),
  ('Fashion Preferences for Fashion Show', '2024-11-24', 10, 10),
  ('Favorite Movie Genre for Charity Gala', '2024-12-10', 11, 11),
  ('Best Dessert in Town for Film Making Workshop', '2024-12-11', 12, 12),
  ('Community Event Suggestions for Outdoor Yoga Retreat', '2024-12-12', 13, 13),
  ('Technology in Daily Life for Classic Car Show', '2024-12-13', 14, 14),
  ('Style and Fashion Trends for Digital Marketing Conference', '2024-12-14', 15, 15),
  ('Fitness Routine Poll for Local Farmers Market', '2024-12-15', 16, 16),
  ('Book Recommendations for Photography Exhibition', '2024-12-16', 17, 17),
  ('Future of Tech for Startup Pitch Competition', '2024-12-17', 18, 18),
  ('Favorite Fashion Designers for Dance Performance Showcase', '2024-12-18', 19, 19),
  ('Cooking Techniques for Environmental Conservation Workshop', '2024-12-19', 20, 20);

INSERT INTO option (name, id_poll) VALUES
  ('Electronic', 1),
  ('Rock', 1),
  ('Pop', 1),
  ('Classic', 1),
  ('Drama', 2),
  ('Comedy', 2),
  ('Action', 2),
  ('Documentary', 2),
  ('Italian', 3),
  ('Japanese', 3),
  ('Mexican', 3),
  ('Indian', 3),
  ('Excellent', 4),
  ('Good', 4),
  ('Average', 4),
  ('Needs Improvement', 4),
  ('Pop', 5),
  ('Electronic', 5),
  ('Jazz', 5),
  ('Country', 5),
  ('Painting', 6),
  ('Sculpture', 6),
  ('Photography', 6),
  ('Mixed Media', 6),
  ('Running', 7),
  ('Yoga', 7),
  ('Weightlifting', 7),
  ('Cycling', 7),
  ('Fiction', 8),
  ('Non-fiction', 8),
  ('Mystery', 8),
  ('Science Fiction', 8),
  ('Artificial Intelligence', 9),
  ('Virtual Reality', 9),
  ('Blockchain', 9),
  ('Cybersecurity', 9),
  ('Casual', 10),
  ('Formal', 10),
  ('Vintage', 10),
  ('Streetwear', 10),
  ('Action', 11),
  ('Comedy', 11),
  ('Drama', 11),
  ('Sci-Fi', 11),
  ('Cake', 12),
  ('Ice Cream', 12),
  ('Pie', 12),
  ('Cookies', 12),
  ('Music Festival', 13),
  ('Outdoor Movie Night', 13),
  ('Community Picnic', 13),
  ('Crafting Workshop', 13),
  ('Smart Home Devices', 14),
  ('Wearable Tech', 14),
  ('AI Assistants', 14),
  ('Tech for Productivity', 14),
  ('Casual', 15),
  ('Formal', 15),
  ('Streetwear', 15),
  ('Vintage', 15),
  ('Running', 16),
  ('Weightlifting', 16),
  ('Yoga', 16),
  ('Cycling', 16),
  ('Science Fiction', 17),
  ('Mystery', 17),
  ('Historical Fiction', 17),
  ('Biography', 17),
  ('Robotics', 18),
  ('Space Exploration', 18),
  ('Biotechnology', 18),
  ('Internet of Things', 18),
  ('Chanel', 19),
  ('Gucci', 19),
  ('Versace', 19),
  ('Prada', 19),
  ('Baking', 20),
  ('Grilling', 20),
  ('Sautéing', 20),
  ('Sous Vide', 20);

INSERT INTO joined (id_event, id_user, date, ticket) VALUES
  (1, 1, '2024-11-01', NULL),
  (1, 2, '2024-11-02', NULL),
  (1, 3, '2024-11-03', NULL),
  (1, 4, '2024-11-04', NULL),
  (2, 2, '2024-11-05', NULL),
  (2, 3, '2024-11-06', NULL),
  (2, 4, '2024-11-07', NULL),
  (2, 5, '2024-11-08', NULL),
  (3, 3, '2024-11-09', NULL),
  (3, 4, '2024-11-10', NULL),
  (3, 5, '2024-11-11', NULL),
  (3, 6, '2024-11-12', NULL),
  (4, 4, '2024-11-13', NULL),
  (4, 5, '2024-11-14', NULL),
  (4, 6, '2024-11-15', NULL),
  (4, 7, '2024-11-16', NULL),
  (5, 5, '2024-11-17', NULL),
  (5, 6, '2024-11-18', NULL),
  (5, 7, '2024-11-19', NULL),
  (5, 8, '2024-11-20', NULL),
  (6, 6, '2024-11-21', NULL),
  (6, 7, '2024-11-22', NULL),
  (6, 8, '2024-11-23', NULL),
  (6, 9, '2024-11-24', NULL),
  (7, 7, '2024-11-25', NULL),
  (7, 8, '2024-11-26', NULL),
  (7, 9, '2024-11-27', NULL),
  (7, 10, '2024-11-28', NULL),
  (8, 8, '2024-11-29', NULL),
  (8, 9, '2024-11-30', NULL),
  (8, 10, '2024-12-01', NULL),
  (8, 11, '2024-12-02', NULL),
  (9, 9, '2024-12-03', NULL),
  (9, 10, '2024-12-04', NULL),
  (9, 11, '2024-12-05', NULL),
  (9, 12, '2024-12-06', NULL),
  (10, 10, '2024-12-07', NULL),
  (10, 11, '2024-12-08', NULL),
  (10, 12, '2024-12-09', NULL),
  (10, 13, '2024-12-10', NULL),
  (11, 11, '2024-12-11', NULL),
  (11, 12, '2024-12-12', NULL),
  (11, 13, '2024-12-13', NULL),
  (11, 14, '2024-12-14', NULL),
  (12, 12, '2024-12-15', NULL),
  (12, 13, '2024-12-16', NULL),
  (12, 14, '2024-12-17', NULL),
  (12, 15, '2024-12-18', NULL),
  (13, 13, '2024-12-19', NULL),
  (13, 14, '2024-12-20', NULL),
  (13, 15, '2024-12-21', NULL),
  (13, 16, '2024-12-22', NULL),
  (14, 14, '2024-12-23', NULL),
  (14, 15, '2024-12-24', NULL),
  (14, 16, '2024-12-25', NULL),
  (14, 17, '2024-12-26', NULL),
  (15, 15, '2024-12-27', NULL),
  (15, 16, '2024-12-28', NULL),
  (15, 17, '2024-12-29', NULL),
  (15, 18, '2024-12-30', NULL),
  (16, 16, '2024-12-31', NULL),
  (16, 17, '2024-01-01', NULL),
  (16, 18, '2024-01-02', NULL),
  (16, 19, '2024-01-03', NULL),
  (17, 17, '2024-01-04', NULL),
  (17, 18, '2024-01-05', NULL),
  (17, 19, '2024-01-06', NULL),
  (17, 20, '2024-01-07', NULL),
  (18, 18, '2024-01-08', NULL),
  (18, 19, '2024-01-09', NULL),
  (18, 20, '2024-01-10', NULL),
  (18, 21, '2024-01-11', NULL),
  (19, 19, '2024-01-12', NULL),
  (19, 20, '2024-01-13', NULL),
  (19, 21, '2024-01-14', NULL),
  (19, 22, '2024-01-15', NULL),
  (20, 20, '2024-01-16', NULL),
  (20, 21, '2024-01-17', NULL),
  (20, 22, '2024-01-18', NULL),
  (20, 23, '2024-01-19', NULL);
/*
INSERT INTO invite (id_eventnotification, id_user)
VALUES
  (1, 1),
  (2, 2),
  (3, 3),
  (4, 4),
  (5, 5),
  (6, 6),
  (7, 7),
  (8, 8),
  (9, 9),
  (10, 10);
  */

INSERT INTO user_option (id_user, id_option) VALUES
  (1, 1),
  (2, 2),
  (3, 3),
  (4, 4),
  (2, 5),
  (3, 6),
  (4, 7),
  (5, 8),
  (3, 9),
  (4, 10),
  (5, 11),
  (6, 12),
  (4, 13),
  (5, 14),
  (6, 15),
  (7, 16),
  (5, 17),
  (6, 18),
  (7, 19),
  (8, 20),
  (6, 21),
  (7, 22),
  (8, 23),
  (9, 24),
  (7, 25),
  (8, 26),
  (9, 27),
  (10, 28),
  (8, 29),
  (9, 30),
  (10, 31),
  (11, 32),
  (9, 33),
  (10, 34),
  (11, 35),
  (12, 36),
  (10, 37),
  (11, 38),
  (12, 39),
  (13, 40),
  (11, 41),
  (12, 42),
  (13, 43),
  (14, 44),
  (12, 45),
  (13, 46),
  (14, 47),
  (15, 48),
  (13, 49),
  (14, 50),
  (15, 51),
  (16, 52),
  (14, 53),
  (15, 54),
  (16, 55),
  (17, 56),
  (15, 57),
  (16, 58),
  (17, 59),
  (18, 60),
  (16, 61),
  (17, 62),
  (18, 63),
  (19, 64),
  (17, 65),
  (18, 66),
  (19, 67),
  (20, 68),
  (18, 69),
  (19, 70),
  (20, 71),
  (21, 72),
  (19, 73),
  (20, 74),
  (21, 75),
  (22, 76),
  (20, 77),
  (21, 78),
  (22, 79),
  (23, 80);

