-- XKUDLA15 & XMUSIL65 --


-- DROP TABLE IF EXISTSs --
DROP TABLE IF EXISTS ohodnotil;
DROP TABLE IF EXISTS vlozil_do_kosiku;
DROP TABLE IF EXISTS upresnuje;
DROP TABLE IF EXISTS obsahuje;
DROP TABLE IF EXISTS objednavka;
DROP TABLE IF EXISTS parametr;
DROP TABLE IF EXISTS produkt;
DROP TABLE IF EXISTS dodavatel;
DROP TABLE IF EXISTS zakaznik;
DROP TABLE IF EXISTS zamestnanec;


-- -----------------------------------------------------
-- Table zakaznik
-- -----------------------------------------------------
CREATE TABLE zakaznik (
  zakaznicke_cislo INT NOT NULL AUTO_INCREMENT,
  jmeno VARCHAR(45) NOT NULL,
  prijmeni VARCHAR(45) NOT NULL,
  email VARCHAR(45) NOT NULL,
  heslo VARCHAR(100) NULL,
  CONSTRAINT pk_zakaznik PRIMARY KEY (zakaznicke_cislo)
);


-- -----------------------------------------------------
-- Table Dodavatel
-- -----------------------------------------------------
CREATE TABLE dodavatel (
  ico NUMERIC(8) NOT NULL,
  nazev VARCHAR(45) NOT NULL,
  kontaktni_osoba VARCHAR(45) NOT NULL,
  ulice VARCHAR(45) NOT NULL,
  mesto VARCHAR(45) NOT NULL,
  psc NUMERIC(5) NOT NULL,
  CONSTRAINT pk_dodavatel PRIMARY KEY (ico),
  dodaci_lhuta VARCHAR(45) NOT NULL,
  email VARCHAR(45),
  telefon VARCHAR(12)
);


-- -----------------------------------------------------
-- Table produkt
-- -----------------------------------------------------
CREATE TABLE produkt (
  katalogove_cislo VARCHAR(45) NOT NULL,
  nazev VARCHAR(45) NOT NULL,
  popis VARCHAR(255),
  cena INT NOT NULL,
  mnozstvi_skladem INT NOT NULL,
  fotografie VARCHAR(255),
  kategorie VARCHAR(45),
  zobrazovat TINYINT(1) UNSIGNED NULL,
  CONSTRAINT pk_produkt PRIMARY KEY (katalogove_cislo),
  dodavatel NUMERIC(8) NOT NULL,
  CONSTRAINT fk_produkt_dodavatel FOREIGN KEY (dodavatel) REFERENCES dodavatel (ico)
);


-- -----------------------------------------------------
-- Table Parametr
-- -----------------------------------------------------
CREATE TABLE parametr (
  nazev VARCHAR(45) NOT NULL,
  CONSTRAINT pk_parametr PRIMARY KEY (nazev)
);


-- -----------------------------------------------------
-- Table Objednavka
-- -----------------------------------------------------
CREATE TABLE objednavka (
  cislo_objednavky INT AUTO_INCREMENT NOT NULL,
  zakaznicke_cislo INT NOT NULL,
  datum_cas DATETIME NOT NULL,
  stav VARCHAR(45) NOT NULL,
  zaplaceno NUMERIC(1) NOT NULL,
  ulice VARCHAR(45) NOT NULL,
  mesto VARCHAR(45) NOT NULL,
  psc NUMERIC(5) NOT NULL,
  zpusob_doruceni INT NOT NULL,
  platebni_metoda INT NOT NULL,
  poznamka VARCHAR(255),
  CONSTRAINT pk_objednavka PRIMARY KEY (cislo_objednavky, zakaznicke_cislo),
  CONSTRAINT fk_objednavka_zakaznik FOREIGN KEY (zakaznicke_cislo) REFERENCES zakaznik (zakaznicke_cislo)
);


-- -----------------------------------------------------
-- Table Obsahuje
-- -----------------------------------------------------
CREATE TABLE obsahuje (
  cislo_objednavky INT NOT NULL,
  zakaznicke_cislo INT NOT NULL,
  katalogove_cislo VARCHAR(45) NOT NULL,
  mnozstvi INT NOT NULL,
  cena INT NOT NULL,
  CONSTRAINT pk_obsahuje PRIMARY KEY (cislo_objednavky, zakaznicke_cislo, katalogove_cislo),
  CONSTRAINT fk_objednavka_obsahuje FOREIGN KEY (cislo_objednavky, zakaznicke_cislo) REFERENCES objednavka (cislo_objednavky, zakaznicke_cislo),
  CONSTRAINT fk_obsahuje_produkt FOREIGN KEY (katalogove_cislo) REFERENCES produkt (katalogove_cislo)
);


-- -----------------------------------------------------
-- Table Upresnuje
-- -----------------------------------------------------
CREATE TABLE upresnuje (
  katalogove_cislo VARCHAR(45) NOT NULL,
  nazev VARCHAR(45) NOT NULL,
  hodnota VARCHAR(45) NOT NULL,
  CONSTRAINT pk_upresnuje PRIMARY KEY (katalogove_cislo, nazev),
  CONSTRAINT fk_upresnuje_parametr FOREIGN KEY (nazev) REFERENCES parametr (nazev),
  CONSTRAINT fk_upresnuje_produkt FOREIGN KEY (katalogove_cislo) REFERENCES produkt (katalogove_cislo)
);


-- -----------------------------------------------------
-- Table vlozil_do_kosiku
-- -----------------------------------------------------
CREATE TABLE vlozil_do_kosiku (
  katalogove_cislo VARCHAR(45) NOT NULL,
  zakaznicke_cislo INT NOT NULL,
  pocet_kusu INT NOT NULL,
  CONSTRAINT pk_vlozil_do_kosiku PRIMARY KEY (katalogove_cislo, zakaznicke_cislo),
  CONSTRAINT fk_zakaznik_vlozil_do_kosiku FOREIGN KEY (zakaznicke_cislo) REFERENCES zakaznik (zakaznicke_cislo),
  CONSTRAINT fk_vlozil_do_kosiku_produkt FOREIGN KEY (katalogove_cislo) REFERENCES produkt (katalogove_cislo)
);


-- -----------------------------------------------------
-- Table ohodnotil
-- -----------------------------------------------------
CREATE TABLE ohodnotil (
  katalogove_cislo VARCHAR(45) NOT NULL,
  zakaznicke_cislo INT NOT NULL,
  pocet_hvezdicek NUMERIC(1) NOT NULL,
  klady VARCHAR(255),
  zapory VARCHAR(255),
  shrnuti VARCHAR(255),
  CONSTRAINT pk_ohodnotil PRIMARY KEY (katalogove_cislo, zakaznicke_cislo),
  CONSTRAINT fk_zakaznik_ohodnotil FOREIGN KEY (zakaznicke_cislo) REFERENCES zakaznik (zakaznicke_cislo),
  CONSTRAINT fk_ohodnotil_produkt FOREIGN KEY (katalogove_cislo) REFERENCES produkt (katalogove_cislo)
);


-- -----------------------------------------------------
-- Table zamestnanec
-- -----------------------------------------------------
CREATE TABLE zamestnanec (
  uzivatelske_jmeno VARCHAR(45) NOT NULL,
  jmeno VARCHAR(45) NOT NULL,
  prijmeni VARCHAR(45) NOT NULL,
  heslo VARCHAR(100) NULL,
  role VARCHAR(45) NOT NULL,
  CONSTRAINT pk_zamestnanec PRIMARY KEY (uzivatelske_jmeno)
);

-- Insert into tables --
INSERT INTO zakaznik VALUES (1, 'Petr', 'Kapr', 'pkapr@example.com', '$2y$10$Ap5ExBNV6qpfjY1h3YL5TOVk2dEmq4QSurke7wV.NBg8b3Fvzdnd6');
INSERT INTO dodavatel VALUES (26359723, 'RANDOM DISTRIBUTION, s.r.o.', 'Vojta Okoun', 'Zahradni 173/2', 'Plzen', 32600, '14 dní', NULL, NULL);
INSERT INTO produkt VALUES ('SVT33300460', 'Bobo Skicak KRTEK lepeny A4 10 listu', 'Lepeny skicak s motivem krtecka obsahuje 10 cistych listu.', 30, 500, 'bobo-skicak-krtek-lepeny-a4-10-listu-33300460.jpg', 'Skicaky', 1, 26359723);
INSERT INTO produkt (katalogove_cislo, nazev, cena, mnozstvi_skladem, zobrazovat, dodavatel) VALUES ('SVT44102600', 'Pastelky CONCORDE trojhranne - 18 barev', 39, 0, 1, 26359723);
INSERT INTO parametr VALUES ('Počet stran'), ('Gramáž');
INSERT INTO objednavka VALUES (1, 1, '2018-03-26 13:29:26', 'čeká na vyřízení', 0, 'Pekarova 4', 'Praha', 18106, 0, 1, 'Dodani do Vanoc, prosim.');
INSERT INTO obsahuje VALUES (1, 1, 'SVT33300460', 2, 30);
INSERT INTO upresnuje VALUES ('SVT33300460', 'Počet stran', '10'), ('SVT33300460', 'Gramáž', '200g/m2');
INSERT INTO vlozil_do_kosiku VALUES ('SVT33300460', 1, 42);
INSERT INTO ohodnotil VALUES ('SVT33300460', 1, 4, 'Vydareny vzhled - krtecek.', 'Vysoka cena, malo listu. Listy se trhaji.', 'Za ty penize bych cekal lepsi kvalitu.');
INSERT INTO ohodnotil (katalogove_cislo, zakaznicke_cislo, pocet_hvezdicek) VALUES ('SVT44102600', 1, 5);

INSERT INTO zamestnanec (uzivatelske_jmeno, jmeno, prijmeni, heslo, role) VALUES ('admin', 'admin', 'admin', '$2y$10$bFL/sppR.wsPK1FEFDNVKeZciAyIHWLZ2VAFF8tBQk8iIM2C06CBC', 'spravce');
INSERT INTO zamestnanec (uzivatelske_jmeno, jmeno, prijmeni, heslo, role) VALUES ('vokoun', 'Vojtěch', 'Okoun', '$2y$10$VMEbXinGky.gKkH94ds7Z.Lt.PIehOjOKLGxC8rB2pkhHLLBlqTzS', 'prodejce');
