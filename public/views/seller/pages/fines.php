<?php $page_title = 'Seller Fines'; ?>
<?= $this->layout('seller/layouts/main', ['meta' => ['title' => 'Seller Fines | LoLBoost.gg']]) ?>
<?= $this->start('styles') ?>
<style>
/* ── Layout ── */
.sp-wrap { display: flex; gap: 24px; align-items: flex-start; padding-bottom: 3rem; }
.sp-sidebar { width: 220px; flex-shrink: 0; position: sticky; top: 80px; }
.sp-content  { flex: 1; min-width: 0; }
.sp-nav { background: #25282a; border: 1px solid rgba(255,255,255,.07); border-radius: 16px; overflow: hidden; }
.sp-nav-header { padding: 14px 16px 10px; font-size: .65rem; font-weight: 900; letter-spacing: .1em; text-transform: uppercase; color: rgba(255,255,255,.3); border-bottom: 1px solid rgba(255,255,255,.06); }
.sp-nav-link { display: flex; align-items: center; gap: 10px; padding: 10px 16px; font-size: .82rem; font-weight: 700; color: rgba(255,255,255,.5); text-decoration: none; border-left: 3px solid transparent; transition: color .12s, background .12s, border-color .12s; cursor: pointer; }
.sp-nav-link:hover { color: rgba(255,255,255,.85); background: rgba(255,255,255,.04); }
.sp-nav-link.active { color: #fb7185; background: rgba(251,113,133,.08); border-left-color: #fb7185; }
.sp-nav-link i { width: 14px; text-align: center; font-size: .8rem; flex-shrink: 0; }
.sp-nav-divider { height: 1px; background: rgba(255,255,255,.05); margin: 4px 0; }

/* Hero */
.sf-hero { border-radius: 18px; background: linear-gradient(135deg, rgba(251,113,133,.15), rgba(251,113,133,.04)); border: 1px solid rgba(251,113,133,.26); padding: 24px 28px; margin-bottom: 20px; display: flex; align-items: center; gap: 16px; }
.sf-hero-icon { width: 50px; height: 50px; border-radius: 14px; flex-shrink: 0; background: rgba(251,113,133,.2); border: 1px solid rgba(251,113,133,.35); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: #fb7185; }
.sf-hero-title { font-size: 1.3rem; font-weight: 950; color: #fff; margin: 0 0 3px; }
.sf-hero-sub   { font-size: .82rem; color: rgba(255,255,255,.45); margin: 0; }

/* Section headers */
.sf-section { margin-bottom: 12px; scroll-margin-top: 90px; }
.sf-section-header { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; padding: 0 2px; }
.sf-section-icon { width: 30px; height: 30px; border-radius: 9px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: .82rem; }
.sf-section-icon--red    { background: rgba(251,113,133,.12); border: 1px solid rgba(251,113,133,.25); color: #fb7185; }
.sf-section-icon--yellow { background: rgba(251,191,36,.12);  border: 1px solid rgba(251,191,36,.25);  color: #fbbf24; }
.sf-section-title { font-size: .92rem; font-weight: 900; color: rgba(255,255,255,.88); }

/* Fine cards */
.sf-fine-card { display: flex; align-items: center; border-radius: 12px; border: 1px solid rgba(255,255,255,.07); background: #25282a; margin-bottom: 7px; overflow: hidden; transition: border-color .15s; }
.sf-fine-card:hover { border-color: rgba(255,255,255,.12); }
.sf-fine-bar { width: 4px; align-self: stretch; flex-shrink: 0; }
.sf-fine-bar--low      { background: #fbbf24; }
.sf-fine-bar--medium   { background: #fb923c; }
.sf-fine-bar--high     { background: #fb7185; }
.sf-fine-bar--critical { background: #dc2626; }
.sf-fine-inner { display: flex; align-items: center; gap: 14px; padding: 14px 16px; flex: 1; min-width: 0; flex-wrap: wrap; }
.sf-fine-left  { flex: 1; min-width: 180px; }
.sf-fine-title { font-size: .88rem; font-weight: 800; color: rgba(255,255,255,.92); margin-bottom: 2px; }
.sf-fine-desc  { font-size: .77rem; color: rgba(255,255,255,.38); line-height: 1.4; }
.sf-fine-meta  { display: flex; align-items: center; gap: 8px; flex-shrink: 0; flex-wrap: wrap; }
.sf-sev { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 99px; font-size: .7rem; font-weight: 800; white-space: nowrap; }
.sf-sev--low      { background: rgba(251,191,36,.12);  border: 1px solid rgba(251,191,36,.24);  color: #fbbf24; }
.sf-sev--medium   { background: rgba(251,146,60,.12);  border: 1px solid rgba(251,146,60,.24);  color: #fb923c; }
.sf-sev--high     { background: rgba(251,113,133,.12); border: 1px solid rgba(251,113,133,.24); color: #fb7185; }
.sf-sev--critical { background: rgba(220,38,38,.14);   border: 1px solid rgba(220,38,38,.28);   color: #f87171; }
.sf-dot { width: 6px; height: 6px; border-radius: 50%; }
.sf-dot--low      { background: #fbbf24; }
.sf-dot--medium   { background: #fb923c; }
.sf-dot--high     { background: #fb7185; }
.sf-dot--critical { background: #f87171; }
.sf-penalty { display: inline-flex; align-items: center; padding: 4px 11px; border-radius: 99px; font-size: .76rem; font-weight: 900; white-space: nowrap; min-width: 110px; justify-content: center; }
.sf-penalty--low      { background: rgba(251,191,36,.1);  border: 1px solid rgba(251,191,36,.24);  color: #fbbf24; }
.sf-penalty--medium   { background: rgba(251,146,60,.1);  border: 1px solid rgba(251,146,60,.24);  color: #fb923c; }
.sf-penalty--high     { background: rgba(251,113,133,.1); border: 1px solid rgba(251,113,133,.24); color: #fb7185; }
.sf-penalty--critical { background: rgba(220,38,38,.12);  border: 1px solid rgba(220,38,38,.26);   color: #f87171; }

/* Balance note */
.sf-note { display: flex; align-items: flex-start; gap: 11px; padding: 14px 16px; border-radius: 12px; background: rgba(251,113,133,.07); border: 1px solid rgba(251,113,133,.16); margin-top: 4px; }
.sf-note i { color: #fb7185; flex-shrink: 0; margin-top: 2px; font-size: .85rem; }
.sf-note p { font-size: .8rem; color: rgba(255,255,255,.52); line-height: 1.6; margin: 0; }
.sf-note strong { color: rgba(255,255,255,.8); }

/* Offences */
.sf-offences-card { background: #25282a; border: 1px solid rgba(255,255,255,.07); border-radius: 12px; padding: 16px 18px; }
.sf-offences-intro { font-size: .82rem; color: rgba(255,255,255,.45); margin: 0 0 12px; line-height: 1.5; }
.sf-offence { display: flex; align-items: center; gap: 12px; padding: 11px 13px; border-radius: 10px; background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.06); margin-bottom: 7px; }
.sf-offence:last-child { margin-bottom: 0; }
.sf-offence-num { width: 24px; height: 24px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: .72rem; font-weight: 900; }
.sf-offence-num--1 { background: rgba(251,191,36,.14);  border: 1px solid rgba(251,191,36,.28);  color: #fbbf24; }
.sf-offence-num--2 { background: rgba(251,146,60,.14);  border: 1px solid rgba(251,146,60,.28);  color: #fb923c; }
.sf-offence-num--3 { background: rgba(251,113,133,.14); border: 1px solid rgba(251,113,133,.28); color: #fb7185; }
.sf-offence-text { font-size: .83rem; color: rgba(255,255,255,.65); }
.sf-offence-text strong { color: rgba(255,255,255,.9); font-weight: 800; }

/* Appeal */
.sf-appeal { border-radius: 14px; border: 1px solid rgba(109,92,255,.2); background: rgba(109,92,255,.07); padding: 18px 20px; display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
.sf-appeal-icon { width: 40px; height: 40px; border-radius: 11px; flex-shrink: 0; background: rgba(109,92,255,.2); border: 1px solid rgba(109,92,255,.3); display: flex; align-items: center; justify-content: center; font-size: .95rem; color: #c4b5fd; }
.sf-appeal-title { font-size: .88rem; font-weight: 900; color: rgba(255,255,255,.85); margin: 0 0 2px; }
.sf-appeal-desc  { font-size: .78rem; color: rgba(255,255,255,.4); }
.sf-appeal-btn { margin-left: auto; display: inline-flex; align-items: center; gap: .4rem; padding: 8px 18px; border-radius: 10px; background: rgba(109,92,255,.2); border: 1px solid rgba(109,92,255,.35); color: #c4b5fd; font-size: .82rem; font-weight: 800; text-decoration: none; transition: background .12s; flex-shrink: 0; }
.sf-appeal-btn:hover { background: rgba(109,92,255,.35); color: #fff; }
</style>
<?= $this->end() ?>

<div class="sp-wrap">
    <!-- Sidebar -->
    <div class="sp-sidebar">
        <nav class="sp-nav">
            <div class="sp-nav-header">Contents</div>
            <a class="sp-nav-link active" href="#schedule" onclick="spNav(this,'schedule')"><i class="fa-solid fa-scale-balanced"></i> Fine Schedule</a>
            <a class="sp-nav-link"        href="#repeat"   onclick="spNav(this,'repeat')"><i class="fa-solid fa-arrow-trend-up"></i> Repeat Offences</a>
            <a class="sp-nav-link"        href="#appeal"   onclick="spNav(this,'appeal')"><i class="fa-solid fa-comments"></i> Appeal</a>
            <div class="sp-nav-divider"></div>
            <a class="sp-nav-link" href="<?= BASE_URL ?>/seller-area/rules" style="color:rgba(196,181,253,.8);">
                <i class="fa-solid fa-book-open"></i> View Rules
            </a>
        </nav>
    </div>

    <!-- Content -->
    <div class="sp-content">
        <div class="sf-hero">
            <div class="sf-hero-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div>
                <h2 class="sf-hero-title">Seller Fines</h2>
                <p class="sf-hero-sub">Stay compliant to avoid penalties. All fines are reviewed by an admin before being applied.</p>
            </div>
        </div>

        <!-- Fine Schedule -->
        <div class="sf-section" id="schedule">
            <div class="sf-section-header">
                <span class="sf-section-icon sf-section-icon--red"><i class="fa-solid fa-scale-balanced"></i></span>
                <span class="sf-section-title">Fine Schedule</span>
            </div>
            <div class="sf-fine-card"><div class="sf-fine-bar sf-fine-bar--medium"></div><div class="sf-fine-inner"><div class="sf-fine-left"><div class="sf-fine-title">Incorrect credentials</div><div class="sf-fine-desc">Login, password or email is wrong at time of delivery</div></div><div class="sf-fine-meta"><span class="sf-sev sf-sev--medium"><span class="sf-dot sf-dot--medium"></span>Medium</span><span class="sf-penalty sf-penalty--medium">€10 – €25</span></div></div></div>
            <div class="sf-fine-card"><div class="sf-fine-bar sf-fine-bar--medium"></div><div class="sf-fine-inner"><div class="sf-fine-left"><div class="sf-fine-title">Misleading listing</div><div class="sf-fine-desc">Rank, server, skins or level does not match the description</div></div><div class="sf-fine-meta"><span class="sf-sev sf-sev--medium"><span class="sf-dot sf-dot--medium"></span>Medium</span><span class="sf-penalty sf-penalty--medium">€15 – €50 + removal</span></div></div></div>
            <div class="sf-fine-card"><div class="sf-fine-bar sf-fine-bar--low"></div><div class="sf-fine-inner"><div class="sf-fine-left"><div class="sf-fine-title">Late delist of sold account</div><div class="sf-fine-desc">Account sold externally but not removed within 24 hours</div></div><div class="sf-fine-meta"><span class="sf-sev sf-sev--low"><span class="sf-dot sf-dot--low"></span>Low</span><span class="sf-penalty sf-penalty--low">€5 – €15</span></div></div></div>
            <div class="sf-fine-card"><div class="sf-fine-bar sf-fine-bar--high"></div><div class="sf-fine-inner"><div class="sf-fine-left"><div class="sf-fine-title">Listing a banned account</div><div class="sf-fine-desc">Account is permanently banned at time of listing</div></div><div class="sf-fine-meta"><span class="sf-sev sf-sev--high"><span class="sf-dot sf-dot--high"></span>High</span><span class="sf-penalty sf-penalty--high">Full refund + €25</span></div></div></div>
            <div class="sf-fine-card"><div class="sf-fine-bar sf-fine-bar--critical"></div><div class="sf-fine-inner"><div class="sf-fine-left"><div class="sf-fine-title">Accessing account after sale</div><div class="sf-fine-desc">Any login or activity on a sold account</div></div><div class="sf-fine-meta"><span class="sf-sev sf-sev--critical"><span class="sf-dot sf-dot--critical"></span>Critical</span><span class="sf-penalty sf-penalty--critical">Balance + Ban</span></div></div></div>
            <div class="sf-fine-card"><div class="sf-fine-bar sf-fine-bar--critical"></div><div class="sf-fine-inner"><div class="sf-fine-left"><div class="sf-fine-title">Off-platform deal attempt</div><div class="sf-fine-desc">Directing a buyer to pay outside the platform</div></div><div class="sf-fine-meta"><span class="sf-sev sf-sev--critical"><span class="sf-dot sf-dot--critical"></span>Critical</span><span class="sf-penalty sf-penalty--critical">Permanent Ban</span></div></div></div>
            <div class="sf-fine-card"><div class="sf-fine-bar sf-fine-bar--critical"></div><div class="sf-fine-inner"><div class="sf-fine-left"><div class="sf-fine-title">Chargeback / payment dispute</div><div class="sf-fine-desc">Filing a dispute or reversing a completed payment</div></div><div class="sf-fine-meta"><span class="sf-sev sf-sev--critical"><span class="sf-dot sf-dot--critical"></span>Critical</span><span class="sf-penalty sf-penalty--critical">Ban + Legal action</span></div></div></div>
            <div class="sf-fine-card"><div class="sf-fine-bar sf-fine-bar--low"></div><div class="sf-fine-inner"><div class="sf-fine-left"><div class="sf-fine-title">Ignoring buyer messages</div><div class="sf-fine-desc">No response to a buyer issue within 48 hours</div></div><div class="sf-fine-meta"><span class="sf-sev sf-sev--low"><span class="sf-dot sf-dot--low"></span>Low</span><span class="sf-penalty sf-penalty--low">Warning → €10 repeat</span></div></div></div>
            <div class="sf-note"><i class="fa-solid fa-circle-info"></i><p>Fines are <strong>deducted directly from your seller balance</strong>. If your balance is insufficient, the difference is carried as a debt and deducted from future earnings before payout. <strong>You will be notified via Discord DM</strong> when a fine is issued.</p></div>
        </div>

        <!-- Repeat Offences -->
        <div class="sf-section" id="repeat">
            <div class="sf-section-header">
                <span class="sf-section-icon sf-section-icon--yellow"><i class="fa-solid fa-arrow-trend-up"></i></span>
                <span class="sf-section-title">Repeat Offences</span>
            </div>
            <div class="sf-offences-card">
                <p class="sf-offences-intro">Repeated violations within a 30-day window result in escalating penalties:</p>
                <div class="sf-offence"><span class="sf-offence-num sf-offence-num--1">1</span><span class="sf-offence-text"><strong>1st offence</strong> &mdash; Warning + standard fine</span></div>
                <div class="sf-offence"><span class="sf-offence-num sf-offence-num--2">2</span><span class="sf-offence-text"><strong>2nd offence</strong> &mdash; Double fine + 7-day listing suspension</span></div>
                <div class="sf-offence"><span class="sf-offence-num sf-offence-num--3">3</span><span class="sf-offence-text"><strong>3rd offence</strong> &mdash; Permanent ban + balance review</span></div>
            </div>
        </div>

        <!-- Appeal -->
        <div class="sf-section" id="appeal">
            <div class="sf-section-header">
                <span class="sf-section-icon sf-section-icon--red"><i class="fa-solid fa-comments"></i></span>
                <span class="sf-section-title">Appeal a Fine</span>
            </div>
            <div class="sf-appeal">
                <div class="sf-appeal-icon"><i class="fa-duotone fa-comments"></i></div>
                <div>
                    <p class="sf-appeal-title">Think a fine was applied incorrectly?</p>
                    <p class="sf-appeal-desc">Contact an admin in the seller Discord and provide your account ID and a brief explanation. Appeals are reviewed within 48 hours.</p>
                </div>
                <a href="https://discord.gg/CGNjgBa4eb" target="_blank" class="sf-appeal-btn"><i class="fa-brands fa-discord"></i> Contact Admin</a>
            </div>
        </div>
    </div>
</div>

<script>
function spNav(el, id) {
    document.querySelectorAll('.sp-nav-link').forEach(l => l.classList.remove('active'));
    el.classList.add('active');
    var target = document.getElementById(id);
    if (target) { target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    return false;
}
window.addEventListener('scroll', function() {
    var sections = ['schedule','repeat','appeal'];
    var current = sections[0];
    sections.forEach(function(id) {
        var el = document.getElementById(id);
        if (el && el.getBoundingClientRect().top <= 110) current = id;
    });
    document.querySelectorAll('.sp-nav-link[href*="#"]').forEach(function(l) {
        l.classList.toggle('active', l.getAttribute('href') === '#' + current);
    });
}, { passive: true });
</script>
