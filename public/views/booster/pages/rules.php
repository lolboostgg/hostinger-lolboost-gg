<?= $this->layout('booster/layouts/main', [
  'meta' => [
    'title' => 'Booster Rules & Fines - Booster Area | LoLBoost.gg',
  ],
  'contain' => false,
]) ?>

<style>
  :root{
    --rules-bg: #25282a;
    --rules-bg-2: #2a2d30;
    --rules-bg-3: #1e2022;
    --rules-border: #2f3235;
    --rules-border-soft: rgba(255,255,255,.075);
    --rules-text-soft: #c5c8cc;
    --rules-text-muted: #91989e;
    --rules-primary: #5c4ae3;
    --rules-danger: #ed4c78;
    --rules-warning: #f5ca99;
    --rules-info: #09a5be;
    --rules-green: #00c9a7;
  }

  .rules-page{
    width: min(100%, 1560px);
    max-width: 1560px;
    margin: 0 auto;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }

  .rules-hero{
    position: relative;
    overflow: hidden;
    border: 1px solid var(--rules-border);
    border-radius: 18px;
    background:
      radial-gradient(circle at 14% 0%, rgba(92,74,227,.22), transparent 34%),
      radial-gradient(circle at 90% 8%, rgba(9,165,190,.12), transparent 32%),
      linear-gradient(180deg, #25282a 0%, #202325 100%);
    box-shadow: 0 .375rem .75rem rgba(30,32,34,.2);
  }

  .rules-hero::after{
    content: '';
    position: absolute;
    inset: auto -90px -130px auto;
    width: 260px;
    height: 260px;
    border-radius: 50%;
    background: rgba(92,74,227,.14);
    pointer-events: none;
  }

  .rules-kicker{
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .35rem .65rem;
    border-radius: .5rem;
    background: rgba(92,74,227,.16);
    border: 1px solid rgba(92,74,227,.35);
    color: #fff;
    font-size: .82rem;
    font-weight: 700;
  }

  .rules-hero h2{
    font-size: clamp(1.75rem, 2.6vw, 2.65rem);
    line-height: 1.05;
    letter-spacing: -.04em;
    color: #fff;
  }

  .rules-search-box{
    position: relative;
    border: 1px solid var(--rules-border);
    background: #25282a;
    border-radius: 16px;
    padding: 1rem;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.03), 0 .375rem .75rem rgba(30,32,34,.18);
  }

  .rules-search-box label{ color: #fff; letter-spacing: -.01em; }

  .rules-search-box .input-group{
    overflow: hidden;
    border: 1px solid var(--rules-border);
    border-radius: .5rem;
    background: #1e2022;
  }

  .rules-search-box .input-group-text{
    border: 0;
    background: #2a2d30;
    color: #c5c8cc;
    min-width: 48px;
    justify-content: center;
  }

  .rules-search-box .form-control{
    min-height: 48px;
    border: 0;
    background: #1e2022;
    color: #fff;
    box-shadow: none;
  }

  .rules-search-box .form-control:focus{
    background: #1e2022;
    box-shadow: inset 0 0 0 1px var(--rules-primary);
  }

  .rules-search-box .form-control::placeholder{ color: #91989e; }

  #rulesClearBtn{
    min-width: 78px;
    border: 0;
    border-left: 1px solid var(--rules-border);
    background: #2a2d30;
    color: #c5c8cc;
    font-weight: 800;
  }

  #rulesClearBtn:hover,
  #rulesClearBtn:focus{ background: var(--rules-danger); color: #fff; box-shadow: none; }

  .rules-stat{
    border: 1px solid var(--rules-border);
    background: #202325;
    border-radius: .75rem;
    padding: .9rem 1rem;
    height: 100%;
  }

  .rules-alert-strip{
    border: 1px solid rgba(237,76,120,.45);
    background: rgba(237,76,120,.12);
    border-radius: 16px;
    color: #fff;
    box-shadow: 0 .375rem .75rem rgba(30,32,34,.18);
  }

  .rules-nav{
    position: sticky;
    top: 0;
    z-index: 5;
    margin: 1.2rem 0;
    padding: .6rem;
    border: 1px solid var(--rules-border);
    border-radius: 16px;
    background: rgba(37,40,42,.96);
    backdrop-filter: blur(14px);
    box-shadow: 0 .375rem .75rem rgba(30,32,34,.2);
  }

  .rules-nav a{
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .62rem .9rem;
    border-radius: .5rem;
    color: rgba(255,255,255,.7);
    text-decoration: none;
    border: 1px solid transparent;
    background: transparent;
    font-weight: 700;
    font-size: .88rem;
    white-space: nowrap;
    transition: color .15s ease, background .15s ease, border-color .15s ease;
  }

  .rules-nav a:hover,
  .rules-nav a.active{
    color: #fff;
    background: rgba(92,74,227,.16);
    border-color: rgba(92,74,227,.4);
  }

  .rules-section{ scroll-margin-top: 92px; margin-bottom: 1rem; }

  .section-head{
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 1rem;
    margin: 1.5rem 0 .8rem;
  }
  .section-head h3{ margin: 0; font-size: 1.25rem; letter-spacing: -.02em; color: #fff; }
  .section-head p{ margin: .25rem 0 0; color: var(--rules-text-muted); }

  .rule-card,
  .fine-card{
    border: 1px solid var(--rules-border);
    background: #25282a;
    border-radius: .75rem;
    box-shadow: 0 .375rem .75rem rgba(30,32,34,.2);
  }

  .rule-card .card-body,
  .fine-card .card-body{ padding: 1.1rem; }

  .rule-row{
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    padding: .78rem 0;
    border-top: 1px solid var(--rules-border);
  }
  .rule-row:first-child{ border-top: 0; padding-top: 0; }
  .rule-row:last-child{ padding-bottom: 0; }

  .rule-icon{
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: .5rem;
    background: #2a2d30;
    border: 1px solid var(--rules-border);
    flex: 0 0 auto;
  }

  .rule-row-title{ font-weight: 800; margin-bottom: .15rem; color: #fff; }
  .rule-row-text,
  .fine-desc,
  .rules-muted{ color: var(--rules-text-muted); }

  .tag{
    display: inline-flex;
    align-items: center;
    padding: .23rem .5rem;
    border-radius: .3125rem;
    font-size: .7rem;
    font-weight: 900;
    letter-spacing: .035em;
    text-transform: uppercase;
  }
  .tag-must{ background: rgba(0,201,167,.12); color: var(--rules-green); }
  .tag-no{ background: rgba(237,76,120,.12); color: var(--rules-danger); }
  .tag-info{ background: rgba(9,165,190,.12); color: var(--rules-info); }

  .fine-category{
    border: 1px solid var(--rules-border);
    border-radius: .75rem;
    background: #25282a;
    overflow: hidden;
    margin-bottom: 1rem;
    box-shadow: 0 .375rem .75rem rgba(30,32,34,.2);
  }

  .fine-category-top{
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .8rem;
    padding: 1rem 1.1rem;
    background: #2a2d30;
    border-bottom: 1px solid var(--rules-border);
  }

  .fine-list{ display: grid; gap: .6rem; padding: .8rem; }

  .fine-item{
    display: grid;
    grid-template-columns: minmax(260px, 1fr) auto minmax(320px, 1.35fr);
    gap: .9rem;
    align-items: center;
    padding: .85rem;
    border-radius: .5rem;
    background: #202325;
    border: 1px solid var(--rules-border);
  }

  .fine-title{ font-weight: 800; color: #fff; }

  .fine-pill{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 68px;
    padding: .32rem .65rem;
    border-radius: 999px;
    font-weight: 900;
    font-size: .8rem;
  }
  .fine-high{ background: rgba(237,76,120,.16); color: var(--rules-danger); border: 1px solid rgba(237,76,120,.35); }
  .fine-mid{ background: rgba(245,202,153,.14); color: var(--rules-warning); border: 1px solid rgba(245,202,153,.3); }
  .fine-low{ background: rgba(9,165,190,.14); color: var(--rules-info); border: 1px solid rgba(9,165,190,.3); }
  .fine-small{ background: rgba(255,255,255,.075); color: #fff; border: 1px solid var(--rules-border); }

  .rules-footer-note{
    border: 1px solid var(--rules-border);
    background: #25282a;
    border-radius: .75rem;
  }

  .d-none-by-search{ display: none !important; }

  @media (max-width: 991.98px){
    .rules-page{ padding-left: 1rem; padding-right: 1rem; }
    .rules-nav{ top: 0; overflow-x: auto; flex-wrap: nowrap !important; }
    .rules-nav::-webkit-scrollbar{ display: none; }
    .fine-item{ grid-template-columns: 1fr auto; }
    .fine-desc{ grid-column: 1 / -1; }
  }

  @media (max-width: 575.98px){
    .rules-page{ padding-left: .75rem; padding-right: .75rem; }
    .rules-hero{ border-radius: .75rem; }
    .rules-hero .p-4{ padding: 1rem !important; }
    .fine-category-top{ align-items: flex-start; flex-direction: column; }
    .fine-item{ grid-template-columns: 1fr; }
    .fine-pill{ width: max-content; }
  }
</style>

<div class="rules-page py-4">
  <section class="rules-hero mb-3">
    <div class="p-4 p-xl-5 position-relative">
      <div class="row g-4 align-items-center">
        <div class="col-12 col-lg-7">
          <div class="rules-kicker mb-3">🛡️ Booster Area Guide</div>
          <h2 class="fw-bold mb-3">Rules &amp; Fines made simple</h2>
          <p class="rules-muted fs-6 mb-4">Follow the order details, keep all communication in the website chat, and ask admins early if something is unclear.</p>
          <div class="row g-2">
            <div class="col-6 col-md-3"><div class="rules-stat"><div class="fw-bold">30 min</div><div class="small rules-muted">Start solo orders</div></div></div>
            <div class="col-6 col-md-3"><div class="rules-stat"><div class="fw-bold">70%</div><div class="small rules-muted">Target winrate</div></div></div>
            <div class="col-6 col-md-3"><div class="rules-stat"><div class="fw-bold">VPN</div><div class="small rules-muted">When required</div></div></div>
          </div>
        </div>

        <div class="col-12 col-lg-5">
          <div class="rules-search-box">
            <label class="form-label fw-bold mb-2" for="rulesSearch">Search</label>
            <div class="input-group">
              <span class="input-group-text">🔎</span>
              <input id="rulesSearch" type="search" class="form-control" placeholder="VPN, Duo, Drop Token, chat ban..." autocomplete="off">
              <button class="btn btn-outline-secondary" type="button" id="rulesClearBtn">Clear</button>
            </div>
            <div class="small rules-muted mt-2">Type a keyword to filter rules and fines instantly.</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="rules-alert-strip p-3 p-md-4 mb-3" data-search-card>
    <div class="d-flex gap-3 align-items-start">
      <div class="fs-3 lh-1">⚠️</div>
      <div>
        <div class="fw-bold fs-6">Never share customer information outside the Website Order Chat.</div>
        <div class="small">This includes Discord DMs, PayPal messages, social media, or any other external platform.</div>
      </div>
    </div>
  </div>

  <nav class="rules-nav d-flex flex-wrap gap-2" aria-label="Rules navigation">
    <a href="#quick">⚡ Quick Rules</a>
    <a href="#orders">📦 Orders</a>
    <a href="#boosting">🎮 Boosting</a>
    <a href="#behavior">💬 Behavior</a>
    <a href="#account">🛡️ Account</a>
    <a href="#pay">💸 Pay</a>
    <a href="#fines">📌 Fines</a>
  </nav>

  <section id="quick" class="rules-section" data-search-card>
    <div class="section-head">
      <div>
        <h3>Quick Rules</h3>
        <p>The most important points before taking an order.</p>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-12 col-md-6 col-xl-3">
        <div class="rule-card h-100"><div class="card-body"><div class="rule-icon mb-3">💬</div><h5 class="fw-bold">Use order chat</h5><p class="rules-muted mb-0">All customer communication must stay inside the Website Order Chat.</p></div></div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="rule-card h-100"><div class="card-body"><div class="rule-icon mb-3">⚡</div><h5 class="fw-bold">Start fast</h5><p class="rules-muted mb-0">Start solo orders within 30 minutes if credentials are provided.</p></div></div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="rule-card h-100"><div class="card-body"><div class="rule-icon mb-3">📈</div><h5 class="fw-bold">Keep quality high</h5><p class="rules-muted mb-0">Aim for 70% winrate and follow all order details exactly.</p></div></div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="rule-card h-100"><div class="card-body"><div class="rule-icon mb-3">🎫</div><h5 class="fw-bold">Ask early</h5><p class="rules-muted mb-0">Open a Discord ticket early if there is any issue or unclear situation.</p></div></div>
      </div>
    </div>
  </section>

  <section id="orders" class="rules-section" data-search-card>
    <div class="section-head"><div><h3>Order Management</h3><p>Rules for Solo and DuoQ orders.</p></div></div>
    <div class="row g-3">
      <div class="col-12 col-lg-6">
        <div class="rule-card h-100"><div class="card-body">
          <h5 class="fw-bold mb-3">Solo orders</h5>
          <div class="rule-row"><span class="tag tag-must">Must</span><div><div class="rule-row-title">Start within 30 minutes</div><div class="rule-row-text">Start after claiming if the login details are provided.</div></div></div>
          <div class="rule-row"><span class="tag tag-must">Must</span><div><div class="rule-row-title">Message the customer</div><div class="rule-row-text">Greet the customer in Order Chat, check order details, and then begin.</div></div></div>
          <div class="rule-row"><span class="tag tag-must">Must</span><div><div class="rule-row-title">Stay active</div><div class="rule-row-text">Play 7-10 games per day. Less can count as inactivity.</div></div></div>
          <div class="rule-row"><span class="tag tag-must">Must</span><div><div class="rule-row-title">Continue paused orders</div><div class="rule-row-text">If an order is paused, continue within 14 hours. Longer delays can be penalized.</div></div></div>
        </div></div>
      </div>

      <div class="col-12 col-lg-6">
        <div class="rule-card h-100"><div class="card-body">
          <h5 class="fw-bold mb-3">DuoQ orders</h5>
          <div class="rule-row"><span class="tag tag-must">Must</span><div><div class="rule-row-title">Schedule in the dashboard</div><div class="rule-row-text">Use the Order Chat dashboard. Do not schedule through League client or Discord DMs.</div></div></div>
          <div class="rule-row"><span class="tag tag-no">Not allowed</span><div><div class="rule-row-title">Missing agreed playtimes</div><div class="rule-row-text">If you cannot play, open a Boosting Ticket early.</div></div></div>
          <div class="rule-row"><span class="tag tag-must">Must</span><div><div class="rule-row-title">Use correct DuoQ account</div><div class="rule-row-text">The account must be within 2 divisions of the customer rank.</div></div></div>
          <div class="rule-row"><span class="tag tag-no">Not allowed</span><div><div class="rule-row-title">Using customer account for DuoQ</div><div class="rule-row-text">Only allowed if staff explicitly approves it.</div></div></div>
        </div></div>
      </div>
    </div>
  </section>

  <section id="boosting" class="rules-section" data-search-card>
    <div class="section-head"><div><h3>Boosting Rules</h3><p>Gameplay, order details, winrate, and compensation rules.</p></div></div>
    <div class="rule-card"><div class="card-body">
      <div class="row g-3">
        <div class="col-12 col-lg-6">
          <div class="rule-row"><span class="tag tag-must">Must</span><div><div class="rule-row-title">Follow order details</div><div class="rule-row-text">Role, champions, summoner spells, streaming, offline mode, Solo Boost, Hidden Duo, and all order notes.</div></div></div>
          <div class="rule-row"><span class="tag tag-must">Must</span><div><div class="rule-row-title">Keep 70% winrate</div><div class="rule-row-text">Target at least 70% winrate during the boost.</div></div></div>
          <div class="rule-row"><span class="tag tag-no">Not allowed</span><div><div class="rule-row-title">Ignoring order specifics</div><div class="rule-row-text">Do not ask the customer to ignore details or switch role/champions.</div></div></div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="rule-row"><span class="tag tag-no">Not allowed</span><div><div class="rule-row-title">Using in-game chat</div><div class="rule-row-text">Only essential shot-calling is allowed.</div></div></div>
          <div class="rule-row"><span class="tag tag-info">Info</span><div><div class="rule-row-title">Win Boost losses</div><div class="rule-row-text">For Win Boosts, each loss requires +1 additional win.</div></div></div>
          <div class="rule-row"><span class="tag tag-info">Info</span><div><div class="rule-row-title">Placements</div><div class="rule-row-text">5 placements require 70% winrate. Fewer matches require replacing losses with equivalent wins.</div></div></div>
        </div>
      </div>
    </div></div>
  </section>

  <section id="behavior" class="rules-section" data-search-card>
    <div class="section-head"><div><h3>Behavior</h3><p>How to communicate with customers, teammates, and staff.</p></div></div>
    <div class="rule-card"><div class="card-body">
      <div class="rule-row"><span class="tag tag-no">Not allowed</span><div><div class="rule-row-title">Toxic behavior</div><div class="rule-row-text">No flaming, griefing, intentional feeding, begging for tips, or rude behavior.</div></div></div>
      <div class="rule-row"><span class="tag tag-no">Not allowed</span><div><div class="rule-row-title">Private boosting or coaching</div><div class="rule-row-text">Do not add customers on any platform or accept private work.</div></div></div>
      <div class="rule-row"><span class="tag tag-must">Must</span><div><div class="rule-row-title">Report private offers</div><div class="rule-row-text">If a customer asks for private boosting/coaching, open a Boosting Ticket immediately.</div></div></div>
      <div class="rule-row"><span class="tag tag-no">Not allowed</span><div><div class="rule-row-title">Messaging admins privately about work</div><div class="rule-row-text">Use Discord Tickets for work-related issues.</div></div></div>
    </div></div>
  </section>

  <section id="account" class="rules-section" data-search-card>
    <div class="section-head"><div><h3>Account &amp; Performance</h3><p>Account safety, screenshots, performance, and order drops.</p></div></div>
    <div class="row g-3">
      <div class="col-12 col-lg-6"><div class="rule-card h-100"><div class="card-body">
        <div class="rule-row"><span class="tag tag-must">Must</span><div><div class="rule-row-title">Finish and notify</div><div class="rule-row-text">Finish the boost, notify the customer, and include the Trustpilot link for feedback.</div></div></div>
        <div class="rule-row"><span class="tag tag-must">Must</span><div><div class="rule-row-title">Send screenshots</div><div class="rule-row-text">Provide proper proof for Rank Boost, Win Boost, and Placement orders.</div></div></div>
        <div class="rule-row"><span class="tag tag-must">Must</span><div><div class="rule-row-title">Use a VPN when required</div><div class="rule-row-text">Use a working VPN and keep account activity safe.</div></div></div>
      </div></div></div>
      <div class="col-12 col-lg-6"><div class="rule-card h-100"><div class="card-body">
        <div class="rule-row"><span class="tag tag-info">Info</span><div><div class="rule-row-title">Dropping order penalties</div><div class="rule-row-text">1 win / 0 losses: paid in full. 1 win / 1 loss: €20 penalty. 1 win / 2 losses: €20 penalty.</div></div></div>
        <div class="rule-row"><span class="tag tag-info">Info</span><div><div class="rule-row-title">Bad score losses</div><div class="rule-row-text">Losses with a bad score require compensation games. ACE or good score losses do not.</div></div></div>
      </div></div></div>
    </div>
  </section>

  <section id="pay" class="rules-section" data-search-card>
    <div class="section-head"><div><h3>Compensation &amp; Pay</h3><p>When extra wins or compensation are required.</p></div></div>
    <div class="rule-card"><div class="card-body">
      <div class="rule-row"><span class="tag tag-must">Must</span><div><div class="rule-row-title">Win Boost compensation</div><div class="rule-row-text">For every loss in a Win Boost, add one extra win.</div></div></div>
      <div class="rule-row"><span class="tag tag-info">Info</span><div><div class="rule-row-title">Promotion handling</div><div class="rule-row-text">Bought wins must be fulfilled normally up to Diamond 4. At Diamond 4+, one win is deducted after a promotion if more than 1 win is pending.</div></div></div>
      <div class="rule-row"><span class="tag tag-must">Must</span><div><div class="rule-row-title">Placement compensation</div><div class="rule-row-text">Provide compensation for placement losses according to the placement rules.</div></div></div>
    </div></div>
  </section>

  <section id="fines" class="rules-section" data-search-card>
    <div class="section-head">
      <div><h3>Fines</h3><p>All fines are deducted from your payout. Amounts are shown in EUR (€).</p></div>
      <div class="rules-muted small">Admins may adjust decisions depending on proof and repeated behavior.</div>
    </div>

    <div class="fine-category" data-search-card>
      <div class="fine-category-top"><div><h5 class="fw-bold mb-1">Major Violations</h5><div class="rules-muted small">Serious trust, safety, or customer-protection issues.</div></div><span class="fine-pill fine-high">€50-€250</span></div>
      <div class="fine-list">
        <div class="fine-item"><div class="fine-title">🚫 Private boosting or coaching</div><span class="fine-pill fine-high">€250</span><div class="fine-desc">Do not offer private boosting, coaching, extra services, or accept tips for extra services.</div></div>
        <div class="fine-item"><div class="fine-title">🤖 Using third-party programs</div><span class="fine-pill fine-high">€200</span><div class="fine-desc">Do not use bots, scripts, automation, or external programs for orders or in-game actions.</div></div>
        <div class="fine-item"><div class="fine-title">💬 Chat ban without telling admins</div><span class="fine-pill fine-high">€100</span><div class="fine-desc">If the customer account gets chat banned, tell admins immediately.</div></div>
        <div class="fine-item"><div class="fine-title">📞 Sharing contact information</div><span class="fine-pill fine-high">€75</span><div class="fine-desc">Do not share Discord, phone number, social media, or talk outside official channels.</div></div>
        <div class="fine-item"><div class="fine-title">📤 Sharing orders without approval</div><span class="fine-pill fine-high">€50</span><div class="fine-desc">Do not give, sell, or pass orders to other boosters or platforms without admin approval.</div></div>
        <div class="fine-item"><div class="fine-title">🔇 Chat ban on customer account</div><span class="fine-pill fine-high">€50</span><div class="fine-desc">Any chat ban caused on the customer account may result in a fine.</div></div>
      </div>
    </div>

    <div class="fine-category" data-search-card>
      <div class="fine-category-top"><div><h5 class="fw-bold mb-1">Standard Violations</h5><div class="rules-muted small">Important order and account rules.</div></div><span class="fine-pill fine-mid">€20-€25</span></div>
      <div class="fine-list">
        <div class="fine-item"><div class="fine-title">🌐 Not using a working VPN</div><span class="fine-pill fine-mid">€25</span><div class="fine-desc">Use a working VPN when it is required for the order.</div></div>
        <div class="fine-item"><div class="fine-title">😡 Bad behavior in-game or toward customer</div><span class="fine-pill fine-mid">€25</span><div class="fine-desc">No flaming, griefing, intentional feeding, begging for tips, or rude behavior.</div></div>
        <div class="fine-item"><div class="fine-title">🛒 Buying or using items without approval</div><span class="fine-pill fine-mid">€25</span><div class="fine-desc">Solo boost only. Do not buy, use, or change anything on the account without approval.</div></div>
        <div class="fine-item"><div class="fine-title">✉️ Messaging people on customer account</div><span class="fine-pill fine-mid">€20</span><div class="fine-desc">Solo boost only. Do not chat with or message people from the customer account.</div></div>
        <div class="fine-item"><div class="fine-title">⚠️ Ignoring order details</div><span class="fine-pill fine-mid">€20</span><div class="fine-desc">Follow champions, role, summoner spells, streaming, offline mode, and all order notes.</div></div>
      </div>
    </div>

    <div class="fine-category" data-search-card>
      <div class="fine-category-top"><div><h5 class="fw-bold mb-1">Minor Violations</h5><div class="rules-muted small">Smaller rule violations that still create problems.</div></div><span class="fine-pill fine-low">€10</span></div>
      <div class="fine-list">
        <div class="fine-item"><div class="fine-title">👻 Not using offline chat mode</div><span class="fine-pill fine-low">€10</span><div class="fine-desc">Solo or DuoQ boost. Use offline mode when required.</div></div>
        <div class="fine-item"><div class="fine-title">🔁 Dropping order without Drop Token</div><span class="fine-pill fine-low">€10</span><div class="fine-desc">Do not drop an order if you do not have a valid Drop Token.</div></div>
        <div class="fine-item"><div class="fine-title">⚠️ Asking customer to drop order for you</div><span class="fine-pill fine-low">€10</span><div class="fine-desc">Do not ask the customer to drop the order because you do not have a Drop Token.</div></div>
      </div>
    </div>

    <div class="fine-category" data-search-card>
      <div class="fine-category-top"><div><h5 class="fw-bold mb-1">Minor Operational Violations</h5><div class="rules-muted small">Small workflow rules that avoid admin work and customer issues.</div></div><span class="fine-pill fine-small">€5</span></div>
      <div class="fine-list">
        <div class="fine-item"><div class="fine-title">🗑️ Invalid delete game request</div><span class="fine-pill fine-small">€5</span><div class="fine-desc">Do not request a game deletion if the game does not qualify for deletion.</div></div>
        <div class="fine-item"><div class="fine-title">📡 Not adding Duo account for API tracking on purpose</div><span class="fine-pill fine-small">€5</span><div class="fine-desc">Do not hide or skip the DuoQ account if it is needed for tracking.</div></div>
        <div class="fine-item"><div class="fine-title">🎮 Accepting DuoQ order without a Duo account ready</div><span class="fine-pill fine-small">€5</span><div class="fine-desc">Do not accept a DuoQ order if you do not have a suitable DuoQ account ready or available soon.</div></div>
        <div class="fine-item"><div class="fine-title">💬 Accepting order without messaging customer</div><span class="fine-pill fine-small">€5</span><div class="fine-desc">After accepting an order, message the customer in the order chat.</div></div>
        <div class="fine-item"><div class="fine-title">⏰ Unnecessary delay after accepting order</div><span class="fine-pill fine-small">€5</span><div class="fine-desc">Do not accept an order and then do nothing. Start, prepare, or update the customer/admins.</div></div>
        <div class="fine-item"><div class="fine-title">📸 Not sending proof when asked</div><span class="fine-pill fine-small">€5</span><div class="fine-desc">Send screenshots, lobby proof, game proof, VPN proof, or other proof when admins ask for it.</div></div>
        <div class="fine-item"><div class="fine-title">🔄 Wrong or missing order status updates</div><span class="fine-pill fine-small">€5</span><div class="fine-desc">Keep the order status updated correctly and report problems when needed.</div></div>
        <div class="fine-item"><div class="fine-title">🚫 Ignoring admin instructions for an active order</div><span class="fine-pill fine-small">€5</span><div class="fine-desc">Follow admin instructions for active orders and customer problems.</div></div>
      </div>
    </div>

    <div class="rules-footer-note p-3 mt-3" data-search-card>
      <div class="fw-bold mb-1">💸 Important</div>
      <div class="rules-muted small">All fines will be taken from your payout. If you are unsure about an order situation, open a Discord ticket early. It is always better to ask than to guess.</div>
    </div>
  </section>
</div>

<script>
(function () {
  const input = document.getElementById('rulesSearch');
  const clearBtn = document.getElementById('rulesClearBtn');
  const cards = Array.from(document.querySelectorAll('[data-search-card]'));
  const navLinks = Array.from(document.querySelectorAll('.rules-nav a[href^="#"]'));

  function normalize(value) {
    return (value || '').toLowerCase().replace(/\s+/g, ' ').trim();
  }

  function applyFilter() {
    const query = normalize(input ? input.value : '');
    cards.forEach(card => {
      if (!query) {
        card.classList.remove('d-none-by-search');
        return;
      }
      card.classList.toggle('d-none-by-search', !normalize(card.textContent).includes(query));
    });
  }

  function scrollToHash(hash) {
    const target = document.querySelector(hash);
    if (!target) return;
    const y = target.getBoundingClientRect().top + window.pageYOffset - 86;
    window.scrollTo({ top: Math.max(0, y), behavior: 'smooth' });
  }

  navLinks.forEach(link => {
    link.addEventListener('click', function (event) {
      event.preventDefault();
      const hash = this.getAttribute('href');
      history.replaceState(null, '', hash);
      scrollToHash(hash);
    });
  });

  if (input) input.addEventListener('input', applyFilter);
  if (clearBtn && input) {
    clearBtn.addEventListener('click', function () {
      input.value = '';
      applyFilter();
      input.focus();
    });
  }

  if (window.location.hash) {
    setTimeout(() => scrollToHash(window.location.hash), 80);
  }
})();
</script>
