<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201008180331 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE season ADD start_year INT DEFAULT NULL, ADD end_year INT DEFAULT NULL, CHANGE title title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE episode ADD number_all INT DEFAULT NULL, ADD path VARCHAR(64) NOT NULL, ADD is_extra TINYINT(1) DEFAULT NULL, CHANGE title title VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE episode DROP number_all, DROP path, DROP is_extra, CHANGE title title VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE season DROP start_year, DROP end_year, CHANGE title title VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
    }
}
