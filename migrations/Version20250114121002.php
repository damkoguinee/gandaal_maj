<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250114121002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE matiere ADD CONSTRAINT FK_9014574ABCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_matiere (id)');
        $this->addSql('CREATE INDEX IDX_9014574ABCF5E72D ON matiere (categorie_id)');
        $this->addSql('ALTER TABLE matiere RENAME INDEX fk_9014574a5200282e TO IDX_9014574A5200282E');
        $this->addSql('ALTER TABLE note_eleve ADD CONSTRAINT FK_89B1A620C583534E FOREIGN KEY (devoir_id) REFERENCES devoir_eleve (id)');
        $this->addSql('ALTER TABLE note_eleve ADD CONSTRAINT FK_89B1A6205DAC5993 FOREIGN KEY (inscription_id) REFERENCES inscription (id)');
        $this->addSql('ALTER TABLE note_eleve ADD CONSTRAINT FK_89B1A620C74AC7FE FOREIGN KEY (saisie_par_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_89B1A620C583534E ON note_eleve (devoir_id)');
        $this->addSql('CREATE INDEX IDX_89B1A6205DAC5993 ON note_eleve (inscription_id)');
        $this->addSql('CREATE INDEX IDX_89B1A620C74AC7FE ON note_eleve (saisie_par_id)');
        $this->addSql('ALTER TABLE personnel ADD signature VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE personnel ADD CONSTRAINT FK_A6BCF3DE57889920 FOREIGN KEY (fonction_id) REFERENCES config_fonction (id)');
        $this->addSql('ALTER TABLE personnel ADD CONSTRAINT FK_A6BCF3DEBF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_A6BCF3DE57889920 ON personnel (fonction_id)');
        $this->addSql('ALTER TABLE personnel_actif ADD CONSTRAINT FK_2F3C4EBC1C109075 FOREIGN KEY (personnel_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_2F3C4EBC1C109075 ON personnel_actif (personnel_id)');
        $this->addSql('ALTER TABLE salaire ADD CONSTRAINT FK_3BCBBD11A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_3BCBBD11A76ED395 ON salaire (user_id)');
        $this->addSql('ALTER TABLE tranche_paiement CHANGE nom nom VARCHAR(100) NOT NULL, CHANGE promo promo VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE tranche_paiement ADD CONSTRAINT FK_57F14A26FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('CREATE INDEX IDX_57F14A26FF631228 ON tranche_paiement (etablissement_id)');
        $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(180) NOT NULL, CHANGE roles roles JSON NOT NULL, CHANGE password password VARCHAR(255) NOT NULL, CHANGE type type VARCHAR(255) NOT NULL, CHANGE type_user type_user VARCHAR(100) NOT NULL, CHANGE nom nom VARCHAR(100) NOT NULL, CHANGE prenom prenom VARCHAR(150) NOT NULL, CHANGE email email VARCHAR(150) DEFAULT NULL, CHANGE telephone telephone VARCHAR(15) DEFAULT NULL, CHANGE adresse adresse VARCHAR(255) DEFAULT NULL, CHANGE ville ville VARCHAR(150) NOT NULL, CHANGE pays pays VARCHAR(150) NOT NULL, CHANGE matricule matricule VARCHAR(20) NOT NULL, CHANGE sexe sexe VARCHAR(10) NOT NULL, CHANGE lieu_naissance lieu_naissance VARCHAR(150) DEFAULT NULL, CHANGE statut statut VARCHAR(10) NOT NULL, CHANGE photo photo VARCHAR(255) DEFAULT NULL, CHANGE nationalite nationalite VARCHAR(100) DEFAULT NULL, CHANGE categorie categorie VARCHAR(15) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649FF631228 ON user (etablissement_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE matiere DROP FOREIGN KEY FK_9014574ABCF5E72D');
        $this->addSql('DROP INDEX IDX_9014574ABCF5E72D ON matiere');
        $this->addSql('ALTER TABLE matiere RENAME INDEX idx_9014574a5200282e TO FK_9014574A5200282E');
        $this->addSql('ALTER TABLE note_eleve DROP FOREIGN KEY FK_89B1A620C583534E');
        $this->addSql('ALTER TABLE note_eleve DROP FOREIGN KEY FK_89B1A6205DAC5993');
        $this->addSql('ALTER TABLE note_eleve DROP FOREIGN KEY FK_89B1A620C74AC7FE');
        $this->addSql('DROP INDEX IDX_89B1A620C583534E ON note_eleve');
        $this->addSql('DROP INDEX IDX_89B1A6205DAC5993 ON note_eleve');
        $this->addSql('DROP INDEX IDX_89B1A620C74AC7FE ON note_eleve');
        $this->addSql('ALTER TABLE personnel DROP FOREIGN KEY FK_A6BCF3DE57889920');
        $this->addSql('ALTER TABLE personnel DROP FOREIGN KEY FK_A6BCF3DEBF396750');
        $this->addSql('DROP INDEX IDX_A6BCF3DE57889920 ON personnel');
        $this->addSql('ALTER TABLE personnel DROP signature');
        $this->addSql('ALTER TABLE personnel_actif DROP FOREIGN KEY FK_2F3C4EBC1C109075');
        $this->addSql('DROP INDEX IDX_2F3C4EBC1C109075 ON personnel_actif');
        $this->addSql('ALTER TABLE salaire DROP FOREIGN KEY FK_3BCBBD11A76ED395');
        $this->addSql('DROP INDEX IDX_3BCBBD11A76ED395 ON salaire');
        $this->addSql('ALTER TABLE tranche_paiement DROP FOREIGN KEY FK_57F14A26FF631228');
        $this->addSql('DROP INDEX IDX_57F14A26FF631228 ON tranche_paiement');
        $this->addSql('ALTER TABLE tranche_paiement CHANGE nom nom VARCHAR(50) NOT NULL, CHANGE promo promo VARCHAR(4) NOT NULL');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649FF631228');
        $this->addSql('DROP INDEX IDX_8D93D649FF631228 ON user');
        $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(180) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE password password VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE nom nom VARCHAR(100) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE prenom prenom VARCHAR(150) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE email email VARCHAR(150) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE telephone telephone VARCHAR(15) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE adresse adresse VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE ville ville VARCHAR(150) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE pays pays VARCHAR(150) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE matricule matricule VARCHAR(20) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE sexe sexe VARCHAR(10) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE lieu_naissance lieu_naissance VARCHAR(150) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE statut statut VARCHAR(10) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE photo photo VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE type_user type_user VARCHAR(100) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE nationalite nationalite VARCHAR(100) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE categorie categorie VARCHAR(15) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE type type VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
