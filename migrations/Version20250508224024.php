<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250508224024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE collection DROP FOREIGN KEY FK_CS_SidS
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE collection_card (idCollection INT NOT NULL, idCard INT NOT NULL, INDEX IDX_5C63B43D7EB97BD8 (idCollection), INDEX IDX_5C63B43D65E9C64D (idCard), PRIMARY KEY(idCollection, idCard)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE collection_card ADD CONSTRAINT FK_5C63B43D7EB97BD8 FOREIGN KEY (idCollection) REFERENCES collection (idCollection)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE collection_card ADD CONSTRAINT FK_5C63B43D65E9C64D FOREIGN KEY (idCard) REFERENCES card (idCard)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tokenauth DROP FOREIGN KEY FK_TAidU_UidU
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tokenauth
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE state
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX cardName ON card
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE collection DROP INDEX collOwner, ADD INDEX IDX_FC4D6532FE6E88D7 (idUser)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE collection DROP FOREIGN KEY FK_CidC_CidC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE collection DROP FOREIGN KEY FK_CidU_UIdU
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX FK_CS_SidS ON collection
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX FK_CidC_CidC ON collection
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE collection DROP idCard, CHANGE idUser idUser INT DEFAULT NULL, CHANGE purchasePrice purchase_price NUMERIC(6, 2) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE collection ADD CONSTRAINT FK_FC4D6532FE6E88D7 FOREIGN KEY (idUser) REFERENCES user (idUser)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deck DROP FOREIGN KEY FK_DidU_UidU
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deck CHANGE idUser idUser INT DEFAULT NULL, CHANGE coverImg coverImg VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deck ADD CONSTRAINT FK_4FAC3637FE6E88D7 FOREIGN KEY (idUser) REFERENCES user (idUser)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deck RENAME INDEX fk_ddo_uun TO IDX_4FAC3637FE6E88D7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deckcard DROP FOREIGN KEY FK_DCidC_CidC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deckcard DROP FOREIGN KEY FK_DCidD_DidD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deckcard CHANGE idDeck idDeck INT DEFAULT NULL, CHANGE idCard idCard INT DEFAULT NULL, CHANGE cardQuantity cardQuantity INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deckcard ADD CONSTRAINT FK_1FEDC1333C5168A9 FOREIGN KEY (idDeck) REFERENCES deck (idDeck)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deckcard ADD CONSTRAINT FK_1FEDC13365E9C64D FOREIGN KEY (idCard) REFERENCES card (idCard)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deckcard RENAME INDEX fk_dcidd_didd TO IDX_1FEDC1333C5168A9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deckcard RENAME INDEX fk_dcidc_cidc TO IDX_1FEDC13365E9C64D
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX profilePic ON user
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX username ON user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE username username VARCHAR(255) NOT NULL, CHANGE isAuth isAuth TINYINT(1) DEFAULT 0 NOT NULL, CHANGE profilePic profilePic VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user RENAME INDEX email TO UNIQ_8D93D649E7927C74
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE tokenauth (token VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, idUser INT NOT NULL, expirationDate DATE NOT NULL, UNIQUE INDEX idUser (idUser), PRIMARY KEY(token)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE state (idState INT NOT NULL, stateName VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, UNIQUE INDEX stateName (stateName), PRIMARY KEY(idState)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tokenauth ADD CONSTRAINT FK_TAidU_UidU FOREIGN KEY (idUser) REFERENCES user (idUser) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE collection_card DROP FOREIGN KEY FK_5C63B43D7EB97BD8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE collection_card DROP FOREIGN KEY FK_5C63B43D65E9C64D
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE collection_card
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX cardName ON card (cardName)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE username username VARCHAR(25) NOT NULL, CHANGE isAuth isAuth INT DEFAULT 0 NOT NULL COMMENT 'se añade a la bd antes de autenticarse por lo que por defecto está a falso (0) hasta que se autentique y pase a verdadero (1)', CHANGE profilePic profilePic VARCHAR(255) DEFAULT NULL COMMENT 'dirección del fichero jpg/png en el servidor'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX profilePic ON user (profilePic)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX username ON user (username)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user RENAME INDEX uniq_8d93d649e7927c74 TO email
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE collection DROP INDEX IDX_FC4D6532FE6E88D7, ADD UNIQUE INDEX collOwner (idUser)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE collection DROP FOREIGN KEY FK_FC4D6532FE6E88D7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE collection ADD idCard INT NOT NULL, CHANGE idUser idUser INT NOT NULL, CHANGE purchase_price purchasePrice NUMERIC(6, 2) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE collection ADD CONSTRAINT FK_CidC_CidC FOREIGN KEY (idCard) REFERENCES card (idCard) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE collection ADD CONSTRAINT FK_CidU_UIdU FOREIGN KEY (idUser) REFERENCES user (idUser) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE collection ADD CONSTRAINT FK_CS_SidS FOREIGN KEY (state) REFERENCES state (idState) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX FK_CS_SidS ON collection (state)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX FK_CidC_CidC ON collection (idCard)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deckCard DROP FOREIGN KEY FK_1FEDC1333C5168A9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deckCard DROP FOREIGN KEY FK_1FEDC13365E9C64D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deckCard CHANGE cardQuantity cardQuantity INT DEFAULT 1 NOT NULL, CHANGE idDeck idDeck INT NOT NULL, CHANGE idCard idCard INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deckCard ADD CONSTRAINT FK_DCidC_CidC FOREIGN KEY (idCard) REFERENCES card (idCard) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deckCard ADD CONSTRAINT FK_DCidD_DidD FOREIGN KEY (idDeck) REFERENCES deck (idDeck) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deckCard RENAME INDEX idx_1fedc13365e9c64d TO FK_DCidC_CidC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deckCard RENAME INDEX idx_1fedc1333c5168a9 TO FK_DCidD_DidD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deck DROP FOREIGN KEY FK_4FAC3637FE6E88D7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deck CHANGE coverImg coverImg VARCHAR(255) DEFAULT '""', CHANGE idUser idUser INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deck ADD CONSTRAINT FK_DidU_UidU FOREIGN KEY (idUser) REFERENCES user (idUser) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE deck RENAME INDEX idx_4fac3637fe6e88d7 TO FK_DDO_UUN
        SQL);
    }
}
