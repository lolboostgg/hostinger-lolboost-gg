<?php if (!empty($accounts)): ?>
    <?php foreach ($accounts as $account): ?>
        <div class="col-12 col-md-4">
            <a href="/lol/account/<?= $account['slug'] ?>" class="text-decoration-none text-dark">
                <div class="card mb-3">
                    <div class="card-body position-relative">
                        <h5 class="mb-0">
                            <img src="<?= util_rank_img('lol', 'mini', $account['current_rank']) ?>" class="rank-icon">
                            <?= strtoupper($account['server']) . ' - ' . util_get_lol_rank($account['current_rank']) ?>
                            <?= $account['current_lp'] ? $account['current_lp'] . 'LP' : util_format_lol_division($account['current_division']) ?>
                        </h5>
                        <p class="my-2 excerpt"><?= implode(' ', array_slice(explode(' ', $account['description']), 0, 20)) ?>
                        </p>
                        <?php
                        $images = json_decode($account['images'], true);
                        $firstImage = $images[0] ?? ASSET_URL . '/core/main/img/banners/account.jpg';
                        $remainingCount = count($images) - 1;
                        ?>
                        <div class="position-relative">
                            <img src="<?= $firstImage ?>" class="img-fluid rounded d-block w-100 main-image">
                            <div class="position-absolute bottom-0 end-0">
                                <span class="badge bg-grey mb-2 me-2"><i class="fas fa-images me-1"></i>
                                    +<?= $remainingCount ?></span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center flex-wrap gap-2 mt-3">
                            <span class="badge bg-grey"><i class="fas fa-helmet-battle"></i>
                                <?= count(explode('|', $account['champions'])) ?> Champions</span>
                            <span class="badge bg-grey"><i class="fas fa-masks-theater"></i>
                                <?= count(explode('|', $account['skins'])) ?> Skins</span>
                            <span class="badge bg-grey"><i class="fas fa-arrow-turn-up"></i> Level
                                <?= $account['level'] ?></span>
                            <span class="badge bg-grey"><i class="fas fa-gem"></i> <?= $account['blue_essence'] ?> BE</span>
                            <span class="badge bg-grey"><i class="fas fa-hand-back-fist"></i> <?= $account['riot_points'] ?>
                                RP</span>
                        </div>
                        <hr class="my-3">
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="d-flex align-items-end gap-2">
                                <span class="fw-bold price-eur">€<?= util_format_price_display($account['price']) ?></span>
                                <small class="text-muted fw-medium">EUR</small>
                            </div>
                            <a href="/lol/account/<?= $account['slug'] ?>" class="btn btn-primary btn-sm px-3">Buy Now</a>
                        </div>
                        <div class="position-absolute top-0 end-0 p-2">
                            <?php if ($account['delivery_type'] === 'instant'): ?>
                                <i class="fas fa-bolt" data-bs-toggle="tooltip" title="Instant Delivery"></i>
                            <?php else: ?>
                                <i class="fas fa-truck" data-bs-toggle="tooltip" title="Manual Delivery"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="col-12 text-center my-5">
        <div class="d-flex flex-column align-items-center">
            <span style="font-size: 3rem;">😢</span>
            <h5 class="mt-3">No accounts match your search</h5>
            <p class="text-muted">
                Worry not! Message us on live chat and we will help find what you’re looking for.
            </p>
            <div class="d-flex gap-2 mt-3">
                <a href="javascript:void(0)" 
               class="btn btn-primary"
               onclick="
                  if (typeof Tawk_API !== 'undefined') {
                      Tawk_API.maximize();
                  } else if (typeof tidioChatApi !== 'undefined') {
                      tidioChatApi.open();
                  } else {
                      alert('Chat system not loaded.');
                  }
               ">
               <i class="fas fa-comments me-2"></i> Talk to Agent
            </a>
            </div>
        </div>
    </div>
<?php endif; ?>