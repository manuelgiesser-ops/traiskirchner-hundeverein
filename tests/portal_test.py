"""Isolated smoke test for portal access boundaries. No real data or hosting changes."""
import os, tempfile, shutil, subprocess, pathlib, secrets, time, http.client, urllib.parse, re, json
root=pathlib.Path(__file__).resolve().parent.parent
with tempfile.TemporaryDirectory() as temp:
    base=pathlib.Path(temp)
    web=base/'public_html';web.mkdir()
    private=base/'thv-private';private.mkdir(mode=0o700)
    for name in ['verein-core.php','vereinsbereich.php','vereinsdaten.php','vereinsartikel.php','studio-media-lib.php','studio-api.php','studio-manifest.json','website-studio.php','vereins-backup.php']:
        shutil.copy(root/name,web/name)
    key=secrets.token_hex(24);adminpw=secrets.token_hex(16);newpw=secrets.token_hex(16)
    (private/'setup.key').write_text(key)
    # Emulate HTTPS only in the isolated local harness, never in deployed code.
    prepend=base/'test-https.php';prepend.write_text("<?php $_SERVER['HTTPS']='on';")
    server=subprocess.Popen(['php','-d','auto_prepend_file='+str(prepend),'-S','127.0.0.1:18764','-t',str(web)],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
    class Client:
        def __init__(self):self.cookie=''
        def req(self,path='/vereinsbereich.php',data=None):
            conn=http.client.HTTPConnection('127.0.0.1',18764,timeout=10)
            headers={'Cookie':self.cookie}
            if data is not None:headers['Content-Type']='application/x-www-form-urlencoded'
            conn.request('POST' if data is not None else 'GET',path,None if data is None else urllib.parse.urlencode(data),headers)
            res=conn.getresponse();status=res.status;body=res.read().decode();loc=res.getheader('Location')
            for k,v in res.getheaders():
                if k.lower()=='set-cookie' and v.startswith('THV_VEREIN='):self.cookie=v.split(';',1)[0]
            conn.close()
            if status==303 and loc:return self.req('/'+loc.lstrip('/'))
            return status,body
        def studio(self,payload):
            conn=http.client.HTTPConnection('127.0.0.1',18764,timeout=10)
            conn.request('POST','/studio-api.php',json.dumps(payload),{'Cookie':self.cookie,'Content-Type':'application/json'})
            res=conn.getresponse();status=res.status;body=json.loads(res.read());conn.close()
            return status,body
        def csrf(self):
            status,body=self.req();assert status==200
            found=re.search(r'name="csrf" value="([a-f0-9]+)"',body);assert found,'No CSRF'
            return found.group(1)
        def post(self,action,**kwargs):return self.req(data={'action':action,'csrf':self.csrf(),**kwargs})
    try:
        time.sleep(0.6)
        admin=Client();anon=Client();member=Client()
        assert 'Ersteinrichtung' in admin.req()[1]
        assert 'eingerichtet' in admin.post('setup',key=key,name='Test Admin',email='admin@example.invalid',password=adminpw)[1]
        assert not (private/'setup.key').exists()
        assert 'Ersteinrichtung' not in anon.req()[1]
        def save(title,visibility,type='post'):
            return admin.post('save-entry',id='',type=type,visibility=visibility,title=title,summary='Test summary',body='Test detail',date='',image='',age='',schedule='')
        for title,visibility,type in [('Public article','public','post'),('Hidden draft','draft','post'),('Internal news','members','post'),('Public course','public','course')]:
            assert 'Eintrag gespeichert.' in save(title,visibility,type)[1]
        entries=json.loads(anon.req('/vereinsdaten.php')[1])['entries']
        assert {e['title'] for e in entries}=={'Public article','Public course'}
        assert all('body' not in e and 'author' not in e for e in entries)
        state=json.loads((private/'vereinsdaten.json').read_text())
        bytitle={e['title']:e['id'] for e in state['entries'].values()}
        assert anon.req('/vereinsartikel.php?id='+bytitle['Hidden draft'])[0]==404
        assert anon.req('/vereinsartikel.php?id='+bytitle['Internal news'])[0]==404
        assert anon.req('/vereinsartikel.php?id='+bytitle['Public article'])[0]==200
        assert anon.req('/thv-private/vereinsdaten.json')[0]==404
        body=admin.post('create-user',name='Test Member',email='member@example.invalid',role='member')[1]
        pw=re.search(r'member@example\.invalid: ([0-9a-f]{24})',body).group(1)
        assert 'Dein eigenes Passwort' in member.post('login',email='member@example.invalid',password=pw)[1]
        assert 'Internal news' in member.post('password',old=pw,password=newpw)[1]
        assert 'Hidden draft' not in member.req()[1]
        assert 'Mitglieder verwalten' not in member.req('/vereinsbereich.php?tab=mitglieder')[1]
        forged=member.post('save-entry',id='',type='post',visibility='public',title='Forged',summary='Bad',body='Bad',date='',image='',age='',schedule='')
        assert 'Berechtigung' in forged[1]
        assert 'Forged' not in anon.req('/vereinsdaten.php')[1]
        assert 'Anfrage wurde gespeichert' in member.post('request',id=bytitle['Public course'],note='Test note')[1]
        assert 'Test note' in member.req('/vereinsbereich.php?tab=anfragen')[1]
        assert 'Test note' in admin.req('/vereinsbereich.php?tab=anfragen')[1]
        state=json.loads((private/'vereinsdaten.json').read_text());qid=next(iter(state['requests']))
        assert 'Berechtigung' in member.post('request-status',id=qid,status='confirmed')[1]
        assert 'Anfrage aktualisiert' in admin.post('request-status',id=qid,status='confirmed')[1]
        assert 'Bestätigt' in member.req('/vereinsbereich.php?tab=anfragen')[1]
        # Studio permission and published-versus-draft checks.
        assert anon.req('/studio-api.php')[0]==403
        assert member.req('/studio-api.php')[0]==403
        ss=json.loads(admin.req('/studio-api.php')[1]);assert len(ss['manifest'])==16
        assert admin.req('/website-studio.php')[0]==200
        assert admin.req('/vereins-backup.php')[0]==200
        key=next(k for k,v in ss['manifest']['index.html']['fields'].items() if v['kind']=='text')
        patch={'index.html':{'fields':{key:'Vorläufiger Testtext\n\nZweiter Absatz'},'format':{key:{'font':'serif','size':'large','weight':'bold'}},'hidden':[],'order':[]}}
        status,answer=admin.studio({'action':'save','csrf':ss['csrf'],'revision':ss['revision'],'draft':patch})
        assert status==200,answer
        assert json.loads(anon.req('/studio-api.php?mode=public&page=index.html')[1])['patch'] is None
        status,answer=admin.studio({'action':'publish','csrf':ss['csrf'],'revision':answer['revision'],'draft':patch})
        assert status==200,answer
        public=json.loads(anon.req('/studio-api.php?mode=public&page=index.html')[1])
        assert public['patch']['fields'][key]=='Vorläufiger Testtext\n\nZweiter Absatz'
        assert public['patch']['format'][key]=={'font':'serif','size':'large','weight':'bold'}
        assert 'history' not in public
        v=answer['history'][-1]['id']
        status,answer=admin.studio({'action':'restore','csrf':ss['csrf'],'revision':answer['revision'],'id':v})
        assert status==200,answer
        assert not answer['draft']
        assert json.loads(anon.req('/studio-api.php?mode=public&page=index.html')[1])['patch']['fields'][key]=='Vorläufiger Testtext\n\nZweiter Absatz'
        status,answer=admin.studio({'action':'publish','csrf':ss['csrf'],'revision':answer['revision'],'draft':{}})
        assert status==200,answer
        assert json.loads(anon.req('/studio-api.php?mode=public&page=index.html')[1])['patch'] is None
        status,answer=member.studio({'action':'save','csrf':member.csrf(),'revision':0,'draft':patch})
        assert status==403
        assert 'Test Member' not in anon.req('/vereinsdaten.php')[1]
        assert 'Passwort' not in anon.req('/vereinsdaten.php')[1]
        print('PASS: setup, login, password change, public/draft/internal content, roles, private storage, course request.')
    finally:
        server.terminate();server.wait(timeout=10)
