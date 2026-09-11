# 🦷 Dental Clinic Management System

Professional web application for managing dental clinics, developed with Symfony 6.4.

## 📋 Table of Contents

- [Features](#-features)
- [Technology Stack](#️-technology-stack)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#️-configuration)
- [Payment Testing](#-payment-testing)
- [Email Configuration](#-email-configuration)
- [Access](#-access)
- [License](#-license)

## ✨ Features

- **Patient Management**: Registration, profiles, and medical history
- **Appointment Scheduling**: Online calendar with confirmations
- **Administration**: Dashboard, billing, and reports
- **Payments**: Secure Stripe integration
- **Documents**: PDF generation for prescriptions and invoices
- **Security**: Patient data protection (GDPR)

## 🛠️ Technology Stack

- **Backend**: Symfony 6.4, Doctrine ORM, MySQL
- **Frontend**: Twig
- **Services**: Stripe (payments), DomPDF (PDF), Mailer (emails)

## 📦 Requirements

- PHP 8.2 or higher
- Composer
- MySQL 8.0 or higher
- XAMPP (for Windows) or equivalent

## 🚀 Installation

### 1. Clone the Project

```bash
git clone https://github.com/maissahamdi047-sys/cabinet-dentaire.git
cd cabinet-dentaire
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure the Environment

```bash
cp .env .env.local
```

Edit `.env.local` with your credentials. See the [Configuration](#️-configuration) section.

### 4. Create the Database

```bash
# Create the MySQL database
mysql -u root -e "CREATE DATABASE IF NOT EXISTS cabinet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Create the database schema
php bin/console doctrine:schema:create

# Or update the existing schema
php bin/console doctrine:schema:update --force
```

### 5. Start the Server

```bash
php -S localhost:8000 -t public
```

Or:

```bash
symfony server:start
```

The application will be available at:

```text
http://localhost:8000
```

## ⚙️ Configuration

Required environment variables in `.env.local`:

```env
# Database
DATABASE_URL="mysql://root:@127.0.0.1:3306/cabinet?serverVersion=8.0.32&charset=utf8mb4"

# Stripe (optional, for payments)
STRIPE_PUBLIC_KEY="pk_test_your_public_stripe_key"
STRIPE_SECRET_KEY="sk_test_your_secret_stripe_key"

# Mailer (optional, for emails)
MAILER_DSN="null://null"
# To use Mailpit: MAILER_DSN="smtp://localhost:1025"
```

## 💳 Payment Testing

To test payments in development using Stripe:

**Test card (successful payment):**

- Number: `4242 4242 4242 4242`
- Type: Visa
- CVV: `123`
- Expiration date: `12/25`

**To obtain Stripe keys:**

1. Create an account on Stripe.
2. Go to **API Keys**.
3. Copy your test keys (`pk_test_...` and `sk_test_...`).
4. Add them to your `.env.local` file.

## 📧 Email Configuration

### Option 1: Disabled (Development)

```env
MAILER_DSN="null://null"
```

Emails will not be sent. This is the default configuration.

### Option 2: Using Mailpit (Recommended for Development)

1. **Download Mailpit** from its official releases page.
2. **Extract the ZIP file** into a folder, for example:
   ```text
   C:\mailpit
   ```
3. **Start Mailpit**:
   - Open Command Prompt.
   - Navigate to the folder:
     ```bash
     cd C:\mailpit
     ```
   - Run:
     ```bash
     mailpit.exe
     ```
4. **Access the web interface**:
   - Mailpit usually provides the interface at:
     ```text
     http://127.0.0.1:8025
     ```
5. **Configure the mailer**:

   ```env
   MAILER_DSN="smtp://localhost:1025"
   ```

All emails sent by the application will appear in the Mailpit interface.

## 🌐 Access

- **Public Website**: `http://localhost:8000`
- **Administration**: `http://localhost:8000/admin`

## 📄 License

Proprietary - All rights reserved.

---

*Developed for healthcare professionals* 🏥
