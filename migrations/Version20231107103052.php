<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231107103052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO admin
(id, username, roles, password)
VALUES (
nextval('admin_id_seq'), 
'admin', 
'[\"ROLE_ADMIN\"]', 
'\$2y\$13\$FTBGTzf6u7G/6BUrfBbBru1zOsk3bR7SKUWMQ/5gU1Gqf8kTKxII.'
)");
    }

    public function down(Schema $schema): void
    {
    }
}
