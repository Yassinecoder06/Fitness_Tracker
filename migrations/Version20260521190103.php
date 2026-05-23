<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260521190103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE diary_notes (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, note LONGTEXT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_400406ADA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE exercise_logs (id INT AUTO_INCREMENT NOT NULL, exercise_name VARCHAR(100) NOT NULL, duration INT NOT NULL, calories_burned INT NOT NULL, date DATE NOT NULL, user_id INT NOT NULL, INDEX IDX_A9AAA4EEA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE meals (id INT AUTO_INCREMENT NOT NULL, food_name VARCHAR(100) NOT NULL, meal_type VARCHAR(50) NOT NULL, calories INT NOT NULL, protein INT NOT NULL, carbs INT NOT NULL, fat INT NOT NULL, date DATE NOT NULL, user_id INT NOT NULL, INDEX IDX_E229E6EAA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE water_intake (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, glasses INT DEFAULT 0 NOT NULL, user_id INT NOT NULL, INDEX IDX_77832F8FA76ED395 (user_id), UNIQUE INDEX user_date_unique (user_id, date), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE diary_notes ADD CONSTRAINT FK_400406ADA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercise_logs ADD CONSTRAINT FK_A9AAA4EEA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meals ADD CONSTRAINT FK_E229E6EAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE water_intake ADD CONSTRAINT FK_77832F8FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE diary_notes DROP FOREIGN KEY FK_400406ADA76ED395');
        $this->addSql('ALTER TABLE exercise_logs DROP FOREIGN KEY FK_A9AAA4EEA76ED395');
        $this->addSql('ALTER TABLE meals DROP FOREIGN KEY FK_E229E6EAA76ED395');
        $this->addSql('ALTER TABLE water_intake DROP FOREIGN KEY FK_77832F8FA76ED395');
        $this->addSql('DROP TABLE diary_notes');
        $this->addSql('DROP TABLE exercise_logs');
        $this->addSql('DROP TABLE meals');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE water_intake');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
