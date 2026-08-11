<?php
/* ── Seller: Create/Edit Digital Good Listing
   Used by both /seller-area/digital-goods/listings/create  AND
              /seller-area/digital-goods/listings/:id/edit
   ─────────────────────────────────────────────────────── */
$isEdit     = isset($listing) && !empty($listing['id']);
$listing    = is_array($listing    ?? null) ? $listing    : [];
$categories = is_array($categories ?? null) ? $categories : [];
$pageTitle  = $isEdit ? 'Edit Listing' : 'New Digital Good';
echo $this->layout('seller/layouts/main', ['meta' => $meta ?? ['title' => $pageTitle . ' | LoLBoost.gg']]);
require_once dirname(__DIR__) . '/_seller_rank.php';
$seller_data = is_array($seller_data ?? null) ? $seller_data : [];

$h   = fn($v) => htmlspecialchars((string)($v??''), ENT_QUOTES, 'UTF-8');
$val = fn($key,$def='') => $h($listing[$key] ?? $def);

$images = json_decode((string)($listing['images']??'[]'), true);
if (!is_array($images)) $images = [];
$priceFmt = $isEdit ? number_format((int)$listing['price']/100, 2, '.', '') : '';
$brandIconPath = preg_replace('#^https?://[^/]+#', '', (string)($listing['brand_icon'] ?? ''));
$brandIconPath = preg_replace('#^/public/assets#', '', $brandIconPath);
?>
<?= $this->start('styles') ?>
<style>
.dgl-create .card{background:var(--bs-card-bg)!important;border:1px solid var(--bs-card-border-color)!important;border-radius:22px!important;box-shadow:none!important;}
.dgl-create .card::before{display:none!important;}
.dglf-section{padding:22px 24px;border-bottom:1px solid rgba(255,255,255,.06);}
.dglf-section:last-child{border-bottom:none;}
.dglf-title{font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.35);font-weight:900;margin:0 0 16px;}
.dglf-label{font-size:.8rem;font-weight:700;color:rgba(255,255,255,.55);margin-bottom:5px;display:block;}
.dglf-input{width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:10px;color:#fff;padding:9px 13px;font-size:.9rem;outline:none;}
.dglf-input:focus{border-color:rgba(139,60,247,.5);box-shadow:0 0 0 3px rgba(139,60,247,.12);}
.dglf-input::placeholder{color:rgba(255,255,255,.25);}
select.dglf-input option{background:#1a1a2e;color:#fff;}
textarea.dglf-input{resize:vertical;min-height:90px;}
.dglf-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.dglf-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;}
.dglf-help{font-size:.76rem;color:rgba(255,255,255,.35);margin-top:4px;}
.dglf-img-preview{display:flex;flex-wrap:wrap;gap:10px;margin-top:12px;}
.dglf-img-tile{width:80px;height:80px;border-radius:12px;overflow:hidden;border:1px solid rgba(255,255,255,.1);position:relative;}
.dglf-img-tile img{width:100%;height:100%;object-fit:cover;}
.dglf-dropzone{position:relative;display:flex;align-items:center;justify-content:center;min-height:150px;border:1px dashed rgba(255,255,255,.18);border-radius:18px;background:rgba(255,255,255,.035);cursor:pointer;transition:border-color .15s,background .15s,transform .15s;}
.dglf-dropzone:hover,.dglf-dropzone.is-dragover{border-color:rgba(139,60,247,.75);background:rgba(139,60,247,.09);transform:translateY(-1px);}
.dglf-dropzone-inner{text-align:center;padding:20px;color:rgba(255,255,255,.62);}
.dglf-dropzone-icon{width:46px;height:46px;border-radius:14px;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;background:rgba(139,60,247,.14);color:#c4b5fd;font-size:1.2rem;}
.dglf-dropzone-title{font-weight:900;color:#fff;font-size:.92rem;}
.dglf-dropzone-text{font-size:.78rem;color:rgba(255,255,255,.42);margin-top:4px;}
.dglf-file-input{position:absolute;inset:0;opacity:0;cursor:pointer;}
.dglf-img-tile.is-new::after{content:'New';position:absolute;left:6px;top:6px;padding:2px 6px;border-radius:99px;background:rgba(139,60,247,.9);color:#fff;font-size:.64rem;font-weight:900;}
.dglf-img-remove{position:absolute;right:5px;top:5px;width:22px;height:22px;border-radius:50%;border:0;background:rgba(15,23,42,.82);color:#fff;font-size:.75rem;display:flex;align-items:center;justify-content:center;cursor:pointer;}
.dglf-img-remove:hover{background:#ef4444;}
.dglf-submit-row{padding:20px 24px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
.dglf-submit-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.65rem 1.6rem;border-radius:13px;background:linear-gradient(135deg,#8b3cf7,#c026d3);border:none;color:#fff;font-weight:900;font-size:.9rem;cursor:pointer;transition:opacity .15s;}
.dglf-submit-btn:hover{opacity:.88;}
.dglf-submit-btn:disabled{opacity:.5;cursor:not-allowed;}
.dglf-cancel-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.65rem 1.4rem;border-radius:13px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.7);font-weight:700;font-size:.9rem;text-decoration:none;}
.dglf-cancel-btn:hover{background:rgba(255,255,255,.1);color:#fff;}
.dglf-toggle-wrap{display:flex;align-items:center;gap:10px;}
.dglf-toggle{appearance:none;width:42px;height:22px;border-radius:99px;background:rgba(255,255,255,.12);cursor:pointer;position:relative;transition:background .2s;border:none;}
.dglf-toggle::after{content:'';position:absolute;top:3px;left:3px;width:16px;height:16px;border-radius:50%;background:#fff;transition:transform .2s;}
.dglf-toggle:checked{background:linear-gradient(135deg,#8b3cf7,#c026d3);}
.dglf-toggle:checked::after{transform:translateX(20px);}
.dglf-toggle-label{font-size:.88rem;font-weight:700;color:rgba(255,255,255,.75);}
.dglf-brand-wrap{position:relative;}
.dglf-brand-line{display:flex;align-items:center;gap:10px;}
.dglf-brand-icon-box{width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);flex-shrink:0;display:flex;align-items:center;justify-content:center;overflow:hidden;}
.dglf-brand-icon-box img{width:100%;height:100%;object-fit:contain;display:none;border-radius:9px;}
.dglf-brand-suggestions{display:none;position:absolute;top:100%;left:0;right:0;z-index:9999;background:#1e2028;border:1px solid rgba(139,60,247,.35);border-radius:12px;margin-top:4px;overflow:hidden;box-shadow:0 8px 28px rgba(0,0,0,.5);max-height:420px;}
@media(max-width:768px){.dglf-row,.dglf-row-3{grid-template-columns:1fr;}}
</style>
<?= $this->stop() ?>

<div class="dgl-create">
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
    <div>
      <h1 style="font-size:1.3rem;font-weight:950;color:#fff;margin:0;"><?= $pageTitle ?></h1>
      <div style="font-size:.82rem;color:rgba(255,255,255,.4);margin-top:3px;"><?= $isEdit?'Update your listing details':'Create a new digital good for buyers to purchase' ?></div>
    </div>
    <a href="<?= BASE_URL ?>/seller-area/digital-goods/listings" class="dglf-cancel-btn"><i class="fa-solid fa-arrow-left"></i> Back</a>
  </div>

  <form id="dglCreateForm" enctype="multipart/form-data">
    <input type="hidden" name="action"  value="<?= $isEdit?'seller_dg_update_listing':'seller_dg_create_listing' ?>">
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int)$listing['id'] ?>"><?php endif; ?>

    <div class="card mb-3">
      <!-- ── Basic Info ── -->
      <div class="dglf-section">
        <div class="dglf-title">Basic Information</div>
        <div class="mb-3">
          <label class="dglf-label">Title <span style="color:#fb7185;">*</span></label>
          <input type="text" name="title" class="dglf-input" required placeholder="e.g. Spotify Premium 1 Month – EU" value="<?= $val('title') ?>">
        </div>
        <div class="dglf-row mb-3">
          <div>
            <label class="dglf-label">Category <span style="color:#fb7185;">*</span></label>
            <select name="category_id" class="dglf-input" required>
              <option value="">— Select Category —</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?= (int)$cat['id'] ?>" <?= (int)($listing['category_id']??0)===(int)$cat['id']?'selected':'' ?>><?= $h($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="dglf-label">Brand / Service</label>
            <div class="dglf-brand-wrap">
              <div class="dglf-brand-line">
                <div class="dglf-brand-icon-box">
                  <i class="fa-solid fa-tag" style="color:rgba(255,255,255,.25);font-size:.9rem;" id="dglBrandIconPlaceholder"></i>
                  <img id="dglBrandIconImg" src="" alt="">
                </div>
                <input type="text" name="brand" id="dglBrandInput" class="dglf-input" placeholder="e.g. Spotify, Netflix, YouTube" value="<?= $val('brand') ?>" autocomplete="off" style="flex:1;">
              </div>
              <div id="dglBrandSuggestions" class="dglf-brand-suggestions"></div>
            </div>
            <input type="hidden" name="brand_icon" id="dglBrandIconField" value="<?= $h($brandIconPath) ?>">
          </div>
        </div>
        <div class="mb-3">
          <label class="dglf-label">Description <span style="color:#fb7185;">*</span></label>
          <textarea name="description" class="dglf-input" required rows="5" placeholder="Describe what the buyer gets, what's included, any requirements..."><?= $val('description') ?></textarea>
        </div>
        <div class="mb-0">
          <label class="dglf-label">Delivery Instructions (shown after payment)</label>
          <textarea name="delivery_instructions" class="dglf-input" rows="3" placeholder="Instructions for the buyer after they've paid..."><?= $val('delivery_instructions') ?></textarea>
        </div>
      </div>

      <!-- ── Pricing & Stock ── -->
      <div class="dglf-section">
        <div class="dglf-title">Pricing & Stock</div>
        <div class="dglf-row-3 mb-3">
          <div>
            <label class="dglf-label">Price (€) <span style="color:#fb7185;">*</span></label>
            <input type="number" name="price_display" class="dglf-input" step="0.01" min="0.01" required placeholder="9.99" value="<?= $priceFmt ?>">
          </div>
          <div>
            <label class="dglf-label">Stock <span style="color:#fb7185;">*</span></label>
            <input type="number" name="stock" class="dglf-input" min="0" required placeholder="50" value="<?= $val('stock','1') ?>">
          </div>
          <div>
            <label class="dglf-label">Validity (days)</label>
            <input type="number" name="validity_days" class="dglf-input" min="0" placeholder="30 = 1 month, 0 = lifetime" value="<?= $val('validity_days','') ?>">
            <div class="dglf-help">0 or blank = one-time/lifetime</div>
          </div>
        </div>
        <div class="dglf-row mb-3">
          <div>
            <label class="dglf-label">Min Purchase Qty</label>
            <input type="number" name="min_purchase_qty" class="dglf-input" min="1" value="<?= $val('min_purchase_qty','1') ?>">
          </div>
          <div>
            <label class="dglf-label">Max Purchase Qty</label>
            <input type="number" name="max_purchase_qty" class="dglf-input" min="1" placeholder="Leave blank = unlimited" value="<?= $val('max_purchase_qty','') ?>">
          </div>
        </div>
      </div>

      <!-- ── Region & Delivery ── -->
      <div class="dglf-section">
        <div class="dglf-title">Region & Delivery</div>
        <div class="dglf-row mb-0">
          <div>
            <label class="dglf-label">Region / Country</label>
            <input type="text" name="region" class="dglf-input" placeholder="e.g. EU, DE, Global, US" value="<?= $val('region','Global') ?>">
          </div>
          <div>
            <label class="dglf-label">Delivery Type</label>
            <select name="delivery_type" class="dglf-input">
              <option value="manual" <?= ($listing['delivery_type']??'manual')==='manual'?'selected':'' ?>>Manual (you deliver after payment)</option>
              <option value="auto"   <?= ($listing['delivery_type']??'')==='auto'?'selected':'' ?>>Auto (instant key from stock)</option>
            </select>
          </div>
        </div>
      </div>

      <!-- ── Images ── -->
      <div class="dglf-section">
        <div class="dglf-title">Product Images (optional)</div>
        <div class="dglf-dropzone" id="dglDropzone" tabindex="0" role="button" aria-label="Upload product images">
          <input type="file" id="dglImageInput" class="dglf-file-input" accept="image/*" multiple>
          <div class="dglf-dropzone-inner">
            <div class="dglf-dropzone-icon"><i class="fa-solid fa-images"></i></div>
            <div class="dglf-dropzone-title">Drag images here or click to upload</div>
            <div class="dglf-dropzone-text">You can also paste copied images with Ctrl/⌘ + V.</div>
          </div>
        </div>
        <div class="dglf-help">Upload cover/product images. JPG, PNG, GIF or WEBP up to the server image limit.</div>
        <div class="dglf-img-preview" id="dglImagePreview">
          <?php foreach ($images as $img): ?>
          <div class="dglf-img-tile" data-existing="<?= $h($img) ?>"><img src="<?= $h($img) ?>" alt=""><button type="button" class="dglf-img-remove" aria-label="Remove image">×</button></div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- ── Status ── -->
      <div class="dglf-section">
        <div class="dglf-title">Visibility</div>
        <div class="dglf-toggle-wrap">
          <input type="checkbox" name="active" id="dglActive" class="dglf-toggle" value="1" <?= !$isEdit||((int)($listing['active']??1)===1)?'checked':'' ?>>
          <label for="dglActive" class="dglf-toggle-label">Listing active (visible to buyers)</label>
        </div>
      </div>

      <!-- ── Submit ── -->
      <div class="dglf-submit-row">
        <button type="submit" class="dglf-submit-btn" id="dglSubmitBtn">
          <i class="fa-solid fa-<?= $isEdit?'save':'plus' ?>"></i> <?= $isEdit?'Save Changes':'Create Listing' ?>
        </button>
        <a href="<?= BASE_URL ?>/seller-area/digital-goods/listings" class="dglf-cancel-btn">Cancel</a>
      </div>
    </div>
  </form>
</div>

<?= $this->start('scripts') ?>
<script>
(function(){
    var dglAjaxUrl = (typeof window.ajax_url !== 'undefined') ? window.ajax_url : '<?= AJAX_URL ?>';
    var DGL_ASSET_URL = (typeof asset_url !== 'undefined') ? asset_url : '<?= addslashes(ASSET_URL) ?>';
    var dgBrandIcons = {
        'youtube'             : '/website/images/digital-goods/youtube.png',
        'spotify'             : '/website/images/digital-goods/spotify.jpg',
        'netflix'             : '/website/images/digital-goods/netflix.svg',
        'discord nitro'       : '/website/images/digital-goods/discord-nitro.png',
        'chatgpt'             : '/website/images/digital-goods/chat-gpt.png',
        'xbox game pass'      : '/website/images/digital-goods/xbox-gamepass.jpg',
        'hytale'              : '/website/images/digital-goods/hytale.webp',
        'adguard premium'     : '/website/images/digital-goods/adguard-premium.webp',
        'voicemod pro'        : '/website/images/digital-goods/voicemod-pro.webp',
        'perplexity'          : '/website/images/digital-goods/perplexity.webp',
        'deezer'              : '/website/images/digital-goods/deezer.webp',
        'fortnite vbucks'     : '/website/images/digital-goods/fortnite-vbucks.webp',
        'grok'                : '/website/images/digital-goods/grok.webp',
        'warframe'            : '/website/images/digital-goods/warframe.webp',
        'rocket league'       : '/website/images/digital-goods/rocket-league.webp',
        'linkedin'            : '/website/images/digital-goods/linkedin.webp',
        'runescape'           : '/website/images/digital-goods/runescape-fantasy.webp',
        'evernote'            : '/website/images/digital-goods/evernote.webp',
        'canva'               : '/website/images/digital-goods/canva.webp',
        'photoroom'           : '/website/images/digital-goods/photoroom.webp',
        'grammarly'           : '/website/images/digital-goods/grammarly.webp',
        'f1 tv'               : '/website/images/digital-goods/f1-tv.webp',
        'steam'               : '/website/images/digital-goods/steam.webp',
        'snapchat'            : '/website/images/digital-goods/snapchat.webp',
        'hbo'                 : '/website/images/digital-goods/hbo.webp',
        'bumble'              : '/website/images/digital-goods/bumble.webp',
        'disney plus'         : '/website/images/digital-goods/disney.webp',
        'capcut'              : '/website/images/digital-goods/capcut.webp',
        'duolingo'            : '/website/images/digital-goods/duolingo.webp',
        'nba league pass'     : '/website/images/digital-goods/nba-pass.webp',
        'reddit'              : '/website/images/digital-goods/reddit.webp',
        'medal tv'            : '/website/images/digital-goods/medaltv.webp',
        'turbo vpn'           : '/website/images/digital-goods/turbo-vpn.webp',
        'prime video'         : '/website/images/digital-goods/prime-video.webp',
        'twitch'              : '/website/images/digital-goods/twitch.webp',
        'adobe creative cloud': '/website/images/digital-goods/adobe-creative-cloud.webp',
        'badoo'               : '/website/images/digital-goods/badoo.webp',
        'claude'              : '/website/images/digital-goods/claude.webp',
        'epic games'          : '/website/images/digital-goods/epic-games.webp',
        'crunchyroll'         : '/website/images/digital-goods/crunchyroll.webp',
        'tinder'              : '/website/images/digital-goods/tinder.webp',
        'ps plus'             : '/website/images/digital-goods/ps-plus.webp',
        'gemini'              : '/website/images/digital-goods/gemini.webp',
        'cod points'          : '/website/images/digital-goods/cod-points.webp',
        'discord'             : '/website/images/digital-goods/discord-nitro.png',
        'chat gpt'            : '/website/images/digital-goods/chat-gpt.png',
        'openai'              : '/website/images/digital-goods/chat-gpt.png',
        'xbox'                : '/website/images/digital-goods/xbox-gamepass.jpg',
        'xbox gamepass'       : '/website/images/digital-goods/xbox-gamepass.jpg',
        'game pass'           : '/website/images/digital-goods/xbox-gamepass.jpg',
        'adguard'             : '/website/images/digital-goods/adguard-premium.webp',
        'voice mod'           : '/website/images/digital-goods/voicemod-pro.webp',
        'voicemod'            : '/website/images/digital-goods/voicemod-pro.webp',
        'fortnite'            : '/website/images/digital-goods/fortnite-vbucks.webp',
        'vbucks'              : '/website/images/digital-goods/fortnite-vbucks.webp',
        'v-bucks'             : '/website/images/digital-goods/fortnite-vbucks.webp',
        'rocketleague'        : '/website/images/digital-goods/rocket-league.webp',
        'runescape fantasy'   : '/website/images/digital-goods/runescape-fantasy.webp',
        'photo room'          : '/website/images/digital-goods/photoroom.webp',
        'formula 1'           : '/website/images/digital-goods/f1-tv.webp',
        'f1tv'                : '/website/images/digital-goods/f1-tv.webp',
        'disney'              : '/website/images/digital-goods/disney.webp',
        'disney+'             : '/website/images/digital-goods/disney.webp',
        'nba pass'            : '/website/images/digital-goods/nba-pass.webp',
        'league pass'         : '/website/images/digital-goods/nba-pass.webp',
        'medaltv'             : '/website/images/digital-goods/medaltv.webp',
        'prime'               : '/website/images/digital-goods/prime-video.webp',
        'amazon prime'        : '/website/images/digital-goods/prime-video.webp',
        'adobe'               : '/website/images/digital-goods/adobe-creative-cloud.webp',
        'creative cloud'      : '/website/images/digital-goods/adobe-creative-cloud.webp',
        'playstation plus'    : '/website/images/digital-goods/ps-plus.webp',
        'playstation'         : '/website/images/digital-goods/ps-plus.webp',
        'cod'                 : '/website/images/digital-goods/cod-points.webp',
        'call of duty'        : '/website/images/digital-goods/cod-points.webp'
    };
    var dgBrandLabels = {
        'youtube'             : 'YouTube',
        'spotify'             : 'Spotify',
        'netflix'             : 'Netflix',
        'discord nitro'       : 'Discord Nitro',
        'chatgpt'             : 'ChatGPT',
        'xbox game pass'      : 'Xbox Game Pass',
        'hytale'              : 'Hytale',
        'adguard premium'     : 'AdGuard Premium',
        'voicemod pro'        : 'VoiceMod Pro',
        'perplexity'          : 'Perplexity',
        'deezer'              : 'Deezer',
        'fortnite vbucks'     : 'Fortnite V-Bucks',
        'grok'                : 'Grok',
        'warframe'            : 'Warframe',
        'rocket league'       : 'Rocket League',
        'linkedin'            : 'LinkedIn',
        'runescape'           : 'RuneScape',
        'evernote'            : 'Evernote',
        'canva'               : 'Canva',
        'photoroom'           : 'PhotoRoom',
        'grammarly'           : 'Grammarly',
        'f1 tv'               : 'F1 TV',
        'steam'               : 'Steam',
        'snapchat'            : 'Snapchat',
        'hbo'                 : 'HBO',
        'bumble'              : 'Bumble',
        'disney plus'         : 'Disney+',
        'capcut'              : 'CapCut',
        'duolingo'            : 'Duolingo',
        'nba league pass'     : 'NBA League Pass',
        'reddit'              : 'Reddit',
        'medal tv'            : 'MedalTV',
        'turbo vpn'           : 'Turbo VPN',
        'prime video'         : 'Prime Video',
        'twitch'              : 'Twitch',
        'adobe creative cloud': 'Adobe Creative Cloud',
        'badoo'               : 'Badoo',
        'claude'              : 'Claude',
        'epic games'          : 'Epic Games',
        'crunchyroll'         : 'Crunchyroll',
        'tinder'              : 'Tinder',
        'ps plus'             : 'PS Plus',
        'gemini'              : 'Gemini',
        'cod points'          : 'COD Points',
        'discord'             : 'Discord Nitro',
        'chat gpt'            : 'ChatGPT',
        'openai'              : 'ChatGPT / OpenAI',
        'xbox'                : 'Xbox Game Pass',
        'xbox gamepass'       : 'Xbox Game Pass',
        'game pass'           : 'Xbox Game Pass',
        'adguard'             : 'AdGuard Premium',
        'voice mod'           : 'VoiceMod Pro',
        'voicemod'            : 'VoiceMod Pro',
        'fortnite'            : 'Fortnite V-Bucks',
        'vbucks'              : 'Fortnite V-Bucks',
        'v-bucks'             : 'Fortnite V-Bucks',
        'rocketleague'        : 'Rocket League',
        'runescape fantasy'   : 'RuneScape',
        'photo room'          : 'PhotoRoom',
        'formula 1'           : 'F1 TV',
        'f1tv'                : 'F1 TV',
        'disney'              : 'Disney+',
        'disney+'             : 'Disney+',
        'nba pass'            : 'NBA League Pass',
        'league pass'         : 'NBA League Pass',
        'medaltv'             : 'MedalTV',
        'prime'               : 'Prime Video',
        'amazon prime'        : 'Prime Video',
        'adobe'               : 'Adobe Creative Cloud',
        'creative cloud'      : 'Adobe Creative Cloud',
        'playstation plus'    : 'PS Plus',
        'playstation'         : 'PS Plus',
        'cod'                 : 'COD Points',
        'call of duty'        : 'COD Points'
    };
    var dgBrandDisplayKeys = ['youtube','spotify','netflix','discord nitro','chatgpt','xbox game pass','hytale','adguard premium','voicemod pro','perplexity','deezer','fortnite vbucks','grok','warframe','rocket league','linkedin','runescape','evernote','canva','photoroom','grammarly','f1 tv','steam','snapchat','hbo','bumble','disney plus','capcut','duolingo','nba league pass','reddit','medal tv','turbo vpn','prime video','twitch','adobe creative cloud','badoo','claude','epic games','crunchyroll','tinder','ps plus','gemini','cod points'];
    var selectedImages = [];
    var imageInput = document.getElementById('dglImageInput');
    var preview = document.getElementById('dglImagePreview');
    var dropzone = document.getElementById('dglDropzone');

    function restoreButton(){
        $('#dglSubmitBtn').prop('disabled',false).html('<i class="fa-solid fa-<?= $isEdit?'save':'plus' ?>"></i> <?= $isEdit?'Save Changes':'Create Listing' ?>');
    }
    function showMessage(msg){
        if (window.toastr && typeof toastr.error === 'function') toastr.error(msg);
        else alert(msg);
    }
    function setBrandIconByPath(path) {
        var imgEl = document.getElementById('dglBrandIconImg');
        var placeholderEl = document.getElementById('dglBrandIconPlaceholder');
        var hiddenEl = document.getElementById('dglBrandIconField');
        path = String(path || '');
        if (!imgEl || !placeholderEl) return;
        if (path) {
            imgEl.src = DGL_ASSET_URL + path;
            imgEl.style.display = 'block';
            placeholderEl.style.display = 'none';
            if (hiddenEl) hiddenEl.value = path;
        } else {
            imgEl.src = '';
            imgEl.style.display = 'none';
            placeholderEl.style.display = 'block';
            if (hiddenEl) hiddenEl.value = '';
        }
    }
    function setBrandIconByValue(value) {
        var key = String(value || '').trim().toLowerCase();
        setBrandIconByPath(dgBrandIcons[key] || '');
    }
    function buildBrandSuggestions(query) {
        var q = String(query || '').toLowerCase().trim();
        if (!q) return dgBrandDisplayKeys.slice();
        return dgBrandDisplayKeys.filter(function(key) {
            var label = (dgBrandLabels[key] || key).toLowerCase();
            return key.indexOf(q) !== -1 || label.indexOf(q) !== -1;
        });
    }
    function renderBrandSuggestions(items) {
        var suggestionsEl = document.getElementById('dglBrandSuggestions');
        var inputEl = document.getElementById('dglBrandInput');
        if (!suggestionsEl || !inputEl) return;
        suggestionsEl.innerHTML = '';
        if (!items || !items.length) { suggestionsEl.style.display = 'none'; return; }

        var searchWrap = document.createElement('div');
        searchWrap.style.cssText = 'position:sticky;top:0;z-index:2;padding:10px;background:#1e2028;border-bottom:1px solid rgba(255,255,255,.07);';
        var searchInput = document.createElement('input');
        searchInput.type = 'search';
        searchInput.placeholder = 'Search Brand...';
        searchInput.autocomplete = 'off';
        searchInput.style.cssText = 'width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:10px;color:#fff;padding:8px 12px;font-size:.86rem;outline:none;';
        searchWrap.appendChild(searchInput);

        var listWrap = document.createElement('div');
        listWrap.style.cssText = 'max-height:340px;overflow-y:auto;overscroll-behavior:contain;scrollbar-width:thin;';

        function drawList(listItems) {
            listWrap.innerHTML = '';
            if (!listItems.length) {
                var empty = document.createElement('div');
                empty.style.cssText = 'padding:12px 13px;color:rgba(255,255,255,.45);font-size:.86rem;';
                empty.textContent = 'No brands found';
                listWrap.appendChild(empty);
                return;
            }
            listItems.forEach(function(key) {
                var icon = dgBrandIcons[key];
                var label = dgBrandLabels[key] || key;
                var item = document.createElement('div');
                item.style.cssText = 'display:flex;align-items:center;gap:10px;padding:9px 13px;cursor:pointer;border-bottom:1px solid rgba(255,255,255,.05);transition:background .1s;';
                item.innerHTML = '<img src="' + DGL_ASSET_URL + icon + '" style="width:28px;height:28px;object-fit:contain;border-radius:6px;flex-shrink:0;" alt="">' +
                                 '<span style="font-size:.88rem;font-weight:800;color:#fff;">' + label + '</span>';
                item.addEventListener('mouseenter', function(){ this.style.background = 'rgba(139,60,247,.18)'; });
                item.addEventListener('mouseleave', function(){ this.style.background = ''; });
                item.addEventListener('mousedown', function(e){
                    e.preventDefault();
                    inputEl.value = label;
                    setBrandIconByValue(key);
                    suggestionsEl.style.display = 'none';
                });
                listWrap.appendChild(item);
            });
        }
        function filterItems(q) {
            q = String(q || '').toLowerCase().trim();
            if (!q) return items.slice();
            return items.filter(function(key) {
                var label = (dgBrandLabels[key] || key).toLowerCase();
                return key.indexOf(q) !== -1 || label.indexOf(q) !== -1;
            });
        }

        searchInput.addEventListener('input', function(){ drawList(filterItems(this.value)); });
        searchInput.addEventListener('keydown', function(e){ if (e.key === 'Escape') suggestionsEl.style.display = 'none'; });
        suggestionsEl.appendChild(searchWrap);
        suggestionsEl.appendChild(listWrap);
        drawList(items);
        suggestionsEl.style.display = 'block';
    }
    function initBrandPicker() {
        var inputEl = document.getElementById('dglBrandInput');
        var suggestionsEl = document.getElementById('dglBrandSuggestions');
        var hiddenEl = document.getElementById('dglBrandIconField');
        if (!inputEl || !suggestionsEl) return;
        inputEl.addEventListener('input', function(){
            setBrandIconByValue(this.value);
            renderBrandSuggestions(buildBrandSuggestions(this.value));
        });
        inputEl.addEventListener('focus', function(){ renderBrandSuggestions(buildBrandSuggestions(this.value)); });
        inputEl.addEventListener('blur', function(){
            setTimeout(function(){
                if (!suggestionsEl.contains(document.activeElement)) suggestionsEl.style.display = 'none';
            }, 180);
        });
        if (hiddenEl && hiddenEl.value) setBrandIconByPath(hiddenEl.value);
        else setBrandIconByValue(inputEl.value);
    }
    initBrandPicker();
    function addFiles(fileList){
        Array.prototype.forEach.call(fileList || [], function(file){
            if (!file || !/^image\//i.test(file.type || '')) return;
            selectedImages.push(file);
        });
        renderNewPreviews();
    }
    function renderNewPreviews(){
        if (!preview) return;
        preview.querySelectorAll('[data-new-index]').forEach(function(el){ el.remove(); });
        selectedImages.forEach(function(file, index){
            var tile = document.createElement('div');
            tile.className = 'dglf-img-tile is-new';
            tile.setAttribute('data-new-index', String(index));
            var img = document.createElement('img');
            img.alt = '';
            img.src = URL.createObjectURL(file);
            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'dglf-img-remove';
            remove.setAttribute('aria-label', 'Remove image');
            remove.innerHTML = '×';
            remove.addEventListener('click', function(){ selectedImages.splice(index, 1); renderNewPreviews(); });
            tile.appendChild(img);
            tile.appendChild(remove);
            preview.appendChild(tile);
        });
    }

    if (imageInput) {
        imageInput.addEventListener('change', function(){ addFiles(imageInput.files); imageInput.value=''; });
    }
    if (dropzone) {
        ['dragenter','dragover'].forEach(function(evt){
            dropzone.addEventListener(evt, function(e){ e.preventDefault(); dropzone.classList.add('is-dragover'); });
        });
        ['dragleave','drop'].forEach(function(evt){
            dropzone.addEventListener(evt, function(e){ e.preventDefault(); dropzone.classList.remove('is-dragover'); });
        });
        dropzone.addEventListener('drop', function(e){ addFiles(e.dataTransfer && e.dataTransfer.files); });
        dropzone.addEventListener('keydown', function(e){ if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); imageInput && imageInput.click(); } });
    }
    document.addEventListener('paste', function(e){
        var items = e.clipboardData && e.clipboardData.items;
        if (!items) return;
        var files = [];
        Array.prototype.forEach.call(items, function(item){
            if (item.kind === 'file' && /^image\//i.test(item.type || '')) {
                var file = item.getAsFile();
                if (file) files.push(file);
            }
        });
        if (files.length) addFiles(files);
    });
    if (preview) {
        preview.addEventListener('click', function(e){
            if (!e.target.classList.contains('dglf-img-remove')) return;
            var tile = e.target.closest('[data-existing]');
            if (tile) tile.remove();
        });
    }

    $('#dglCreateForm').on('submit',function(e){
        e.preventDefault();
        var $btn=$('#dglSubmitBtn');
        $btn.prop('disabled',true).html('<i class="fa-solid fa-spinner fa-spin"></i> Saving...');
        var fd=new FormData(this);
        if(!$('#dglActive').is(':checked'))fd.set('active','0');

        var existingImages = [];
        if (preview) {
            preview.querySelectorAll('[data-existing]').forEach(function(tile){
                var value = tile.getAttribute('data-existing');
                if (value) existingImages.push(value);
            });
        }
        fd.set('existing_images_json', JSON.stringify(existingImages));
        selectedImages.forEach(function(file, index){ fd.append('item_images[]', file, file.name || ('product-image-' + (index + 1) + '.png')); });

        $.ajax({
            url:dglAjaxUrl,type:'POST',data:fd,processData:false,contentType:false,dataType:'json',
            success:function(r){
                restoreButton();
                if(r&&r.success){
                    if(r.redirectUrl)window.location.href=r.redirectUrl;
                    else if(r.refreshPage)window.location.reload();
                    else if(r.sendToast&&r.sendToast.message)showMessage(r.sendToast.message);
                } else if(r&&r.sendToast){
                    showMessage(r.sendToast.message || 'Something went wrong.');
                    if(r.validationErrors)console.warn(r.validationErrors);
                } else {
                    showMessage('Listing could not be saved. Please check the browser console/network response.');
                }
            },
            error:function(xhr){
                restoreButton();
                console.error('Digital goods listing AJAX failed:', xhr && xhr.responseText ? xhr.responseText : xhr);
                var msg = 'Could not reach the server. Please try again.';
                if (xhr && xhr.responseText) {
                    try {
                        var parsed = JSON.parse(xhr.responseText);
                        if (parsed && parsed.sendToast && parsed.sendToast.message) msg = parsed.sendToast.message;
                    } catch(e) {
                        msg += '\n\nServer response: ' + xhr.responseText.substring(0, 500);
                    }
                }
                showMessage(msg);
            }
        });
    });
})();
</script>
<?= $this->stop() ?>
