# Traiskirchner Hundeverein
Eigenständige statische Website. Alle Seiten funktionieren ohne Buildsystem. `index.html` öffnen oder Dateien in den Webroot von Hostinger kopieren.

## Freigabe vor Veröffentlichung
- Die 25 Vereinsfotos sind als 68 lokale responsive WebP-Dateien eingebunden. Die Original-URLs verbleiben in `assets.json`. `optimize_web_assets.py` und der GitHub-Workflow erzeugen Varianten und den Größenbericht `performance-report.json`. Nutzungsberechtigung sowie Datenschutzbeschreibung und absolute Open-Graph-Bild-URL vor dem endgültigen Domainwechsel prüfen.
- Kurszeiten, Preise, Vorstand, aktuelle Kurse und Termine durch den Verein bestätigen lassen. Die Quellen widersprechen sich teilweise. DogActivity und Gehorsam PLUS sind in der Meldung Frühjahr 2026 enthalten, aber nicht in der heute abrufbaren Kursübersicht; daher nicht als reguläre aktuelle Kurse beworben.
- Die Galerie enthält eine kuratierte Auswahl der erreichbaren Originalbilder. Die Galerie-Hauptseite lieferte HTTP 403; vollständiges Archiv und Dokumente müssen vom Verein ergänzt werden.
- Kontaktformular öffnet eine E-Mail im Mailprogramm; kein Serverversand. Die bestehende Online-Beitrittserklärung ist verlinkt.
- Impressum/Datenschutz durch Verein prüfen und Hostingdaten ergänzen. Keine automatische Veröffentlichung oder Domainumstellung.
- Sitemap nach Festlegung der Domain generieren, mit `python sitemap.py https://beispiel.at/`.
- Bilder auf kleine Auflösungen und echte Bildausschnitte im finalen Hosting prüfen.

## Quellen
https://traiskirchner-hundeverein.jimdofree.com/ (Start, Über uns, Vorstand, Kursleiter, Kurse, Kurszeiten, News, Kontakt, Galerie 2023/2024)
https://freiwilligencenter.at/vereine/vereinsdatenbank/180471/traiskirchner-hundeverein
https://www.traiskirchen.gv.at/vereine/freizeit-natur/traiskirchner-hundeverein/

## GitHub
Eigenes Repository `traiskirchner-hundeverein` erstellen und diese Dateien hochladen. Keine Verbindung zum SeiDoRyu-Projekt erforderlich.
## Web-Performance
- Bildauslieferung: `srcset`, `sizes`, feste Bildabmessungen, Lazy Loading und Priorisierung des Titelbildes. Die vollständige Auflösung wird erst in der Fotoansicht geladen.
- Größenbericht: 25 Quelldateien, insgesamt 26.8 MB; größte WebP-Variante je Foto zusammen rund 2.98 MB. Kleine Vorschauen zusammen rund 0.72 MB. Dies sind Datei-Größen, keine gemessene Seitenladezeit.
- `.htaccess`: Cache-Regeln und Textkompression für kompatible Server; tatsächliche HTTP-Header auf Hostinger prüfen. 
