<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190528193833 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE transfer_data (id INT AUTO_INCREMENT NOT NULL, file_name VARCHAR(255) NOT NULL, link VARCHAR(16000) NOT NULL, is_used TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_transfer_data (user_id INT NOT NULL, transfer_data_id INT NOT NULL, INDEX IDX_7B887251A76ED395 (user_id), INDEX IDX_7B8872518840D377 (transfer_data_id), PRIMARY KEY(user_id, transfer_data_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_transfer_data ADD CONSTRAINT FK_7B887251A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_transfer_data ADD CONSTRAINT FK_7B8872518840D377 FOREIGN KEY (transfer_data_id) REFERENCES transfer_data (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_transfer_data DROP FOREIGN KEY FK_7B8872518840D377');
        $this->addSql('ALTER TABLE user_transfer_data DROP FOREIGN KEY FK_7B887251A76ED395');
        $this->addSql('DROP TABLE transfer_data');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_transfer_data');
    }
}
