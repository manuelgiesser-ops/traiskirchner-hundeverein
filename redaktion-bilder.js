(()=>{'use strict';
for(const area of document.querySelectorAll('[data-entry-media]')){
 const form=area.closest('form'),select=area.querySelector('select[name=image]'),file=area.querySelector('[data-entry-file]'),upload=area.querySelector('[data-entry-upload]'),clear=area.querySelector('[data-entry-clear]'),status=area.querySelector('[data-entry-upload-status]'),preview=area.querySelector('[data-media-preview]'),img=preview.querySelector('img'),save=form.querySelector('button:not([type]),button[type=submit]');
 let busy=false,failed=false;
 const show=()=>{preview.hidden=!select.value;if(select.value)img.src=select.value;else img.removeAttribute('src')};
 const lock=()=>{const pending=!!file.files.length;upload.disabled=busy||!pending;clear.disabled=busy;file.disabled=busy;select.disabled=busy;if(save)save.disabled=busy||pending;};
 const setStatus=(message,error=false)=>{status.textContent=message;status.classList.toggle('upload-error',error)};
 const send=async()=>{if(busy||!file.files[0])return;const chosen=file.files[0];if(chosen.size>12000000||!['image/jpeg','image/png','image/webp'].includes(chosen.type)){setStatus('Bitte JPG, PNG oder WebP bis 12 MB wählen.',true);failed=true;lock();return}
 busy=true;failed=false;lock();setStatus('Bild wird hochgeladen und automatisch optimiert …');
 try{const body=new FormData();body.append('csrf',form.querySelector('input[name=csrf]').value);body.append('name',chosen.name);body.append('image',chosen);const response=await fetch('studio-upload.php',{method:'POST',body,credentials:'same-origin'});const result=await response.json().catch(()=>null);if(!response.ok||!result||!/^website-media\.php\?id=[a-f0-9]{24}$/.test(result.url||''))throw Error(result?.error||'Upload fehlgeschlagen. Bitte erneut versuchen.');let option=[...select.options].find(o=>o.value===result.url);if(!option){option=new Option(result.name,result.url);select.add(option)}select.value=result.url;file.value='';show();setStatus('Optimiert und ausgewählt: '+result.width+' × '+result.height+' Pixel · '+Math.ceil(result.bytes/1024)+' KB · WebP');}
 catch(error){failed=true;setStatus(error.message||'Upload fehlgeschlagen.',true)}finally{busy=false;lock()}};
 file.addEventListener('change',()=>{failed=false;if(file.files.length)send();else{lock();setStatus('')}});
 upload.addEventListener('click',send);
 select.addEventListener('change',()=>{file.value='';failed=false;setStatus('');show();lock()});
 clear.addEventListener('click',()=>{file.value='';select.value='';failed=false;show();lock();setStatus('Kein Titelbild ausgewählt.')});
 form.addEventListener('submit',event=>{if(busy||file.files.length){event.preventDefault();setStatus(busy?'Bitte warte, bis das Bild optimiert wurde.':'Bild erneut hochladen oder eine andere Bildauswahl treffen.',true)}});
 show();lock();
}
})();