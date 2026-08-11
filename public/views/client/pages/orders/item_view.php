<?= $this->layout('client/layouts/main', ['meta' => $meta]) ?>
<?php
$purchase         = $purchase ?? [];
$seller           = $seller ?? null;
$details          = $details ?? [];
$remaining        = $remaining ?? null;
$can_review       = $can_review ?? false;
$already_reviewed = $already_reviewed ?? false;
$is_completed     = strtolower($purchase['status'] ?? '') === 'completed';
$seller_id_rv     = (int)($purchase['seller_id'] ?? 0);
$purchase_id_rv   = (int)($purchase['id'] ?? 0);
$seller_name_rv   = htmlspecialchars($seller['username'] ?? 'the seller');
?>
<div class="content container-fluid">
    <div class="page-header"><div class="row align-items-center"><div class="col"><h1 class="page-header-title">Item Order #<?= (int)$purchase['id'] ?></h1></div></div></div>
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card"><div class="card-body">
                <div class="mb-3"><div class="small text-muted">Item</div><div class="fw-semibold"><?= htmlspecialchars($purchase['item_title'] ?? 'Item') ?></div></div>
                <div class="mb-3"><div class="small text-muted">Seller</div><div class="fw-semibold"><?= htmlspecialchars($seller['username'] ?? 'Seller') ?></div></div>
                <div class="mb-3"><div class="small text-muted">Wanted gift</div><div class="fw-semibold"><?= htmlspecialchars($details['wanted_gift'] ?? '—') ?></div></div>
                <div class="mb-3"><div class="small text-muted">Riot ID</div><div class="fw-semibold"><?= htmlspecialchars(($details['riot_game_name'] ?? '—') . (!empty($details['riot_tagline']) ? '#' . $details['riot_tagline'] : '')) ?></div></div>
                <div class="mb-3"><div class="small text-muted">Status</div><span class="badge bg-soft-primary text-primary"><?= htmlspecialchars($purchase['status']) ?></span></div>
                <?php if (!empty($purchase['friendship_confirmed_at'])): ?>
                    <div class="alert alert-soft-primary mb-0">
                        <div class="fw-semibold">Friendship confirmed by seller</div>
                        <div class="small">Ready at: <?= !empty($purchase['friendship_ready_at']) ? date('d.m.Y H:i', strtotime($purchase['friendship_ready_at'])) : '—' ?></div>
                        <div class="small" id="friendshipCountdown" data-ready-at="<?= htmlspecialchars($purchase['friendship_ready_at'] ?? '') ?>"><?= $remaining !== null && $remaining > 0 ? '' : 'Gifting is now available.' ?></div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-soft-secondary mb-0">Waiting for seller to confirm friendship.</div>
                <?php endif; ?>
            </div></div>
        </div>
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Seller Chat</h4></div>
                <div class="card-body">
                    <div id="itemChatMessages" style="max-height:520px;overflow:auto;display:flex;flex-direction:column;gap:10px;padding-right:4px;"></div>
                    <form class="ajax-form mt-3" action="<?= AJAX_URL ?>" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="client_item_chat_send">
                        <input type="hidden" name="purchase_id" value="<?= (int)$purchase['id'] ?>">
                        <div class="row g-2">
                            <div class="col"><textarea class="form-control" name="message" rows="3" placeholder="Write a message..."></textarea></div>
                            <div class="col-12 col-md-auto"><input type="file" class="form-control" name="chat_image" accept="image/*"></div>
                            <div class="col-12 col-md-auto"><button type="submit" class="btn btn-primary h-100">Send</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->start('styles') ?>
<style>
.lb-modal .modal-content{background:#25282a;border:1px solid rgba(255,255,255,.10);border-radius:18px;}
.lb-modal .modal-header{padding:1.1rem 1.25rem;border-bottom:1px solid rgba(255,255,255,.07);}
.lb-modal .modal-footer{padding:.9rem 1.25rem;border-top:1px solid rgba(255,255,255,.07);}
.lb-modal .lb-modal-head{display:flex;align-items:flex-start;gap:.85rem;min-width:0;}
.lb-modal .lb-modal-ico{width:46px;height:46px;border-radius:16px;display:grid;place-items:center;background:rgba(88,101,242,.14);border:1px solid rgba(88,101,242,.26);color:#cfd5ff;flex:0 0 auto;}
.lb-modal .lb-modal-headtxt{min-width:0;}
.lb-modal .lb-modal-title{margin:0;font-weight:950;font-size:1.05rem;line-height:1.2;}
.lb-modal .lb-modal-sub{margin:.25rem 0 0;opacity:.72;font-size:.9rem;line-height:1.35;}
.lb-modal .lb-modal-x{width:42px;height:42px;border-radius:14px;display:grid;place-items:center;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.85);transition:.15s ease;flex:0 0 auto;}
.lb-modal .lb-modal-x:hover{background:rgba(255,255,255,.07);border-color:rgba(255,255,255,.16);color:#fff;transform:translateY(-1px);}
.lb-star{width:42px;height:42px;border-radius:14px;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.03);display:grid;place-items:center;transition:.15s ease;padding:0;cursor:pointer;}
.lb-star:hover{transform:translateY(-1px);background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.16);}
.lb-star svg{width:26px;height:26px;}
.lb-star svg path{fill:transparent;stroke:rgba(31,230,198,.60);stroke-width:2;transition:fill .12s,stroke .12s,filter .12s;}
.lb-star.is-on svg path{fill:rgba(31,230,198,1);stroke:rgba(31,230,198,1);filter:drop-shadow(0 8px 18px rgba(31,230,198,.22));}
.sr-review-card{border-radius:16px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.03);}
.sr-suggestion-pill{border:1px solid rgba(88,101,242,.22);background:rgba(88,101,242,.10);color:#dbe1ff;border-radius:999px;padding:.45rem .8rem;font-size:.78rem;font-weight:700;line-height:1;transition:.15s ease;cursor:pointer;}
.sr-suggestion-pill:hover{background:rgba(88,101,242,.18);border-color:rgba(88,101,242,.34);transform:translateY(-1px);}
.sr-suggestion-pill.is-active{background:rgba(88,101,242,.24);border-color:rgba(88,101,242,.42);color:#fff;box-shadow:0 8px 18px rgba(88,101,242,.18);}
</style>
<?= $this->end() ?>

<?php if ($can_review && !$already_reviewed && $seller_id_rv): ?>
<!-- Completed Feedback Modal -->
<div id="sr_completed_md" class="modal fade lb-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div class="lb-modal-head">
          <div class="lb-modal-ico"><i class="fa-duotone fa-party-horn"></i></div>
          <div class="lb-modal-headtxt">
            <h5 class="lb-modal-title">Order completed 🎉</h5>
            <p class="lb-modal-sub">How was your experience with <?= $seller_name_rv ?>?</p>
          </div>
        </div>
        <button type="button" class="lb-modal-x" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <div class="sr-review-card p-3">
              <div class="d-flex align-items-center gap-3 mb-3">
                <?php if ($seller_icon_rv): ?>
                  <span class="avatar" style="width:44px;height:44px;"><img class="avatar-img" src="<?= $seller_icon_rv ?>" alt=""></span>
                <?php else: ?>
                  <span class="avatar" style="width:44px;height:44px;background:rgba(88,101,242,.2);border-radius:50%;display:grid;place-items:center;font-weight:900;color:#cfd5ff;font-size:1.1rem;"><?= strtoupper(substr($seller['username'] ?? 'S', 0, 1)) ?></span>
                <?php endif; ?>
                <div>
                  <div class="fw-bold">Rate <?= $seller_name_rv ?></div>
                  <div class="text-muted small">Helps other buyers find great sellers.</div>
                </div>
              </div>
              <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#sr_leave_review_md">
                <i class="fa-duotone fa-star me-2"></i> Leave a Review
              </button>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="sr-review-card p-3">
              <div class="fw-bold mb-1">Review us on Trustpilot</div>
              <div class="text-muted small mb-3">Tap a star to open Trustpilot in a new tab.</div>
              <div id="sr_tp_stars" class="d-flex gap-2 mb-3">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <button type="button" class="lb-star" data-index="<?= $i ?>">
                    <svg viewBox="0 0 24 24"><path d="M12 17.25L6.545 20.4l1.045-6.1L3 9.75l6.273-.9L12 3.75l2.727 5.1 6.273.9-4.59 4.55 1.045 6.1L12 17.25z"/></svg>
                  </button>
                <?php endfor; ?>
              </div>
              <a class="btn btn-white border" href="https://www.trustpilot.com/evaluate/lolboost.gg" target="_blank" rel="noopener">
                <i class="fa-duotone fa-arrow-up-right-from-square me-2"></i> Open Trustpilot
              </a>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer d-flex justify-content-between">
        <button type="button" id="sr_dismiss_btn" class="btn btn-link text-muted p-0" data-bs-dismiss="modal">I don't want to review now</button>
        <div class="small text-muted">You can review anytime from this page.</div>
      </div>
    </div>
  </div>
</div>

<!-- Leave Review Modal -->
<div id="sr_leave_review_md" class="modal fade lb-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
    <div class="modal-content">
      <div class="modal-header">
        <div class="lb-modal-head">
          <div class="lb-modal-ico"><i class="fa-duotone fa-star"></i></div>
          <div class="lb-modal-headtxt">
            <h5 class="lb-modal-title">Leave a Review</h5>
            <p class="lb-modal-sub"><?= $seller_name_rv ?></p>
          </div>
        </div>
        <button type="button" class="lb-modal-x" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="sr-review-card p-3 mb-3">
          <div class="fw-bold mb-1">How would you rate <?= $seller_name_rv ?>?</div>
          <div class="text-muted small mb-3">1 = poor, 5 = excellent</div>
          <div id="sr_review_stars" class="d-flex gap-2">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <button type="button" class="lb-star" data-index="<?= $i ?>">
                <svg viewBox="0 0 24 24"><path d="M12 17.25L6.545 20.4l1.045-6.1L3 9.75l6.273-.9L12 3.75l2.727 5.1 6.273.9-4.59 4.55 1.045 6.1L12 17.25z"/></svg>
              </button>
            <?php endfor; ?>
          </div>
          <input type="hidden" id="sr_rating_val" value="0">
        </div>
        <div class="sr-review-card p-3">
          <div class="fw-bold mb-2">Comment <span class="text-muted fw-normal" style="font-size:.82rem;">(Optional)</span></div>
          <textarea id="sr_comment_val" class="form-control" rows="4" placeholder="Share your experience..." style="resize:none;"></textarea>
          <div class="text-muted small mt-2 mb-2">Quick suggestions, tap one if you do not want to type everything manually.</div>
          <div id="sr_comment_suggestions" class="d-flex flex-wrap gap-2">
            <button type="button" class="sr-suggestion-pill" data-text="Fast delivery, great communication, and everything was exactly as described.">Fast delivery</button>
            <button type="button" class="sr-suggestion-pill" data-text="Very friendly seller, smooth transaction, and I would definitely buy again.">Friendly seller</button>
            <button type="button" class="sr-suggestion-pill" data-text="The account was exactly as described and the whole process was smooth and easy.">As described</button>
            <button type="button" class="sr-suggestion-pill" data-text="Good experience overall, quick support, and the purchase went without any issues.">Good overall</button>
          </div>
        </div>
      </div>
      <div class="modal-footer d-flex justify-content-between align-items-center">
        <p id="sr_review_err" class="text-danger small mb-0"></p>
        <button type="button" id="sr_review_submit" class="btn btn-primary">
          Submit Review <i class="fa-duotone fa-paper-plane ms-2"></i>
          <span id="sr_review_spin" class="spinner-border spinner-border-sm d-none ms-1" role="status"></span>
        </button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?= $this->start('scripts') ?>
<script>
(function(){
  const container = document.getElementById('itemChatMessages');
  const purchaseId = <?= (int)($purchase['id'] ?? 0) ?>;
  if (container && purchaseId) {
    function esc(str){ const d=document.createElement('div'); d.textContent=str||''; return d.innerHTML; }
    function render(messages){
      container.innerHTML='';
      (messages||[]).forEach(m=>{
        const wrap=document.createElement('div');
        const mine=(m.sender==='client');
        wrap.style.maxWidth='78%'; wrap.style.alignSelf=mine?'flex-end':'flex-start';
        wrap.innerHTML='<div style="font-size:12px;color:rgba(255,255,255,.45);margin-bottom:4px;">'+esc(m.sender_name||m.sender||'')+' • '+(m.time?new Date(m.time*1000).toLocaleString():'')+'</div>'+
          '<div style="padding:10px 12px;border-radius:12px;background:'+(mine?'rgba(99,102,241,.2)':'rgba(255,255,255,.06)')+';">'+(m.message_type==='image'?m.content:esc(m.content||''))+'</div>';
        container.appendChild(wrap);
      });
      container.scrollTop=container.scrollHeight;
    }
    function load(){ $.post(AJAX_URL,{action:'item_chat_load',purchase_id:purchaseId},function(resp){ try{if(typeof resp==='string')resp=JSON.parse(resp);if(resp&&resp.success)render(resp.messages||[]);}catch(e){} }); }
    load();

    window.lbOrderViewChatUpdate = function (data) {
      if (!data || data.order_id === ('itempurch_' + purchaseId)) {
        load();
      }
    };

    setInterval(function () {
      if (document.visibilityState !== 'visible') return;
      if (window.lbRealtimeConnected) return;
      load();
    }, 30000);

    setInterval(function () {
      if (document.visibilityState === 'visible' && window.lbRealtimeConnected) return;
      load();
    }, 60000);
  }
  const cd=document.getElementById('friendshipCountdown');
  if(cd&&cd.dataset.readyAt){
    function tick(){const ready=new Date(cd.dataset.readyAt.replace(' ','T')).getTime();const diff=ready-Date.now();if(isNaN(ready))return;if(diff<=0){cd.textContent='Gifting is now available.';return;}const d=Math.floor(diff/86400000),h=Math.floor((diff%86400000)/3600000),m=Math.floor((diff%3600000)/60000);cd.textContent=d+'d '+h+'h '+m+'m remaining';}
    tick(); setInterval(tick,60000);
  }

  <?php if ($can_review && !$already_reviewed && $seller_id_rv): ?>
  (function(){
    const SELLER_ID   = <?= $seller_id_rv ?>;
    const PURCHASE_ID = <?= $purchase_id_rv ?>;
    const key         = 'lb_sr_popup_' + PURCHASE_ID;
    const isDismissed  = ()=>{ try{return localStorage.getItem(key)==='1';}catch(e){return false;} };
    const markDismissed= ()=>{ try{localStorage.setItem(key,'1');}catch(e){} };

    document.addEventListener('DOMContentLoaded', function(){
      if(isDismissed()) return;
      const el=document.getElementById('sr_completed_md');
      if(!el||!window.bootstrap) return;
      const modal=bootstrap.Modal.getOrCreateInstance(el);
      const dismissBtn=document.getElementById('sr_dismiss_btn');
      if(dismissBtn) dismissBtn.addEventListener('click',markDismissed);
      el.querySelectorAll('[data-bs-target="#sr_leave_review_md"]').forEach(function(b){b.addEventListener('click',markDismissed);});
      setTimeout(function(){
        if(document.querySelector('.modal.show')) return;
        if(isDismissed()) return;
        modal.show();
      },700);
    });

    // Trustpilot stars
    const tpWrap=document.getElementById('sr_tp_stars');
    if(tpWrap){
      const tpStars=Array.from(tpWrap.querySelectorAll('.lb-star'));
      tpStars.forEach(function(s){
        s.addEventListener('mouseover',function(){const v=parseInt(this.dataset.index);tpStars.forEach(function(x){x.classList.toggle('is-on',parseInt(x.dataset.index)<=v);});});
        s.addEventListener('mouseout',function(){tpStars.forEach(function(x){x.classList.remove('is-on');});});
        s.addEventListener('click',function(){markDismissed();window.open('https://www.trustpilot.com/evaluate/lolboost.gg?stars='+this.dataset.index,'_blank');});
      });
    }

    // Review stars
    const rvWrap=document.getElementById('sr_review_stars');
    if(rvWrap){
      const rvStars=Array.from(rvWrap.querySelectorAll('.lb-star'));
      const hidden=document.getElementById('sr_rating_val');
      let selected=0;
      rvStars.forEach(function(s){
        s.addEventListener('mouseover',function(){const v=parseInt(this.dataset.index);rvStars.forEach(function(x){x.classList.toggle('is-on',parseInt(x.dataset.index)<=v);});});
        s.addEventListener('mouseout',function(){rvStars.forEach(function(x){x.classList.toggle('is-on',parseInt(x.dataset.index)<=selected);});});
        s.addEventListener('click',function(){selected=parseInt(this.dataset.index);if(hidden)hidden.value=selected;rvStars.forEach(function(x){x.classList.toggle('is-on',parseInt(x.dataset.index)<=selected);});});
      });
    }

    const commentInput=document.getElementById('sr_comment_val');
    const suggestionWrap=document.getElementById('sr_comment_suggestions');
    if(suggestionWrap && commentInput){
      suggestionWrap.querySelectorAll('.sr-suggestion-pill').forEach(function(btn){
        btn.addEventListener('click',function(){
          commentInput.value=this.dataset.text||'';
          suggestionWrap.querySelectorAll('.sr-suggestion-pill').forEach(function(x){x.classList.remove('is-active');});
          this.classList.add('is-active');
          commentInput.focus();
          if(errEl) errEl.textContent='';
        });
      });
      commentInput.addEventListener('input',function(){
        suggestionWrap.querySelectorAll('.sr-suggestion-pill').forEach(function(x){
          x.classList.toggle('is-active', (x.dataset.text||'') === commentInput.value.trim());
        });
      });
    }

    // Submit
    const submitBtn=document.getElementById('sr_review_submit');
    const submitSpin=document.getElementById('sr_review_spin');
    const errEl=document.getElementById('sr_review_err');
    if(submitBtn) submitBtn.addEventListener('click',function(){
      const rating=parseInt((document.getElementById('sr_rating_val')||{}).value||0);
      const comment=((document.getElementById('sr_comment_val')||{}).value||'').trim();
      if(errEl) errEl.textContent='';
      if(rating<1||rating>5){if(errEl)errEl.textContent='Please select a star rating.';return;}
      submitBtn.disabled=true;
      if(submitSpin) submitSpin.classList.remove('d-none');
      var fd=new FormData();
      fd.append('action','submit_seller_review');
      fd.append('seller_id',SELLER_ID);
      fd.append('purchase_id',PURCHASE_ID);
      fd.append('rating',rating);
      fd.append('comment',comment);
      fetch(typeof AJAX_URL!=='undefined'?AJAX_URL:'/ajax',{method:'POST',body:fd,credentials:'same-origin'})
      .then(function(r){return r.json();})
      .then(function(res){
        var t=res.sendToast||{};
        if(t.type==='success'||t.type==='warning'){
          markDismissed();
          const m=bootstrap.Modal.getInstance(document.getElementById('sr_leave_review_md'));
          if(m) m.hide();
          if(typeof create_toast==='function') create_toast(t.type,t.title||'Done',t.message||'Review submitted!');
        } else {
          if(errEl) errEl.textContent=t.message||'Something went wrong.';
          submitBtn.disabled=false;
          if(submitSpin) submitSpin.classList.add('d-none');
        }
      })
      .catch(function(){
        if(errEl) errEl.textContent='Could not submit. Try again.';
        submitBtn.disabled=false;
        if(submitSpin) submitSpin.classList.add('d-none');
      });
    });
  })();
  <?php endif; ?>
})();
</script>
<?= $this->end() ?>
