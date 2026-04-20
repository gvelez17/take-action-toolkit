# Take Action Toolkit — Next Steps

## Current Status

The code is built and pushed to **https://github.com/gvelez17/take-action-toolkit**. Nothing is deployed yet.

### What's in the repo

| Directory | What it is | Status |
|-----------|-----------|--------|
| `plugin/` | WordPress plugin — CPTs, calendar integration, 4 blocks, setup wizard, settings, REST API | Code complete, needs `npm run build` and live testing |
| `theme/` | Block theme — theme.json, templates, header/footer, hero pattern, CSS | Code complete, needs live testing |
| `ansible/` | Ansible playbook for provisioning a fresh VM | Written, needs inventory updated with real VM IP |
| `OPTIONS.md` | High-level options analysis | Done |
| `ONBOARDING.md` | Onboarding flow design | Done |
| `MODERN-WP-NOTES.md` | WP development research notes | Done |

---

## Deployment Options

### Option A: Ansible Playbook (Full Automation)

Provisions a fresh Ubuntu VM with everything needed. Best for a clean VM.

**Prerequisites:**
- A VM running Ubuntu 22.04+ with SSH access from this dev VM
- DNS for the desired domain pointing to the VM's IP (or set up after)
- Ansible installed on this dev VM

**Steps:**

```bash
# 1. Verify Ansible is available
ansible --version

# 2. Update the inventory with the real VM IP and domain
vim ~/take-action-toolkit/ansible/inventory.yml
# Set: ansible_host, domain, wp_admin_email
# Set passwords (use ansible-vault for production)

# 3. Run the playbook
cd ~/take-action-toolkit/ansible
ansible-playbook -i inventory.yml playbook.yml

# 4. Visit https://your-domain/ — the site should be live
# 5. Visit https://your-domain/wp-admin/ — the setup wizard will appear
```

**What the playbook installs:**
- PHP 8.3 + FPM + extensions
- MariaDB (database)
- Caddy (web server + auto-SSL)
- Node.js 20 (for building plugin JS)
- WP-CLI
- WordPress (downloaded, configured, installed)
- Plugin + theme (cloned from GitHub, built, deployed, activated)
- System cron for WP-Cron
- Security hardening (DISALLOW_FILE_EDIT, xmlrpc blocked)

---

### Option B: Manual Installation (If VM Already Has Some of This)

Use this if the VM already has PHP, MySQL/MariaDB, or a web server. Adapt as needed.

**1. Install prerequisites (if not already present)**

```bash
# PHP 8.x with required extensions
sudo apt install php8.3-fpm php8.3-mysql php8.3-xml php8.3-mbstring \
    php8.3-curl php8.3-zip php8.3-gd php8.3-intl

# MariaDB (or MySQL)
sudo apt install mariadb-server

# Node.js 20 (for building plugin assets)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo bash -
sudo apt install nodejs

# WP-CLI
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp
```

**2. Create database**

```bash
sudo mysql -e "CREATE DATABASE takeaction_wp;"
sudo mysql -e "CREATE USER 'takeaction'@'localhost' IDENTIFIED BY 'YOUR_PASSWORD';"
sudo mysql -e "GRANT ALL ON takeaction_wp.* TO 'takeaction'@'localhost';"
```

**3. Install WordPress**

```bash
WP_PATH=/var/www/takeaction
sudo mkdir -p $WP_PATH
sudo chown www-data:www-data $WP_PATH

sudo -u www-data wp core download --path=$WP_PATH

sudo -u www-data wp config create \
    --path=$WP_PATH \
    --dbname=takeaction_wp \
    --dbuser=takeaction \
    --dbpass=YOUR_PASSWORD \
    --dbhost=localhost

sudo -u www-data wp core install \
    --path=$WP_PATH \
    --url=https://your-domain.org \
    --title="Take Action" \
    --admin_user=admin \
    --admin_password=YOUR_ADMIN_PASSWORD \
    --admin_email=admin@your-domain.org
```

**4. Clone and build the toolkit**

```bash
cd /opt
sudo git clone https://github.com/gvelez17/take-action-toolkit.git
cd take-action-toolkit/plugin
npm ci
npm run build
```

**5. Deploy plugin and theme**

```bash
WP_PATH=/var/www/takeaction

# Plugin
sudo rsync -av --exclude=node_modules --exclude=.git \
    /opt/take-action-toolkit/plugin/ \
    $WP_PATH/wp-content/plugins/take-action-toolkit/

# Theme
sudo rsync -av --exclude=.git \
    /opt/take-action-toolkit/theme/ \
    $WP_PATH/wp-content/themes/take-action-theme/

# Fix ownership
sudo chown -R www-data:www-data $WP_PATH/wp-content/

# Activate
sudo -u www-data wp plugin activate take-action-toolkit --path=$WP_PATH
sudo -u www-data wp theme activate take-action-theme --path=$WP_PATH
sudo -u www-data wp rewrite structure '/%postname%/' --path=$WP_PATH
```

**6. Configure web server**

If using Caddy (recommended for auto-SSL):
```
# /etc/caddy/Caddyfile
your-domain.org {
    root * /var/www/takeaction
    php_fastcgi unix//run/php/php8.3-fpm.sock
    file_server
    encode gzip

    @blocked path /xmlrpc.php
    respond @blocked 403
}
```

If using nginx:
```nginx
server {
    listen 80;
    server_name your-domain.org;
    root /var/www/takeaction;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|webp|woff2?)$ {
        expires 30d;
    }
}
```

**7. Set up WP-Cron via system cron**

```bash
# Disable WP-Cron (add to wp-config.php before "That's all, stop editing")
define('DISABLE_WP_CRON', true);

# Add system cron
(sudo -u www-data crontab -l 2>/dev/null; echo "*/5 * * * * cd /var/www/takeaction && php wp-cron.php > /dev/null 2>&1") | sudo -u www-data crontab -
```

---

### Option C: Direct Install on Existing VM with Other Services

If the VM already runs other apps (e.g., volunteer tasks), WordPress can share the VM. Key considerations:

- **Use a separate MariaDB/MySQL database** — don't share databases with other apps
- **Use a separate document root** (`/var/www/takeaction/`)
- **Use a separate Caddy/nginx virtual host** for the domain
- **PHP-FPM pool**: shared is fine for low-traffic; separate pool if you want isolation
- **Port conflicts**: WordPress uses PHP-FPM (unix socket), not a port — no conflicts with Node/Python apps

---

## After Installation

### First Visit

1. Go to `https://your-domain.org/wp-admin/`
2. Log in with the admin credentials
3. The **setup wizard** will appear automatically
4. Walk through: Location → Branding → Calendar → Action Links → Launch

### Google Calendar Setup

You need either:

**Option A — Google Calendar API key (recommended)**
1. Go to [console.cloud.google.com](https://console.cloud.google.com)
2. Create a project (or use existing)
3. Enable the "Google Calendar API"
4. Create an API key (restrict to Google Calendar API and your domain's referrer)
5. Add to `wp-config.php`: `define('TAT_GOOGLE_API_KEY', 'your-key-here');`
6. Enter the Calendar ID in the setup wizard

**Option B — iCal/ICS feed (simpler, no API key)**
1. In Google Calendar → Settings → your calendar → "Integrate calendar"
2. Copy the "Public address in iCal format" URL
3. Paste it in the setup wizard under "iCal/ICS Feed"

### Adding Organizations

- Go to WP Admin → Organizations → Add New
- Enter: name, description, featured image (logo), website, email, phone, social links
- Assign categories (Healthcare, Labor, Environment, etc.)
- Or use the REST API CSV import: POST to `/wp-json/take-action/v1/import/organizations`

### Adding Businesses

- Same as organizations but under WP Admin → Businesses → Add New
- Additional fields: address, map URL

### DNS & Domain

For Caddy on the host Proxmox (our infrastructure):
```bash
# From the host, add a Caddy route
caddy-domain add your-domain.org 10.0.0.XXX
```

For external domains (self-hosted users):
- Point A record to the VM's public IP
- Caddy handles SSL automatically

---

## Updating the Toolkit

When we push updates to the GitHub repo:

```bash
cd /opt/take-action-toolkit
git pull

# Rebuild plugin assets
cd plugin
npm ci
npm run build

# Redeploy
WP_PATH=/var/www/takeaction
sudo rsync -av --exclude=node_modules --exclude=.git \
    /opt/take-action-toolkit/plugin/ \
    $WP_PATH/wp-content/plugins/take-action-toolkit/

sudo rsync -av --exclude=.git \
    /opt/take-action-toolkit/theme/ \
    $WP_PATH/wp-content/themes/take-action-theme/

sudo chown -R www-data:www-data $WP_PATH/wp-content/
```

---

## Things to Test Once Running

- [ ] Setup wizard completes successfully
- [ ] Google Calendar connection test passes
- [ ] Events display on the homepage
- [ ] In-Person / Virtual filter works (Interactivity API)
- [ ] Organization directory displays with category filtering
- [ ] Business directory displays
- [ ] Action hub buttons appear (volunteer, donate, newsletter)
- [ ] Calendar subscribe links work (Google, Apple/Outlook)
- [ ] Settings page saves and loads correctly
- [ ] Block editor: can insert all 4 blocks
- [ ] Theme colors change when primary/secondary color is updated
- [ ] Mobile responsive layout
- [ ] Add an organization via WP admin
- [ ] REST API org import works

---

## Known Gaps (To Address After First Deploy)

1. **Block CSS** — Each block needs a `style-index.css` file in its build output. Currently the CSS is in the theme's `style.css`. May need to split per-block stylesheets.
2. **Editor preview** — Blocks show placeholders in the editor, not live data. Could add ServerSideRender for richer editor preview.
3. **CSV import UI** — The REST endpoint exists but there's no admin UI for uploading a CSV yet. Would need a simple admin page with file upload + column mapping.
4. **Theme color sync** — Plugin settings have primary/secondary color, but these need to dynamically override the theme.json palette values. May need a small PHP filter.
5. **Social icons** — Org/business cards use dashicons for social links. Should use proper SVG icons for each platform.
6. **WordPress.org listing** — Plugin and theme need readme.txt files and screenshot.png for the WP plugin/theme directories.
7. **Multisite support** — For managed hosting with multiple cities, need to test on WordPress Multisite.
