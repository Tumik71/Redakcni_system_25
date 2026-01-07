Tumik CMS (PHP)

- Responzivní CMS s jednoduchou administrací a napojením na MariaDB.
- Optimalizováno pro nasazení na ISPConfig (PHP-FPM 8.3) a doménu tumik.cz.

Struktura
- public/ — veřejná část webu
- admin/ — administrace
- src/ — aplikační logika (DB, Auth, helpery)
- config/ — konfigurace a načítání .env
- db/ — SQL schéma a seed data

Rychlý start
1) Vytvořte `.env` podle `.env.example` a vyplňte hesla.
2) Importujte `db/schema.sql` a volitelně `db/seed.sql` do MariaDB.
3) Spusťte projekt lokálně (PHP built-in server) nebo nasazení dle ISPConfig.

Bezpečnost
- `.env` je ignorován v Gitu, necommituje se.
- V produkci pravidelně rotujte hesla a udržujte aktuální SSL.

Nasazení na ISPConfig (tumik.cz)
- Server: `server.tumik.cz`, PHP: `PHP-FPM`, verze: `8.3`, SSL: `Let’s Encrypt` (zapnuto)
- Document Root v ISPConfig: `/var/www/clients/client1/web5` (veřejný web je v podadresáři `web`)
- Umístění souborů:
  - Nahrajte obsah složky `public/` do `/var/www/clients/client1/web5/web/`
  - Nahrajte složky `admin/`, `src/`, `config/` také do `/var/www/clients/client1/web5/web/`
  - `.htaccess` v `public/` zajistí přesměrování na `index.php` a blokaci přístupu na `config/` a `src/`
- Databáze:
  - Název: `c1dirty18`, uživatel: `c1tumik71`, heslo: nastavte v `.env`
  - Importujte `db/schema.sql` a `db/seed.sql` (volitelné) přes phpMyAdmin/CLI
- Test:
  - Ověřte http(s) `https://tumik.cz/` a přihlášení v `https://tumik.cz/admin/`

Role a oprávnění
- Role: `admin` > `editor` > `author`
- Přihlášenému uživateli se ukládá role do session; stránky pro správu obsahu vyžadují minimálně `editor`.
- API pro zápis vyžaduje přihlášení a roli `editor`.

API
- Veřejné: `GET /api/posts.php`, `GET /api/post.php?slug=...`, `GET /api/pages.php`
- Admin (zápis): `POST /admin/api/post_save.php`, `POST /admin/api/post_delete.php`, `POST /admin/api/page_save.php`, `POST /admin/api/page_delete.php`

GitHub
1) Vytvořte repo na GitHubu (prázdné).
2) Nastavte remote:
   - `powershell ./scripts/set-remote.ps1 -RepoUrl "https://github.com/<uzivatel>/<repo>.git"`
3) Odeslání na GitHub:
   - `powershell ./scripts/push.ps1`
4) Další změny:
   - `git add .`
   - `git commit -m "Popis změny"`
   - `git push`
