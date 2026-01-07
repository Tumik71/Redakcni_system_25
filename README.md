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

Automatické odesílání na GitHub
- Spuštění na pozadí (při změnách se provede commit a push):
  - `powershell -ExecutionPolicy Bypass -File ./scripts/auto-push.ps1`
- Debounce je 5s; ignoruje `.git`, `node_modules`, `vendor`, `.env` je ignorován díky `.gitignore`.

CI/CD – GitHub Actions (deploy na server)
- Workflow: `.github/workflows/deploy.yml` – spouští se na `push` do `main` nebo ručně.
- Nastav na GitHubu v repo Secrets → Actions tyto položky:
  - `SSH_HOST` = `server.tumik.cz`
  - `SSH_PORT` = `22`
  - `SSH_USER` = uživatel webu (např. `web5`)
  - `SSH_KEY` = privátní SSH klíč (PEM) s veřejnou částí přidanou na server (`~/.ssh/authorized_keys`)
  - `DEPLOY_PATH` = `/var/www/clients/client1/web5/web`
- Co se nasazuje: celý projekt s výjimkou `.env*`, `db/`, `.git/`, `.github/`, `scripts/`, `vendor/`, `node_modules/`, `README.md`.
- Práva po deploy: volitelný krok nastaví `644` pro soubory a `755` pro složky.

Instalátor
- Spusťte `https://tumik.cz/install/` a projděte kroky: kontrola prostředí → zadání DB a URL → nastavení admina → vytvoření `.env`, schématu a migrací.
- Pokud `.env` chybí nebo DB není dostupná, systém automaticky přesměruje do instalátoru.

Uploady a .htaccess
- Instalátor vytvoří složku pro uploady (`public/uploads`) a otestuje zápis.
- Pokud chybí `public/.htaccess`, instalátor ho vygeneruje s pravidly pro přesměrování na `index.php` a blokaci přístupu do `config/` a `src/`.

Správa médií
- Administrace: `admin/media.php` (seznam, náhled, mazání) a `admin/media_upload.php` (nahrání souboru).
- Podporované typy: obrázky (jpeg/png/gif/webp) a PDF.
- Proměnná prostředí `UPLOAD_DIR` určuje cílovou složku (výchozí `public/uploads`).
- Cesty v DB (`media.path`) jsou webové cesty, např. `/uploads/20260107-xxxx.jpg`.
- Náhledy: pro obrázky se generují miniatury v `uploads/thumbs/` a používají se v administraci.
- Vložení do editoru: v editaci článku/stránky je panel „Vložit médium“, který vkládá `<img>` nebo odkaz do obsahu.
 - Nastavení omezení: `admin/media_settings.php` umožní měnit povolené MIME typy a maximální velikost souborů (MB). Výchozí hodnoty se vytvoří migrací `003_settings.sql`.

Správa uživatelů
- Administrace: `admin/users.php` (list, vytvoření, editace, smazání, reset hesla)
- Role vyžadované: minimálně `admin` pro správu uživatelů
- Reset hesla (admin): `admin/user_reset.php?id=<id>`
- Reset hesla (veřejně):
  - `GET /forgot.php` — zadání uživatelského jména, vygeneruje se odkaz
  - `GET/POST /reset.php?token=...` — zadání nového hesla

Registrace a aktivace
- `GET/POST /register.php` — registrace nového uživatele (role `author`, stav `active=0`)
- `GET /activate.php?token=...` — aktivace účtu přes e-mailový odkaz

E-mail odesílání
- Používá PHP `mail()` s hlavičkami HTML; nastavte v `.env`:
  - `MAIL_FROM=noreply@tumik.cz`
  - `MAIL_FROM_NAME=Tumik CMS`
- ISPConfig + Postfix zajistí doručení; případné SPF/DKIM/DMARC nastavte v DNS.

Migrace DB
- Přidejte email a tabulku resetů:
  - přes phpMyAdmin nebo cli: importujte `db/migrations/001_add_email_and_password_resets.sql`
- Aktivace účtů:
  - importujte `db/migrations/002_user_activations.sql`
