<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260313072427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversation_user DROP FOREIGN KEY `FK_5AECB5559AC0396`');
        $this->addSql('ALTER TABLE conversation_user DROP FOREIGN KEY `FK_5AECB555A76ED395`');
        $this->addSql('DROP TABLE conversation_user');
        $this->addSql('ALTER TABLE conversation ADD updated_at DATETIME NOT NULL, ADD article_id INT DEFAULT NULL, ADD buyer_id INT DEFAULT NULL, ADD seller_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E97294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E96C755722 FOREIGN KEY (buyer_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E98DE820D9 FOREIGN KEY (seller_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_8A8E26E97294869C ON conversation (article_id)');
        $this->addSql('CREATE INDEX IDX_8A8E26E96C755722 ON conversation (buyer_id)');
        $this->addSql('CREATE INDEX IDX_8A8E26E98DE820D9 ON conversation (seller_id)');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY `FK_B6BD307FF675F31B`');
        $this->addSql('DROP INDEX IDX_B6BD307FF675F31B ON message');
        $this->addSql('ALTER TABLE message ADD is_read TINYINT NOT NULL, ADD sender_id INT DEFAULT NULL, DROP author_id, CHANGE content content VARCHAR(255) NOT NULL, CHANGE conversation_id conversation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FF624B39D ON message (sender_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conversation_user (conversation_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_5AECB5559AC0396 (conversation_id), INDEX IDX_5AECB555A76ED395 (user_id), PRIMARY KEY (conversation_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_uca1400_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE conversation_user ADD CONSTRAINT `FK_5AECB5559AC0396` FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversation_user ADD CONSTRAINT `FK_5AECB555A76ED395` FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E97294869C');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E96C755722');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E98DE820D9');
        $this->addSql('DROP INDEX IDX_8A8E26E97294869C ON conversation');
        $this->addSql('DROP INDEX IDX_8A8E26E96C755722 ON conversation');
        $this->addSql('DROP INDEX IDX_8A8E26E98DE820D9 ON conversation');
        $this->addSql('ALTER TABLE conversation DROP updated_at, DROP article_id, DROP buyer_id, DROP seller_id');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('DROP INDEX IDX_B6BD307FF624B39D ON message');
        $this->addSql('ALTER TABLE message ADD author_id INT NOT NULL, DROP is_read, DROP sender_id, CHANGE content content LONGTEXT NOT NULL, CHANGE conversation_id conversation_id INT NOT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT `FK_B6BD307FF675F31B` FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FF675F31B ON message (author_id)');
    }
}
