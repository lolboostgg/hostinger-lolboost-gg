<?= $this->layout('main/layouts/main', ['meta' => $meta]) ?>

<?= $this->insert('main/components/heroes/three', ['title' => '', 'lead' => '']) ?>

<?php
$h2 = function ($text) {
    return "<h2 class=\"h4 mb-1 mt-5\">$text</h2>";
};

$p = function ($text) {
    return "<p class=\"mb-2\">$text</p>";
};

?>

<section class="container mt-4 pt-lg-2 pb-3">
    <h2 class="pb-1 mb-2" style="max-width: 970px;">Terms of Service</h2>
    <div class="d-flex flex-md-row flex-column align-items-md-center justify-content-md-between mb-3">
        <div class="d-flex align-items-center flex-wrap text-muted mb-md-0 mb-4">
            <div class="fs-xs border-end pe-3 me-3 mb-2">
                <span class="badge bg-faded-primary text-primary fs-base">Legal</span>
            </div>
            <div class="fs-sm pe-3 me-3 mb-2">01 September, 2025</div>
        </div>
    </div>
</section>

<section class="container mb-5 pt-4 pb-2 py-mg-4">
    <div class="row gy-4">
        <div class="col-12">
            <?= $h2('1. Terms') ?>
            <?= $p('Please read these Terms of Service carefully before using the https://lolboost.gg website.') ?>
            <?= $p('Your access to and use of the Service is conditioned on your acceptance of and compliance with these Terms. These Terms apply to all visitors, users and others who access or use the Service.') ?>
            <?= $p('By accessing or using the Service you agree to be bound by these Terms. If you disagree with any part of the terms then you may not access the Service.') ?>

            <?= $h2('2. Purchases') ?>
            <?= $p('If you wish to purchase any product or service made available through the Service ("Purchase"), you may be asked to supply certain information relevant to your Purchase including, without limitation, your credit card number, the expiration date of your credit card, your billing address, and your shipping information.') ?>
            <?= $p('You represent and warrant that: (i) you have the legal right to use any credit card(s) or other payment method(s) in connection with any Purchase; and that (ii) the information you supply to us is true, correct and complete.') ?>
            <?= $p('By submitting such information, you grant us the right to provide the information to third parties for purposes of facilitating the completion of Purchases.') ?>
            <?= $p('We reserve the right to refuse or cancel your order at any time for reasons including but not limited to: product or service availability, errors in the description or price of the product or service, error in your order or other reasons.') ?>
            <?= $p('We reserve the right to refuse or cancel your order if fraud or an unauthorized or illegal transaction is suspected.') ?>

            <?= $h2('3. Availability, Errors and Inaccuracies') ?>
            <?= $p('We are constantly updating our offerings of products and services on the Service. The products or services available on our Service may be mispriced, described inaccurately, or unavailable, and we may experience delays in updating information on the Service and in our advertising on other web sites.') ?>
            <?= $p('We cannot and do not guarantee the accuracy or completeness of any information, including prices, product images, specifications, availability, and services. We reserve the right to change or update information and to correct errors, inaccuracies, or omissions at any time without prior notice.') ?>

            <?= $h2('4. Accounts') ?>
            <?= $p('When you create an account with us, you must provide us information that is accurate, complete, and current at all times. Failure to do so constitutes a breach of the Terms, which may result in immediate termination of your account on our Service.') ?>
            <?= $p('You are responsible for safeguarding the password that you use to access the Service and for any activities or actions under your password, whether your password is with our Service or a third-party service.') ?>
            <?= $p('You agree not to disclose your password to any third party. You must notify us immediately upon becoming aware of any breach of security or unauthorized use of your account.') ?>
            <?= $p('You may not use as a username the name of another person or entity or that is not lawfully available for use, a name or trademark that is subject to any rights of another person or entity other than you, without appropriate authorization. You may not use as a username any name that is offensive, vulgar or obscene.') ?>

            <?= $h2('5. Customer Duties When Using Or Planning To Use Our Service') ?>
            <?= $p('LB Gaming Services is not associated with Riot Games or any unauthorized entity in any way, shape, or form. LB Gaming Services warns any customer or potential customer to refrain from violating, infringing, or making any illegal action in regards the intellectual property rights of Riot Games or any unauthorized entity.') ?>
            <?= $p('By using our Site or any Service included under LB Gaming Services, You accept and have made yourself aware of all our Terms of use, and You are aware that by purchasing any Service under LB Gaming Services you know what you are paying for, and that the Service you are paying for matches your in-Game information.') ?>
            <?= $p('You accept that by buying our Service you are aware that you cannot dispute any purchase with LB Gaming Services after the Service is done or the service has been partially completed, and that you as a customer cannot violate the rules of ChargeBacks stipulated in any payment method provided by LB Gaming Services.') ?>
            <?= $p('You, the customer, accept that if you open a claim after the order has gone through or the service has started or been completed, you are in direct breach of LB Gaming Services’s terms of use, and legally bound to either close the claim or pay back the same amount in addition to a fee determined by LB Gaming Services, no less than $50 and no more than three times the original amount if the order placed cost more than $50. Should you fail to abide by either option, you, the customer, accept full liability in a court of law as determined by the European Trade Laws.') ?>
            <?= $p('You accept that once your purchase has gone through and is marked as “In Progress”, or a booster or coach has been assigned, you are no longer eligible to receive a refund. If service is not completed by LB Gaming Services you have the right to receive store credit fitting what is missing from your service.') ?>

            <?= $h2('6. Intellectual Property') ?>
            <?= $p('The Service and its original content, features and functionality are and will remain the exclusive property of LB Gaming Services and its licensors. The Service is protected by copyright, trademark, and other laws of both Germany and foreign countries. Our trademarks and trade dress may not be used in connection with any product or service without the prior written consent of LB Gaming Services.') ?>

            <?= $h2('7. Refund Policy') ?>
            <?= $p('Due to the nature of LB Gaming Services’ products (non-tangible digital goods), it is not possible to “return” the product therefore we DO NOT offer refund after purchase is made. If your order has not started you are eligible for a partial refund (55%) or store credit for the amount paid to us.') ?>
            <?= $p('Once the service has started, you are no longer eligible to receive a refund. If service is not completed by LB Gaming Services you have the right to receive store credit fitting what is missing from your service.') ?>
            <?= $p('If we are not able to complete the purchase until the season end, we are only able to refund the remaining part of the order.') ?>

            <?= $h2('8. Links To Other Web Sites') ?>
            <?= $p('Our Service may contain links to third-party web sites or services that are not owned or controlled by LB Gaming Services.') ?>
            <?= $p('LB Gaming Services has no control over, and assumes no responsibility for, the content, privacy policies, or practices of any third party web sites or services. You further acknowledge and agree that LB Gaming Services shall not be responsible or liable, directly or indirectly, for any damage or loss caused or alleged to be caused by or in connection with use of or reliance on any such content, goods or services available on or through any such web sites or services.') ?>
            <?= $p('We strongly advise you to read the terms and conditions and privacy policies of any third-party web sites or services that you visit.') ?>

            <?= $h2('9. Termination') ?>
            <?= $p('We may terminate or suspend access to our Service immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach the Terms.') ?>

            <?= $h2('10. Governing Law') ?>
            <?= $p('These Terms shall be governed and construed in accordance with the laws of Germany, without regard to its conflict of law provisions.') ?>
            <?= $p('Our failure to enforce any right or provision of these Terms will not be considered a waiver of those rights. If any provision of these Terms is held to be invalid or unenforceable by a court, the remaining provisions of these Terms will remain in effect. These Terms constitute the entire agreement between us regarding our Service, and supersede and replace any prior agreements we might have between us regarding the Service.') ?>

            <?= $h2('11. Changes To Service') ?>
            <?= $p('We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material we will try to provide at least 30 days notice prior to any new terms taking effect. What constitutes a material change will be determined at our sole discretion.') ?>
            <?= $p('By continuing to access or use our Service after those revisions become effective, you agree to be bound by the revised terms. If you do not agree to the new terms, please stop using the Service.') ?>

            <?= $h2('12. Contact Us') ?>
            <?= $p('If you have any questions about these Terms, please contact us.') ?>
            <?= $p('Registered Company Name:<br>
            LB Gaming Services') ?>

            <?= $p('Email: admin@lolboost.gg<br>
            Telephone: +49 1522 3458817') ?>

            <?= $h2('13. Game Publisher Rules, Boosting & Risk Disclosure') ?>
            <?= $p('You acknowledge and agree that “boosting”, account sharing, or any similar activity may violate the Terms of Service and/or rules of game publishers, including but not limited to Riot Games for League of Legends. Any use of our Services that contravenes a publisher’s rules is undertaken at your sole risk.') ?>
            <?= $p('LB Gaming Services is not affiliated with, endorsed by, or in any way associated with Riot Games. Riot Games may impose penalties for violations of its Terms, which can include, without limitation: temporary or permanent account suspensions or bans, MMR or rank resets, removal of in-game content or rewards, queue restrictions, or other enforcement actions.') ?>
            <?= $p('By purchasing or using any Service that could be deemed “boosting” or account sharing, you expressly accept and assume the foregoing risks. You further agree that LB Gaming Services shall have no liability for any penalties imposed by Riot Games or any other publisher, including (without limitation) account bans, rank/MMR resets, content removals, or loss of access to your account.') ?>
            <?= $p('No refunds or compensation will be provided where a publisher’s enforcement action occurs during or after the performance of the Service. The use of VPNs, specific play schedules, duo-queue options, or other protective measures does not guarantee avoidance of detection or penalties. We may suspend or terminate the Service if we believe continued performance would increase the risk of enforcement or violate applicable rules.') ?>

            <?= $h2('14. Account Ownership, Age & Authorizations') ?>
            <?= $p('You represent that you are at least 18 years old (or the age of majority in your jurisdiction) and the lawful owner of the game account(s) you provide, or otherwise duly authorized by the lawful owner to engage our Services. You are solely responsible for ensuring that any use of the Services complies with applicable publisher Terms (including Riot Games).') ?>
            <?= $p('You agree to provide accurate login information only where necessary for the provision of the Service, to change your password before and after the Service (if account sharing is used), and to secure your account. LB Gaming Services will not be liable for losses arising from unauthorized access where you fail to follow these instructions.') ?>

            <?= $h2('15. Consumer Right of Withdrawal (EU) for Digital Services') ?>
            <?= $p('If you are a consumer residing in the European Union, you may have a statutory right of withdrawal. By placing an order for digital services to be performed immediately or before the end of the withdrawal period, and by expressly consenting to the commencement of performance and acknowledging the consequent loss of the right of withdrawal upon full performance, you agree that your right of withdrawal will be lost once the Service has been fully performed. If performance has begun but is not yet complete, we may charge you a proportionate amount for the Service already provided.') ?>
        </div>
    </div>
</section>