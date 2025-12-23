# Quixote

A web-based competitive Scrabble score tracking and rating application for accurate player skill assessment.

## Overview

Quixote provides game tracking and player rating management for competitive Scrabble. The system maintains player profiles, records game results, tracks bingo words, and calculates dynamic ratings using the Glicko-2 algorithm developed by Mark Glickman.

### Key Features

- Player management with registration number and faculty tracking
- Game result recording with automatic rating calculations
- Bingo word tracking with point values
- Historical rating progression and game statistics
- Leaderboard with win/loss/draw records and performance metrics
- CSRF protection and input validation
- Responsive design with Gruvbox color scheme

## System Requirements

### Local Deployment
- PHP 8.0 or higher with mysqli extension
- MySQL 8.0 or MariaDB 10.5+
- Apache 2.4+ with mod_rewrite enabled
- Linux-based operating system

### Docker Deployment
- Docker Engine 20.10+
- Docker Compose 2.0+

## File Structure

```
Quixote/
├── config/
│   ├── credentials.php       # Environment variable loader
│   └── database.php          # Database connection and query helpers
├── database/
│   ├── CREATE.sql            # Database schema
│   └── DELETE.sql            # Data cleanup script
├── includes/
│   ├── glicko2.php           # Glicko-2 rating algorithm
│   └── security.php          # CSRF, validation, and sanitization
├── pages/
│   ├── add-player.php        # Player creation controller
│   ├── enter-game.php        # Game recording controller
│   ├── home.php              # Dashboard controller
│   ├── leaderboard.php       # Player rankings controller
│   └── player.php            # Player profile controller
├── templates/
│   ├── css/
│   │   └── style.css         # Application styling
│   ├── html/
│   │   ├── add-player.php    # Player form view
│   │   ├── enter-game.php    # Game entry view
│   │   ├── home.php          # Dashboard view
│   │   ├── leaderboard.php   # Rankings view
│   │   └── player.php        # Profile view
│   └── partials/
│       ├── header.php        # HTML head and navigation
│       ├── nav.php           # Navigation menu
│       └── footer.php        # Footer content
├── index.php                 # Application entry point and router
└── .htaccess                 # Apache configuration
```

## Local Deployment

### Prerequisites Installation

**Mint/Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install apache2 mysql-server php php-mysqli
```

**Fedora/RHEL:**
```bash
sudo dnf install httpd mysql-server php php-mysqlnd
```

### Database Configuration

1. Create environment file:
```bash
touch .env
```

2. copy into `.env` and edit database credentials:
```bash
DB_HOST=localhost
DB_NAME=scrabble_db
DB_USER=scrabble_user
DB_PASS=your_secure_password
```

3. Set file permissions:
```bash
chmod 640 .env
```

### Manual Setup

1. Create database:
```bash
mysql -u root -p
```
```sql
CREATE DATABASE scrabble_db;
CREATE USER 'scrabble_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON scrabble_db.* TO 'scrabble_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

2. Import schema:
```bash
mysql -u scrabble_user -p scrabble_db < database/CREATE.sql
```

3. Configure Apache virtual host in `/etc/apache2/sites-available/quixote.conf`:
```apache
<VirtualHost *:80>
    ServerName quixote.local
    DocumentRoot /path/to/Quixote
    
    <Directory /path/to/Quixote>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

4. Enable site and restart Apache:
```bash
sudo a2ensite quixote
sudo systemctl restart apache2
```

## Docker Deployment

### Quick Start

1. Create environment file:
```bash
touch .env
```

2. Generate secure passwords:
```bash
openssl rand -base64 32
```

3. Copy into `.env` and update with generated passwords:
```bash
DB_HOST=db
DB_NAME=scrabble_db
DB_USER=scrabble_user
DB_PASS=your_generated_password
DB_ROOT_PASS=your_generated_root_password
```

4. Set file permissions:
```bash
chmod 640 .env
```

5. Start services:
```bash
docker-compose up -d
```

6. Access application:
```
http://localhost:8080
```

### Docker Management Commands

**Start services:**
```bash
docker-compose up -d
```

**Stop services:**
```bash
docker-compose down
```

**View logs:**
```bash
docker-compose logs -f
```

**Access MySQL:**
```bash
docker exec -it quixote_db mysql -u scrabble_user -p scrabble_db
```

**Restart after code changes:**
```bash
docker-compose restart web
```

**Complete cleanup (removes data):**
```bash
docker-compose down -v
```

## Rating System

The application implements the Glicko-2 rating system, which extends the Glicko system with a volatility parameter. This provides more accurate ratings by accounting for:

- Rating reliability (rating deviation)
- Consistency of performance (volatility)
- Opponent strength
- Time between competitions

Initial values:
- Rating: 1500
- Rating Deviation: 350
- Volatility: 0.06

## Security Features

- CSRF token protection on all forms
- Parameterized SQL queries to prevent injection
- Input validation and sanitization
- Secure password handling via environment variables
- Restricted directory access via `.htaccess`
- Security headers (X-Frame-Options, X-XSS-Protection, etc.)

## Troubleshooting

### Permission Errors
```bash
# Local deployment
sudo chown -R www-data:www-data /path/to/Quixote
chmod -R 755 /path/to/Quixote
chmod 640 .env

# Docker deployment
docker exec quixote_web chown -R www-data:www-data /var/www/html
```

### Database Connection Issues
Verify credentials in `.env` file match database configuration. For Docker, ensure `DB_HOST=db`.

### Internal Server Error
Check Apache error logs:
```bash
# Local
tail -f /var/log/apache2/error.log

# Docker
docker logs quixote_web
```

## References

- Glicko-2 Rating System: http://www.glicko.net/glicko/glicko2.pdf
- Mark Glickman's Research: http://www.glicko.net/
