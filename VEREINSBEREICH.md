# Vereinsbereich
## Freischaltung im Hostinger-Dateimanager
1. Oberhalb von public_html (nicht darin) einen privaten Ordner thv-private anlegen. Schreibbar für den PHP-Benutzer, möglichst Rechte 700; niemals öffentlich freigeben.
2. Darin eine Textdatei setup.key mit einem selbst gewählten zufälligen geheimen Schlüssel von mindestens 32 Zeichen anlegen (Dateirechte 600). Den Schlüssel niemals in GitHub speichern.
3. Über HTTPS vereinsbereich.php aufrufen, Einrichtungsschlüssel, Name, E-Mail und eigenes Passwort setzen. Der Schlüssel wird nach erfolgreicher Einrichtung entfernt; selbst wenn das Löschen nicht möglich ist, bleibt die Ersteinrichtung gesperrt, sobald ein Nutzer existiert.
4. Über Mitglieder weitere Zugänge anlegen. Einmalige Startpasswörter persönlich sicher übergeben; beim ersten Login muss das Passwort geändert werden.
5. Den Ordner thv-private regelmäßig durch den Verein sichern; enthält Konten, Beiträge, Anfragen. Nicht im Git-Deployment ablegen.
## Rollen
Kursteilnehmer (technische Bestandsrolle `member`): interne Nachrichten, zusätzliche Kursangebote, eigene Kursanfragen und Konto.
Redaktion: Beiträge und zusätzliche Kurse, Entwurf, öffentlich oder nur Mitglieder; Kursanfragen bearbeiten.
Vereinsverwaltung: zusätzlich Nutzer, Rollen, Sperren und Passwort-Reset.
## Umfang
Bestehende statische Kurse und Rückblicke bleiben erhalten. Die Redaktion ergänzt neue Beiträge und Kurse auf Startseite, Aktuelles und Kursübersicht. Veröffentlichte Inhalte erscheinen nur entsprechend der gewählten Sichtbarkeit. Bilder aus vorhandener Vereinsgalerie auswählen; kein freier Datei-Upload. Kursanfragen sind keine automatische Buchung. Keine E-Mail-Benachrichtigung; Status wird im Vereinsbereich angezeigt.
## Betrieb
PHP 8.1+ und HTTPS erforderlich. Daten werden mit exklusiven Dateisperren und atomarem Schreiben im privaten Ordner gespeichert. Kein externes Datenbankkonto nötig. Vor Produktivbetrieb Zugänge, HTTPS, Schreibrechte, Datenschutzinformation, Aufbewahrungsfristen, Backups und Rollenprüfung überprüfen. Keine personenbezogenen Informationen in öffentlichen Beiträgen ohne Freigabe veröffentlichen. Keine echten Mitgliederdaten vor der Freischaltung eintragen.

## Website Studio und Medien
Redaktion und Verwaltung öffnen das Studio über die Übersicht. 16 öffentliche Seiten, markierte Textfelder und Bilder; Impressum, animierter Header-Hintergrund und bestehende Trainerporträts sind bewusst vom Bildtausch ausgenommen. Inhalte als Entwurf speichern; nur Verwaltung veröffentlicht Studio-Änderungen. Bis zu 20 frühere Veröffentlichungen als Entwurf wiederherstellen. Bilder via GD zu WebP konvertiert und privat gespeichert; erst nach öffentlicher Verwendung frei abrufbar. PHP GD mit WebP, EXIF optional. Bilder maximal 12 MB/20 Megapixel vor Konvertierung, 200 Medien/60 MB Archiv.
## Umzug und Sicherung
Verwaltung: Datensicherung herunterladen (PHP ZIP erforderlich). Bewahre die ZIP streng vertraulich und verschlüsselt auf. Auf dem neuen Hosting dieselbe Website-Version bereitstellen, privaten Ordner einrichten, vorläufigen Administrator anlegen, danach ZIP wiederherstellen. Anschließend mit einem Konto aus der Sicherung anmelden. Die Website-Dateien selbst werden aus GitHub bezogen. Alternativ den ganzen Ordner thv-private (inkl. media/) außerhalb public_html kopieren, Dateirechte prüfen. Bei Upload-Limits größere Sicherungen über den Dateimanager übertragen. Es werden keine echten Daten im Repository gespeichert.

Trainerteam: vorhandene Trainerkarten umsortieren, ausblenden und wieder anzeigen; neue Karten mit Bild, Name und Trainingsbereichen ergänzen. Neue Karten nutzen das Standardlayout, ohne den handvermessenen Fokuspunkt der ursprünglichen Porträts.

Der Mitgliederbereich zeigt kommende datierte Beiträge und Kurse im Vereinskalender. Vergangene Termine erscheinen weiterhin in den Nachrichten und Kursangeboten.

## Studio 2
Dreispaltige Oberfläche mit Seitenliste, durchsuchbaren Inhaltsfeldern, Live-Vorschau und kontextbezogenen Eigenschaften. Desktop-, Tablet- und Mobilansicht, Vorschau ohne Markierung, Text-Doppelklick, automatische Entwurfsspeicherung, Rückgängig/Wiederholen (bis 60 lokale Zustände), Tastenkürzel Strg+S und Strg+Z. Zusätzlich Ausrichtung, Zeilenabstand, Kursiv und Vereinsfarben. Neue Seitenbausteine (Text, Bild/Text, Infobox, Kontaktbutton, FAQ) können angelegt, sortiert und entfernt werden. Entwürfe und Medien bleiben geschützt. Nur Verwaltung veröffentlicht. Bestehende Seitenabschnitte und neue Bausteine lassen sich innerhalb ihrer jeweiligen Liste ziehen oder per Pfeiltasten sortieren. Kein freies Überlagern oder pixelgenaues Positionieren der bestehenden Elemente.

## Öffentliche Kursanfragen und Kalender
- /kursanfrage.php: ohne Login, mit CSRF, Honeypot und Rate Limit. Daten werden im privaten Vereinsdatenspeicher abgelegt. Die Redaktion und Administration betreuen sie unter /vereins-tools.php. Keine verbindliche Buchung.
- /vereins-tools.php?tab=training: bestätigte Termine, Uhrzeit, Status und Hinweise pflegen. Keine automatisch bestätigten Termine aus der alten Liste. Kalenderdownload nur für nicht abgesagte Termine.
- E-Mail optional: im privaten Verzeichnis thv-private mail-settings.json anlegen, mit {"from":"office@traiskirchner-hundeverein.at","office":"office@traiskirchner-hundeverein.at","trainers":{}}. Bestätigte Absenderadresse und funktionierendes PHP-mail erforderlich. Trainerzuordnung optional nach Kurs-ID. Ohne Konfiguration funktioniert das Postfach, kein E-Mail-Versprechen. Zustellung am Hosting testen.
- Daten regelmäßig prüfen und löschen, sobald sie nicht mehr benötigt werden. Backups vertraulich behandeln. Datenschutztext und Lerninhalte vor Vereinsfreigabe prüfen.
- Hundeschule und Einstiegsbegleiter sind im Website Studio bearbeitbar. Fortschritt bleibt lokal im Browser und kann zurückgesetzt werden.

## Getrennte Zugänge
- `vereinsbereich.php`: Auswahlseite und Weiterleitung für bereits angemeldete Konten.
- `kursteilnehmer.php`: eigenständige Anmeldung nur für die Bestandsrolle member. Keine Verwaltungsaktionen.
- `verwaltung.php`: eigenständige Anmeldung nur für admin und editor; Ersteinrichtung ausschließlich hier.
- Konten, gespeicherte Kursanfragen und bestehende Session-/Passwortschutzmechanismen bleiben unverändert. Keine automatische Umwidmung von Vereinsmitgliedern: Verein prüft, welche bestehenden member-Konten als Kursteilnehmer dienen. Redaktion und Verwaltung bleiben die vorhandenen Rollen; eine gesonderte, kursbezogene Trainerrolle und persönliche Hundedaten sind noch nicht implementiert.
- Vor Veröffentlichung prüfen: beide Logins, falsche Rollen, Startpasswort, Logout, direkter Zugriff auf Verwaltungsaktionen, bestehende Anfragen und Weiterleitungen unter HTTPS.

## Veranstaltungen und Turniere
Unter Vereinsverwaltung → Redaktion stehen zusätzlich „+ Veranstaltung“ und „+ Turnier“ bereit. Datum und Ort sind bei Veröffentlichung erforderlich; Beginn und Ende sind optional, dazu Titel, Beschreibung, Titelbild und Sichtbarkeit. Öffentliche Einträge erscheinen unter Aktuelles, auf der Startseite und im öffentlichen Kalender (90 Tage Vorschau); interne Einträge erscheinen nur angemeldeten Benutzern im Vereinskalender. Keine Turnieranmeldung, automatische Teilnehmerliste oder Buchung. Alte Beiträge und Kursanfragen funktionieren unverändert. Sicherung und Wiederherstellung akzeptieren die neuen Typen.

## Redaktion: endgültig löschen
Administratoren und Redakteure können selbst angelegte Beiträge, Kurse, Veranstaltungen und Turniere nach ausdrücklicher Bestätigung endgültig löschen. „Als Entwurf“ bleibt zum vorübergehenden Ausblenden erhalten. Zugehörige Trainingstermine werden entfernt. Interne Kursanfragen behalten den bisherigen Kurstitel und Status, öffentliche Anfragen bleiben unverändert. Die Sicherungsprüfung unterstützt historische Anfragen gelöschter Kurse. Fest im HTML hinterlegte reguläre Kursangebote werden hiervon nicht verändert; diese lassen sich separat im Website Studio bearbeiten.

## Titelbilder in der Redaktion
Beim Anlegen oder Bearbeiten eines Beitrags, Kurses, Events oder Turniers kann unter Titelbild ein eigenes JPG, PNG oder WebP gewählt werden. Der vorhandene, geschützte Studio-Upload erzeugt automatisch WebP (maximal 1600 Pixel an der längeren Seite, Qualität 84, bis 12 MB Original und 20 Megapixel), übernimmt das optimierte Bild in die gemeinsame Mediathek und wählt es im Formular aus. Speichern veröffentlicht die Zuordnung je nach Sichtbarkeit. PHP GD mit WebP muss auf dem Hosting verfügbar sein. Die öffentliche Startseite zeigt bis zu drei kommende Veranstaltungen und Turniere kompakt und chronologisch; Aktuelles zeigt bis zu 100. Vergangene Termine bleiben in den Nachrichten sichtbar.
