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
