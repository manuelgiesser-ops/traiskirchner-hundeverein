# Website Studio — separate Installation

Der Hundeverein nutzt seinen bestehenden Login als Adapter. Neue Websites nutzen den Standalone-Adapter mit **separatem Login, Entwurf, Medien, Versionshistorie und privatem Ordner**. Es gibt keinen zentralen Dienst.

## Voraussetzungen
- PHP 8.1+ mit GD/WebP für Uploads, HTTPS, Website mit HTML-Dateien auf einer Ebene und `main`-Elementen.
- Private, serverseitig beschreibbare Verzeichnisse außerhalb des tatsächlichen Dokumentenstamms. Je Website ein eigener leerer Ordner.
- Python 3 für die einmalige Installation. Bestehende Inhalte werden markiert, nicht neu gestaltet.

## Einrichten
1. Repository lokal auschecken und Website sichern.
2. Aus dem Repository: `python3 install_studio.py /pfad/website --name "Meine Website" --storage /pfad/privat/site-a --document-root /pfad/zum/tatsaechlichen/webroot`.
3. Alle Dateien hochladen; den privaten Speicher bzw. den Identitätsmarker am Server auf den tatsächlichen Serverpfad abstimmen. Am einfachsten den Installer direkt auf dem Zielserver ausführen. PHP muss dort Schreibrechte besitzen.
4. Im Verzeichnis der Website einen eigenen Admin anlegen: `read -rsp "Passwort: " PW; printf '\\n'; printf '%s\\n' "$PW" | php studio-user-cli.php admin@example.com "Admin" admin; unset PW`. Für Bearbeiter Rolle `editor` verwenden. Keine Passwörter in Git speichern.
5. `/studio-login.php` über HTTPS öffnen. Der Admin darf veröffentlichen, der Editor Entwürfe speichern. Passwortänderung per CLI invalidiert alte Sessions.

Erneute Installation überschreibt keine bestehenden Editor-Dateien. Bei Umzug: Backup von Website und privatem Speicher erstellen, den privaten Pfad in studio-site.php ändern und nach Prüfung den Identitätsmarker auf den neuen absoluten Websitepfad setzen. Andere Websites erhalten niemals denselben Ordner.

## Integration
Das Installationsskript übernimmt Root-HTML-Seiten, Texte, Bilder, Buttons und Hauptabschnitte. Mit `data-studio-protect` lassen sich Elemente vor Bearbeitung schützen. Komplexe Widgets, verschachtelte Routen, React/Next.js oder serverseitige Templates benötigen einen eigenen Adapter. Ein visueller Editor für vorhandene Inhalte ist kein völlig freier Pixel-Builder. 
Farben für neue Bausteine über CSS-Variablen `--studio-accent`, `--studio-ink`, `--studio-soft`, `--studio-light`, `--studio-highlight`, `--studio-radius`, `--studio-width` anpassen.
Bestehende IDs bei Änderungen an HTML beibehalten; Manifest synchron ergänzen. Nicht erneut nummerieren. 

## Geräte-Layouts und angemeldet bleiben
Neu eingefügte Bausteine können mit den Griffen innerhalb des Seitenbereichs positioniert und skaliert werden. Desktop und Smartphone bekommen getrennte Werte. Die Anmeldung kann optional 30 Tage gemerkt werden; im privaten Speicher liegt nur ein Token-Hash. Die Zugangsdaten selbst speichert der Browser-Passwortmanager, nicht das Studio. Abmelden widerruft die dauerhafte Anmeldung auf dem aktuellen Gerät.

## Freie Layout-Abschnitte (V54)
Neue Grid-Abschnitte enthalten unabhängig bearbeitbare Überschriften, Texte, Fotos, Buttons, Linien und Abstände. Desktop und Mobil erhalten getrennte Rasterpositionen; das Inhaltsmodell bleibt gemeinsam. Die Grenzen (40 neue Abschnitte je Seite, 24 Elemente je Layout-Abschnitt, 64 Zeilen) schützen Server und Bedienung. Die ursprünglichen HTML-Layouts bleiben als Adapter erhalten. Eigene hochgeladene Rasterbilder werden wie bisher erst nach Veröffentlichung öffentlich erreichbar.
