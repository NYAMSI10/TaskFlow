<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114120050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE shared (id INT AUTO_INCREMENT NOT NULL, task_id INT DEFAULT NULL, comment VARCHAR(255) DEFAULT NULL, permission VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_138CF4BB8DB60186 (task_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shared_user (shared_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_3765BDD13B943F60 (shared_id), INDEX IDX_3765BDD1A76ED395 (user_id), PRIMARY KEY(shared_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE shared ADD CONSTRAINT FK_138CF4BB8DB60186 FOREIGN KEY (task_id) REFERENCES task (id)');
        $this->addSql('ALTER TABLE shared_user ADD CONSTRAINT FK_3765BDD13B943F60 FOREIGN KEY (shared_id) REFERENCES shared (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shared_user ADD CONSTRAINT FK_3765BDD1A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shared DROP FOREIGN KEY FK_138CF4BB8DB60186');
        $this->addSql('ALTER TABLE shared_user DROP FOREIGN KEY FK_3765BDD13B943F60');
        $this->addSql('ALTER TABLE shared_user DROP FOREIGN KEY FK_3765BDD1A76ED395');
        $this->addSql('DROP TABLE shared');
        $this->addSql('DROP TABLE shared_user');
    }
}
