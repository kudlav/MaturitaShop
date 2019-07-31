# IIShop
Nette project made for school leaving exam. Basic system for e-shops containing order process, product list and static pages. Order process can be disabled - useful for orders only over phone and email.

http://kudlac.tode.cz/iishop/

### Installing
#### Windows
1. Install this project using [Composer](https://getcomposer.org/).

   At first install [Composer](https://getcomposer.org/) and [Git](https://git-scm.com/).

   Download project:
   ```
   git clone git@github.com:moosejackson/IIS.git
   cd IIS
   composer install
   ```

   This will download project into *IIS* and resolve dependencies.

2. Import database

   Import database using *database.sql*.

3. Change *app\config\config.local.neon*  to fit your database

### License
- IIShop: MIT License
- Nette: New BSD License or GPL 2.0 or 3.0 (https://nette.org/license)
- Adminer: Apache License 2.0 or GPL 2 (https://www.adminer.org)
