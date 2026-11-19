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

## Direkte Textbearbeitung und SEO
Kontextwerkzeuge am ausgewählten Text statt wechselnder Menüs. Neu eingefügte Überschriften und Texte getrennt formatierbar. Entfernen über X ist rückgängig machbar; geschützte Seitenfunktionen werden nicht gelöscht. SEO-Titel und Beschreibungen werden als Entwurf gespeichert und erst bei Veröffentlichung serverseitig im HTML-Head ausgegeben. Suchbegriff- und Längenprüfung sind redaktionelle Hinweise, keine Rankinggarantie.


# Website Studio 2.0 – verbindlicher Ausbauplan
Stand 19.11.2026. Ziel: professionelle Gestaltung mit der Einfachheit eines Baukastens. Vorbilder: Hostinger (direkte Bedienung), Squarespace (intelligentes Raster), Wix Studio (responsive Layouts), Canva (kontextbezogene Werkzeuge), Webflow (Designsystem). Keine proprietären Codes kopieren.

## Produktregeln
1. Die echte Website ist die Arbeitsfläche. Klick selektiert, Doppelklick bearbeitet, X entfernt, Undo macht Änderungen rückgängig. Keine Pflicht zum Umschalten zwischen Verwaltungsformularen.
2. Die Oberfläche ist ruhig: linke Seiten-/Elementnavigation, großzügige Vorschau, kontextbezogene rechte Eigenschaften. Nur die unmittelbar nötigen Einstellungen zeigen, Fachoptionen aufklappbar. Touch, Tastatur und Maus sind gleichberechtigte Wege; alle Icons haben Namen und Tooltips.
3. Vor Veröffentlichung muss jedes neue Feature den echten Entwurf, Reload, Vorschau, veröffentlichte Darstellung und Rückgängig/Wiederholen korrekt behandeln. Keine stillen Fehler und keine toten Buttons.
4. Bestehende individuell programmierte Websites behalten ihre Gestaltung und geschützten Komponenten. Kein Überschreiben von Login, Skripten, Formulareingängen, Navigation, SEO und Benutzerdaten. Stabile IDs, Migration mit Backup und verlustfreie Rückkehr.
5. Jede Website besitzt ihren eigenen Editor, privaten Speicher, Zugang, Medien, Rollen, Inhalt, Historie und Konfiguration. Kein projektübergreifender Zugriff und kein zentraler SaaS-Zwang.

## Zielarchitektur
Versioniertes Inhaltsmodell: Website > Seite > Abschnitt > Container > Element. Elemente besitzen stabile IDs, typisierte Inhalte, validierte Stile, responsive Positionen. Ursprüngliche HTML-Komponenten bleiben als geschützte Adapter erhalten. Gleicher Renderer für Vorschau und öffentliche Ausgabe; öffentliche Ausgabe ausschließlich veröffentlichter Daten. Keine ungesicherte HTML-Injektion. Getrennte Module für Modell, Darstellung, Interaktion, Medien, SEO, Auth und Persistenz. Keine Erweiterung durch unkontrollierte globale CSS-Overrides. Schemaänderungen rückwärtskompatibel; alte Entwürfe testen. Atomare Schreibvorgänge, Revisionen, Fehlermeldungen, Wiederherstellung und Export.

## Priorität 1 – visueller Kern (Ausbau in Version 54 begonnen)
Freie Layout-Abschnitte mit unabhängigen Überschriften, Texten, Bildern, Buttons, Trennlinien und Abständen. Responsive 12-Spalten mit content-getriebenen Zeilen; getrennte Desktop-/Mobilpositionen. Pointer-Drag und Größenanfasser mit sichtbarer Rasterhilfe, gleichwertige Zahlen-/Pfeilsteuerung; Kollisionen automatisch auflösen. Elemente direkt auswählen, formatieren, duplizieren und mit X löschen. Mediathek und Upload am Bildelement. Künftige Schritte: freie Mehrfachauswahl, Gruppierung, Ebenen, bewusste Überlappung, Container, rich text auf Zeichenebene mit sicheren strukturierten Runs und Zeilen-/Absatzformatierung. Keine unleserlichen Layouts bei langen Texten; bei 320px keine horizontalen Scrollbalken.

## Priorität 2 – professionelle Designwerkzeuge
Globale Stile mit Farben, Font-Familien, typografischer Skala, Abständen, Radien, Schatten und Button-Varianten. Website-Standard erbt sich an neue Elemente; lokale Änderungen sind sichtbar und zurücksetzbar. Bildeditor mit Originalen, nicht destruktiven Zuschnitten, Fokuspunkt, Seitenverhältnis und Kompression; Bibliothek mit Suche, Ordnern, Alt-Text, Austausch, Referenzprüfung und sicherem Löschen. Hintergrundbilder/-farben, Overlays, Animationen aus erlaubten Presets, reduzierte Bewegung. Vorlagen für Hero, Leistungen, Teams, Testimonials, FAQ, Kontakt und Galerien. Eigene Abschnittsvorlagen je Website speichern.

## Priorität 3 – komplette Website-Verwaltung
Seiten erstellen, duplizieren, umbenennen, als Entwurf speichern, löschen und wiederherstellen. Stabile URL-Slugs, Redirects bei Änderung, interne Linkprüfung, Sitemap. Header, Navigation, Untermenüs und Footer global und responsiv bearbeiten, aber bestehende spezielle Funktionen nicht beschädigen. Sichere Bausteine für Video, Audio, Galerie, Slider, Icons, Karten und Formulare. Externe Einbettungen nur über erlaubte Anbieter und mit Datenschutz/Consent; keine beliebigen Skripte. Formulare validieren serverseitig, Spam- und Rate-Limits, Datenschutz, Zustellstatus und Export. Optional Shop-/Terminmodule erst bei eigener Anforderung.

## Priorität 4 – SEO, Qualität und Leistung
SEO pro Seite: Titel, Beschreibung, Google-Vorschau, Social Sharing, Open Graph, Canonical, robots/noindex, Sitemap, Redirects, Alt-Texte, Überschriftenstruktur und interne Links. Titel und Metadaten serverseitig vor JS; öffentliche URLs, keine Entwurfsindizierung. Weitere Prüfungen: fehlende Links, mobile Überläufe, Kontraste, Bildabmessungen, Ladezeiten, semantische HTML-Struktur und Tastaturfokus. SEO-Hinweise ehrlich als Empfehlungen, keine Rankinggarantie. Messung statt erfundener Scores. Keine automatische Veröffentlichung ungeprüfter KI-Texte.

## Priorität 5 – Sicherheit, Betrieb und Wiederverwendung
HTTPS, serverseitige Rollen und Berechtigungen, CSRF, XSS-Vermeidung, sichere Uploads mit Größenlimits und MIME-Prüfung, Pfadvalidierung, Schutz privater Dateien, sichere Passwort-Hashes, Rate-Limits, widerrufbare per Website isolierte Anmeldung. Passwortmanager-Autofill, niemals Klartextpasswörter speichern; optionales Angemeldetbleiben mit sicherem Token und expliziter Wahl des Nutzers. Administrator darf veröffentlichen, Redaktion nur Entwürfe. Backups schließen Login-Tokens aus; Wiederherstellung und Umzug testen. Neue Installationen per dokumentiertem Installer, getrennte Projekte, aktualisierbare Module und versionierte Migrationen. Apache/PHP als primäres Hosting, alternative Hosts nur mit passenden Adaptern.

## Abnahme für jede Version
- Nutzer ohne Vorkenntnisse: Text einfügen, direkt umschreiben, Schrift/Größe/Farbe ändern, Bild hinzufügen und ersetzen, zwei Elemente nebeneinander anordnen, eines per X entfernen, Undo und veröffentlichen.
- Desktop und Smartphone: responsive Positionen getrennt speichern, längere Texte prüfen, Touch tatsächlich testen, keine Überlappung ohne expliziten Layer-Modus.
- Server: Entwurf nicht öffentlich, Rechte und ungültige Payloads ablehnen, veröffentlichte Medien zugänglich, nicht veröffentlichte Medien privat.
- Stabilität: Reload, Browserwechsel, Versionskonflikt, langsames Netz, fehlgeschlagener Upload, leere Felder, Escape/Abbruch, Autosave und Undo, Rollback.
- Regression: alte Website inklusive Animationen, Links, Verein und geschützter Funktionen unverändert; zwei unabhängige Installationen testen.
- Dokumentation: klar zwischen implementiert, automatisiert getestet und realem Live-Hosting unterscheiden. Ein grüner CI-Test allein beweist nicht die tatsächliche Hostinger-Auslieferung. Keine Freigabe mit bekannten Fehlern in den Kernabläufen.

## Verbindliche Bedienregel: stabiler Viewport und Drag-Autoscroll
Keine Scrollsprünge beim Ändern, Einfügen, Formatieren, Löschen, automatischen Speichern oder Undo. Bestehende unveränderte DOM-Bausteine und Bilder behalten; Scrollposition vor Mutation sichern und sofort wiederherstellen. Explizite Navigation zu einem Element darf nur so weit wie nötig scrollen. Beim Halten eines Drag-Griffs am oberen/unteren Rand scrollt die Arbeitsfläche kontinuierlich mit variabler Geschwindigkeit, auch ohne weitere Mausbewegung. Beim Loslassen, Abbruch und Escape endet Autoscroll. Touchgriffe und native Drag-and-drop-Ziele, auch Strukturpanel, berücksichtigen. Rasterpositionen während Scrollen aktualisieren. Browser-Test: Scrollposition bei Feldänderung und Einfügen, echtes Drag-Autoscroll in beide Richtungen, Drop nach langem Scrollen, speichern und neu laden.

## V56: Design-Ausbau
Rasterelemente unterstützen strukturierte Wortmarkierungen (Fett, Kursiv, Unterstrich), nicht destruktive Bildformate, Fokus und Ecken sowie fünf editierbare Abschnittsvorlagen. Bilder im Editor auch außerhalb des sichtbaren Bereichs laden; Besucher behalten Lazy Loading. Weitere Masterprompt-Module bleiben offen.
