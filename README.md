# LM3 Pharmacy Inventory System

A comprehensive pharmacy inventory and sales management system deployed on AWS cloud infrastructure with Docker containerization.

## Cloud Architecture

User → DuckDNS (lm3pharmacy.duckdns.org) → AWS EC2 → Docker Containers → AWS S3

## ☁️ Cloud Integration Features

### 1. **AWS EC2 Hosting**
- Ubuntu 22.04 LTS instance (t3.micro)
- Secure SSH access with key pairs
- Security groups configured for ports 22 (SSH), 80 (HTTP), 443 (HTTPS)

### 2. **Docker Containerization**
- Nginx container (reverse proxy)
- Laravel PHP-FPM application container
- MySQL 8.0 database container
- Docker Compose for orchestration

### 3. **AWS S3 Storage**
- Automatic receipt storage for every sale
- Report generation and cloud backup
- Permanent storage with unique URLs

### 4. **Free Domain & SSL**
- **DuckDNS**: Free domain (lm3pharmacy.duckdns.org)
- **Let's Encrypt SSL**: Free HTTPS certificates via acme.sh

### 5. **Process Management**
- **tmux**: Keeps containers running after SSH disconnect
- Session persistence and easy reattachment

## 🏗 Deployment Architecture

| Component | Technology | Purpose |
|-----------|------------|---------|
| **Cloud Server** | AWS EC2 | Hosting infrastructure |
| **Containerization** | Docker + Compose | Environment consistency |
| **Web Server** | Nginx | Reverse proxy |
| **Database** | MySQL 8.0 | Data persistence |
| **Storage** | AWS S3 | Receipts & reports |
| **Domain** | DuckDNS | Free domain name |
| **SSL** | Let's Encrypt | HTTPS security |
| **Process Manager** | tmux | 24/7 uptime |

## 🔧 Key Deployment Commands

```bash
# EC2 Setup
ssh -i key.pem ubuntu@ec2-ip
sudo apt update && sudo apt install docker.io docker-compose -y

# Deploy Application
git clone https://github.com/yourusername/pharmacy-system.git
cd pharmacy-system
docker-compose up -d

# SSL Setup (after DuckDNS)
curl https://get.acme.sh | sh
~/.acme.sh/acme.sh --issue -d yourdomain.duckdns.org --standalone

# Keep Running
tmux new -s laravel
docker-compose up -d
# Ctrl+B, D to detach