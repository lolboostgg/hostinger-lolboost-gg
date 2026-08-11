<?= $this->layout('main/layouts/main', ['meta' => $meta]) ?>

<?= $this->insert('main/components/heroes/three', [
    'title' => 'Become a Booster',
    'lead' => 'Join our team of dedicated boosters and help players achieve their gaming goals. Apply now to become a part of our community!',
    'banner' => 'Blog.gif',
]) ?>

<?= $this->start('styles') ?>
<style>
    [data-user-theme="dark"] table th,
    [data-user-theme="dark"] table td,
    [data-user-theme="blue"] table th,
    [data-user-theme="blue"] table td {
        color: #9ca3af !important;
    }
</style>
<?= $this->end() ?>

<section class="container py-5">
    <div class="text-center mb-5">
        <h2 class="h3 fw-bold">Ready to Turn Your Skills Into Real Income?</h2>
        <p>lolboost.gg is calling all high-ELO warriors, mechanical gods, and strategic masterminds. Whether you're
            Challenger in League of Legends or Radiant in Valorant, it's time to get paid for your grind. We offer
            flexible boosting opportunities with serious rewards for serious players.</p>
        <p>Join an experienced team, boost with integrity, and earn money doing what you already love. You bring the
            skill—we handle the customers, security, and platform.</p>
    </div>

    <div class="row g-4 align-items-center mb-5">
        <div class="col-lg-6">
            <img src="<?= ASSET_URL ?>/core/main/img/illustrations/rules.svg" alt="Pro Gamer" class="img-fluid rounded">
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h4 fw-semibold">Booster Requirements</h2>
                    <ul class="mt-3">
                        <li>Maintain a courteous and helpful attitude at all times.</li>
                        <li>Start your order within one hour of claiming it.</li>
                        <li>Contact the customer within 5 minutes of locking in the order.</li>
                        <li>Inform the customer about any breaks in playtime in advance.</li>
                        <li>Do not share personal info (e.g., PayPal, Discord) outside the website chat.</li>
                        <li>Do not change the position of Summoner Spells.</li>
                        <li>Always use Offline Mode when boosting.</li>
                        <li>Do not engage in in-game chat unless necessary.</li>
                        <li>Report any account issues directly to the admins.</li>
                        <li>Do not use Blue Essence or RP without explicit customer approval.</li>
                        <li>Do not use any third-party software.</li>
                        <li>Do not share orders with other boosters.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 align-items-center mb-5">
        <div class="col-lg-6 order-lg-2">
            <img src="<?= ASSET_URL ?>/core/main/img/illustrations/fines.svg" alt="Penalty" class="img-fluid rounded">
        </div>
        <div class="col-lg-6 order-lg-1">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h4 fw-semibold">Fines and Penalties</h2>
                    <p class="mb-3">To maintain integrity and professionalism, violating any of the following rules will
                        result in fines:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Violation</th>
                                    <th>Fine</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Private Boosting/Coaching</td>
                                    <td>250€</td>
                                </tr>
                                <tr>
                                    <td>Using third-party programs</td>
                                    <td>200€</td>
                                </tr>
                                <tr>
                                    <td>Chat banning without notifying staff</td>
                                    <td>100€</td>
                                </tr>
                                <tr>
                                    <td>Sharing contact info outside official channels</td>
                                    <td>75€</td>
                                </tr>
                                <tr>
                                    <td>Sharing orders without admin notice</td>
                                    <td>50€</td>
                                </tr>
                                <tr>
                                    <td>Unauthorized chat banning</td>
                                    <td>50€</td>
                                </tr>
                                <tr>
                                    <td>Failure to use VPN</td>
                                    <td>25€</td>
                                </tr>
                                <tr>
                                    <td>Inappropriate in-game behavior</td>
                                    <td>25€</td>
                                </tr>
                                <tr>
                                    <td>Unauthorized item use</td>
                                    <td>25€</td>
                                </tr>
                                <tr>
                                    <td>Messaging on customer’s account</td>
                                    <td>20€</td>
                                </tr>
                                <tr>
                                    <td>Ignoring order specifics</td>
                                    <td>20€</td>
                                </tr>
                                <tr>
                                    <td>Failure to use Offline Mode</td>
                                    <td>10€</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="bg-secondary py-5 rounded shadow-sm">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="px-4">
                    <h2 class="h4 fw-bold">Why Boost with lolboost.gg?</h2>
                    <p>lolboost.gg is a trusted platform for League of Legends and Valorant boosting. We offer a secure
                        and professional space for top-tier players to monetize their skills and help others climb the
                        competitive ladder.</p>
                    <p>We provide competitive payouts, 24/7 support, and a seamless experience. Work on your own
                        schedule and be part of an elite team dedicated to excellence and integrity.</p>
                    <p>If you're ready to boost with honor, professionalism, and passion, we want you on our team.</p>
                </div>
            </div>
            <div class="col-md-6 text-center">
                <img src="<?= ASSET_URL ?>/core/main/img/illustrations/join.svg" alt="Join Team"
                    class="img-fluid rounded">
            </div>
        </div>
    </section>

    <div class="text-center mt-5">
        <a href="/apply-for-booster" class="btn btn-primary btn-lg px-5 py-3 rounded-pill">Apply for Booster</a>
    </div>
</section>