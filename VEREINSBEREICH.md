# Vereinsbereich
## Freischaltung im Hostinger-Dateimanager
1. Oberhalb von public_html (nicht darin) einen privaten Ordner thv-private anlegen. Schreibbar für den PHP-Benutzer, möglichst Rechte 700; niemals öffentlich freigeben.
2. Darin eine Textdatei setup.key mit einem selbst gewählten zufälligen geheimen Schlüssel von mindestens 32 Zeichen anlegen (Dateirechte 600). Den Schlüssel niemals in GitHub speichern.
3. Über HTTPS vereinsbereich.php aufrufen, Einrichtungsschlüssel, Name, E-Mail und eigenes Passwort setzen. Der Schlüssel wird nach erfolgreicher Einrichtung entfernt; selbst wenn das Löschen nicht möglich ist, bleibt die Ersteinrichtung gesperrt, sobald ein Nutzer existiert.
4. Über Mitglieder weitere Zugänge anlegen. Einmalige Startpasswörter persönlich sicher übergeben; beim ersten Login muss das Passwort geändert werden.
5. Den Ordner thv-private regelmäßig durch den Verein sichern; enthält Konten, Beiträge, Anfragen. Nicht im Git-Deployment ablegen.
## Rollen
Mitglied: interne Nachrichten, zusätzliche Kursangebote, eigene Kursanfragen und Konto.
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
