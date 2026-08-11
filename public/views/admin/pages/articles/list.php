<?php
if (!function_exists('article_list_safe_text')) {
    function article_list_safe_text($value): string
    {
        $text = (string)($value ?? '');

        for ($i = 0; $i < 3; $i++) {
            $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($decoded === $text) {
                break;
            }
            $text = $decoded;
        }

        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
?>

<?= $this->layout('admin/layouts/main', ['meta' => ['title' => 'Articles List - Admin Area | LoLBoost.gg', 'h1' => 'Articles List', 'description' => 'View, create, and edit blog articles.']]) ?>

<style>
/* ── Theme tokens ───────────────────────────────────────────────────────────
   body bg: #1e2022 | card bg: #25282a | border: #2f3235 | text: #c5c8cc
   teal: #00c9a7 | danger: #ed4c78 | primary: #5c4ae3 | amber: #f5ca99
   muted: #91989e
   ──────────────────────────────────────────────────────────────────────── */

.btn-tbl {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .25rem .7rem; border-radius: .45rem;
    font-size: .75rem; font-weight: 600; cursor: pointer;
    border: 1px solid; transition: all .15s ease; white-space: nowrap; text-decoration: none;
}
.btn-tbl-edit { background: rgba(92,74,227,.10); border-color: rgba(92,74,227,.35); color: #9b8bf0; }
.btn-tbl-edit:hover { background: rgba(92,74,227,.22); border-color: rgba(92,74,227,.6); color: #c4b8ff; }
.btn-tbl-add  { background: #5c4ae3; border-color: #5c4ae3; color: #fff; }
.btn-tbl-add:hover { background: #6d5ef0; border-color: #6d5ef0; }

.article-excerpt {
    font-size: .82rem; color: #91989e;
    max-width: 380px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
</style>

<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between w-100 gap-3">
            <h5 class="card-header-title mb-0">
                <i class="fa-duotone fa-newspaper me-2"></i>Articles List
            </h5>
            <div class="d-flex align-items-center gap-2">
                <div class="input-group input-group-merge input-group-flush" style="width:220px;">
                    <div class="input-group-prepend input-group-text">
                        <i class="fa-duotone fa-search"></i>
                    </div>
                    <input id="datatableWithSearchInput" type="search" class="form-control"
                           placeholder="Search articles…" aria-label="Search Articles">
                </div>
                <a href="<?= ADMN_URL ?>/article/add" class="btn-tbl btn-tbl-add">
                    <i class="fa-duotone fa-plus"></i> New Article
                </a>
            </div>
        </div>
    </div>

    <div class="table-responsive datatable-custom">
        <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
               data-hs-datatables-options='{
                   "columnDefs": [{"targets": [4], "orderable": false}],
                   "order": [[3, "desc"]],
                   "info":    {"totalQty": "#datatableEntriesInfoTotalQty"},
                   "entries": "#datatableEntries",
                   "search":  "#datatableWithSearchInput",
                   "isResponsive": false,
                   "isShowPaging": false,
                   "pagination": "datatableWithSearchPagination"
               }'
               id="articles_table">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Excerpt</th>
                    <th class="text-end">Updated At</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td>
                        <a href="<?= ADMN_URL ?>/article/<?= (int)$row['id'] ?>"
                           style="color:#9b8bf0;font-weight:600;font-size:.85rem;text-decoration:none;">
                            #<?= (int)$row['id'] ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?= ADMN_URL ?>/article/<?= (int)$row['id'] ?>"
                           style="color:#c5c8cc;font-weight:600;font-size:.88rem;text-decoration:none;">
                            <?= article_list_safe_text($row['title'] ?? '') ?>
                        </a>
                    </td>
                    <td>
                        <span class="article-excerpt">
                            <?= article_list_safe_text(substr((string)($row['excerpt'] ?? ''), 0, 90)) ?>…
                        </span>
                    </td>
                    <td class="text-end" data-order="<?= htmlspecialchars((string)($row['updated_at']??''), ENT_QUOTES, 'UTF-8') ?>"
                        style="font-size:.82rem;color:#91989e;">
                        <?= util_format_date_display($row['updated_at']) ?>
                    </td>
                    <td class="text-end">
                        <a href="<?= ADMN_URL ?>/article/<?= (int)$row['id'] ?>" class="btn-tbl btn-tbl-edit">
                            <i class="fa-duotone fa-pen-to-square" style="font-size:.8rem;"></i> Edit
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
            <div class="col-sm mb-2 mb-sm-0">
                <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
                    <span class="me-2">Showing:</span>
                    <div class="tom-select-custom">
                        <select id="datatableEntries" class="js-select form-select form-select-borderless w-auto"
                                autocomplete="off"
                                data-hs-tom-select-options='{"searchInDropdown":false,"hideSearch":true}'>
                            <option value="4">4</option>
                            <option value="6">6</option>
                            <option value="8">8</option>
                            <option value="12" selected>12</option>
                        </select>
                    </div>
                    <span class="text-secondary me-2">of</span>
                    <span id="datatableEntriesInfoTotalQty"></span>
                </div>
            </div>
            <div class="col-sm-auto">
                <div class="d-flex justify-content-center justify-content-sm-end">
                    <nav id="datatableWithSearchPagination" aria-label="Articles pagination"></nav>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
$(document).on('ready', function () {
    HSCore.components.HSDatatables.init($('#articles_table'), {
        language: {
            zeroRecords: `<div class="text-center p-4">
                <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations/oc-browse.svg" alt="" style="width:10rem;" data-hs-theme-appearance="default">
                <img class="mb-3" src="<?= ASSET_URL ?>/origin/dash/svg/illustrations-light/oc-browse.svg" alt="" style="width:10rem;" data-hs-theme-appearance="dark">
                <p class="mb-0">No articles found</p>
            </div>`
        }
    });
});
</script>
<?= $this->end() ?>
