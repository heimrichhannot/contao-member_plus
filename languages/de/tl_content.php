<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package member_plus
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

$arrLang = &$GLOBALS['TL_LANG']['tl_content'];

/**
 * Fields
 */

$arrLang['mlGroups'][0] = 'Mitgliedergruppen';
$arrLang['mlGroups'][1] = 'Wählen Sie die anzugeigenden Mitgliedergruppen aus.';

$arrLang['mlSort'][0] = 'Mitglieder';
$arrLang['mlSort'][1] = 'Hier können Sie die Standard-Weiterleitung überschreiben.';

$arrLang['mlSource'][0] = 'Weiterleitungsziel';
$arrLang['mlSource'][1] = 'Hier können Sie die Standard-Weiterleitung angeben.';

$arrLang['mlTemplate'][0] = 'Mitglieder-Template';
$arrLang['mlTemplate'][1] = 'Hier können Sie das Template zur Anzeige des Mitgliedes auswählen.';

$arrLang['mlJumpTo'][0] = 'Weiterleitungsseite';
$arrLang['mlJumpTo'][1] = 'Bitte wählen Sie die Mitgliederleser-Seite aus, zu der Besucher weitergeleitet werden, wenn Sie ein Mitglied anklicken.';

$arrLang['mlArticleId'][0] = 'Artikel';
$arrLang['mlArticleId'][1] = 'Bitte wählen Sie den Artikel aus, zu der Besucher weitergeleitet werden, wenn Sie ein Mitglied anklicken.';

$arrLang['mlUrl'][0] = 'Link-Adresse';
$arrLang['mlUrl'][1] = 'Geben Sie eine Web-Adresse (http://…), eine E-Mail-Adresse (mailto:…) oder ein Inserttag ein.';

$arrLang['mlTarget'][0] = 'In neuem Fenster öffnen';
$arrLang['mlTarget'][1] = 'Den Link in einem neuen Browserfenster öffnen.';

$arrLang['mlLoadContent'][0] = 'Mitglieder-Inhaltselemente laden/anzeigen';
$arrLang['mlLoadContent'][1] =
	'Hier können Sie auswählen, ob die Inhaltselemente zu einem Mitglied geladen werden sollen. In eine Listendarstellung empfiehlt es sich aus Performancegründen darauf zu verzichten.';

$arrLang['mlDisableImages'][0] = 'Mitgliederbilder deaktivieren';
$arrLang['mlDisableImages'][1] = 'Deaktivieren Sie die Anzeige von Mitgliederbildern (Platzhalterbilder werden ebenfalls ausgeblendet).';

$arrLang['mlDisableDummyImages'][0] = 'Platzhalterbilder deaktivieren';
$arrLang['mlDisableDummyImages'][1] = 'Deaktivieren Sie die Verwendung von Platzhalterbildern, wenn kein Bild für das Mitglied hinterlegt wurde.';

$arrLang['mlAddCustomDummyImages'][0] = 'Eigene Platzhalterbilder';
$arrLang['mlAddCustomDummyImages'][1] = 'Überschreiben Sie die Platzhalterbilder.';

$arrLang['mlDummyImageMale'][0] = 'Platzhalterbild für Männer';
$arrLang['mlDummyImageMale'][1] = 'Bitte wählen Sie eine Datei oder einen Ordner aus der Dateiübersicht.';

$arrLang['mlDummyImageFemale'][0] = 'Platzhalterbild für Frauen';
$arrLang['mlDummyImageFemale'][1] = 'Bitte wählen Sie eine Datei oder einen Ordner aus der Dateiübersicht.';

$arrLang['mlSkipFields'][0] = 'Felder ausschließen';
$arrLang['mlSkipFields'][1] = 'Felder von der Ausgabe ausschließen';

$arrLang['mlFields'][0] = 'Folgende Felder ausschließen';
$arrLang['mlFields'][1] = 'Folgende Felder von der Ausgabe ausschließen';

/**
 * Legends
 */
$arrLang['ml_config_legend'] = 'Memberlisten-Einstellungen';


/**
 * References
 * $arrLang['tl_content']['article'] leads to Xliff-Error
 */
$arrLang['memberPlusReference']['default']        = 'Keine Weiterleitung';
$arrLang['memberPlusReference']['internal']       = 'Seite';
$arrLang['memberPlusReference']['article']        = 'Artikel';
$arrLang['memberPlusReference']['article_reader'] = 'Artikel mit Mitgliederleser';
$arrLang['memberPlusReference']['external']       = 'Externe URL';