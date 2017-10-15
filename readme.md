# MaturitaShop
Nette project made for school leaving exam. Basic system for e-shops containing order process, product list and static pages.

---
Jedná se o mou maturitní práci. Cílem bylo vytvořit eshop s vlastní administrací a objednávkovým systémem. Momentálně toho umí eshop poměrně málo, to-do list je dosti dlouhý a chybějící funkce budou postupně doplňovány. Nyní jsou zpět kategorie produktů a přibyla možnost mít více fotek k jednomu produktu a vyhledávání je spolehlivé i pro starší verze databází. V dohledné době bude zjednodušen objednávkový systém, cíleno bude na objednávání emailovým formulářem a telefonickou cestou. Stránka je responzivní a také ji lze v mobilním telefonu připnout stejně jako aplikaci. Demo neobsahuje aktuální verzi, chybí v něm nové funkce.

Testovací zákazník - a@b.cz, heslo: test
Administrace - master, heslo: test

http://vladan.azurewebsites.net

![eshop](http://kudlac.tode.cz/myown/maturamac.png)

### Installing
#### Windows
1. Install this project using [Composer](https://getcomposer.org/).

   At first install [Composer](https://getcomposer.org/) and [Git](https://git-scm.com/).

   Download project using Composer:
   ```
   composer create-project kudlav/maturita-shop folder --stability=dev
   ```

   This will download project into *folder* and resolve dependencies.

2. Import database

   Import database using *database.sql*. This file was exported from *database.mwb* using [MySQL Workbench 6.3](https://downloads.mysql.com/archives/workbench/).

   You can fill your database with testing data using stored procedures (*default_xxx*)

3. Change *app\config\config.local.neon*  to fit your database

### License
- MaturitaShop: Will be specified, ask author (http://kudlac.tode.cz)
- Nette: New BSD License or GPL 2.0 or 3.0 (https://nette.org/license)
- Adminer: Apache License 2.0 or GPL 2 (https://www.adminer.org)
