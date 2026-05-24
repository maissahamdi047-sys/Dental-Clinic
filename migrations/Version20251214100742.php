<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251214100742 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appointments (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, patient VARCHAR(255) NOT NULL, service VARCHAR(255) NOT NULL, scheduled_at DATETIME NOT NULL, doctor VARCHAR(255) NOT NULL, status VARCHAR(32) NOT NULL, patient_email VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE TABLE contact_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(150) NOT NULL, email VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, message CLOB NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE TABLE patients (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, first_name VARCHAR(150) NOT NULL, last_name VARCHAR(150) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(32) NOT NULL, dob DATE NOT NULL, password_hash VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX uniq_patient_email ON patients (email)');
        $this->addSql('CREATE TABLE payments (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, amount NUMERIC(10, 2) NOT NULL, currency VARCHAR(8) NOT NULL, method VARCHAR(32) NOT NULL, status VARCHAR(32) NOT NULL, reference VARCHAR(64) DEFAULT NULL, created_at DATETIME NOT NULL, appointment_id INTEGER DEFAULT NULL, CONSTRAINT FK_65D29B32E5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointments (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_65D29B32E5B533F9 ON payments (appointment_id)');
        $this->addSql('CREATE TABLE prescriptions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, patient_email VARCHAR(255) NOT NULL, filename VARCHAR(255) NOT NULL, sent_at DATETIME NOT NULL)');
        $this->addSql('CREATE TABLE reviews (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author VARCHAR(150) NOT NULL, email VARCHAR(255) DEFAULT NULL, rating SMALLINT NOT NULL, comment CLOB NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE TABLE services (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description CLOB NOT NULL, price NUMERIC(10, 2) NOT NULL, duration_min INTEGER NOT NULL, category VARCHAR(64) DEFAULT \'General Dentistry\' NOT NULL, image_url VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, role VARCHAR(32) NOT NULL, status VARCHAR(32) NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE appointments');
        $this->addSql('DROP TABLE contact_messages');
        $this->addSql('DROP TABLE patients');
        $this->addSql('DROP TABLE payments');
        $this->addSql('DROP TABLE prescriptions');
        $this->addSql('DROP TABLE reviews');
        $this->addSql('DROP TABLE services');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
