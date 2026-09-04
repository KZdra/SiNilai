#!/usr/bin/env bash
# =============================================================================
#  SiNilai — Deploy Production Script
#  Target : Debian 11/12/13 & Ubuntu 20.04 / 22.04 / 24.04 LTS
#  Usage  : sudo bash deploy.sh
# =============================================================================
set -euo pipefail

export DEBIAN_FRONTEND=noninteractive
export APT_LISTCHANGES_FRONTEND=none
export NEEDRESTART_MODE=a

# ===========================================================================
# WARNA OUTPUT
# ===========================================================================
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

print_banner() {
    echo -e "${BLUE}${BOLD}"
    echo "╔══════════════════════════════════════════════════════════════╗"
    echo "║          SiNilai — AUTO DEPLOY PRODUCTION SCRIPT             ║"
    echo "║     Support: Debian 11/12/13 (Trixie) & Ubuntu 20/22/24      ║"
    echo "║       Co-exist dengan CBT ICB (Tanpa Bentrok Port/DB)       ║"
    echo "╚══════════════════════════════════════════════════════════════╝"
    echo -e "${NC}"
}

info()    { echo -e "${CYAN}[INFO]${NC}  $1"; }
success() { echo -e "${GREEN}[OK]${NC}    $1"; }
warn()    { echo -e "${YELLOW}[WARN]${NC}  $1"; }
error()   { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }
step()    { echo -e "\n${BOLD}${BLUE}━━━ STEP $1 ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"; }

confirm() {
    read -p "$(echo -e "${YELLOW}?${NC} $1 [y/N]: ")" -r reply
    [[ "$reply" =~ ^[Yy]$ ]]
}

check_root() {
    if [[ $EUID -ne 0 ]]; then
        error "Script ini harus dijalankan sebagai root. Gunakan: sudo bash deploy.sh"
    fi
}

detect_distro() {
    DISTRO_ID="unknown"
    DISTRO_CODENAME="unknown"
    if [[ -f /etc/os-release ]]; then
        . /etc/os-release
        DISTRO_ID="${ID:-unknown}"
        DISTRO_CODENAME="${VERSION_CODENAME:-}"
    fi
    info "Distro terdeteksi : ${BOLD}${DISTRO_ID} (${DISTRO_CODENAME})${NC}"
}

run_as_app() {
    if command -v sudo &>/dev/null; then
        sudo -u www-data env COMPOSER_HOME=/var/www/.composer "$@"
    else
        su -s /bin/bash www-data -c "COMPOSER_HOME=/var/www/.composer $*"
    fi
}

# ===========================================================================
# DEFAULT VARIABEL KONFIGURASI
# ===========================================================================
APP_DIR=""
SERVER_IP=""
APP_PORT="8002"
REPO_URL="https://github.com/KZdra/SiNilai.git"
REPO_BRANCH="prod"
DB_NAME="sinilai_db"
DB_USER="cbt_user"
DB_PASS="321aa321"
DB_ROOT_PASS=""
CBT_SYNC_TOKEN="cbt_sync_secret_token_2026"

# ===========================================================================
# STEP 0: BANNER + KONFIRMASI
# ===========================================================================
print_banner
check_root
detect_distro

echo -e "${YELLOW}Catatan Arsitektur Multi-Aplikasi (Anti-Bentrok):${NC}"
echo "  • NGINX Web Port : Menggunakan Port terpisah (${APP_PORT}) agar tidak bentrok dengan CBT ICB (Port 80)"
echo "  • MySQL Database : Menggunakan Port 3306 yang sama, namun Database dipisah (${DB_NAME})"
echo "  • User Database  : Menggunakan '${DB_USER}' dengan hak akses ke '${DB_NAME}'"
echo ""
confirm "Lanjutkan proses deploy SiNilai?" || { echo "Deploy dibatalkan."; exit 0; }

# ===========================================================================
# STEP 1: INPUT KONFIGURASI
# ===========================================================================
step "1 | Konfigurasi Deploy SiNilai"

echo ""
read -p "$(echo -e "${CYAN}?${NC} Direktori instalasi aplikasi [/var/www/sinilai]: ")" APP_DIR
APP_DIR="${APP_DIR:-/var/www/sinilai}"

read -p "$(echo -e "${CYAN}?${NC} IP Server (misal 192.168.0.9): ")" SERVER_IP
while [[ -z "$SERVER_IP" ]]; do
    warn "IP Server tidak boleh kosong!"
    read -p "$(echo -e "${CYAN}?${NC} IP Server: ")" SERVER_IP
done

read -p "$(echo -e "${CYAN}?${NC} Port Web SiNilai (jangan 80 jika CBT sudah pakai 80) [8002]: ")" INPUT_PORT
APP_PORT="${INPUT_PORT:-8002}"

read -p "$(echo -e "${CYAN}?${NC} Git Branch SiNilai [prod]: ")" INPUT_BRANCH
REPO_BRANCH="${INPUT_BRANCH:-prod}"

read -p "$(echo -e "${CYAN}?${NC} Nama Database SiNilai [sinilai_db]: ")" INPUT_DB
DB_NAME="${INPUT_DB:-sinilai_db}"

read -p "$(echo -e "${CYAN}?${NC} Username Database [cbt_user]: ")" INPUT_USER
DB_USER="${INPUT_USER:-cbt_user}"

read -s -p "$(echo -e "${CYAN}?${NC} Password Database user [321aa321]: ")" INPUT_PASS
echo ""
DB_PASS="${INPUT_PASS:-321aa321}"

read -s -p "$(echo -e "${CYAN}?${NC} Password root MySQL/MariaDB (Kosongkan jika baru install / tanpa password): ")" DB_ROOT_PASS
echo ""

read -p "$(echo -e "${CYAN}?${NC} CBT Sync Token [cbt_sync_secret_token_2026]: ")" INPUT_TOKEN
CBT_SYNC_TOKEN="${INPUT_TOKEN:-cbt_sync_secret_token_2026}"

echo ""
echo -e "${YELLOW}${BOLD}── Ringkasan Konfigurasi SiNilai ──────────────────${NC}"
echo -e "  Direktori    : ${CYAN}$APP_DIR${NC}"
echo -e "  Akses Web    : ${CYAN}http://$SERVER_IP:$APP_PORT${NC}"
echo -e "  Database     : ${CYAN}$DB_NAME${NC} @ user ${CYAN}$DB_USER${NC} (Port 3306)"
echo -e "  Branch       : ${CYAN}$REPO_BRANCH${NC}"
echo -e "  CBT Token    : ${CYAN}$CBT_SYNC_TOKEN${NC}"
echo ""
confirm "Konfirmasi konfigurasi di atas dan mulai proses?" || { echo "Deploy dibatalkan."; exit 0; }

# ===========================================================================
# STEP 2: DEPENDENSI SISTEM
# ===========================================================================
step "2 | Pengecekan & Instalasi Dependensi Sistem"

info "Update repositori apt..."
apt-get update -y

apt-get install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    git \
    unzip \
    gnupg \
    lsb-release \
    sudo

# Konfigurasi PPA / Repository PHP jika belum ada
if ! command -v php &>/dev/null; then
    if [[ "$DISTRO_ID" == "ubuntu" ]]; then
        info "Setup repository PPA ondrej/php..."
        apt-get install -y software-properties-common
        LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php
        apt-get update -y
    else
        info "Setup repository sury.org untuk Debian..."
        SURY_SUITE="${DISTRO_CODENAME}"
        if [[ "$DISTRO_CODENAME" == "trixie" || "$DISTRO_CODENAME" == "sid" || -z "$DISTRO_CODENAME" ]]; then
            SURY_SUITE="bookworm"
        fi
        mkdir -p /etc/apt/trusted.gpg.d
        curl -sSL https://packages.sury.org/php/apt.gpg | gpg --dearmor -o /etc/apt/trusted.gpg.d/php-sury.gpg 2>/dev/null || true
        echo "deb https://packages.sury.org/php/ ${SURY_SUITE} main" > /etc/apt/sources.list.d/php-sury.list
        apt-get update -y || { rm -f /etc/apt/sources.list.d/php-sury.list; apt-get update -y; }
    fi
fi

# Pastikan PHP & ekstensi Laravel 11 terpasang
PHP_TARGET="8.2"
if apt-cache show php8.2-fpm &>/dev/null; then
    PHP_TARGET="8.2"
elif apt-cache show php8.3-fpm &>/dev/null; then
    PHP_TARGET="8.3"
else
    PHP_TARGET=""
fi

if [[ -n "$PHP_TARGET" ]]; then
    apt-get install -y \
        php${PHP_TARGET} php${PHP_TARGET}-fpm php${PHP_TARGET}-cli \
        php${PHP_TARGET}-mysql php${PHP_TARGET}-xml php${PHP_TARGET}-mbstring \
        php${PHP_TARGET}-curl php${PHP_TARGET}-zip php${PHP_TARGET}-bcmath \
        php${PHP_TARGET}-gd php${PHP_TARGET}-intl php${PHP_TARGET}-tokenizer \
        php${PHP_TARGET}-readline
else
    apt-get install -y \
        php php-fpm php-cli php-mysql php-xml php-mbstring \
        php-curl php-zip php-bcmath php-gd php-intl php-readline
fi

INSTALLED_PHP_VER=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "8.2")
success "PHP terinstal: PHP ${INSTALLED_PHP_VER}"

# Pastikan NGINX terpasang
if ! command -v nginx &>/dev/null; then
    info "Menginstal NGINX..."
    apt-get install -y nginx
fi
success "NGINX terpasang"

# Pastikan Database Server (MySQL/MariaDB) terpasang
if ! command -v mysql &>/dev/null && ! command -v mariadb &>/dev/null; then
    if [[ "$DISTRO_ID" == "ubuntu" ]]; then
        apt-get install -y mysql-server mysql-client
    else
        apt-get install -y mariadb-server mariadb-client || apt-get install -y default-mysql-server default-mysql-client
    fi
fi
success "Database server siap"

# Pastikan Composer terpasang
if ! command -v composer &>/dev/null; then
    info "Menginstal Composer..."
    curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
    php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm -f /tmp/composer-setup.php
fi
success "Composer terinstal: $(composer --version --no-ansi | head -1)"

# Pastikan Node.js & npm terpasang
if ! command -v node &>/dev/null; then
    export NVM_DIR="${HOME}/.nvm"
    if [[ ! -s "$NVM_DIR/nvm.sh" ]]; then
        curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.1/install.sh | bash
    fi
    if [[ -s "$NVM_DIR/nvm.sh" ]]; then
        \. "$NVM_DIR/nvm.sh"
        nvm install 20
        nvm use 20
        nvm alias default 20
        ln -sf "$(which node)" /usr/local/bin/node 2>/dev/null || true
        ln -sf "$(which npm)" /usr/local/bin/npm 2>/dev/null || true
        ln -sf "$(which npx)" /usr/local/bin/npx 2>/dev/null || true
    fi
fi
success "Node.js: $(node -v 2>/dev/null || echo 'tersedia') | npm $(npm -v 2>/dev/null || echo 'tersedia')"

# ===========================================================================
# STEP 3: SETUP DATABASE SINILAI
# ===========================================================================
step "3 | Setup Database SiNilai ('${DB_NAME}')"

systemctl start mariadb 2>/dev/null || systemctl start mysql 2>/dev/null || true
systemctl enable mariadb 2>/dev/null || systemctl enable mysql 2>/dev/null || true

DB_CLI="mysql"
command -v mariadb &>/dev/null && DB_CLI="mariadb"

if [[ -n "$DB_ROOT_PASS" ]]; then
    MYSQL_RUN="${DB_CLI} -u root -p${DB_ROOT_PASS}"
else
    MYSQL_RUN="${DB_CLI} -u root"
fi

info "Membuat database '${DB_NAME}' & hak akses user '${DB_USER}'..."
$MYSQL_RUN <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
CREATE USER IF NOT EXISTS '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';

ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';

GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

success "Database '${DB_NAME}' siap di port 3306 untuk user '${DB_USER}'"

# ===========================================================================
# STEP 4: CLONE / UPDATE KODE SINILAI
# ===========================================================================
step "4 | Deploy Kode SiNilai dari GitHub"

git config --global --add safe.directory "$APP_DIR" 2>/dev/null || true

if [[ -d "$APP_DIR/.git" ]]; then
    warn "Direktori $APP_DIR sudah ada. Menjalankan git fetch & checkout..."
    cd "$APP_DIR"
    git fetch origin
    git checkout "$REPO_BRANCH"
    git pull origin "$REPO_BRANCH"
else
    info "Cloning SiNilai dari $REPO_URL (branch: $REPO_BRANCH)..."
    mkdir -p "$(dirname "$APP_DIR")"
    git clone --branch "$REPO_BRANCH" "$REPO_URL" "$APP_DIR"
    cd "$APP_DIR"
fi
success "Kode SiNilai siap di $APP_DIR"

# ===========================================================================
# STEP 5: PERMISSION DIREKTORI
# ===========================================================================
step "5 | Konfigurasi Hak Akses Direktori"

mkdir -p /var/www/.composer /var/www/.cache
chown -R www-data:www-data /var/www/.composer /var/www/.cache

chown -R www-data:www-data "$APP_DIR"
find "$APP_DIR" -type f -not -path "*/node_modules/*" -not -path "*/.git/*" -exec chmod 644 {} \;
find "$APP_DIR" -type d -exec chmod 755 {} \;
chmod +x "$APP_DIR/artisan" 2>/dev/null || true
chmod -R 775 "$APP_DIR/storage"
chmod -R 775 "$APP_DIR/bootstrap/cache"
success "Hak akses file diatur untuk www-data"

# ===========================================================================
# STEP 6: COMPOSER INSTALL
# ===========================================================================
step "6 | Install Dependensi Composer (SiNilai)"

cd "$APP_DIR"
run_as_app composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist
success "Composer dependencies berhasil diinstal"

# ===========================================================================
# STEP 7: KONFIGURASI .ENV SINILAI
# ===========================================================================
step "7 | Setup File .env SiNilai"

if [[ -f "$APP_DIR/.env" ]]; then
    warn ".env lama dicadangkan ke .env.bak.$(date +%Y%m%d)"
    cp "$APP_DIR/.env" "$APP_DIR/.env.bak.$(date +%Y%m%d)"
fi

if [[ -f "$APP_DIR/.env.example" ]]; then
    cp "$APP_DIR/.env.example" "$APP_DIR/.env"
fi

info "Generate Laravel APP_KEY..."
APP_KEY=$(cd "$APP_DIR" && run_as_app php artisan key:generate --show --no-ansi)

cat > "$APP_DIR/.env" <<ENVFILE
APP_NAME="SiNilai SMK ICB"
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://${SERVER_IP}:${APP_PORT}

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID

APP_MAINTENANCE_DRIVER=file

PHP_CLI_SERVER_WORKERS=4
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

SESSION_DRIVER=file
SESSION_LIFETIME=180
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=
SESSION_SECURE_COOKIE=false
SESSION_COOKIE=sinilai_session

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=public
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=sinilai_cache_

CBT_SYNC_TOKEN=${CBT_SYNC_TOKEN}
ENVFILE

chown www-data:www-data "$APP_DIR/.env"
chmod 640 "$APP_DIR/.env"
success ".env SiNilai berhasil dikonfigurasi"

# ===========================================================================
# STEP 8: BUILD FRONTEND VITE
# ===========================================================================
step "8 | Build Aset Frontend Vite"

cd "$APP_DIR"
export NVM_DIR="${HOME}/.nvm"
if [[ -s "$NVM_DIR/nvm.sh" ]]; then
    \. "$NVM_DIR/nvm.sh"
fi

if [[ -f "$APP_DIR/package.json" ]]; then
    info "npm install..."
    npm install --no-audit --no-fund
    chmod -R +x "$APP_DIR/node_modules/.bin" 2>/dev/null || true
    info "npm run build..."
    npm run build
    chown -R www-data:www-data "$APP_DIR/public"
    success "Frontend Vite selesai di-build"
fi

# ===========================================================================
# STEP 9: MIGRASI DATABASE & SEEDER
# ===========================================================================
step "9 | Migrasi Database SiNilai"

cd "$APP_DIR"
run_as_app php artisan migrate --force

if confirm "Jalankan seeder awal SiNilai (data default)?"; then
    run_as_app php artisan db:seed --force || warn "Seeder gagal atau tidak ditemukan seeder default."
fi

run_as_app php artisan storage:link 2>/dev/null || true
success "Database SiNilai berhasil dimigrasi"

# ===========================================================================
# STEP 10: OPTIMASI CACHE
# ===========================================================================
step "10 | Optimasi Cache Laravel"

cd "$APP_DIR"
run_as_app php artisan optimize:clear
run_as_app php artisan config:cache
run_as_app php artisan route:cache
run_as_app php artisan view:cache
success "Laravel cache siap"

# ===========================================================================
# STEP 11: KONFIGURASI NGINX (PORT 8002 - TIDAK BENTROK)
# ===========================================================================
step "11 | Konfigurasi NGINX Virtual Host (Port ${APP_PORT})"

PHP_SOCK="/var/run/php/php${INSTALLED_PHP_VER}-fpm.sock"
if [[ ! -S "$PHP_SOCK" ]]; then
    FOUND_SOCK=$(ls -1 /run/php/php*-fpm.sock 2>/dev/null | head -1 || true)
    if [[ -n "$FOUND_SOCK" ]]; then
        PHP_SOCK="$FOUND_SOCK"
    fi
fi
info "Menggunakan PHP-FPM socket: ${PHP_SOCK}"

NGINX_CONF="/etc/nginx/sites-available/sinilai"

cat > "$NGINX_CONF" <<NGINXCONF
server {
    listen ${APP_PORT};
    server_name ${SERVER_IP} localhost;

    port_in_redirect on;

    root ${APP_DIR}/public;
    index index.php index.html;

    charset utf-8;
    client_max_body_size 50M;

    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
    gzip_min_length 256;
    gzip_vary on;
    gzip_comp_level 5;

    location ~* \.(css|js|png|jpg|jpeg|gif|ico|woff|woff2|ttf|svg|webp)\$ {
        expires 7d;
        add_header Cache-Control "public, immutable";
        try_files \$uri =404;
    }

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass unix:${PHP_SOCK};
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_param HTTP_HOST \$http_host;
        fastcgi_param SERVER_PORT \$server_port;
        fastcgi_param HTTP_AUTHORIZATION \$http_authorization;
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_buffers 8 16k;
        fastcgi_buffer_size 32k;
    }

    location ~ /\.(?!well-known).* { deny all; }
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    access_log /var/log/nginx/sinilai-access.log;
    error_log  /var/log/nginx/sinilai-error.log;
}
NGINXCONF

# Buat symlink ke sites-enabled (TIDAK MENGHAPUS cbt-icb yang ada!)
ln -sf "$NGINX_CONF" /etc/nginx/sites-enabled/sinilai

if nginx -t; then
    systemctl reload nginx || systemctl restart nginx
    success "NGINX virtual host SiNilai aktif di port ${APP_PORT} (http://${SERVER_IP}:${APP_PORT})"
else
    error "Konfigurasi NGINX gagal divalidasi! Cek dengan: nginx -t"
fi

# ===========================================================================
# STEP 12: VERIFIKASI AKHIR
# ===========================================================================
step "12 | Verifikasi Instalasi SiNilai"

cd "$APP_DIR"
DB_TEST=$(run_as_app php -r "
try {
    \$pdo = new PDO('mysql:host=127.0.0.1;dbname=${DB_NAME}','${DB_USER}','${DB_PASS}');
    echo 'OK';
} catch(Exception \$e) {
    echo 'FAIL: ' . \$e->getMessage();
}
" 2>/dev/null || echo "FAIL")

if [[ "$DB_TEST" == "OK" ]]; then
    success "Koneksi DB SiNilai: OK (127.0.0.1:3306 -> ${DB_NAME} @ ${DB_USER})"
else
    warn "Koneksi DB: $DB_TEST"
fi

# ===========================================================================
# SELESAI!
# ===========================================================================
echo ""
echo -e "${GREEN}${BOLD}"
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║          ✅  DEPLOY SINILAI BERHASIL SELESAI!               ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo -e "${NC}"
echo -e "  ${BOLD}URL SiNilai      :${NC} ${CYAN}http://${SERVER_IP}:${APP_PORT}${NC}"
echo -e "  ${BOLD}Direktori        :${NC} ${APP_DIR}"
echo -e "  ${BOLD}Database SiNilai :${NC} ${DB_NAME} @ user ${DB_USER}"
echo -e "  ${BOLD}Log NGINX        :${NC} /var/log/nginx/sinilai-error.log"
echo ""
echo -e "${YELLOW}${BOLD}Koneksi Sinkronisasi dengan CBT ICB:${NC}"
echo -e "  Di file .env CBT ICB (/var/www/cbt-icb/.env), pastikan nilai ini terisi:"
echo -e "  ${BOLD}SINILAI_API_URL=${NC}http://127.0.0.1:${APP_PORT}"
echo -e "  ${BOLD}SINILAI_SYNC_TOKEN=${NC}${CBT_SYNC_TOKEN}"
echo ""
