-- XKUDLA15 & XMUSIL65 --


-- DROP TABLE IF EXISTSs --
DROP TABLE IF EXISTS Ohodnotil;
DROP TABLE IF EXISTS Vlozil_do_kosiku;
DROP TABLE IF EXISTS Upresnuje;
DROP TABLE IF EXISTS Obsahuje;
DROP TABLE IF EXISTS Objednavka;
DROP TABLE IF EXISTS Parametr;
DROP TABLE IF EXISTS Produkt;
DROP TABLE IF EXISTS Dodavatel;
DROP TABLE IF EXISTS Zakaznik;


-- -----------------------------------------------------
-- Table Zakaznik
-- -----------------------------------------------------
CREATE TABLE Zakaznik (
  zakaznicke_cislo INT NOT NULL AUTO_INCREMENT,
  jmeno VARCHAR(45) NOT NULL,
  prijmeni VARCHAR(45) NOT NULL,
  email VARCHAR(45) NOT NULL,
  heslo VARCHAR(100) NULL,
  CONSTRAINT pk_Zakaznik PRIMARY KEY (zakaznicke_cislo)
);


-- -----------------------------------------------------
-- Table Dodavatel
-- -----------------------------------------------------
CREATE TABLE Dodavatel (
  ico NUMERIC(8) NOT NULL,
  nazev VARCHAR(45) NOT NULL,
  kontaktni_osoba VARCHAR(45) NOT NULL,
  ulice VARCHAR(45) NOT NULL,
  mesto VARCHAR(45) NOT NULL,
  psc NUMERIC(5) NOT NULL,
  CONSTRAINT pk_Dodavatel PRIMARY KEY (ico)
);


-- -----------------------------------------------------
-- Table Produkt
-- -----------------------------------------------------
CREATE TABLE Produkt (
  katalogove_cislo VARCHAR(45) NOT NULL,
  nazev VARCHAR(45) NOT NULL,
  popis VARCHAR(255),
  cena INT NOT NULL,
  mnozstvi_skladem INT NOT NULL,
  fotografie VARCHAR(255),
  kategorie VARCHAR(45),
  zobrazovat TINYINT(1) UNSIGNED NULL,
  CONSTRAINT pk_Produkt PRIMARY KEY (katalogove_cislo)
);


-- -----------------------------------------------------
-- Table Parametr
-- -----------------------------------------------------
CREATE TABLE Parametr (
  nazev VARCHAR(45) NOT NULL,
  CONSTRAINT pk_Parametr PRIMARY KEY (nazev)
);


-- -----------------------------------------------------
-- Table Objednavka
-- -----------------------------------------------------
CREATE TABLE Objednavka (
  cislo_objednavky VARCHAR(45) NOT NULL,
  zakaznicke_cislo INT NOT NULL,
  datum_cas DATE NOT NULL,
  stav VARCHAR(45) NOT NULL,
  zaplaceno NUMERIC(1) NOT NULL,
  ulice VARCHAR(45) NOT NULL,
  mesto VARCHAR(45) NOT NULL,
  psc NUMERIC(5) NOT NULL,
  zpusob_doruceni VARCHAR(45) NOT NULL,
  platebni_metoda VARCHAR(45) NOT NULL,
  poznamka VARCHAR(255),
  CONSTRAINT pk_Objednavka PRIMARY KEY (cislo_objednavky, zakaznicke_cislo),
  CONSTRAINT fk_Objednavka_Zakaznik FOREIGN KEY (zakaznicke_cislo) REFERENCES Zakaznik (zakaznicke_cislo)
);


-- -----------------------------------------------------
-- Table Obsahuje
-- -----------------------------------------------------
CREATE TABLE Obsahuje (
  cislo_objednavky VARCHAR(45) NOT NULL,
  zakaznicke_cislo INT NOT NULL,
  katalogove_cislo VARCHAR(45) NOT NULL,
  mnozstvi INT NOT NULL,
  cena INT NOT NULL,
  CONSTRAINT pk_Obsahuje PRIMARY KEY (cislo_objednavky, zakaznicke_cislo, katalogove_cislo),
  CONSTRAINT fk_Objednavka_Obsahuje FOREIGN KEY (cislo_objednavky, zakaznicke_cislo) REFERENCES Objednavka (cislo_objednavky, zakaznicke_cislo),
  CONSTRAINT fk_Obsahuje_Produkt FOREIGN KEY (katalogove_cislo) REFERENCES Produkt (katalogove_cislo)
);


-- -----------------------------------------------------
-- Table Upresnuje
-- -----------------------------------------------------
CREATE TABLE Upresnuje (
  katalogove_cislo VARCHAR(45) NOT NULL,
  nazev VARCHAR(45) NOT NULL,
  hodnota VARCHAR(45) NOT NULL,
  CONSTRAINT pk_Upresnuje PRIMARY KEY (katalogove_cislo, nazev),
  CONSTRAINT fk_Upresnuje_Parametr FOREIGN KEY (nazev) REFERENCES Parametr (nazev),
  CONSTRAINT fk_Upresnuje_Produkt FOREIGN KEY (katalogove_cislo) REFERENCES Produkt (katalogove_cislo)
);


-- -----------------------------------------------------
-- Table Vlozil_do_kosiku
-- -----------------------------------------------------
CREATE TABLE Vlozil_do_kosiku (
  katalogove_cislo VARCHAR(45) NOT NULL,
  zakaznicke_cislo INT NOT NULL,
  pocet_kusu INT NOT NULL,
  CONSTRAINT pk_Vlozil_do_kosiku PRIMARY KEY (katalogove_cislo, zakaznicke_cislo),
  CONSTRAINT fk_Zakaznik_Vlozil_do_kosiku FOREIGN KEY (zakaznicke_cislo) REFERENCES Zakaznik (zakaznicke_cislo),
  CONSTRAINT fk_Vlozil_do_kosiku_Produkt FOREIGN KEY (katalogove_cislo) REFERENCES Produkt (katalogove_cislo)
);


-- -----------------------------------------------------
-- Table Ohodnotil
-- -----------------------------------------------------
CREATE TABLE Ohodnotil (
  katalogove_cislo VARCHAR(45) NOT NULL,
  zakaznicke_cislo INT NOT NULL,
  pocet_hvezdicek NUMERIC(1) NOT NULL,
  klady VARCHAR(255),
  zapory VARCHAR(255),
  shrnuti VARCHAR(255),
  CONSTRAINT pk_Ohodnotil PRIMARY KEY (katalogove_cislo, zakaznicke_cislo),
  CONSTRAINT fk_Zakaznik_Ohodnotil FOREIGN KEY (zakaznicke_cislo) REFERENCES Zakaznik (zakaznicke_cislo),
  CONSTRAINT fk_Ohodnotil_Produkt FOREIGN KEY (katalogove_cislo) REFERENCES Produkt (katalogove_cislo)
);


-- Insert into tables --
INSERT INTO Zakaznik VALUES (1, 'Petr', 'Kapr', '', NULL);
INSERT INTO Dodavatel VALUES (26359723, 'RANDOM DISTRIBUTION, s.r.o.', 'Vojta Okoun', 'Zahradni 173/2', 'Plzen', 32600);
INSERT INTO Produkt VALUES ('SVT33300460', 'Bobo Skicak KRTEK lepeny A4 10 listu', 'Lepeny skicak s motivem krtecka obsahuje 10 cistych listu.', 30, 500, 'bobo-skicak-krtek-lepeny-a4-10-listu-33300460.jpg', 'Skicaky', 1);
INSERT INTO Produkt (katalogove_cislo, nazev, cena, mnozstvi_skladem, zobrazovat) VALUES ('SVT44102600', 'Pastelky CONCORDE trojhranne - 18 barev', 39, 0, 1);
INSERT INTO Parametr VALUES ('Pocet stran');
INSERT INTO Objednavka VALUES ('1', 1, '2018-03-26 13:29:26', 'Prijata', 0, 'Pekarova 4', 'Praha', 18106, 'Ceska posta', 'Dobirkou', 'Dodani do Vanoc, prosim.');
INSERT INTO Obsahuje VALUES ('1', 1, 'SVT33300460', 2, 30);
INSERT INTO Upresnuje VALUES ('SVT33300460', 'Pocet stran', '10');
INSERT INTO Vlozil_do_kosiku VALUES ('SVT33300460', 1, 42);
INSERT INTO Ohodnotil VALUES ('SVT33300460', 1, 4, 'Vydareny vzhled - krtecek.', 'Vysoka cena, malo listu. Listy se trhaji.', 'Za ty penize bych cekal lepsi kvalitu.');
INSERT INTO Ohodnotil (katalogove_cislo, zakaznicke_cislo, pocet_hvezdicek) VALUES ('SVT44102600', 1, 5);
