<?php
if (!function_exists('article_admin_decode_entities')) {
    function article_admin_decode_entities($value) {
        $value = (string)$value;
        for ($i = 0; $i < 3; $i++) {
            $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($decoded === $value) {
                break;
            }
            $value = $decoded;
        }
        return $value;
    }
}
if (!function_exists('article_admin_e')) {
    function article_admin_e($value) {
        return htmlspecialchars(article_admin_decode_entities($value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
?>
<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Create Article - Admin Area | LoLBoost.gg']]) ?>

<?= $this->start('styles') ?>
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/default.min.css">
<style>
/* ── Theme tokens ───────────────────────────────────────────────────────────
   body bg: #1e2022 | card bg: #25282a | border: #2f3235 | text: #c5c8cc
   primary: #5c4ae3 | muted: #91989e
   ──────────────────────────────────────────────────────────────────────── */

.pkg-form-row {
    display: grid; grid-template-columns: 180px 1fr;
    align-items: flex-start; gap: 1rem;
    padding: .9rem 0; border-bottom: 1px solid #2f3235;
}
.pkg-form-row.centered { align-items: center; }
.pkg-form-row:last-child { border-bottom: none; }
.pkg-form-label { font-size: .82rem; font-weight: 600; color: #91989e; padding-top: .4rem; }
.pkg-form-label small { display: block; font-size: .72rem; color: #555d65; font-weight: 400; margin-top: .15rem; }

/* Quill editor */
#editor {
    min-height: 420px; font-size: 16px; line-height: 1.6;
    color: #e6e6e6; background: #1e2022;
    padding: 16px; border-radius: 0 0 8px 8px;
    border: 1px solid #2f3235; border-top: none;
}
.ql-editor p  { margin: 0 0 1em; }
.ql-editor h1, .ql-editor h2, .ql-editor h3 { font-weight: 600; color: #fff; margin: 1em 0 .5em; }
.ql-editor blockquote { border-left: 3px solid #3a3d42; margin: 0 0 1em; padding-left: 1em; color: #b0b0b0; font-style: italic; }
.ql-editor pre.ql-syntax { background: #2a2d31; padding: 10px; border-radius: 6px; font-family: "Fira Code", monospace; font-size: 14px; color: #e6e6e6; overflow-x: auto; }
.ql-editor a  { color: #4ea1f3; text-decoration: none; }
.ql-editor a:hover { text-decoration: underline; color: #82c4ff; }
.ql-editor.ql-blank::before { color: #888 !important; font-style: italic; }
.ql-editor table { width: 100%; border-collapse: collapse; margin: 1em 0; }
.ql-editor th, .ql-editor td { border: 1px solid #3a3d42; padding: 8px 10px; vertical-align: top; }
.ql-editor th { background: #26292c; color: #fff; font-weight: 600; }
.ql-toolbar {
    border-radius: 8px 8px 0 0; border: 1px solid #2f3235;
    background: #25282a;
}
.ql-toolbar button { color: #ccc; }
.ql-toolbar button:hover, .ql-toolbar button.ql-active { color: #fff; }
.ql-toolbar button:hover .ql-stroke, .ql-toolbar button.ql-active .ql-stroke { stroke: #fff; }
.ql-toolbar button:hover .ql-fill,   .ql-toolbar button.ql-active .ql-fill   { fill: #fff; }
.ql-toolbar .ql-stroke { stroke: #ccc; }
.ql-toolbar .ql-fill   { fill: #ccc; }
.ql-toolbar .ql-picker  { color: #ccc; }
.ql-toolbar .ql-picker-options { background: #25282a; border: 1px solid #2f3235; }
.ql-toolbar .ql-picker-options span { color: #ccc; }
.ql-toolbar .ql-picker-options span:hover { background: rgba(92,74,227,.15); color: #fff; }

/* Thumbnail preview */
.thumb-preview {
    display: flex; align-items: center; gap: .75rem; margin-top: .6rem;
    padding: .6rem .85rem; background: rgba(0,0,0,.15);
    border: 1px solid #2f3235; border-radius: .5rem;
}
.thumb-preview img { height: 52px; border-radius: .35rem; object-fit: cover; }
.thumb-preview span { font-size: .78rem; color: #91989e; }
/* ── Game picker (searchable, with icons) ── */
.ag-game-dd{position:relative;max-width:340px;}
.ag-game-trigger{display:flex;align-items:center;gap:.55rem;width:100%;padding:.5rem .75rem;border-radius:.5rem;
  border:1px solid #2f3235;background:#1e2022;color:#c5c8cc;font-size:.85rem;cursor:pointer;text-align:left;}
.ag-game-trigger:hover{border-color:#3a3e42;}
.ag-game-chevron{margin-left:auto;font-size:.62rem;opacity:.45;transition:transform .18s;}
.ag-game-trigger.is-open .ag-game-chevron{transform:rotate(180deg);}
.ag-game-icon{width:20px;height:20px;border-radius:5px;object-fit:contain;flex:0 0 auto;}
.ag-game-icon--none{display:inline-flex;align-items:center;justify-content:center;opacity:.4;font-size:.75rem;}
.ag-game-menu{position:absolute;top:calc(100% + 6px);left:0;z-index:9999;width:100%;background:#1e2124;
  border:1px solid rgba(255,255,255,.11);border-radius:.7rem;box-shadow:0 12px 40px rgba(0,0,0,.5);
  overflow:hidden;opacity:0;transform:translateY(-6px) scale(.97);pointer-events:none;transition:opacity .15s,transform .15s;}
.ag-game-menu.is-open{opacity:1;transform:translateY(0) scale(1);pointer-events:auto;}
.ag-game-search-wrap{position:relative;padding:.6rem .6rem .4rem;}
.ag-game-search{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:.45rem;
  color:#c5c8cc;padding:.45rem .6rem .45rem 2rem;font-size:.8rem;font-family:inherit;box-sizing:border-box;}
.ag-game-search:focus{outline:none;border-color:rgba(109,92,255,.4);}
.ag-game-search-icon{position:absolute;left:1.2rem;top:calc(50% + .1rem);transform:translateY(-50%);opacity:.35;font-size:.72rem;pointer-events:none;}
.ag-game-list{max-height:260px;overflow-y:auto;padding:.25rem;}
.ag-game-opt{display:flex;align-items:center;gap:.55rem;width:100%;padding:.45rem .55rem;border:0;border-radius:.45rem;
  background:transparent;color:#c5c8cc;font-size:.83rem;cursor:pointer;text-align:left;}
.ag-game-opt:hover{background:rgba(109,92,255,.14);}
.ag-game-opt.is-active{background:rgba(109,92,255,.22);color:#c4b5fd;}
</style>
<?= $this->end() ?>

<form class="form ajax-form" action="<?= AJAX_URL ?>">
    <input type="hidden" name="action"  value="admin_add_article">
    <input type="hidden" name="content" id="content">

    <!-- ── Page header ── -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0" style="font-weight:700;color:#c5c8cc;">
                <i class="fa-duotone fa-file-pen me-2" style="color:#5c4ae3;"></i>Create Article
            </h4>
            <div style="font-size:.82rem;color:#91989e;margin-top:.2rem;">New blog post for LoLBoost.gg</div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= ADMN_URL ?>/articles" style="display:inline-flex;align-items:center;gap:.35rem;font-size:.78rem;color:#91989e;text-decoration:none;padding:.3rem .7rem;border:1px solid #2f3235;border-radius:.45rem;background:#25282a;">
                <i class="fa-duotone fa-arrow-left"></i> Back
            </a>
            <button type="submit" class="btn btn-primary btn-sm">
                <span class="indicator-label"><i class="fa-duotone fa-floppy-disk me-1"></i>Create Article</span>
                <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle"></span></span>
            </button>
        </div>
    </div>

    <!-- ── Meta card ── -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-header-title mb-0"><i class="fa-duotone fa-circle-info me-2"></i>Article Meta</h5>
        </div>
        <div class="card-body pt-2 pb-0">

            <div class="pkg-form-row centered">
                <label class="pkg-form-label">
                    Title
                    <small>Main article heading</small>
                </label>
                <input type="text" class="form-control" name="title" id="article-title"
                       placeholder="League of Legends Mastery Guide">
            </div>

            <div class="pkg-form-row">
                <label class="pkg-form-label">
                    SEO Description
                    <small>Meta description for search engines</small>
                </label>
                <textarea class="form-control" name="description" rows="3"
                          placeholder="A comprehensive guide to mastering League of Legends…"></textarea>
            </div>

            <div class="pkg-form-row">
                <label class="pkg-form-label">
                    Excerpt
                    <small>Short preview shown in article lists</small>
                </label>
                <textarea class="form-control" name="excerpt" rows="3"
                          placeholder="League of Legends, often known as League, is a multiplayer online battle arena…"></textarea>
            </div>


            <div class="pkg-form-row">
                <label class="pkg-form-label">
                    Game
                    <small>Tags the article so it can be filtered per game</small>
                </label>
                <div>
                    <?php
                      // Searchable dropdown with icons. A plain <select> can't show the game
                      // icons, so the value lives in a hidden input and the list is rendered here.
                      $agGames    = $games ?? [];
                      $agSelected = (int)($data['game_id'] ?? 0);
                      $agCurrent  = null;
                      foreach ($agGames as $agGame) {
                          if ((int)($agGame['id'] ?? 0) === $agSelected) { $agCurrent = $agGame; break; }
                      }
                      $agIcon = static function ($game) {
                          $icon = trim((string)($game['icon'] ?? ''));
                          if ($icon === '' && function_exists('util_game_icon_url')) {
                              $icon = (string)util_game_icon_url((string)($game['slug'] ?? ''));
                          }
                          if ($icon !== '' && !preg_match('~^https?://~i', $icon)) {
                              $icon = rtrim(BASE_URL, '/') . '/' . ltrim($icon, '/');
                          }
                          return $icon;
                      };
                    ?>
                    <input type="hidden" name="game_id" id="agGameId" value="<?= $agSelected ?: '' ?>">
                    <div class="ag-game-dd">
                        <button type="button" class="ag-game-trigger" id="agGameTrigger">
                            <?php $agCurIcon = $agCurrent ? $agIcon($agCurrent) : ''; ?>
                            <img class="ag-game-icon" id="agGameCurIcon" src="<?= htmlspecialchars($agCurIcon, ENT_QUOTES) ?>"
                                 alt="" style="<?= $agCurIcon === '' ? 'display:none;' : '' ?>">
                            <span id="agGameCurLabel"><?= $agCurrent ? htmlspecialchars((string)$agCurrent['name'], ENT_QUOTES) : 'No game (general)' ?></span>
                            <i class="fa-solid fa-chevron-down ag-game-chevron"></i>
                        </button>
                        <div class="ag-game-menu" id="agGameMenu">
                            <div class="ag-game-search-wrap">
                                <i class="fa-solid fa-magnifying-glass ag-game-search-icon"></i>
                                <input type="text" class="ag-game-search" id="agGameSearch" placeholder="Search game…" autocomplete="off" spellcheck="false">
                            </div>
                            <div class="ag-game-list" id="agGameList">
                                <button type="button" class="ag-game-opt" data-id="" data-label="No game (general)" data-icon="">
                                    <span class="ag-game-icon ag-game-icon--none"><i class="fa-duotone fa-ban"></i></span>
                                    <span>No game (general)</span>
                                </button>
                                <?php foreach ($agGames as $agGame): $agOptIcon = $agIcon($agGame); ?>
                                    <button type="button" class="ag-game-opt<?= (int)$agGame['id'] === $agSelected ? ' is-active' : '' ?>"
                                            data-id="<?= (int)$agGame['id'] ?>"
                                            data-label="<?= htmlspecialchars((string)$agGame['name'], ENT_QUOTES) ?>"
                                            data-icon="<?= htmlspecialchars($agOptIcon, ENT_QUOTES) ?>">
                                        <?php if ($agOptIcon !== ''): ?>
                                            <img class="ag-game-icon" src="<?= htmlspecialchars($agOptIcon, ENT_QUOTES) ?>" alt="" loading="lazy">
                                        <?php else: ?>
                                            <span class="ag-game-icon ag-game-icon--none"><i class="fa-duotone fa-gamepad"></i></span>
                                        <?php endif; ?>
                                        <span><?= htmlspecialchars((string)$agGame['name'], ENT_QUOTES) ?></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pkg-form-row">
                <label class="pkg-form-label">
                    Thumbnail
                    <small>Cover image for the article</small>
                </label>
                <div>
                    <input class="form-control" type="file" name="image_url" id="image_url" accept="image/*">
                    <div id="thumb-preview-wrap" style="display:none;" class="thumb-preview mt-2">
                        <img id="thumb-preview-img" src="" alt="Preview">
                        <span id="thumb-preview-name"></span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ── Body card ── -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-header-title mb-0"><i class="fa-duotone fa-align-left me-2"></i>Article Body</h5>
        </div>
        <div class="card-body p-0">
            <div id="editor"></div>
        </div>
    </div>

</form>

<?= $this->start('scripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
(function () {
    /* ── Game picker ── */
    (function () {
        var trigger = document.getElementById('agGameTrigger');
        var menu    = document.getElementById('agGameMenu');
        var search  = document.getElementById('agGameSearch');
        var hidden  = document.getElementById('agGameId');
        var curIcon = document.getElementById('agGameCurIcon');
        var curLbl  = document.getElementById('agGameCurLabel');
        if (!trigger || !menu) return;

        function close() { menu.classList.remove('is-open'); trigger.classList.remove('is-open'); }

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = menu.classList.toggle('is-open');
            trigger.classList.toggle('is-open', open);
            if (open && search) { search.value = ''; search.focus(); filter(''); }
        });
        document.addEventListener('click', function (e) {
            if (!menu.contains(e.target) && e.target !== trigger) close();
        });

        function filter(term) {
            term = (term || '').toLowerCase();
            menu.querySelectorAll('.ag-game-opt').forEach(function (opt) {
                var label = (opt.dataset.label || '').toLowerCase();
                opt.style.display = (!term || label.indexOf(term) !== -1) ? '' : 'none';
            });
        }
        if (search) search.addEventListener('input', function () { filter(this.value); });

        menu.addEventListener('click', function (e) {
            var opt = e.target.closest('.ag-game-opt');
            if (!opt) return;
            menu.querySelectorAll('.ag-game-opt').forEach(function (o) { o.classList.remove('is-active'); });
            opt.classList.add('is-active');
            hidden.value = opt.dataset.id || '';
            curLbl.textContent = opt.dataset.label || 'No game (general)';
            var icon = opt.dataset.icon || '';
            if (icon) { curIcon.src = icon; curIcon.style.display = ''; }
            else { curIcon.removeAttribute('src'); curIcon.style.display = 'none'; }
            close();
        });
    })();

    /* ── Thumbnail preview ── */
    var fileInput = document.getElementById('image_url');
    var previewWrap = document.getElementById('thumb-preview-wrap');
    var previewImg  = document.getElementById('thumb-preview-img');
    var previewName = document.getElementById('thumb-preview-name');
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            var file = this.files[0];
            if (!file) { previewWrap.style.display = 'none'; return; }
            previewImg.src  = URL.createObjectURL(file);
            previewName.textContent = file.name;
            previewWrap.style.display = 'flex';
        });
    }

    /* ── Quill ── */
    var quill = new Quill('#editor', {
        debug: 'warn',
        placeholder: 'Write your blog post here…',
        theme: 'snow',
        formats: ['header','bold','italic','underline','strike','blockquote','code-block','list','indent','link','image','video','align','color','background','script','table','table-row','table-cell'],
        modules: {
            table: true,
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ list: 'ordered' }, { list: 'bullet' }, { indent: '-1' }, { indent: '+1' }],
                [{ align: [] }],
                [{ color: [] }, { background: [] }],
                ['link', 'image', 'video'],
                ['table'],
                ['clean']
            ],
            history:   { delay: 1000, maxStack: 100, userOnly: true },
            clipboard: { matchVisual: false },
            syntax: true
        }
    });

    var toolbar = quill.getModule('toolbar');
    toolbar.addHandler('table', function () {
        var tbl = quill.getModule('table');
        if (tbl && typeof tbl.insertTable === 'function') tbl.insertTable(3, 3);
    });
    toolbar.addHandler('image', function () {
        var input = document.createElement('input');
        input.type = 'file'; input.accept = 'image/*'; input.click();
        input.onchange = async function () {
            var file = input.files[0]; if (!file) return;
            var url = await uploadImage(file);
            if (url) { var r = quill.getSelection(true); quill.insertEmbed(r.index, 'image', url, 'user'); quill.setSelection(r.index + 1); }
        };
    });

    async function uploadImage(file) {
        var fd = new FormData();
        fd.append('file', file); fd.append('action', 'upload_quill_image');
        try {
            var res = await fetch('<?= AJAX_URL ?>', { method: 'POST', body: fd });
            if (!res.ok) throw new Error(res.statusText);
            var d = await res.json(); return d.url;
        } catch (e) { console.error(e); alert('Image upload failed'); return null; }
    }

    // Same helpers the edit page uses. They were missing here, so the submit handler
    // threw a ReferenceError before it could copy the Quill HTML into #content —
    // the form then posted an empty body and the server answered
    // "Please fill all required fields".
    function decodeEntities(value) {
        var textarea = document.createElement('textarea');
        var previous = String(value || '');
        for (var i = 0; i < 3; i++) {
            textarea.innerHTML = previous;
            if (textarea.value === previous) break;
            previous = textarea.value;
        }
        return previous;
    }

    function normalizeArticleMetaFields(form) {
        ['title', 'description', 'excerpt'].forEach(function (name) {
            var field = form.querySelector('[name="' + name + '"]');
            if (field) field.value = decodeEntities(field.value);
        });
    }

    document.addEventListener('submit', function (e) {
        var form = e.target.closest('.ajax-form'); if (!form) return;
        normalizeArticleMetaFields(form);
        var cf = form.querySelector('#content'); if (cf) cf.value = quill.root.innerHTML;
    }, true);
})();
</script>
<?= $this->end() ?>
