<?php $page_title = 'Seller Rules'; ?>
<?= $this->layout('seller/layouts/main', ['meta' => ['title' => 'Seller Rules | LoLBoost.gg']]) ?>
<?= $this->start('styles') ?>
<style>
/* ── Layout ── */
.sp-wrap { display: flex; gap: 24px; align-items: flex-start; padding-bottom: 3rem; }
.sp-sidebar {
    width: 220px; flex-shrink: 0;
    position: sticky; top: 80px;
}
.sp-content { flex: 1; min-width: 0; }

/* Sidebar nav */
.sp-nav { background: #25282a; border: 1px solid rgba(255,255,255,.07); border-radius: 16px; overflow: hidden; }
.sp-nav-header {
    padding: 14px 16px 10px;
    font-size: .65rem; font-weight: 900; letter-spacing: .1em; text-transform: uppercase;
    color: rgba(255,255,255,.3); border-bottom: 1px solid rgba(255,255,255,.06);
}
.sp-nav-link {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 16px; font-size: .82rem; font-weight: 700;
    color: rgba(255,255,255,.5); text-decoration: none;
    border-left: 3px solid transparent;
    transition: color .12s, background .12s, border-color .12s;
    cursor: pointer;
}
.sp-nav-link:hover { color: rgba(255,255,255,.85); background: rgba(255,255,255,.04); }
.sp-nav-link.active { color: #c4b5fd; background: rgba(109,92,255,.1); border-left-color: #c4b5fd; }
.sp-nav-link i { width: 14px; text-align: center; font-size: .8rem; flex-shrink: 0; }
.sp-nav-divider { height: 1px; background: rgba(255,255,255,.05); margin: 4px 0; }

/* Hero */
.sr-hero {
    border-radius: 18px;
    background: linear-gradient(135deg, rgba(109,92,255,.18), rgba(109,92,255,.05));
    border: 1px solid rgba(109,92,255,.28);
    padding: 24px 28px; margin-bottom: 20px;
    display: flex; align-items: center; gap: 16px;
}
.sr-hero-icon { width: 50px; height: 50px; border-radius: 14px; flex-shrink: 0; background: rgba(109,92,255,.25); border: 1px solid rgba(109,92,255,.4); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: #c4b5fd; }
.sr-hero-title { font-size: 1.3rem; font-weight: 950; color: #fff; margin: 0 0 3px; }
.sr-hero-sub   { font-size: .82rem; color: rgba(255,255,255,.45); margin: 0; }

/* Sections */
.sr-section { margin-bottom: 12px; scroll-margin-top: 90px; }
.sr-section-header {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 8px; padding: 0 2px;
}
.sr-section-icon { width: 30px; height: 30px; border-radius: 9px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: .82rem; }
.sr-section-icon--purple { background: rgba(109,92,255,.2); border: 1px solid rgba(109,92,255,.3); color: #c4b5fd; }
.sr-section-icon--green  { background: rgba(74,222,128,.12); border: 1px solid rgba(74,222,128,.25); color: #4ade80; }
.sr-section-icon--yellow { background: rgba(251,191,36,.12); border: 1px solid rgba(251,191,36,.25); color: #fbbf24; }
.sr-section-title { font-size: .92rem; font-weight: 900; color: rgba(255,255,255,.88); }

/* Rule rows */
.sr-rule { display: flex; align-items: flex-start; gap: 13px; padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,.07); background: #25282a; margin-bottom: 7px; transition: border-color .15s, background .15s; }
.sr-rule:hover { border-color: rgba(109,92,255,.22); background: rgba(109,92,255,.04); }
.sr-rule-num { width: 24px; height: 24px; border-radius: 50%; flex-shrink: 0; font-size: .72rem; font-weight: 900; display: flex; align-items: center; justify-content: center; margin-top: 1px; }
.sr-rule-num--purple { background: rgba(109,92,255,.18); border: 1px solid rgba(109,92,255,.3); color: #c4b5fd; }
.sr-rule-num--green  { background: rgba(74,222,128,.12);  border: 1px solid rgba(74,222,128,.3);  color: #4ade80; }
.sr-rule-num--yellow { background: rgba(251,191,36,.12);  border: 1px solid rgba(251,191,36,.3);  color: #fbbf24; }
.sr-rule-body { flex: 1; min-width: 0; }
.sr-rule-title { font-size: .87rem; font-weight: 800; color: rgba(255,255,255,.92); margin-bottom: 3px; display: flex; align-items: center; gap: 7px; flex-wrap: wrap; line-height: 1.4; }
.sr-rule-desc  { font-size: .8rem; color: rgba(255,255,255,.42); line-height: 1.5; }
.sr-badge { display: inline-flex; align-items: center; gap: .22rem; padding: 2px 7px; border-radius: 99px; font-size: .66rem; font-weight: 800; white-space: nowrap; }
.sr-badge--required { background: rgba(251,191,36,.14); border: 1px solid rgba(251,191,36,.28); color: #fbbf24; }
.sr-badge--never    { background: rgba(251,113,133,.12); border: 1px solid rgba(251,113,133,.28); color: #fb7185; }
.sr-badge--tip      { background: rgba(74,222,128,.10);  border: 1px solid rgba(74,222,128,.24);  color: #4ade80; }
.sr-warning { display: flex; align-items: flex-start; gap: 11px; padding: 14px 16px; border-radius: 12px; background: rgba(109,92,255,.08); border: 1px solid rgba(109,92,255,.18); margin-top: 6px; }
.sr-warning i { color: #c4b5fd; font-size: .85rem; margin-top: 2px; flex-shrink: 0; }
.sr-warning p { font-size: .8rem; color: rgba(255,255,255,.58); line-height: 1.6; margin: 0; }
.sr-warning strong { color: rgba(255,255,255,.82); }
</style>
<?= $this->end() ?>

<div class="sp-wrap">
    <!-- Sidebar -->
    <div class="sp-sidebar">
        <nav class="sp-nav">
            <div class="sp-nav-header">Contents</div>
            <a class="sp-nav-link active" href="#general"  onclick="spNav(this,'general')"><i class="fa-solid fa-list-check"></i> General Rules</a>
            <a class="sp-nav-link"        href="#delivery" onclick="spNav(this,'delivery')"><i class="fa-solid fa-bolt"></i> Delivery Rules</a>
            <a class="sp-nav-link"        href="#conduct"  onclick="spNav(this,'conduct')"><i class="fa-solid fa-comments"></i> Conduct</a>
            <div class="sp-nav-divider"></div>
            <a class="sp-nav-link" href="<?= BASE_URL ?>/seller-area/fines" style="color:rgba(251,113,133,.8);">
                <i class="fa-solid fa-triangle-exclamation"></i> View Fines
            </a>
        </nav>
    </div>

    <!-- Content -->
    <div class="sp-content">
        <div class="sr-hero">
            <div class="sr-hero-icon"><i class="fa-duotone fa-book-open"></i></div>
            <div>
                <h2 class="sr-hero-title">Seller Rules</h2>
                <p class="sr-hero-sub">Read and follow these rules to keep your account in good standing.</p>
            </div>
        </div>

        <div class="sr-section" id="general">
            <div class="sr-section-header">
                <span class="sr-section-icon sr-section-icon--purple"><i class="fa-solid fa-list-check"></i></span>
                <span class="sr-section-title">General Rules</span>
            </div>
            <div class="sr-rule"><span class="sr-rule-num sr-rule-num--purple">1</span><div class="sr-rule-body"><div class="sr-rule-title">Accurate listings only.</div><div class="sr-rule-desc">All account info (rank, server, level, skins, credentials) must be truthful and up to date. Misleading listings will result in immediate removal and a fine.</div></div></div>
            <div class="sr-rule"><span class="sr-rule-num sr-rule-num--purple">2</span><div class="sr-rule-body"><div class="sr-rule-title">No duplicate listings.</div><div class="sr-rule-desc">You may not list the same account more than once. Duplicates will be removed without notice.</div></div></div>
            <div class="sr-rule"><span class="sr-rule-num sr-rule-num--purple">3</span><div class="sr-rule-body"><div class="sr-rule-title">Delist sold accounts within 24h. <span class="sr-badge sr-badge--required"><i class="fa-solid fa-check"></i> Required</span></div><div class="sr-rule-desc">If sold outside the platform, mark it sold or remove it within 24 hours to avoid a fine.</div></div></div>
            <div class="sr-rule"><span class="sr-rule-num sr-rule-num--purple">4</span><div class="sr-rule-body"><div class="sr-rule-title">Credentials must be valid.</div><div class="sr-rule-desc">Login, password, email and email password must be correct at time of listing and delivery. Incorrect credentials = automatic fine.</div></div></div>
            <div class="sr-rule"><span class="sr-rule-num sr-rule-num--purple">5</span><div class="sr-rule-body"><div class="sr-rule-title">Never list banned accounts. <span class="sr-badge sr-badge--never"><i class="fa-solid fa-ban"></i> Never</span></div><div class="sr-rule-desc">Listing permanently banned accounts is strictly forbidden. Violators will be fined and removed.</div></div></div>
            <div class="sr-rule"><span class="sr-rule-num sr-rule-num--purple">6</span><div class="sr-rule-body"><div class="sr-rule-title">No chargebacks or disputes. <span class="sr-badge sr-badge--never"><i class="fa-solid fa-ban"></i> Never</span></div><div class="sr-rule-desc">Attempting to reverse payments or file disputes after a completed sale results in a permanent ban.</div></div></div>
        </div>

        <div class="sr-section" id="delivery">
            <div class="sr-section-header">
                <span class="sr-section-icon sr-section-icon--green"><i class="fa-solid fa-bolt"></i></span>
                <span class="sr-section-title">Delivery Rules</span>
            </div>
            <div class="sr-rule"><span class="sr-rule-num sr-rule-num--green">1</span><div class="sr-rule-body"><div class="sr-rule-title">Save credentials before listing goes live.</div><div class="sr-rule-desc">Instant Delivery sends credentials automatically — no action needed from you after a sale.</div></div></div>
            <div class="sr-rule"><span class="sr-rule-num sr-rule-num--green">2</span><div class="sr-rule-body"><div class="sr-rule-title">Respond to buyer issues within 24h. <span class="sr-badge sr-badge--required"><i class="fa-solid fa-check"></i> Required</span></div><div class="sr-rule-desc">Ignoring buyers after delivery will result in a warning or fine.</div></div></div>
            <div class="sr-rule"><span class="sr-rule-num sr-rule-num--green">3</span><div class="sr-rule-body"><div class="sr-rule-title">Do not access the account after sale. <span class="sr-badge sr-badge--never"><i class="fa-solid fa-ban"></i> Never</span></div><div class="sr-rule-desc">Any login or activity on a sold account = immediate ban and balance forfeiture.</div></div></div>
            <div class="sr-rule"><span class="sr-rule-num sr-rule-num--green">4</span><div class="sr-rule-body"><div class="sr-rule-title">Email access must be transferable. <span class="sr-badge sr-badge--tip"><i class="fa-solid fa-lightbulb"></i> Best Practice</span></div><div class="sr-rule-desc">If listed as "Verified", the buyer must be able to change the email. Provide correct email credentials.</div></div></div>
        </div>

        <div class="sr-section" id="conduct">
            <div class="sr-section-header">
                <span class="sr-section-icon sr-section-icon--yellow"><i class="fa-solid fa-comments"></i></span>
                <span class="sr-section-title">Communication & Conduct</span>
            </div>
            <div class="sr-rule"><span class="sr-rule-num sr-rule-num--yellow">1</span><div class="sr-rule-body"><div class="sr-rule-title">Be respectful at all times.</div><div class="sr-rule-desc">All communication with buyers and staff must remain professional. Harassment results in immediate suspension.</div></div></div>
            <div class="sr-rule"><span class="sr-rule-num sr-rule-num--yellow">2</span><div class="sr-rule-body"><div class="sr-rule-title">No off-platform deals. <span class="sr-badge sr-badge--never"><i class="fa-solid fa-ban"></i> Never</span></div><div class="sr-rule-desc">Directing buyers to pay outside the platform to avoid fees = permanent ban.</div></div></div>
            <div class="sr-rule"><span class="sr-rule-num sr-rule-num--yellow">3</span><div class="sr-rule-body"><div class="sr-rule-title">Report issues to support immediately.</div><div class="sr-rule-desc">Contact support via Discord or admin chat if you encounter any issue with a listing or delivery.</div></div></div>
            <div class="sr-warning"><i class="fa-solid fa-circle-info"></i><p>Violations may result in <strong>fines, suspension, or permanent ban</strong>. Your balance may be withheld until disputes are resolved. Questions? Contact an admin in the seller Discord.</p></div>
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
// Highlight nav on scroll
window.addEventListener('scroll', function() {
    var sections = ['general','delivery','conduct'];
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
