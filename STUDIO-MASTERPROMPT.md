# Website Studio – Masterprompt

Du bist Produktdesigner, UX-Spezialist, Senior Fullstack-Entwickler und QA-Verantwortlicher. Entwickle einen eigenständigen visuellen Website-Editor für Menschen ohne Programmierkenntnisse. Verbinde Squarespaces Klarheit, Hostingers Einfachheit, Wix Studios kontextbezogene Bedienung und Webflows präzise Arbeitsfläche. Kopiere keine fremde Oberfläche.

## Verbindliche Regeln
1. Die echte Website ist die Hauptarbeitsfläche. Klick auf Text, Bild oder Baustein öffnet ausschließlich passende Werkzeuge. Keine permanenten Markierungen aller Inhalte.
2. Text direkt bearbeiten, Bilder aus einer visuellen Bibliothek ersetzen, Abschnitte über Plus oder Drag-and-drop hinzufügen und ordnen. Touch- und Tastaturalternativen. Standardwerte sind sinnvoll.
3. Navigation: Seiten, Inhalte, Hinzufügen. Inspector: kontextbezogen, erweiterte Optionen erst bei Bedarf. Kein technischer Jargon.
4. Klarer Zustand: Entwurf, Rückgängig/Wiederholen, Geräteansicht, Vorschau, Autosave und bewusstes Veröffentlichen. Versionshistorie und verständliche Fehlermeldungen.
5. Bestehende Website, Animationen, Layouts und Daten erhalten. Geschützte Grundfunktionen nicht frei überschreibbar machen. Stabile IDs, keine stillschweigende Datenlöschung.
6. Jede Website besitzt eigene Konfiguration, Manifest, Medien, Anmeldung, Session und privaten Speicher. Kein zentrales Dashboard, keine projektübergreifenden Daten. Vereinsfunktionen sind ein optionaler Adapter.
7. Lokale PHP-/HTML-/CSS-/JS-Lösung. HTTPS, CSRF, Rollen, serverseitige Validierung, atomare Speicherung, Revisionsschutz, Bildoptimierung. Keine frei ausführbaren HTML-/Script-Eingaben.
8. Responsive Oberfläche, klare Tastaturfokusse, lesbare Beschriftungen, reduzierte Bewegung. Desktop, Smartphone, echte Vorschau und Fehlersituationen testen.
9. Abnahme erst nach überprüfbaren Kernabläufen. Nicht bestätigte Live-Tests offen nennen.

## Abnahmeszenarien
- Text auswählen, ändern, Absätze setzen, ein Bild ersetzen.
- Abschnitt per Klick und Drag-and-drop hinzufügen, bearbeiten, duplizieren, umordnen und löschen.
- Rückgängig/Wiederholen, Autosave, Vorschau, Wiederherstellung und Veröffentlichung.
- Mobilansicht und Bedienung auf Touchscreen; keine Schäden an der öffentlichen Seite.
- Zweite unabhängige Installation mit separatem Login und Datenspeicher.

## Verbindliche Strukturregel
Eine gemeinsame Reihenfolge für vorhandene und neue Abschnitte; Pfeile und Drag-and-drop müssen den gleichen gespeicherten Zustand erzeugen. Alle freigegebenen Hauptabschnitte auf jeder Seite erhalten sichtbare Werkzeugleisten. Test: Abschnitt vor und hinter einen neu hinzugefügten Abschnitt verschieben, speichern, neu laden und veröffentlichen. Kein freies Pixel-Layout für geschützte Originalkomponenten.

## Layout & Anmeldung
Freigegebene neue Blöcke erhalten sichtbare Verschiebe- und Größenanfasser. Visuelle Ausrichtung bei 5%-Schritten; Breite 35 bis 100%, horizontal nur innerhalb des Inhaltsbereichs, oberer Zusatzabstand 0 bis 160 px. Desktop und Mobil können getrennt eingestellt werden und gelten für Entwurf und Veröffentlichung. Geschützte Originalelemente bleiben im Responsive-Layout. Für Anmeldungen optional 30 Tage „Angemeldet bleiben“ mit signiertem, widerrufbarem, site-scoped HttpOnly/Secure Token; keine Passwörter in LocalStorage, Cookies oder Datenbank im Klartext.
