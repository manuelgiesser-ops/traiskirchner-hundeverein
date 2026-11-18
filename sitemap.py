from pathlib import Path
import sys
from xml.sax.saxutils import escape
p=Path(__file__).parent;base=sys.argv[1].rstrip('/')+'/'
(p/'sitemap.xml').write_text('<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n'+''.join('<url><loc>'+escape(base+('' if f.name=='index.html' else f.name))+'</loc></url>\n' for f in sorted(p.glob('*.html')))+'</urlset>')
(p/'robots.txt').write_text('User-agent: *\nAllow: /\nSitemap: '+base+'sitemap.xml\n')
