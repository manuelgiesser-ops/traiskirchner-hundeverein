# Traiskirchner Hundeverein
Eigenständige statische Website. Alle Seiten funktionieren ohne Buildsystem. `index.html` öffnen oder Dateien in den Webroot von Hostinger kopieren.

## Freigabe vor Veröffentlichung
- Fotos und Logo sind derzeit über die Original-Bild-URLs eingebunden. Berechtigung prüfen; mit `python import_assets.py` (Pillow nötig) lokal als WebP ablegen. Anschließend die Datenschutzbeschreibung und absolute Open-Graph-Bild-URL an die tatsächliche Domain anpassen.
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