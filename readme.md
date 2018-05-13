# MaturitaShop
Nette project made for school leaving exam. Basic system for e-shops containing order process, product list and static pages. Order process can be disabled - useful for orders only over phone and email. orders.

---
Jedná se o mou maturitní práci. Cílem bylo vytvořit eshop s vlastní administrací a objednávkovým systémem. Produkty lze řadit do kategorií a přibyla možnost mít více fotek k jednomu produktu. Vyhledávání je spolehlivé i pro starší verze databází. Objednávkový systém lze vypnout, to může být užitečné, pokud plánujete objednávání emailovým formulářem a telefonickou cestou (např.: bazar, inzertní server). Stránka je responzivní a v mobilním telefonu ji lze připnout stejně jako aplikaci. Chybějící funkce budou postupně doplňovány, aktuálně je plánováno dokončit administrační rozhraní a košík pro nepřihlášené.

Testovací zákazník - a@b.cz, heslo: test
Administrace - master, heslo: test

http://vladan.azurewebsites.net

![eshop](http://kudlac.tode.cz/assets/images/maturamac.png)

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
- MaturitaShop: MIT License (http://kudlac.tode.cz)
- Nette: New BSD License or GPL 2.0 or 3.0 (https://nette.org/license)
- Adminer: Apache License 2.0 or GPL 2 (https://www.adminer.org)
