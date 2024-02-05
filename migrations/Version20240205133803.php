<?php

declare(strict_types=1);

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240205133803 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $addSetting = fn (array $parameters) => $this->addSql('INSERT INTO `setting` (`name`, `description`, `type`, `form_type`, `value`) VALUES (:name, :description, :type, :form_type, :value)', $parameters);

        $addSetting([
            'name' => 'admin_gdpr',
            'description' => 'Information on GDPR (terms and conditions)',
            'type' => 'text',
            'form_type' => 'texteditor',
            'value' => json_encode(
                <<<'CONTENT'
<h1>General Data Protection Regulation (GDPR)</h1>

<p><a href="https://www.eugdpr.org/">https://www.eugdpr.org/</a></p>
CONTENT
            )]);

        $addSetting([
            'name' => 'app_about_content',
            'description' => <<<'DESCRIPTION'
Content on the "About" page.

Placeholders:

app://guide_url: Url to the app guide
DESCRIPTION,
            'type' => 'text',
            'form_type' => 'texteditor',
            'value' => json_encode(
                <<<'CONTENT'
<p><a href="app://guide_url">Se app-vejledningen</a></p>
CONTENT
            )]);

        $addSetting([
            'name' => 'app_about_header',
            'description' => <<<'DESCRIPTION'
The about header
DESCRIPTION,
            'type' => 'string',
            'form_type' => 'text',
            'value' => json_encode(
                <<<'CONTENT'
Om denne app
CONTENT
            )]);

        $addSetting([
            'name' => 'app_about_lead',
            'description' => <<<'DESCRIPTION'
The about lead
DESCRIPTION,
            'type' => 'string',
            'form_type' => 'text',
            'value' => json_encode(
                <<<'CONTENT'
CONTENT
            )]);

        $addSetting([
            'name' => 'app_expired_content',
            'description' => <<<'DESCRIPTION'
The app expired content


Placeholders:

%expired_on%: The date on which the app expired.
%expired_at%: The date and time at which the app expired.
DESCRIPTION,
            'type' => 'text',
            'form_type' => 'texteditor',
            'value' => json_encode(
                <<<'CONTENT'
<p>Din Dokk1 app udløb %expired_on%</p>
CONTENT
            )]);

        $addSetting([
            'name' => 'app_expired_header',
            'description' => <<<'DESCRIPTION'
The app expired header
DESCRIPTION,
            'type' => 'string',
            'form_type' => 'text',
            'value' => json_encode(
                <<<'CONTENT'
Din app er udløbet
CONTENT
            )]);

        $addSetting([
            'name' => 'app_terms_content',
            'description' => 'The terms and conditions for using the app',
            'type' => 'text',
            'form_type' => 'texteditor',
            'value' => json_encode(
                <<<'CONTENT'
<h1>Betingelser for brug af Dokk1 app</h1>

<p>En Dokk1 medarbejder har givet dig ret til adgang til de lukkede områder i Dokk1 via fremsendte app i en begrænset periode.</p>

<p>Du kan bruge adgangsappen til at få en adgangskode for én dag ad gangen.</p>

<p>Koden er personlig, og du må ikke overdrage den til andre.</p>

<p>Du må heller ikke tage andre personer med ind ved hjælp af koden.</p>

<p>Du skal kunne identificere dig ved at kunne fremvise din adgangstilladelse, som findes i Dokk1 app'en på din mobil. Du skal derfor altid medbringe din mobil, når du befinder dig på Dokk1.</p>

<p>Adgangskoden gælder kun i en dag.</p>

<p>Har Dokk1 medarbejderen givet dig ret til at komme ind i Dokk1 flere dage, skal du hver dag bruge app'en til at få en ny kode.</p>

<h1>Hvad indhenter vi af persondata?</h1>

<p>Vi behandler personoplysninger om dig i form af:</p>

<ul>
	<li>Navn</li>
	<li>Mobilnummer</li>
	<li>Hvilken adgang der er tildelt</li>
	<li>Hvilken periode der er tildelt</li>
</ul>

<p>Det gør vi for at kunne administrere din adgang.</p>

<p>Det er frivilligt, om du vil gøre brug af tjenesten. Hvis du accepterer betingelserne, er app'en klar til brug. Hvis du ikke accepterer betingelserne, vil dine personoplysninger blive slettet i systemet efter 30 dage.</p>

<h1>Oplysning om behandling af persondata</h1>

<p>Inden du accepterer betingelserne, har vi pligt til at oplyse om databehandlingen jf. Databeskyttelsesforordningen. Vi skal informere dig om en række detaljer i vores håndtering af dine oplysninger, og oplyse dig om dine rettigheder i den forbindelse.</p>

<h1>Retsgrundlag</h1>

<p>Vores behandling af personoplysningerne sker på baggrund af Databeskyttelseslovens artikel 6, stk. 1, litra a (samtykke).</p>

<p>Oplysningerne i elektronisk form bliver fysisk opbevaret sikkert hos vores leverandører så længe du har adgang til brug af app'en. Oplysningerne videregives ikke til andre, og data bliver udelukkende behandlet hos vores leverandører.</p>

<p>Dine rettigheder</p>

<p>Vi opbevarer dine personoplysninger i den periode, du har adgang til app'en. Dine oplysninger bliver slettet 30 dage efter din adgangsperiode er udløbet. Du har til enhver tid ret til at trække dit samtykke tilbage.</p>

<p>Du har ret til at anmode om indsigt i oplysningerne, vi har om dig.<br />
Du har ret til at anmode om berigtigelse eller sletning af oplysningerne.</p>

<h1>Hvem anvender dine oplysninger?</h1>

<p>Den dataansvarlige hører under Aarhus Kommune, som overholder EU's bekendtgørelse om databeskyttelse - General Data Protection Regulation (GDPR). https://www.eugdpr.org/</p>

<p>Dine personoplysninger behandles alene af dataansvarlig:</p>

<p>Aarhus Kommune<br />
Drift og ressourcer<br />
Hack Kampmanns Plads 2<br />
8000 Aarhus C</p>

<p>Telefon: 89409200<br />
E-mail: <a href="mailto:dokk1@aarhus.dk">dokk1@aarhus.dk</a></p>

<p>Har du spørgsmål i forbindelse med Aarhus Kommunes behandlinger af dine oplysninger, så kan du også kontakte Aarhus Kommunes databeskyttelsesrådgiver på mail databeskyttelsesraadgiver@aarhus.dk.</p>

<p>Det er muligt at klage til Datatilsynet over behandlingen af personoplysningerne på www.datatilsynet.dk</p>
CONTENT
            ),
        ]);

        $addSetting([
                    'name' => 'app_terms_header',
                    'description' => <<<'DESCRIPTION'
The app terms header
DESCRIPTION,
                    'type' => 'string',
                    'form_type' => 'text',
                    'value' => json_encode(
                        <<<'CONTENT'
Accepter betingelser.
CONTENT
                    )]);

        $addSetting([
            'name' => 'app_terms_lead',
            'description' => 'The app terms lead',
            'type' => 'string',
            'form_type' => 'text',
            'value' => json_encode(
                <<<'CONTENT'
CONTENT
            ),
        ]);

        $addSetting([
            'name' => 'guest_app_sms_body_template',
            'description' => <<<'DESCRIPTION'
Template for message sent to guest with generated code.

Placeholders:

{{ guest.name }}
{{ code.template.name }}
{{ code_valid_time_period }}
{{ site_name }}
DESCRIPTION,
            'type' => 'text',
            'form_type' => 'textarea',
            'value' => json_encode(
                <<<'CONTENT'
Hej {{ guest.name }}

Her er din adgangskode til Dokk1: {{ code.identifier }}

Koden indtastes på tal-panelet ved siden af døren eller inde i elevatoren. OBS: Når du taster koden skal du huske at afslutte med E, dvs. du skal taste “{{ code.identifier }}E”.

Koden giver adgang til “{{ code.template.name }}” i tidsrummet {{ code_valid_time_period }}.

Bemærk at der kan gå op til 30 minutter før koden er aktiv.

Venlig hilsen
{{ site_name }}
CONTENT
            ),
        ]);

        $addSetting([
            'name' => 'guest_code_sms_template',
            'description' => <<<'DESCRIPTION'
Template for message sent to guest with generated code.

Placeholders:

{{ guest.name }}
{{ code.template.name }}
{{ code_valid_time_period }}
{{ site_name }}
DESCRIPTION,
            'type' => 'text',
            'form_type' => 'textarea',
            'value' => json_encode(
                <<<'CONTENT'
Hej {{ guest.name }}

Her er din adgangskode til Dokk1: {{ code.identifier }}

Koden indtastes på tal-panelet ved siden af døren eller inde i elevatoren. OBS: Når du taster koden skal du huske at afslutte med E, dvs. du skal taste “{{ code.identifier }}E”.

Koden giver adgang til “{{ code.template.name }}” i tidsrummet {{ code_valid_time_period }}.

Bemærk at der kan gå op til 30 minutter før koden er aktiv.

Venlig hilsen
{{ site_name }}
CONTENT
            ),
        ]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM `setting` WHERE `name` = 'admin_gdpr'");
        $this->addSql("DELETE FROM `setting` WHERE `name` = 'app_about_content'");
        $this->addSql("DELETE FROM `setting` WHERE `name` = 'app_about_header'");
        $this->addSql("DELETE FROM `setting` WHERE `name` = 'app_about_lead'");
        $this->addSql("DELETE FROM `setting` WHERE `name` = 'app_expired_content'");
        $this->addSql("DELETE FROM `setting` WHERE `name` = 'app_expired_header'");
        $this->addSql("DELETE FROM `setting` WHERE `name` = 'app_terms_content'");
        $this->addSql("DELETE FROM `setting` WHERE `name` = 'app_terms_header'");
        $this->addSql("DELETE FROM `setting` WHERE `name` = 'app_terms_lead'");
        $this->addSql("DELETE FROM `setting` WHERE `name` = 'guest_app_sms_body_template'");
        $this->addSql("DELETE FROM `setting` WHERE `name` = 'guest_code_sms_template'");
    }
}
