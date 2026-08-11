<?php
$cfg       = $accountsConfig ?? [];
$gameId    = (int)$game['id'];
$slug      = $game['slug'];
$pageTitle = $cfg['page_title']       ?? '';
$pageDesc  = $cfg['page_description'] ?? '';
$filters   = $cfg['filters']          ?? ['server','rank','price'];
$servers   = $cfg['servers']          ?? [];
$ranks     = $cfg['ranks']            ?? [];
$roles     = $cfg['roles']            ?? [];
$typeCards = !empty($cfg['show_type_cards']);
$presetServers = ['euw','eune','na','br','tr','kr','jp','oce','las','lan','ru','ph','sg','th','tw','vn','me'];
$presetRanks   = ['Unranked','Iron','Bronze','Silver','Gold','Platinum','Emerald','Diamond','Master','Grandmaster','Challenger'];
$presetRoles   = ['TopLane','Jungle','MidLane','AdCarry','Support'];
?>
<style>
.ac2 { }
.ac2-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px; }
@media(max-width:900px){ .ac2-grid{grid-template-columns:1fr;} }
.ac2-block {
  background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.08);
  border-radius:12px; padding:16px 18px;
}
.ac2-block--full { grid-column:1/-1; }
.ac2-label {
  font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.1em;
  color:rgba(255,255,255,.35); margin-bottom:10px;
  display:flex; align-items:center; gap:6px;
}
.ac2-label i { font-size:11px; opacity:.7; }
.ac2-input {
  width:100%; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.09);
  border-radius:8px; padding:9px 12px; color:#fff; font-size:13px; font-weight:500;
  outline:none; transition:border-color .18s; resize:vertical;
}
.ac2-input:focus { border-color:rgba(129,140,248,.5); background:rgba(255,255,255,.07); }

/* Filter toggles */
.ac2-filters { display:flex; flex-wrap:wrap; gap:7px; }
.ac2-ftoggle {
  display:inline-flex; align-items:center; gap:7px; padding:7px 14px;
  border-radius:9px; cursor:pointer; user-select:none; transition:all .15s;
  border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.04); color:rgba(255,255,255,.5); font-size:13px; font-weight:700;
}
.ac2-ftoggle:hover { border-color:rgba(255,255,255,.2); color:rgba(255,255,255,.75); }
.ac2-ftoggle.on { background:rgba(99,102,241,.18); border-color:rgba(129,140,248,.35); color:#c7d2fe; }
.ac2-ftoggle input { display:none; }
.ac2-ftoggle-ico { width:16px; height:16px; border-radius:5px; border:1.5px solid rgba(255,255,255,.25); display:flex; align-items:center; justify-content:center; transition:all .15s; flex-shrink:0; }
.ac2-ftoggle.on .ac2-ftoggle-ico { background:#6366f1; border-color:#6366f1; }
.ac2-ftoggle.on .ac2-ftoggle-ico::after { content:''; display:block; width:9px; height:5px; border-left:2px solid #fff; border-bottom:2px solid #fff; transform:rotate(-45deg) translate(1px,-1px); }

/* Tag cloud — presets ARE the tags, toggle on/off */
.ac2-tagcloud { display:flex; flex-wrap:wrap; gap:6px; }
.ac2-tog {
  padding:5px 12px; border-radius:8px; font-size:12px; font-weight:700;
  cursor:pointer; user-select:none; transition:all .15s;
  border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.04); color:rgba(255,255,255,.5);
}
.ac2-tog:hover { border-color:rgba(255,255,255,.18); color:rgba(255,255,255,.8); }
.ac2-tog.on {
  background:rgba(99,102,241,.2); border-color:rgba(129,140,248,.32); color:#c7d2fe;
}
.ac2-tog.on::before { content:'✓ '; font-size:10px; opacity:.8; }

/* Custom input for non-presets */
.ac2-custom { display:flex; align-items:center; gap:8px; margin-top:10px; }
.ac2-custom input { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.09); border-radius:8px; padding:7px 12px; color:#fff; font-size:12px; outline:none; flex:1; transition:border-color .18s; }
.ac2-custom input:focus { border-color:rgba(129,140,248,.5); }
.ac2-custom button { padding:7px 14px; border-radius:8px; border:1px solid rgba(129,140,248,.3); background:rgba(99,102,241,.18); color:#c7d2fe; font-size:12px; font-weight:700; cursor:pointer; transition:all .15s; white-space:nowrap; }
.ac2-custom button:hover { background:rgba(99,102,241,.32); }
.ac2-extra-tags { display:flex; flex-wrap:wrap; gap:5px; margin-top:7px; }
.ac2-etag { display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:7px; font-size:11px; font-weight:700; background:rgba(99,102,241,.2); color:#c7d2fe; border:1px solid rgba(129,140,248,.28); }
.ac2-etag button { border:none; background:none; padding:0; color:inherit; opacity:.6; cursor:pointer; font-size:12px; line-height:1; }
.ac2-etag button:hover { opacity:1; }

/* Type cards switch */
.ac2-switch-row { display:flex; align-items:center; gap:14px; }
.ac2-switch-body { flex:1; }
.ac2-switch-body b { font-size:13px; font-weight:800; color:#fff; display:block; }
.ac2-switch-body span { font-size:11px; color:rgba(255,255,255,.38); }


/* ── V6 polished accounts builder ─────────────────────────── */
#acccountsConfigCard,.ac2-shell{border-radius:18px}.ac2-hero{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:16px;padding:18px 20px;border-radius:16px;background:linear-gradient(135deg,rgba(16,185,129,.14),rgba(99,102,241,.10)),rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.08)}.ac2-hero__kicker{font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.13em;color:#86efac;margin-bottom:4px}.ac2-hero__title{font-size:20px;font-weight:900;color:#fff;margin:0}.ac2-hero__sub{font-size:12px;color:rgba(255,255,255,.5);margin-top:3px}.ac2-grid{gap:16px}.ac2-block{border-radius:16px;background:rgba(8,12,18,.34);border:1px solid rgba(255,255,255,.075);box-shadow:inset 0 1px 0 rgba(255,255,255,.035);transition:.16s}.ac2-block:hover{border-color:rgba(16,185,129,.22);background:rgba(16,185,129,.045)}.ac2-label{color:rgba(255,255,255,.5)}.ac2-input{border-radius:11px;background:rgba(8,12,18,.42);border-color:rgba(255,255,255,.1)}.ac2-input:focus{border-color:rgba(16,185,129,.55);box-shadow:0 0 0 3px rgba(16,185,129,.12)}.ac2-ftoggle,.ac2-tog,.ac2-etag{border-radius:999px}.ac2-ftoggle.on{background:rgba(16,185,129,.14);border-color:rgba(16,185,129,.34);color:#a7f3d0}.ac2-tog.on{background:rgba(16,185,129,.14);border-color:rgba(16,185,129,.32);color:#a7f3d0}.ac2-custom input{border-radius:10px;background:rgba(8,12,18,.42)}.ac2-custom button{border-radius:10px;background:rgba(16,185,129,.12);border-color:rgba(16,185,129,.28);color:#a7f3d0}.ac2-savebar{display:flex;justify-content:flex-end;align-items:center;gap:10px;margin-top:16px;padding:12px;border:1px solid rgba(16,185,129,.18);border-radius:14px;background:rgba(8,12,18,.35)}



/* ── V7 full visual pass for Accounts Config ─────────────── */
#accountsConfigCard{border-radius:20px!important;background:linear-gradient(180deg,rgba(255,255,255,.045),rgba(255,255,255,.02))!important;border:1px solid rgba(255,255,255,.08)!important;box-shadow:0 18px 45px rgba(0,0,0,.20);overflow:hidden}.ac2-hero{border-radius:20px!important;padding:22px 24px!important;background:radial-gradient(circle at 12% 0%,rgba(16,185,129,.22),transparent 35%),linear-gradient(135deg,rgba(16,185,129,.13),rgba(99,102,241,.09)),rgba(255,255,255,.025)!important}.ac2-hero__title{font-size:25px!important;letter-spacing:-.035em}.ac2-block{border-radius:18px!important;padding:18px 20px!important;background:linear-gradient(180deg,rgba(255,255,255,.035),rgba(255,255,255,.015))!important;border-color:rgba(255,255,255,.08)!important}.ac2-block:hover{transform:translateY(-1px);box-shadow:0 14px 35px rgba(0,0,0,.16)}.ac2-input{height:42px;border-radius:12px!important}.ac2 textarea.ac2-input{height:auto}.ac2-label{font-size:11px!important;color:rgba(255,255,255,.52)!important}.ac2-ftoggle,.ac2-tog{padding:8px 14px!important}.ac2-savebar{border-radius:18px!important;padding:14px!important;position:sticky;bottom:16px;z-index:7;background:rgba(20,23,28,.86)!important;backdrop-filter:blur(12px)}.ac2-custom input,.ac2-custom button{height:38px;border-radius:12px!important}

</style>

<div class="card mb-4" id="accountsConfigCard">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-header-title">
      <i class="fa-solid fa-user-circle me-2 text-success"></i>
      Accounts Config
      <code class="fw-normal small ms-2 text-muted">/<?= htmlspecialchars($slug) ?>/accounts</code>
    </h5>
    <div class="d-flex gap-2">
      <a href="/<?= htmlspecialchars($slug) ?>/accounts" target="_blank" class="btn btn-ghost-secondary btn-sm">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Preview
      </a>
    </div>
  </div>
  <div class="card-body ac2">
    <div class="ac2-hero">
      <div>
        <div class="ac2-hero__kicker">Account Shop</div>
        <h2 class="ac2-hero__title">Accounts Builder</h2>
        <div class="ac2-hero__sub">SEO text, filters, servers, ranks, roles and account type cards.</div>
      </div>
      <a href="/<?= htmlspecialchars($slug) ?>/accounts" target="_blank" class="btn btn-ghost-secondary btn-sm"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Preview</a>
    </div>

    <div class="ac2-grid">

      <!-- Page Title -->
      <div class="ac2-block">
        <div class="ac2-label"><i class="fa-solid fa-heading"></i> Page Title</div>
        <input type="text" class="ac2-input" id="acPageTitle"
               value="<?= htmlspecialchars($pageTitle) ?>"
               placeholder="e.g. LoL Ranked Accounts">
      </div>

      <!-- Active Filters -->
      <div class="ac2-block">
        <div class="ac2-label"><i class="fa-solid fa-filter"></i> Active Filters</div>
        <div class="ac2-filters">
          <?php foreach (['server'=>'Server','rank'=>'Rank','roles'=>'Roles','price'=>'Price'] as $fk=>$fl): ?>
          <label class="ac2-ftoggle <?= in_array($fk, $filters, true) ? 'on' : '' ?>">
            <span class="ac2-ftoggle-ico"></span>
            <input type="checkbox" class="ac2-filter-cb" value="<?= $fk ?>"
                   <?= in_array($fk, $filters, true) ? 'checked' : '' ?>>
            <?= $fl ?>
          </label>
          <?php endforeach ?>
        </div>
      </div>

      <!-- Page Description — full width -->
      <div class="ac2-block ac2-block--full">
        <div class="ac2-label"><i class="fa-solid fa-align-left"></i> Page Description</div>
        <textarea class="ac2-input" id="acPageDesc" rows="2"
                  placeholder="Buy ranked premium accounts..."><?= htmlspecialchars($pageDesc) ?></textarea>
      </div>

      <!-- Servers -->
      <div class="ac2-block">
        <div class="ac2-label"><i class="fa-solid fa-server"></i> Servers</div>
        <div class="ac2-tagcloud" id="ac-srv-cloud">
          <?php foreach ($presetServers as $ps): ?>
          <span class="ac2-tog <?= in_array($ps, $servers) ? 'on' : '' ?>"
                onclick="ac2Toggle(this,'ac-hid-srv','ac-srv-cloud')" data-val="<?= $ps ?>">
            <?= strtoupper($ps) ?>
          </span>
          <?php endforeach ?>
        </div>
        <div class="ac2-custom">
          <input type="text" id="ac-srv-custom" placeholder="Custom server…">
          <button type="button" onclick="ac2AddCustom('ac-srv-custom','ac-hid-srv','ac-srv-extra')">+ Add</button>
        </div>
        <div class="ac2-extra-tags" id="ac-srv-extra">
          <?php foreach (array_diff($servers, $presetServers) as $s): ?>
          <span class="ac2-etag" data-val="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?><button onclick="ac2RmExtra(this,'ac-hid-srv','ac-srv-extra')">×</button></span>
          <?php endforeach ?>
        </div>
        <input type="hidden" id="ac-hid-srv" value="<?= htmlspecialchars(implode(',', $servers)) ?>">
      </div>

      <!-- Ranks -->
      <div class="ac2-block">
        <div class="ac2-label"><i class="fa-solid fa-trophy"></i> Ranks</div>
        <div class="ac2-tagcloud" id="ac-rnk-cloud">
          <?php foreach ($presetRanks as $pr): ?>
          <span class="ac2-tog <?= in_array($pr, $ranks) ? 'on' : '' ?>"
                onclick="ac2Toggle(this,'ac-hid-rnk','ac-rnk-cloud')" data-val="<?= $pr ?>">
            <?= $pr ?>
          </span>
          <?php endforeach ?>
        </div>
        <div class="ac2-custom">
          <input type="text" id="ac-rnk-custom" placeholder="Custom rank…">
          <button type="button" onclick="ac2AddCustom('ac-rnk-custom','ac-hid-rnk','ac-rnk-extra')">+ Add</button>
        </div>
        <div class="ac2-extra-tags" id="ac-rnk-extra">
          <?php foreach (array_diff($ranks, $presetRanks) as $r): ?>
          <span class="ac2-etag" data-val="<?= htmlspecialchars($r) ?>"><?= htmlspecialchars($r) ?><button onclick="ac2RmExtra(this,'ac-hid-rnk','ac-rnk-extra')">×</button></span>
          <?php endforeach ?>
        </div>
        <input type="hidden" id="ac-hid-rnk" value="<?= htmlspecialchars(implode(',', $ranks)) ?>">
      </div>

      <!-- Roles + Type Cards -->
      <div class="ac2-block ac2-block--full">
        <div class="ac2-grid" style="margin-bottom:0;">
          <div>
            <div class="ac2-label"><i class="fa-solid fa-users"></i> Roles</div>
            <div class="ac2-tagcloud" id="ac-rol-cloud">
              <?php foreach ($presetRoles as $pr): ?>
              <span class="ac2-tog <?= in_array($pr, $roles) ? 'on' : '' ?>"
                    onclick="ac2Toggle(this,'ac-hid-rol','ac-rol-cloud')" data-val="<?= $pr ?>">
                <?= $pr ?>
              </span>
              <?php endforeach ?>
            </div>
            <div class="ac2-custom">
              <input type="text" id="ac-rol-custom" placeholder="Custom role…">
              <button type="button" onclick="ac2AddCustom('ac-rol-custom','ac-hid-rol','ac-rol-extra')">+ Add</button>
            </div>
            <div class="ac2-extra-tags" id="ac-rol-extra">
              <?php foreach (array_diff($roles, $presetRoles) as $r): ?>
              <span class="ac2-etag" data-val="<?= htmlspecialchars($r) ?>"><?= htmlspecialchars($r) ?><button onclick="ac2RmExtra(this,'ac-hid-rol','ac-rol-extra')">×</button></span>
              <?php endforeach ?>
            </div>
            <input type="hidden" id="ac-hid-rol" value="<?= htmlspecialchars(implode(',', $roles)) ?>">
          </div>
          <div>
            <div class="ac2-label"><i class="fa-solid fa-layer-group"></i> Account Type Cards</div>
            <div class="ac2-switch-row" style="padding:12px 0;">
              <div class="ac2-switch-body">
                <b>Show type cards at top</b>
                <span>Only needed for LoL & Valorant with multiple account types.</span>
              </div>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="acTypeCards" <?= $typeCards ? 'checked' : '' ?>>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /ac2-grid -->

    <div class="ac2-savebar">
      <button type="button" class="btn btn-primary" onclick="saveAccountsCfg(<?= $gameId ?>)">
        <i class="fa-solid fa-floppy-disk me-1"></i> Save Accounts Config
      </button>
    </div>

  </div>
</div>

<script>
/* Filter toggle pills */
document.querySelectorAll('.ac2-ftoggle').forEach(function(el){
  el.addEventListener('click',function(){
    var cb=this.querySelector('input');
    cb.checked=!cb.checked;
    this.classList.toggle('on',cb.checked);
  });
});

/* Preset tag toggle */
function ac2Sync(hidId, cloudId, extraId) {
  var vals=[];
  document.querySelectorAll('#'+cloudId+' .ac2-tog.on').forEach(function(t){vals.push(t.dataset.val);});
  if(extraId) document.querySelectorAll('#'+extraId+' .ac2-etag').forEach(function(t){vals.push(t.dataset.val);});
  document.getElementById(hidId).value=vals.join(',');
}
function ac2Toggle(el, hidId, cloudId) {
  el.classList.toggle('on');
  ac2Sync(hidId, cloudId, hidId.replace('hid','extra').replace('-','2-').replace('ac-hid-','ac-').replace('srv','srv-extra').replace('rnk','rnk-extra').replace('rol','rol-extra'));
  // simpler: just resync directly
  var extraMap={'ac-hid-srv':'ac-srv-extra','ac-hid-rnk':'ac-rnk-extra','ac-hid-rol':'ac-rol-extra'};
  ac2Sync(hidId, cloudId, extraMap[hidId]);
}
function ac2AddCustom(inputId, hidId, extraId) {
  var inp=document.getElementById(inputId);
  var val=inp.value.trim(); if(!val){return;}
  var cloud=hidId.replace('hid','').replace('ac--','ac-').replace('-srv','-srv-cloud').replace('-rnk','-rnk-cloud').replace('-rol','-rol-cloud');
  // Check not duplicate
  var existing=document.getElementById(hidId).value.split(',').map(function(v){return v.toLowerCase();});
  if(existing.includes(val.toLowerCase())){inp.value='';return;}
  var tag=document.createElement('span');
  tag.className='ac2-etag'; tag.dataset.val=val;
  tag.innerHTML=val+'<button onclick="ac2RmExtra(this,\''+hidId+'\',\''+extraId+'\')">×</button>';
  document.getElementById(extraId).appendChild(tag);
  var cloudId=hidId.replace('ac-hid-','ac-').replace('srv','srv-cloud').replace('rnk','rnk-cloud').replace('rol','rol-cloud');
  ac2Sync(hidId, cloudId, extraId);
  inp.value='';
}
function ac2RmExtra(btn, hidId, extraId) {
  btn.closest('.ac2-etag').remove();
  var cloudId=hidId.replace('ac-hid-','ac-').replace('srv','srv-cloud').replace('rnk','rnk-cloud').replace('rol','rol-cloud');
  ac2Sync(hidId, cloudId, extraId);
}

/* AJAX Save */
function _acSaveUnused(gameId) {
  var filters=[];
  document.querySelectorAll('.ac2-filter-cb:checked').forEach(function(cb){filters.push(cb.value);});
  var body=new FormData();
  body.append('page_title',       document.getElementById('acPageTitle').value);
  body.append('page_description', document.getElementById('acPageDesc').value);
  body.append('servers',          document.getElementById('ac-hid-srv').value);
  body.append('ranks',            document.getElementById('ac-hid-rnk').value);
  body.append('roles',            document.getElementById('ac-hid-rol').value);
  body.append('show_type_cards',  document.getElementById('acTypeCards').checked?'1':'0');
  filters.forEach(function(f){body.append('filters[]',f);});
  fetch('/admin-area/games/'+gameId+'/accounts-config',{method:'POST',body:body})
    .then(function(){if(typeof toast==='function')toast('Accounts config saved!','success');})
    .catch(function(){if(typeof toast==='function')toast('Save failed','error');});
}
</script>
