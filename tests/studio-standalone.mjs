import {chromium} from 'playwright';
import fs from 'node:fs';import os from 'node:os';import path from 'node:path';import {execFileSync,spawn} from 'node:child_process';import assert from 'node:assert/strict';
const base=fs.mkdtempSync(path.join(os.tmpdir(),'studio-independent-')),web=path.join(base,'www');
fs.mkdirSync(web);const password='Independent-test-12345';
const php=path.join(base,'https.php');fs.writeFileSync(php,"<?php $_SERVER['HTTPS']='on';");
for(const id of ['a','b']){
 const dir=path.join(web,id),store=path.join(base,'private-'+id);fs.mkdirSync(dir);
 fs.writeFileSync(path.join(dir,'index.html'),'<html><head><title>Studio</title></head><body><main><section><h1>Original '+id+'</h1><p>Textabsatz</p></section><section><h2>Zweiter Abschnitt</h2><p>Mehr Text</p></section></main></body></html>');
 execFileSync('python3',['install_studio.py',dir,'--name','Website '+id,'--storage',store,'--document-root',web],{stdio:'pipe'});
 execFileSync('php',[path.join(dir,'studio-user-cli.php'),id+'@example.invalid','Editor '+id,'admin'],{input:password+'\n',stdio:'pipe'});
}
const server=spawn('php',['-d','auto_prepend_file='+php,'-S','127.0.0.1:18766','-t',web],{stdio:'ignore'});let browser;
try{
 await new Promise(r=>setTimeout(r,700));browser=await chromium.launch({headless:true,args:['--no-sandbox'],...(process.env.STUDIO_BROWSER?{executablePath:process.env.STUDIO_BROWSER}:{})});
 const page=await browser.newPage({viewport:{width:1400,height:900}});
 const errors=[];page.on('pageerror',e=>errors.push(e.message));
 const origin='http://localhost:18766';
 await page.goto(origin+'/a/studio-login.php');await page.locator('[name=email]').fill('a@example.invalid');await page.locator('[name=password]').fill(password);await page.getByRole('button',{name:'Anmelden'}).click();
 await page.locator('#studio-message').filter({hasText:'Bereit.'}).waitFor();
 await page.frameLocator('#preview').locator('h1').click();await page.locator('#text').fill('Nur Website A');
 await page.frameLocator('#preview').getByRole('heading',{name:'Nur Website A',exact:true}).waitFor();
 await page.frameLocator('#preview').locator('[data-layout-id="s002"] [data-studio-action=up]').click();
 assert.equal(await page.frameLocator('#preview').locator('main > [data-studio-section]').first().getAttribute('data-studio-section'),'s002');
 await page.locator('#studio-message').filter({hasText:'Automatisch gespeichert'}).waitFor({timeout:15000});
 page.once('dialog',d=>d.accept());await page.locator('#publish').click();await page.locator('#studio-message').filter({hasText:'veröffentlicht'}).waitFor();
 await page.goto(origin+'/b/studio-api.php');assert.equal(await page.locator('body').textContent(),'{"error":"Dafür fehlt die Berechtigung."}');
 await page.goto(origin+'/b/studio-login.php');await page.locator('[name=email]').fill('b@example.invalid');await page.locator('[name=password]').fill(password);await page.getByRole('button',{name:'Anmelden'}).click();
 await page.frameLocator('#preview').getByRole('heading',{name:'Original b',exact:true}).waitFor();
 const ans=await (await page.request.get(origin+'/b/studio-api.php')).json();assert.deepEqual(ans.published,[]);
 const other=await browser.newPage();await other.goto(origin+'/a/index.html');await other.getByRole('heading',{name:'Nur Website A',exact:true}).waitFor();assert.equal(await other.locator('main > [data-studio-section]').first().getAttribute('data-studio-section'),'s002');await other.goto(origin+'/b/index.html');assert.equal(await other.locator('h1').textContent(),'Original b');
 assert.deepEqual(errors,[]);console.log('PASS: two installed websites, independent accounts, private storage, draft and publishing');
}finally{await browser?.close();server.kill();fs.rmSync(base,{recursive:true,force:true})}
