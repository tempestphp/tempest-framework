<?php

namespace Tempest\Intl\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Intl\Catalog\XliffParser;
use Tempest\Intl\IntlConfig;
use Tempest\Intl\Locale;
use Tempest\Intl\MessageFormat\Formatter\MessageFormatter;
use Tempest\Intl\MessageFormat\Functions\NumberFunction;
use Tempest\Intl\MessageFormat\Functions\StringFunction;

final class XliffParserTest extends TestCase
{
    #[Test]
    public function parses_xliff_1_2_resource_identifiers_and_source_aliases(): void
    {
        $messages = XliffParser::parse(<<<'XLIFF'
        <xliff xmlns="urn:oasis:names:tc:xliff:document:1.2" version="1.2">
          <file original="namespace1" datatype="plaintext" source-language="en-US" target-language="de-CH">
            <body>
              <trans-unit id="key1">
                <source>Hello</source>
                <target>Hallo</target>
              </trans-unit>
              <trans-unit id="key.nested">
                <source>XLIFF Data Manager</source>
                <target>XLIFF Daten Manager</target>
              </trans-unit>
              <trans-unit id="keyWithEmptySource">
                <source></source>
                <target>Anything</target>
              </trans-unit>
              <trans-unit id="generated-id" resname="account.password">
                <source>Password</source>
                <target>Passwort</target>
              </trans-unit>
            </body>
          </file>
        </xliff>
        XLIFF);

        $this->assertSame('Hallo', $messages['key1']);
        $this->assertSame('Hallo', $messages['Hello']);
        $this->assertSame('XLIFF Daten Manager', $messages['key.nested']);
        $this->assertSame('Anything', $messages['keyWithEmptySource']);
        $this->assertSame('Passwort', $messages['generated-id']);
        $this->assertSame('Passwort', $messages['account.password']);
        $this->assertSame('Passwort', $messages['Password']);
    }

    #[Test]
    public function preserves_xliff_1_2_inline_equivalent_text(): void
    {
        $messages = XliffParser::parse(<<<'XLIFF'
        <xliff xmlns="urn:oasis:names:tc:xliff:document:1.2" version="1.2">
          <file original="ng2.template" datatype="plaintext" source-language="en-US" target-language="de-CH">
            <body>
              <trans-unit id="greeting">
                <source>Hello <x id="INTERPOLATION" equiv-text="{$name}"/>!</source>
                <target>Hallo <x id="INTERPOLATION" equiv-text="{$name}"/>!</target>
              </trans-unit>
            </body>
          </file>
        </xliff>
        XLIFF);

        $this->assertSame('Hallo {$name}!', $messages['greeting']);
    }

    #[Test]
    public function parses_xliff_2_resource_identifiers_and_source_aliases(): void
    {
        $messages = XliffParser::parse(<<<'XLIFF'
        <xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0" srcLang="en-US" trgLang="de-CH">
          <file id="namespace1">
            <unit id="key1">
              <segment>
                <source>Hello</source>
                <target>Hallo</target>
              </segment>
            </unit>
            <unit id="keyWithEmptySource">
              <segment>
                <source></source>
                <target>Anything</target>
              </segment>
            </unit>
            <unit id="generated-id" name="account.password">
              <segment>
                <source>Password</source>
                <target>Passwort</target>
              </segment>
            </unit>
          </file>
        </xliff>
        XLIFF);

        $this->assertSame('Hallo', $messages['key1']);
        $this->assertSame('Hallo', $messages['Hello']);
        $this->assertSame('Anything', $messages['keyWithEmptySource']);
        $this->assertSame('Passwort', $messages['generated-id']);
        $this->assertSame('Passwort', $messages['account.password']);
        $this->assertSame('Passwort', $messages['Password']);
    }

    #[Test]
    public function concatenates_xliff_2_segments_in_target_order(): void
    {
        $messages = XliffParser::parse(<<<'XLIFF'
        <xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0" srcLang="en" trgLang="fr">
          <file id="messages">
            <unit id="paragraph" name="message.paragraph">
              <segment id="a">
                <source>Sentence A.</source>
                <target order="3">Phrase A.</target>
              </segment>
              <ignorable id="space">
                <source> </source>
                <target order="2"> </target>
              </ignorable>
              <segment id="b">
                <source>Sentence B.</source>
                <target order="1">Phrase B.</target>
              </segment>
            </unit>
          </file>
        </xliff>
        XLIFF);

        $this->assertSame('Phrase B. Phrase A.', $messages['paragraph']);
        $this->assertSame('Phrase B. Phrase A.', $messages['message.paragraph']);
        $this->assertSame('Phrase A.', $messages['Sentence A.']);
        $this->assertSame('Phrase B.', $messages['Sentence B.']);
    }

    #[Test]
    public function preserves_xliff_2_inline_content(): void
    {
        $messages = XliffParser::parse(<<<'XLIFF'
        <xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0" srcLang="en" trgLang="fr">
          <file id="messages">
            <unit id="greeting">
              <originalData>
                <data id="variable">{$name}</data>
                <data id="strong-start">{#strong}</data>
                <data id="strong-end">{/strong}</data>
              </originalData>
              <segment>
                <source>Hello <ph id="variable" dataRef="variable"/> <pc id="strong" dataRefStart="strong-start" dataRefEnd="strong-end">friend</pc><cp hex="0021"/></source>
                <target>Bonjour <ph id="variable" dataRef="variable"/> <pc id="strong" dataRefStart="strong-start" dataRefEnd="strong-end">ami</pc><cp hex="0021"/></target>
              </segment>
            </unit>
          </file>
        </xliff>
        XLIFF);

        $this->assertSame('Bonjour {$name} {#strong}ami{/strong}!', $messages['greeting']);
    }

    #[TestWith(['2.0'])]
    #[TestWith(['2.1'])]
    #[TestWith(['2.2'])]
    #[Test]
    public function supports_xliff_2_versions(string $version): void
    {
        $messages = XliffParser::parse(<<<XLIFF
        <xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="{$version}" srcLang="en" trgLang="fr">
          <file id="messages">
            <unit id="hello"><segment><source>Hello</source><target>Bonjour</target></segment></unit>
          </file>
        </xliff>
        XLIFF);

        $this->assertSame('Bonjour', $messages['hello']);
    }

    #[Test]
    public function parses_xliff_2_2_plural_gender_select_messages(): void
    {
        $messages = XliffParser::parse(<<<'XLIFF'
        <xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" xmlns:pgs="urn:oasis:names:tc:xliff:pgs:1.0" version="2.2" srcLang="en" trgLang="fr">
          <file id="messages">
            <unit id="files" name="file_deleted" pgs:switch="plural:file_count">
              <segment id="none" pgs:case="0">
                <source>You deleted no file.</source>
                <target>Vous n'avez supprimé aucun fichier.</target>
              </segment>
              <segment id="one" pgs:case="1">
                <source>You deleted one file.</source>
                <target>Vous avez supprimé un fichier.</target>
              </segment>
              <segment id="other" pgs:case="other">
                <source>You deleted <ph id="count" disp="file_count"/> files.</source>
                <target>Vous avez supprimé <ph id="count" disp="file_count"/> fichiers.</target>
              </segment>
            </unit>
          </file>
        </xliff>
        XLIFF);

        $this->assertSame("Vous n'avez supprimé aucun fichier.", $this->format($messages['file_deleted'], file_count: 0));
        $this->assertSame('Vous avez supprimé un fichier.', $this->format($messages['file_deleted'], file_count: 1));
        $this->assertSame('Vous avez supprimé 3 fichiers.', $this->format($messages['file_deleted'], file_count: 3));
        $this->assertSame($messages['file_deleted'], $messages['files']);
    }

    #[Test]
    public function parses_xliff_2_2_combined_selectors(): void
    {
        $messages = XliffParser::parse(<<<'XLIFF'
        <xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" xmlns:pgs="urn:oasis:names:tc:xliff:pgs:1.0" version="2.2" srcLang="en" trgLang="fr">
          <file id="messages">
            <unit id="party" name="party_host" pgs:switch="gender:host_gender plural:guest_count">
              <segment id="female-none" pgs:case="feminine 0">
                <source><ph id="host" disp="host_name"/> invited nobody.</source>
                <target><ph id="host" disp="host_name"/> n'a invité personne.</target>
              </segment>
              <segment id="female-other" pgs:case="feminine other">
                <source><ph id="host" disp="host_name"/> invited <ph id="count" disp="guest_count"/> guests.</source>
                <target><ph id="host" disp="host_name"/> a invité <ph id="count" disp="guest_count"/> convives.</target>
              </segment>
              <segment id="other-none" pgs:case="other 0">
                <source><ph id="host" disp="host_name"/> invited nobody.</source>
                <target><ph id="host" disp="host_name"/> n'a invité personne.</target>
              </segment>
              <segment id="other-other" pgs:case="other other">
                <source><ph id="host" disp="host_name"/> invited <ph id="count" disp="guest_count"/> guests.</source>
                <target><ph id="host" disp="host_name"/> a invité <ph id="count" disp="guest_count"/> convives.</target>
              </segment>
            </unit>
          </file>
        </xliff>
        XLIFF);

        $this->assertSame(
            "Jeanne n'a invité personne.",
            $this->format($messages['party_host'], host_gender: 'feminine', guest_count: 0, host_name: 'Jeanne'),
        );
        $this->assertSame(
            'Jeanne a invité 3 convives.',
            $this->format($messages['party_host'], host_gender: 'feminine', guest_count: 3, host_name: 'Jeanne'),
        );
        $this->assertSame(
            "Claude n'a invité personne.",
            $this->format($messages['party_host'], host_gender: 'masculine', guest_count: 0, host_name: 'Claude'),
        );
    }

    #[Test]
    public function parses_xliff_2_2_ordinal_messages(): void
    {
        $messages = XliffParser::parse(<<<'XLIFF'
        <xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" xmlns:pgs="urn:oasis:names:tc:xliff:pgs:1.0" version="2.2" srcLang="fr" trgLang="en">
          <file id="messages">
            <unit id="ranking" pgs:switch="ordinal:place">
              <segment id="one" pgs:case="one"><source>one</source><target><ph id="place" disp="place"/>st place</target></segment>
              <segment id="two" pgs:case="two"><source>two</source><target><ph id="place" disp="place"/>nd place</target></segment>
              <segment id="few" pgs:case="few"><source>few</source><target><ph id="place" disp="place"/>rd place</target></segment>
              <segment id="other" pgs:case="other"><source>other</source><target><ph id="place" disp="place"/>th place</target></segment>
            </unit>
          </file>
        </xliff>
        XLIFF);

        $config = new IntlConfig(
            currentLocale: Locale::ENGLISH,
            fallbackLocale: Locale::ENGLISH,
        );
        $formatter = new MessageFormatter([
            new StringFunction(),
            new NumberFunction($config),
        ]);

        $this->assertSame('1st place', $formatter->format($messages['ranking'], place: 1));
        $this->assertSame('2nd place', $formatter->format($messages['ranking'], place: 2));
        $this->assertSame('3rd place', $formatter->format($messages['ranking'], place: 3));
        $this->assertSame('4th place', $formatter->format($messages['ranking'], place: 4));
        $this->assertSame('21st place', $formatter->format($messages['ranking'], place: 21));
    }

    private function format(string $message, mixed ...$variables): string
    {
        $config = new IntlConfig(
            currentLocale: Locale::FRENCH,
            fallbackLocale: Locale::ENGLISH,
        );

        return new MessageFormatter([
            new StringFunction(),
            new NumberFunction($config),
        ])->format($message, ...$variables);
    }
}
