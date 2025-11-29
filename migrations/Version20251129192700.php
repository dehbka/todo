<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251129192700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema for todos and comments';
    }

    public function up(Schema $schema): void
    {
        // Create todos table
        $this->addSql("CREATE TABLE todos (id VARCHAR(36) NOT NULL, title VARCHAR(200) NOT NULL, status VARCHAR(20) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX idx_todos_created_at ON todos (created_at)");
        $this->addSql("CREATE INDEX idx_todos_status ON todos (status)");

        // Create comments table
        $this->addSql("CREATE TABLE comments (id VARCHAR(36) NOT NULL, todo_id VARCHAR(36) NOT NULL, message VARCHAR(2000) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX idx_comments_todo_id ON comments (todo_id)");
        $this->addSql("CREATE INDEX idx_comments_created_at ON comments (created_at)");
        $this->addSql("ALTER TABLE comments ADD CONSTRAINT FK_comments_todo FOREIGN KEY (todo_id) REFERENCES todos (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comments DROP CONSTRAINT FK_comments_todo');
        $this->addSql('DROP TABLE comments');
        $this->addSql('DROP TABLE todos');
    }
}
