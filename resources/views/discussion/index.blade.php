@extends('layouts.app')

@section('section_name', 'Diskusi')

@section('content')
<style>
    body,.content-wrapper{background:#f3f6fd!important}
    .content-wrapper>.content-header{display:none}
    .content-wrapper>.content{padding:.55rem}
    .content-wrapper>.content>.container-fluid{padding:0;max-width:none}
    .dc-wrap{display:grid;grid-template-columns:260px minmax(0,1fr);gap:10px;height:calc(100vh - 74px);min-height:calc(100vh - 74px)}
    .dc-card{background:#fff;border:1px solid #dbe7ff;border-radius:14px;box-shadow:0 8px 24px rgba(15,23,42,.08)}
    .dc-side{padding:12px;overflow:auto}
    .dc-side h4{margin:0;font-weight:800;font-size:18px}
    .dc-side p{margin:4px 0 10px;color:#64748b;font-size:12px}
    .dc-channel{display:block;padding:8px 10px;border-radius:10px;color:#0f172a;text-decoration:none;font-weight:600;font-size:13px}
    .dc-channel:hover{background:#eef4ff;text-decoration:none}
    .dc-channel.active{background:#dbeafe;color:#1e3a8a}
    .dc-main{display:flex;flex-direction:column;height:100%;min-height:0;overflow:hidden}
    .dc-head{padding:12px 14px;border-bottom:1px solid #dbe7ff;display:flex;justify-content:space-between;align-items:center;gap:10px}
    .dc-head h5{margin:0;font-size:18px;font-weight:800}
    .dc-head-r{display:flex;align-items:center;gap:8px}
    .dc-search{width:220px;max-width:38vw;border:1px solid #cbd5e1;border-radius:8px;padding:6px 9px;font-size:12px}
    .dc-search:focus{outline:none;border-color:#60a5fa;box-shadow:0 0 0 3px rgba(96,165,250,.2)}
    .dc-live{font-size:11px;font-weight:800;color:#15803d}
    .dc-pin-box{padding:6px 10px;border-bottom:1px solid #dbe7ff;background:#fffbeb;max-height:98px;overflow:auto}
    .dc-pin-title{font-size:10px;font-weight:800;color:#92400e;letter-spacing:.05em;text-transform:uppercase;margin-bottom:4px;display:flex;align-items:center;gap:5px}
    #dcPinList{display:flex;flex-direction:column;gap:4px}
    .dc-pin-item{display:flex;align-items:center;justify-content:space-between;gap:8px;width:100%;text-align:left;background:#fff7d6;border:1px solid #f6d67b;border-radius:7px;padding:4px 6px;cursor:pointer}
    .dc-pin-main{min-width:0;display:flex;align-items:center;gap:7px}
    .dc-pin-text{font-size:12px;color:#1f2937;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:56vw}
    .dc-pin-meta{font-size:11px;color:#78716c;white-space:nowrap}
    .dc-pin-doc{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700;color:#1e3a8a;text-decoration:none;white-space:nowrap}
    .dc-pin-doc:hover{text-decoration:underline}
    .dc-pin-empty{font-size:12px;color:#78716c}
    .dc-list{flex:1;min-height:0;overflow:auto;padding:12px;background:linear-gradient(180deg,#f7fbff,#fff)}
    .dc-empty{text-align:center;color:#64748b;font-size:13px;padding:24px}
    .dc-msg{display:flex;gap:8px;max-width:92%;margin-bottom:10px}
    .dc-msg.own{margin-left:auto;flex-direction:row-reverse}
    .dc-msg.selected .dc-bub,.dc-msg.selected .dc-vn{box-shadow:0 0 0 3px rgba(59,130,246,.28)}
    .dc-msg.hl .dc-bub,.dc-msg.hl .dc-vn{box-shadow:0 0 0 3px rgba(245,158,11,.28)}
    .dc-av{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#3b82f6);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;flex-shrink:0}
    .dc-body{min-width:0;width:100%}
    .dc-meta{display:flex;justify-content:space-between;gap:6px;font-size:11px;margin-bottom:4px}
    .dc-meta-l{display:flex;gap:6px;flex-wrap:wrap;align-items:center}
    .dc-role{background:#e2e8f0;border-radius:999px;padding:1px 7px;font-weight:600}
    .dc-time{color:#94a3b8}
    .dc-pin-badge{background:#fef3c7;border:1px solid #fcd34d;border-radius:999px;padding:1px 6px;color:#92400e;font-size:10px;font-weight:700}
    .dc-bub{background:#f8fafc;border:1px solid #dbe7ff;border-radius:10px;padding:8px 10px;font-size:13px;white-space:pre-wrap;word-break:break-word}
    .dc-msg.own .dc-bub{background:#dbeafe}
    .dc-file{margin-top:6px;display:inline-block;background:#eef2ff;border:1px solid #c7d2fe;border-radius:8px;padding:5px 8px;font-size:12px;font-weight:700;color:#1e3a8a;text-decoration:none}
    .dc-vn{margin-top:6px;border:1px solid #bfdbfe;background:#eff6ff;border-radius:9px;padding:6px}
    .dc-vn span{display:block;font-size:11px;color:#1e3a8a;font-weight:700;margin-bottom:3px}
    .dc-vn audio{width:min(330px,100%);height:32px}
    .dc-form{position:sticky;bottom:0;background:#fff;border-top:1px solid #dbe7ff;padding:12px;z-index:3}
    .dc-act{display:flex;gap:6px;align-items:center;flex-wrap:wrap;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:8px;margin-bottom:8px}
    .dc-act-label{font-size:12px;font-weight:700;color:#1e3a8a}
    .dc-act-btn{border:1px solid #cbd5e1;border-radius:7px;background:#fff;padding:5px 8px;font-size:11px;font-weight:700;color:#334155}
    .dc-act-btn.pin{background:#f0fdf4;border-color:#86efac;color:#166534}
    .dc-act-btn.warn{background:#fef2f2;border-color:#fecaca;color:#991b1b}
    .dc-act-sel{border:1px solid #cbd5e1;border-radius:7px;background:#fff;padding:5px 8px;font-size:11px;font-weight:700;color:#334155}
    .dc-ta{width:100%;min-height:78px;max-height:170px;resize:vertical;border:1px solid #cbd5e1;border-radius:10px;padding:8px 10px;font-size:13px}
    .dc-row{margin-top:8px;display:flex;gap:6px;align-items:center;flex-wrap:wrap}
    .dc-f-trg{position:relative;display:inline-flex;gap:5px;align-items:center;background:#dbeafe;border:1px solid #bfdbfe;border-radius:8px;padding:6px 9px;font-size:12px;font-weight:700;color:#1e40af;cursor:pointer}
    .dc-file-in{position:absolute;opacity:0;width:1px;height:1px;pointer-events:none}
    .dc-note{font-size:12px;color:#64748b}
    .dc-rec,.dc-rec-clear{border:1px solid #cbd5e1;border-radius:8px;background:#fff;padding:6px 9px;font-size:12px;font-weight:700}
    .dc-rec.active{background:#fee2e2;border-color:#fca5a5;color:#b91c1c}
    .dc-vn-preview{margin-top:7px;border:1px solid #bfdbfe;background:#eff6ff;border-radius:9px;padding:6px}
    .dc-vn-preview span{display:block;font-size:11px;color:#1e3a8a;font-weight:700;margin-bottom:4px}
    .dc-vn-preview audio{width:min(360px,100%);height:34px}
    .dc-send{margin-left:auto;border:none;border-radius:8px;background:linear-gradient(135deg,#2563eb,#3b82f6);color:#fff;padding:7px 12px;font-size:13px;font-weight:700}
    @media(max-width:991px){.dc-wrap{grid-template-columns:1fr;height:calc(100vh - 70px);min-height:calc(100vh - 70px)}.dc-main{height:100%}.dc-side{display:none}.dc-search{width:180px}.dc-pin-text{max-width:45vw}}
    @media(max-width:576px){.dc-send{width:100%;margin-left:0}.dc-msg{max-width:100%}}
</style>

<div class="dc-wrap">
    <aside class="dc-card dc-side">
        <h4>Diskusi Tim</h4>
        <p>Chat, voice note, pin berdurasi, dan hapus pesan sendiri.</p>
        @foreach($channels as $channel)
            <a href="{{ route('discussion.index', ['channel' => $channel->id]) }}" class="dc-channel {{ (int) $activeChannel->id === (int) $channel->id ? 'active' : '' }}">
                <i class="fas fa-hashtag"></i> {{ $channel->name }}
            </a>
        @endforeach
    </aside>

    <section class="dc-card dc-main">
        <div class="dc-head">
            <div>
                <h5>#{{ $activeChannel->name }}</h5>
                <small>{{ $activeChannel->description ?: 'Kanal diskusi bersama lintas role.' }}</small>
            </div>
            <div class="dc-head-r">
                <input id="dcSearch" class="dc-search" type="text" placeholder="Search chat...">
                <span class="dc-live"><i class="fas fa-circle"></i> LIVE</span>
            </div>
        </div>

        <div class="dc-pin-box">
            <div class="dc-pin-title"><i class="fas fa-thumbtack"></i> Pesan Dipin</div>
            <div id="dcPinList"></div>
            <div id="dcPinEmpty" class="dc-pin-empty">Belum ada pesan dipin.</div>
        </div>

        <div id="dcList" class="dc-list">
            <div id="dcEmpty" class="dc-empty">Belum ada pesan. Mulai diskusi sekarang.</div>
        </div>

        <form id="dcForm" class="dc-form" enctype="multipart/form-data">
            <input type="hidden" name="channel_id" value="{{ (int) $activeChannel->id }}">

            <div id="dcAction" class="dc-act" style="display:none;">
                <span id="dcActionLabel" class="dc-act-label">Pesan terpilih</span>
                <select id="dcPinDuration" class="dc-act-sel">
                    <option value="24h">24 Jam</option>
                    <option value="48h">48 Jam</option>
                    <option value="1w" selected>1 Minggu</option>
                    <option value="1m">1 Bulan</option>
                </select>
                <button type="button" id="dcPinAction" class="dc-act-btn pin">Pin Chat</button>
                <button type="button" id="dcUnpin" class="dc-act-btn">Lepas Pin</button>
                <button type="button" id="dcDelete" class="dc-act-btn warn">Hapus Pesan</button>
                <button type="button" id="dcCancelSel" class="dc-act-btn">Batal</button>
            </div>

            <textarea id="dcMessage" name="message" class="dc-ta" placeholder="Ketik pesan untuk semua role..."></textarea>

            <div id="dcVoicePreviewWrap" class="dc-vn-preview" style="display:none;">
                <span id="dcVoicePreviewLabel">Preview voice note</span>
                <audio id="dcVoicePreview" controls preload="metadata"></audio>
            </div>

            <div class="dc-row">
                <label class="dc-f-trg">
                    <i class="fas fa-paperclip"></i> Lampiran
                    <input id="dcFile" class="dc-file-in" type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip,.rar">
                </label>
                <span id="dcFileName" class="dc-note">Tidak ada file.</span>

                <input id="dcVoice" class="dc-file-in" type="file" name="voice_note" accept=".webm,.ogg,.oga,.mp3,.wav,.wave,.m4a,.aac,.mp4,.3gp,.amr,audio/*">
                <button id="dcRec" type="button" class="dc-rec"><i class="fas fa-microphone"></i> Voice Note</button>
                <button id="dcRecClear" type="button" class="dc-rec-clear" style="display:none;"><i class="fas fa-times"></i> Hapus VN</button>
                <span id="dcVoiceStat" class="dc-note">Belum ada voice note.</span>

                <button id="dcSend" type="submit" class="dc-send">Kirim</button>
            </div>
        </form>
    </section>
</div>
@endsection

@section('js')
<script>
(function(){
    const POLL=2000,currentUserId=@json($currentUserId),channelId=@json((int)$activeChannel->id);
    const fetchUrl=@json(route('discussion.messages')),storeUrl=@json(route('discussion.messages.store'));
    const pinTpl=@json(route('discussion.messages.pin',['message'=>'__ID__'])),delTpl=@json(route('discussion.messages.destroy',['message'=>'__ID__']));
    const initialMessages=@json($initialMessages),initialPinned=@json($initialPinnedMessages);
    const form=document.getElementById('dcForm'),list=document.getElementById('dcList'),empty=document.getElementById('dcEmpty');
    const pinList=document.getElementById('dcPinList'),pinEmpty=document.getElementById('dcPinEmpty');
    const msgInput=document.getElementById('dcMessage'),fileInput=document.getElementById('dcFile'),fileName=document.getElementById('dcFileName');
    const voiceInput=document.getElementById('dcVoice'),voiceStat=document.getElementById('dcVoiceStat'),voicePrev=document.getElementById('dcVoicePreview');
    const voicePrevWrap=document.getElementById('dcVoicePreviewWrap'),voicePrevLabel=document.getElementById('dcVoicePreviewLabel');
    const recBtn=document.getElementById('dcRec'),clearRecBtn=document.getElementById('dcRecClear'),sendBtn=document.getElementById('dcSend'),searchInput=document.getElementById('dcSearch');
    const actionBox=document.getElementById('dcAction'),actionLabel=document.getElementById('dcActionLabel'),pinDurationSel=document.getElementById('dcPinDuration'),pinActionBtn=document.getElementById('dcPinAction'),unpinBtn=document.getElementById('dcUnpin'),delBtn=document.getElementById('dcDelete'),cancelSelBtn=document.getElementById('dcCancelSel');
    const st={lastId:0,known:new Set(),pinned:new Set(),polling:false,submit:false,pinning:false,mr:null,stream:null,chunks:[],timer:null,sec:0,selectedId:null,selectedMine:false,selectedPinned:false,selectedHasAttachment:false,previewUrl:null,discardRecording:false,searchQuery:''};
    const fsize=(b)=>{b=Number(b||0);if(!b)return'';const u=['B','KB','MB','GB'];let i=0;while(b>=1024&&i<u.length-1){b/=1024;i++;}return`${b.toFixed(b>=10||i===0?0:1)} ${u[i]}`};
    const ftime=(s)=>`${String(Math.floor(s/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`;
    const esc=(v)=>String(v??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    const initial=(n)=>((n||'?').trim().charAt(0)||'?').toUpperCase();
    const nearBottom=()=>list.scrollHeight-list.scrollTop-list.clientHeight<140,toBottom=()=>{list.scrollTop=list.scrollHeight};
    const pinUrl=(id)=>pinTpl.replace('__ID__',String(id)),delUrl=(id)=>delTpl.replace('__ID__',String(id));
    const msgPreview=(m)=>{const t=String(m?.message||'').trim();if(t)return t.length>90?`${t.slice(0,90)}...`:t;if(m?.voice_note_url)return'[Voice Note]';if(m?.attachment_name)return`[Lampiran] ${m.attachment_name}`;return'(Pesan)'};
    function updateFile(){const f=fileInput.files?.[0];fileName.textContent=f?f.name:'Tidak ada file.'}
    function clearPreview(){if(st.previewUrl){URL.revokeObjectURL(st.previewUrl);st.previewUrl=null}voicePrev.removeAttribute('src');voicePrevWrap.style.display='none'}
    function syncPreview(){const f=voiceInput.files?.[0];if(!f){clearPreview();return}clearPreview();st.previewUrl=URL.createObjectURL(f);voicePrev.src=st.previewUrl;voicePrevLabel.textContent=`Preview voice note (${fsize(f.size)})`;voicePrevWrap.style.display='block'}
    function updateRecBtn(){const rec=!!(st.mr&&st.mr.state==='recording');recBtn.classList.toggle('active',rec);recBtn.innerHTML=rec?'<i class="fas fa-stop"></i> Stop Rekam':'<i class="fas fa-microphone"></i> Voice Note'}
    function updateVoice(){if(st.mr&&st.mr.state==='recording'){voiceStat.textContent=`Merekam... ${ftime(st.sec)}`;return}const f=voiceInput.files?.[0];if(f){voiceStat.textContent=`Voice note siap (${fsize(f.size)})`;clearRecBtn.style.display='inline-flex';syncPreview();return}voiceStat.textContent='Belum ada voice note.';clearRecBtn.style.display='none';clearPreview()}
    function stopTracks(){if(st.stream){st.stream.getTracks().forEach(t=>t.stop());st.stream=null}}
    function clearTimer(){if(st.timer){clearInterval(st.timer);st.timer=null}}
    function clearVoice(){if(st.mr&&st.mr.state==='recording'){st.discardRecording=true;st.mr.stop()}voiceInput.value='';st.sec=0;clearTimer();stopTracks();st.mr=null;st.chunks=[];updateRecBtn();updateVoice()}
    function setVoice(blob){const mt=(blob.type||'audio/webm').toLowerCase();let ext='webm';if(mt.includes('ogg'))ext='ogg';else if(mt.includes('mp3')||mt.includes('mpeg'))ext='mp3';else if(mt.includes('wav'))ext='wav';else if(mt.includes('mp4')||mt.includes('m4a')||mt.includes('aac'))ext='m4a';const f=new File([blob],`voice-note-${Date.now()}.${ext}`,{type:blob.type||'audio/webm'});const dt=new DataTransfer();dt.items.add(f);voiceInput.files=dt.files;updateVoice()}
    async function recToggle(){if(st.submit)return;if(st.mr&&st.mr.state==='recording'){st.mr.stop();return}if(!window.MediaRecorder||!navigator.mediaDevices?.getUserMedia){Notification.warning('Browser ini belum mendukung perekaman voice note.');return}
        try{const s=await navigator.mediaDevices.getUserMedia({audio:true});stopTracks();st.stream=s;st.chunks=[];st.sec=0;const m=['audio/webm;codecs=opus','audio/webm','audio/ogg;codecs=opus','audio/ogg','audio/mp4'].find(v=>MediaRecorder.isTypeSupported(v));st.mr=m?new MediaRecorder(s,{mimeType:m}):new MediaRecorder(s);
            st.mr.ondataavailable=(e)=>{if(e.data?.size)st.chunks.push(e.data)};
            st.mr.onstop=()=>{const b=new Blob(st.chunks,{type:st.mr?.mimeType||'audio/webm'});if(!st.discardRecording&&b.size>0)setVoice(b);st.discardRecording=false;clearTimer();stopTracks();st.mr=null;st.chunks=[];st.sec=0;updateRecBtn();updateVoice()};
            st.mr.start();clearTimer();st.timer=setInterval(()=>{st.sec+=1;updateVoice()},1000);updateRecBtn();updateVoice();
        }catch(e){Notification.error('Akses mikrofon ditolak atau tidak tersedia.');clearVoice()}}
    function visibleMessageCount(){return Array.from(list.querySelectorAll('.dc-msg[data-message-id]')).filter((n)=>n.style.display!=='none').length}
    function toggleEmpty(){const count=visibleMessageCount();if(count>0){empty.style.display='none';return}empty.style.display='block';empty.textContent=st.searchQuery?'Tidak ada chat yang cocok.':'Belum ada pesan. Mulai diskusi sekarang.'}
    function refreshAction(){if(!st.selectedId){actionBox.style.display='none';return}actionBox.style.display='flex';actionLabel.textContent=`Pesan terpilih #${st.selectedId}`;pinActionBtn.textContent=st.selectedHasAttachment?'Pin Dokumen':'Pin Chat';unpinBtn.style.display=st.selectedPinned?'inline-block':'none';delBtn.style.display=st.selectedMine?'inline-block':'none'}
    function selectMessage(node){list.querySelectorAll('.dc-msg.selected').forEach(n=>n.classList.remove('selected'));if(!node){st.selectedId=null;st.selectedMine=false;st.selectedPinned=false;st.selectedHasAttachment=false;refreshAction();return}
        node.classList.add('selected');st.selectedId=Number(node.dataset.messageId||0);st.selectedMine=node.dataset.isMine==='1';st.selectedPinned=node.dataset.isPinned==='1';st.selectedHasAttachment=node.dataset.hasAttachment==='1';refreshAction()}
    function applySearch(){const q=String(st.searchQuery||'').trim().toLowerCase();list.querySelectorAll('.dc-msg[data-message-id]').forEach((node)=>{const text=String(node.dataset.searchText||'').toLowerCase();const ok=q===''||text.includes(q);node.style.display=ok?'':'none'});const selectedNode=st.selectedId?list.querySelector(`[data-message-id="${st.selectedId}"]`):null;if(selectedNode&&selectedNode.style.display==='none')selectMessage(null);toggleEmpty()}
    function applyPin(m){if(!m?.id)return;const id=Number(m.id);if(m.is_pinned)st.pinned.add(id);else st.pinned.delete(id);const node=list.querySelector(`[data-message-id="${id}"]`);if(!node)return;node.dataset.isPinned=m.is_pinned?'1':'0';const badge=node.querySelector('.dc-pin-badge');if(badge)badge.style.display=m.is_pinned?'inline-flex':'none';if(st.selectedId===id){st.selectedPinned=!!m.is_pinned;refreshAction()}}
    function jump(id){const t=document.getElementById(`dc-msg-${id}`);if(!t){Notification.warning('Pesan belum ada di tampilan ini.');return}t.scrollIntoView({behavior:'smooth',block:'center'});t.classList.add('hl');setTimeout(()=>t.classList.remove('hl'),1500);selectMessage(t)}
    function renderPins(arr){pinList.innerHTML='';st.pinned.clear();arr=Array.isArray(arr)?arr:[];if(!arr.length){pinEmpty.style.display='block';document.querySelectorAll('.dc-msg[data-message-id]').forEach(n=>applyPin({id:Number(n.dataset.messageId),is_pinned:false}));return}pinEmpty.style.display='none';
        arr.forEach(m=>{const id=Number(m.id||0);if(id)st.pinned.add(id);const b=document.createElement('div');b.className='dc-pin-item';b.dataset.jumpMessageId=String(id);const meta=[];if(m.pinned_by_name)meta.push(`oleh ${esc(m.pinned_by_name)}`);if(m.pin_expires_at_label)meta.push(`sampai ${esc(m.pin_expires_at_label)}`);
            const docLink=(m.attachment_url&&m.attachment_name)?`<a class="dc-pin-doc" href="${m.attachment_url}" target="_blank" rel="noopener noreferrer"><i class="far fa-file-alt"></i> Dok</a>`:'';
            b.innerHTML=`<div class="dc-pin-main"><span class="dc-pin-text">${esc(msgPreview(m))}</span>${docLink}</div>${meta.length?`<span class="dc-pin-meta">${meta.join(' | ')}</span>`:''}`;pinList.appendChild(b)});
        document.querySelectorAll('.dc-msg[data-message-id]').forEach(n=>{const id=Number(n.dataset.messageId);applyPin({id,is_pinned:st.pinned.has(id)})})}
    function mkMsg(m){const own=!!m.is_mine,p=!!m.is_pinned||st.pinned.has(Number(m.id));const n=document.createElement('div');n.id=`dc-msg-${m.id}`;n.className=`dc-msg ${own?'own':'other'}`;n.dataset.messageId=String(m.id||'');n.dataset.isMine=own?'1':'0';n.dataset.isPinned=p?'1':'0';n.dataset.hasAttachment=(m.attachment_url&&m.attachment_name)?'1':'0';
        n.dataset.searchText=[m?.sender?.name,m?.sender?.role,m?.message,m?.attachment_name,m?.voice_note_name].filter(Boolean).join(' ').toLowerCase();
        const pinBadge=`<span class="dc-pin-badge" style="display:${p?'inline-flex':'none'}">Pinned</span>`;
        const vn=m.voice_note_url?`<div class="dc-vn"><span>Voice Note ${m.voice_note_size?`(${fsize(m.voice_note_size)})`:''}</span><audio controls preload="none" src="${m.voice_note_url}"></audio></div>`:'';
        const f=m.attachment_url&&m.attachment_name?`<a class="dc-file" href="${m.attachment_url}" target="_blank" rel="noopener noreferrer">${esc(m.attachment_name)}${m.attachment_size?` (${fsize(m.attachment_size)})`:''}</a>`:'';
        n.innerHTML=`<div class="dc-av">${esc(initial(m?.sender?.name||''))}</div><div class="dc-body"><div class="dc-meta"><div class="dc-meta-l"><strong>${esc(m?.sender?.name||'Pengguna')}</strong><span class="dc-role">${esc(m?.sender?.role||'-')}</span><span class="dc-time">${esc(m.created_at_label||'-')}</span>${pinBadge}</div></div>${m.message?`<div class="dc-bub">${esc(m.message)}</div>`:''}${vn}${f}</div>`;
        return n}
    function addMsg(m,force){if(!m?.id||st.known.has(m.id))return false;st.known.add(m.id);st.lastId=Math.max(st.lastId,Number(m.id)||0);const n=mkMsg(m);list.appendChild(n);applyPin(m);applySearch();if(force)toBottom();return true}
    function removeMsg(id){const node=list.querySelector(`[data-message-id="${id}"]`);if(node)node.remove();if(st.selectedId===Number(id))selectMessage(null);applySearch()}
    function renderInitial(){(Array.isArray(initialMessages)?initialMessages:[]).forEach(m=>addMsg(m,false));applySearch();toBottom()}
    function poll(){if(st.polling)return;st.polling=true;const should=nearBottom();Http.get(fetchUrl,{channel_id:channelId,after_id:st.lastId}).done((r)=>{if(Array.isArray(r?.pinned_messages))renderPins(r.pinned_messages);let fresh=false;(Array.isArray(r?.messages)?r.messages:[]).forEach(m=>{fresh=addMsg(m,false)||fresh});if(r?.latest_id)st.lastId=Math.max(st.lastId,Number(r.latest_id)||0);if(fresh&&should&&st.searchQuery==='')toBottom()}).always(()=>st.polling=false)}
    function pinSelected(duration){if(!st.selectedId||st.pinning)return;st.pinning=true;Http.post(pinUrl(st.selectedId),{channel_id:channelId,action:'pin',pin_duration:duration}).done((r)=>{if(r?.data)applyPin(r.data);if(Array.isArray(r?.pinned_messages))renderPins(r.pinned_messages)}).fail((e)=>Notification.error(e)).always(()=>st.pinning=false)}
    function unpinSelected(){if(!st.selectedId||st.pinning)return;st.pinning=true;Http.post(pinUrl(st.selectedId),{channel_id:channelId,action:'unpin'}).done((r)=>{if(r?.data)applyPin(r.data);if(Array.isArray(r?.pinned_messages))renderPins(r.pinned_messages)}).fail((e)=>Notification.error(e)).always(()=>st.pinning=false)}
    async function deleteSelected(){if(!st.selectedId)return;if(!st.selectedMine){Notification.warning('Anda hanya bisa menghapus pesan milik sendiri.');return}const confirm=await Notification.confirmation('Yakin ingin menghapus pesan ini?');if(!confirm.isConfirmed)return;
        Http.delete(delUrl(st.selectedId),{channel_id:channelId}).done((r)=>{removeMsg(st.selectedId);if(Array.isArray(r?.pinned_messages))renderPins(r.pinned_messages)}).fail((e)=>Notification.error(e))}
    function submit(e){e.preventDefault();if(st.submit)return;if(st.mr&&st.mr.state==='recording'){Notification.warning('Stop rekaman voice note dulu sebelum kirim pesan.');return}
        const txt=msgInput.value.trim(),hasFile=fileInput.files?.length>0,hasVoice=voiceInput.files?.length>0;if(!txt&&!hasFile&&!hasVoice){Notification.warning('Isi pesan, upload file, atau kirim voice note.');return}
        st.submit=true;sendBtn.disabled=true;Http.post(storeUrl,new FormData(form)).done((r)=>{if(r?.data)addMsg(r.data,true);if(Array.isArray(r?.pinned_messages))renderPins(r.pinned_messages);form.reset();updateFile();clearVoice();msgInput.focus()}).fail((er)=>Notification.error(er)).always(()=>{st.submit=false;sendBtn.disabled=false})}
    form.addEventListener('submit',submit);fileInput.addEventListener('change',updateFile);recBtn.addEventListener('click',recToggle);clearRecBtn.addEventListener('click',clearVoice);
    searchInput.addEventListener('input',()=>{st.searchQuery=searchInput.value||'';applySearch()});
    pinActionBtn.addEventListener('click',()=>pinSelected(String(pinDurationSel.value||'1w')));unpinBtn.addEventListener('click',unpinSelected);delBtn.addEventListener('click',deleteSelected);cancelSelBtn.addEventListener('click',()=>selectMessage(null));
    pinList.addEventListener('click',(e)=>{if(e.target.closest('.dc-pin-doc'))return;const t=e.target.closest('[data-jump-message-id]');if(t)jump(Number(t.dataset.jumpMessageId||0))});
    list.addEventListener('click',(e)=>{if(e.target===list){selectMessage(null);return}if(e.target.closest('a,audio,button'))return;const row=e.target.closest('.dc-msg');if(row)selectMessage(row)});
    renderInitial();renderPins(initialPinned);updateFile();updateRecBtn();updateVoice();refreshAction();setInterval(poll,POLL);
})();
</script>
@endsection
