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
    <h2 class="pb-1 mb-2" style="max-width: 970px;">Privacy Policy</h2>
    <div class="d-flex flex-md-row flex-column align-items-md-center justify-content-md-between mb-3">
        <div class="d-flex align-items-center flex-wrap text-muted mb-md-0 mb-4">
            <div class="fs-xs border-end pe-3 me-3 mb-2">
                <span class="badge bg-faded-primary text-primary fs-base">Legal</span>
            </div>
            <div class="fs-sm pe-3 me-3 mb-2">5 January, 2023</div>
        </div>
    </div>
</section>

<section class="container mb-5 pt-4 pb-2 py-mg-4">
    <div class="row gy-4">
        <div class="col-12">
            <?= $h2('Introduction') ?>
            <?= $p('Personal data (usually referred to just as “data” below) will only be processed by us to the extent necessary and for the purpose of providing a functional and user-friendly website, including its contents, and the services offered there.') ?>
            <?= $p('Per Art. 4 No. 1 of Regulation (EU) 2016/679, i.e. the General Data Protection Regulation (hereinafter referred to as the “GDPR”), “processing” refers to any operation or set of operations such as collection, recording, organization, structuring, storage, adaptation, alteration, retrieval, consultation, use, disclosure by transmission, dissemination, or otherwise making available, alignment, or combination, restriction, erasure, or destruction performed on personal data, whether by automated means or not.') ?>
            <?= $p('The following privacy policy is intended to inform you in particular about the type, scope, purpose, duration, and legal basis for the processing of such data either under our own control or in conjunction with others. We also inform you below about the third-party components we use to optimize our website and improve the user experience which may result in said third parties also processing data they collect and control.') ?>
            <?= $p('Our privacy policy is structured as follows:<br>I. Information about us as controllers of your data<br>II. The rights of users and data subjects<br>III. Information about the data processing') ?>
            <?= $h2('1. Information about us as controllers of your data') ?>
            <?= $p('The party responsible for this website (the “controller”) for purposes of data protection law is:') ?>
            <?= $p('LB Gaming Services<br>71-75 Shelton Street<br>Covent Garden<br>WC2H 9JQ London<br>United Kingdom <br>Telephone: +4917665363069<br>Email: support@lolboost.gg') ?>
            <?= $h2('2. The rights of users and data subjects') ?>
            <p>With regard to the data processing to be described in more detail below, users and data subjects have the
                right</p>
            <ul>
                <li>to confirmation of whether data concerning them is being processed, information about the data being
                    processed,
                    further information about the nature of the data processing, and copies of the data (cf. also Art.
                    15 GDPR);
                </li>
                <li>to correct or complete incorrect or incomplete data (cf. also Art. 16 GDPR);</li>
                <li>to the immediate deletion of data concerning them (cf. also Art. 17 DSGVO), or, alternatively, if
                    further
                    processing is necessary as stipulated in Art. 17 Para. 3 GDPR, to restrict said processing per Art.
                    18 GDPR;
                </li>
                <li>to receive copies of the data concerning them and/or provided by them and to have the same
                    transmitted to other
                    providers/controllers (cf. also Art. 20 GDPR);</li>
                <li>to file complaints with the supervisory authority if they believe that data concerning them is being
                    processed
                    by the controller in breach of data protection provisions (see also Art. 77 GDPR).</li>
            </ul>
            <p>In addition, the controller is obliged to inform all recipients to whom it discloses data of any such
                corrections,
                deletions, or restrictions placed on processing the same per Art. 16, 17 Para. 1, 18 GDPR. However, this
                obligation
                does not apply if such notification is impossible or involves a disproportionate effort. Nevertheless,
                users have a
                right to information about these recipients.</p>
            <p>Likewise, under Art. 21 GDPR, users and data subjects have the right to object to the controller’s future
                processing
                of their data pursuant to Art. 6 Para. 1 lit. f) GDPR. In particular, an objection to data processing
                for the
                purpose of direct advertising is permissible.</p>
            <h3>III. Information about the data processing</h3>
            <p>Your data processed when using our website will be deleted or blocked as soon as the purpose for its
                storage ceases
                to apply, provided the deletion of the same is not in breach of any statutory storage obligations or
                unless
                otherwise stipulated below.</p>
            <h4 class="h4 mt-4 mb-2">Server data</h4>
            <p>For technical reasons, the following data sent by your internet browser to us or to our server provider
                will be
                collected, especially to ensure a secure and stable website: These server log files record the type and
                version of
                your browser, operating system, the website from which you came (referrer URL), the webpages on our site
                visited,
                the date and time of your visit, as well as the IP address from which you visited our site.</p>
            <p>The data thus collected will be temporarily stored, but not in association with any other of your data.
            </p>
            <p>The basis for this storage is Art. 6 Para. 1 lit. f) GDPR. Our legitimate interest lies in the
                improvement,
                stability, functionality, and security of our website.</p>
            <p>The data will be deleted within no more than seven days, unless continued storage is required for
                evidentiary
                purposes. In which case, all or part of the data will be excluded from deletion until the investigation
                of the
                relevant incident is finally resolved.</p>
            <h4 class="h4 mt-4 mb-2">Cookies</h4>
            <h5>a) Session cookies</h5>
            <p>We use cookies on our website. Cookies are small text files or other storage technologies stored on your
                computer by
                your browser. These cookies process certain specific information about you, such as your browser,
                location data, or
                IP address. </p>
            <p>This processing makes our website more user-friendly, efficient, and secure, allowing us, for example, to
                display our
                website in different languages or to offer a shopping cart function.</p>
            <p>The legal basis for such processing is Art. 6 Para. 1 lit. b) GDPR, insofar as these cookies are used to
                collect data
                to initiate or process contractual relationships.</p>
            <p>If the processing does not serve to initiate or process a contract, our legitimate interest lies in
                improving the
                functionality of our website. The legal basis is then Art. 6 Para. 1 lit. f) GDPR.</p>
            <p>When you close your browser, these session cookies are deleted.</p>
            <h5>b) Third-party cookies</h5>
            <p>If necessary, our website may also use cookies from companies with whom we cooperate for the purpose of
                advertising,
                analyzing, or improving the features of our website.</p>
            <p>Please refer to the following information for details, in particular for the legal basis and purpose of
                such
                third-party collection and processing of data collected through cookies.</p>
            <h5>c) Disabling cookies</h5>
            <p>You can refuse the use of cookies by changing the settings on your browser. Likewise, you can use the
                browser to
                delete cookies that have already been stored. However, the steps and measures required vary, depending
                on the
                browser you use. If you have any questions, please use the help function or consult the documentation
                for your
                browser or contact its maker for support. Browser settings cannot prevent so-called flash cookies from
                being set.
                Instead, you will need to change the setting of your Flash player. The steps and measures required for
                this also
                depend on the Flash player you are using. If you have any questions, please use the help function or
                consult the
                documentation for your Flash player or contact its maker for support.</p>
            <p>If you prevent or restrict the installation of cookies, not all of the functions on our site may be fully
                usable.</p>
            <h4 class="h4 mt-4 mb-2">Order processing</h4>
            <p>The data you submit when ordering goods and/or services from us will have to be processed in order to
                fulfill your
                order. Please note that orders cannot be processed without providing this data.</p>
            <p>The legal basis for this processing is Art. 6 Para. 1 lit. b) GDPR.</p>
            <p>After your order has been completed, your personal data will be deleted, but only after the retention
                periods
                required by tax and commercial law.</p>
            <p>In order to process your order, we will share your data with the shipping company responsible for
                delivery to the
                extent required to deliver your order and/or with the payment service provider to the extent required to
                process
                your payment.</p>
            <h5>The legal basis for the transfer of this data is Art. 6 Para. 1 lit. b) GDPR.</h5>
            <h4 class="h4 mt-4 mb-2">Customer account/registration</h4>
            <p>If you create a customer account with us via our website, we will use the data you entered during
                registration (e.g.
                your name, your address, or your email address) exclusively for services leading up to your potential
                placement of
                an order or entering some other contractual relationship with us, to fulfill such orders or contracts,
                and to
                provide customer care (e.g. to provide you with an overview of your previous orders or to be able to
                offer you a
                wishlist function). We also store your IP address and the date and time of your registration. This data
                will not be
                transferred to third parties.</p>
            <p>During the registration process, your consent will be obtained for this processing of your data, with
                reference made
                to this privacy policy. The data collected by us will be used exclusively to provide your customer
                account.
            </p>
            <p>If you give your consent to this processing, Art. 6 Para. 1 lit. a) GDPR is the legal basis for this
                processing.</p>
            <p>If the opening of the customer account is also intended to lead to the initiation of a contractual
                relationship with
                us or to fulfill an existing contract with us, the legal basis for this processing is also Art. 6 Para.
                1 lit. b)
                GDPR.</p>
            <p>You may revoke your prior consent to the processing of your personal data at any time under Art. 7 Para.
                3 GDPR with
                future effect. All you have to do is inform us that you are revoking your consent.</p>
            <p>The data previously collected will then be deleted as soon as processing is no longer necessary. However,
                we must
                observe any retention periods required under tax and commercial law.</p>
            <h4 class="h4 mt-4 mb-2">Newsletter</h4>
            <p>If you register for our free newsletter, the data requested from you for this purpose, i.e. your email
                address and,
                optionally, your name and address, will be sent to us. We also store the IP address of your computer and
                the date
                and time of your registration. During the registration process, we will obtain your consent to receive
                this
                newsletter and the type of content it will offer, with reference made to this privacy policy. The data
                collected
                will be used exclusively to send the newsletter and will not be passed on to third parties.</p>
            <p>The legal basis for this is Art. 6 Para. 1 lit. a) GDPR.</p>
            <p>You may revoke your prior consent to receive this newsletter under Art. 7 Para. 3 GDPR with future
                effect. All you
                have to do is inform us that you are revoking your consent or click on the unsubscribe link contained in
                each
                newsletter.</p>
            <h4 class="h4 mt-4 mb-2">Contact</h4>
            <p>If you contact us via email or the contact form, the data you provide will be used for the purpose of
                processing your
                request. We must have this data in order to process and answer your inquiry; otherwise we will not be
                able to answer
                it in full or at all.</p>
            <p>The legal basis for this data processing is Art. 6 Para. 1 lit. b) GDPR.</p>
            <p>Your data will be deleted once we have fully answered your inquiry and there is no further legal
                obligation to store
                your data, such as if an order or contract resulted therefrom.</p>
            <h4 class="h4 mt-4 mb-2">Follow-up comments</h4>
            <p>If you make posts on our website, we also offer you the opportunity to subscribe to any subsequent
                follow-up comments
                made by third parties. In order to be able to inform you about these follow-up comments, we will need to
                process
                your email address.</p>
            <p>The legal basis for this is Art. 6 Para. 1 lit. a) GDPR. You may revoke your prior consent to this
                subscription under
                Art. 7 Para. 3 GDPR with future effect. All you have to do is inform us that you are revoking your
                consent or click
                on the unsubscribe link contained in each email.</p>
            <h4 class="h4 mt-4 mb-2">Contests</h4>
            <p>We offer you the opportunity to take part in contests on our website. If you participate in one of our
                competitions,
                the data you provide when you enter will be processed without your further consent, but only to run the
                contest.</p>
            <p>As part of the competition, we will pass on your data to the transport company entrusted with the
                delivery of the
                goods or to a financial service provider if the transfer is necessary for the delivery or payment of
                your winnings.
                If you win and your information is to be published, you will be informed of this in the declaration of
                consent.</p>
            <p>The legal basis for the transfer of this data is Art. 6 Para. 1 lit. b) GDPR.</p>
            <p>Your consent to the processing of your data for participation in our competitions may be exercised in
                accordance with
                Art. 7 Para. 3 GDPR with future effect. All you have to do is inform us that you are revoking your
                consent.</p>
            <h4 class="h4 mt-4 mb-2">Twitter</h4>
            <p>We maintain an online presence on Twitter to present our company and our services and to communicate with
                customers/prospects. Twitter is a service provided by Twitter Inc., 1355 Market Street, Suite 900, San
                Francisco, CA
                94103, USA.</p>
            <p>We would like to point out that this might cause user data to be processed outside the European Union,
                particularly
                in the United States. This may increase risks for users that, for example, may make subsequent access to
                the user
                data more difficult. We also do not have access to this user data. Access is only available to Twitter.
            </p>
            <p>The privacy policy of Twitter can be found at</p>
            <a target="_blank" href="https://twitter.com/privacy">
                https://twitter.com/privacy
            </a>
            <h4 class="h4 mt-4 mb-2">YouTube</h4>
            <p>We maintain an online presence on YouTube to present our company and our services and to communicate with
                customers/prospects. YouTube is a service of Google Ireland Limited, Gordon House, Barrow Street, Dublin
                4, Ireland,
                a subsidiary of Google LLC, 1600 Amphitheater Parkway, Mountain View, CA 94043 USA.</p>
            <p>We would like to point out that this might cause user data to be processed outside the European Union,
                particularly
                in the United States. This may increase risks for users that, for example, may make subsequent access to
                the user
                data more difficult. We also do not have access to this user data. Access is only available to YouTube.
            </p>
            <p>The YouTube privacy policy can be found here:</p>
            <a target="_blank" href="https://policies.google.com/privacy">
                https://policies.google.com/privacy
            </a>
            <h4 class="h4 mt-4 mb-2">Facebook</h4>
            <p>To advertise our products and services as well as to communicate with interested parties or customers, we
                have a
                presence on the Facebook platform.</p>
            <p>On this social media platform, we are jointly responsible with Facebook Ireland Ltd., 4 Grand Canal
                Square, Grand
                Canal Harbor, Dublin 2, Ireland.</p>
            <p>The data protection officer of Facebook can be reached via this contact form:</p>
            <a target="_blank" href="https://www.facebook.com/help/contact/540977946302970">
                https://www.facebook.com/help/contact/540977946302970
            </a>
            <p>We have defined the joint responsibility in an agreement regarding the respective obligations within the
                meaning of
                the GDPR. This agreement, which sets out the reciprocal obligations, is available at the following link:
            </p>
            <a target="_blank" href="https://www.facebook.com/legal/terms/page_controller_addendum">
                https://www.facebook.com/legal/terms/page_controller_addendum
            </a>
            <p>The legal basis for the processing of the resulting and subsequently disclosed personal data is Art. 6
                para. 1 lit. f
                GDPR. Our legitimate interest lies in the analysis, communication, sales, and promotion of our products
                and
                services.</p>
            <p>The legal basis may also be your consent per Art. 6 para. 1 lit. a GDPR granted to the platform operator.
                Per Art. 7
                para. 3 GDPR, you may revoke this consent with the platform operator at any time with future effect.</p>
            <p>When accessing our online presence on the Facebook platform, Facebook Ireland Ltd. as the operator of the
                platform in
                the EU will process your data (e.g. personal information, IP address, etc.).</p>
            <p>This data of the user is used for statistical information on the use of our company presence on Facebook.
                Facebook
                Ireland Ltd. uses this data for market research and advertising purposes as well as for the creation of
                user
                profiles. Based on these profiles, Facebook Ireland Ltd. can provide advertising both within and outside
                of Facebook
                based on your interests. If you are logged into Facebook at the time you access our site, Facebook
                Ireland Ltd. will
                also link this data to your user account.</p>
            <p>If you contact us via Facebook, the personal data your provide at that time will be used to process the
                request. We
                will delete this data once we have completely responded to your query, unless there are legal
                obligations to retain
                the data, such as for subsequent fulfillment of contracts.</p>
            <p>Facebook Ireland Ltd. might also set cookies when processing your data.</p>
            <p>If you do not agree to this processing, you have the option of preventing the installation of cookies by
                making the
                appropriate settings in your browser. Cookies that have already been saved can be deleted at any time.
                The
                instructions to do this depend on the browser and system being used. For Flash cookies, the processing
                cannot be
                prevented by the settings in your browser, but instead by making the appropriate settings in your Flash
                player. If
                you prevent or restrict the installation of cookies, not all of the functions of Facebook may be fully
                usable.</p>
            <p>Details on the processing activities, their suppression, and the deletion of the data processed by
                Facebook can be
                found in its privacy policy:</p>
            <a target="_blank" href="https://www.facebook.com/privacy/explanation">
                https://www.facebook.com/privacy/explanation
            </a>
            <p>It cannot be excluded that the processing by Facebook Ireland Ltd. will also take place in the United
                States by
                Facebook Inc., 1601 Willow Road, Menlo Park, California 94025.</p>
            <h4 class="h4 mt-4 mb-2">Instagram</h4>
            <p>To advertise our products and services as well as to communicate with interested parties or customers, we
                have a
                presence on the Instagram platform.</p>
            <p>On this social media platform, we are jointly responsible with Facebook Ireland Ltd., 4 Grand Canal
                Square, Grand
                Canal Harbour, Dublin 2 Ireland.</p>
            <p>The data protection officer of Instagram can be reached via this contact form:</p>
            <a target="_blank" href="https://www.facebook.com/help/contact/540977946302970">
                https://www.facebook.com/help/contact/540977946302970
            </a>
            <p>We have defined the joint responsibility in an agreement regarding the respective obligations within the
                meaning of
                the GDPR. This agreement, which sets out the reciprocal obligations, is available at the following link:
            </p>
            <a target="_blank" href="https://www.facebook.com/legal/terms/page_controller_addendum">
                https://www.facebook.com/legal/terms/page_controller_addendum
            </a>
            <p>The legal basis for the processing of the resulting and subsequently disclosed personal data is Art. 6
                para. 1 lit. f
                GDPR. Our legitimate interest lies in the analysis, communication, sales, and promotion of our products
                and
                services.</p>
            <p>The legal basis may also be your consent per Art. 6 para. 1 lit. a GDPR granted to the platform operator.
                Per Art. 7
                para. 3 GDPR, you may revoke this consent with the platform operator at any time with future effect.</p>
            <p>When accessing our online presence on the Instagram platform, Facebook Ireland Ltd. as the operator of
                the platform
                in the EU will process your data (e.g. personal information, IP address, etc.).</p>
            <p>This data of the user is used for statistical information on the use of our company presence on
                Instagram. Facebook
                Ireland Ltd. uses this data for market research and advertising purposes as well as for the creation of
                user
                profiles. Based on these profiles, Facebook Ireland Ltd. can provide advertising both within and outside
                of
                Instagram based on your interests. If you are logged into Instagram at the time you access our site,
                Facebook
                Ireland Ltd. will also link this data to your user account.</p>
            <p>If you contact us via Instagram, the personal data your provide at that time will be used to process the
                request. We
                will delete this data once we have completely responded to your query, unless there are legal
                obligations to retain
                the data, such as for subsequent fulfillment of contracts.</p>
            <p>Facebook Ireland Ltd. might also set cookies when processing your data.</p>
            <p>If you do not agree to this processing, you have the option of preventing the installation of cookies by
                making the
                appropriate settings in your browser. Cookies that have already been saved can be deleted at any time.
                The
                instructions to do this depend on the browser and system being used. For Flash cookies, the processing
                cannot be
                prevented by the settings in your browser, but instead by making the appropriate settings in your Flash
                player. If
                you prevent or restrict the installation of cookies, not all of the functions of Instagram may be fully
                usable.</p>
            <p>Details on the processing activities, their suppression, and the deletion of the data processed by
                Instagram can be
                found in its privacy policy:</p>
            <a target="_blank" href="https://help.instagram.com/519522125107875">
                https://help.instagram.com/519522125107875
            </a>
            <p>It cannot be excluded that the processing by Facebook Ireland Ltd. will also take place in the United
                States by
                Facebook Inc., 1601 Willow Road, Menlo Park, California 94025.</p>
            <h4 class="h4 mt-4 mb-2">Social media links via graphics</h4>
            <p>We also integrate the following social media sites into our website. The integration takes place via a
                linked graphic
                of the respective site. The use of these graphics stored on our own servers prevents the automatic
                connection to the
                servers of these networks for their display. Only by clicking on the corresponding graphic will you be
                forwarded to
                the service of the respective social network.</p>
            <p>Once you click, that network may record information about you and your visit to our site. It cannot be
                ruled out that
                such data will be processed in the United States.</p>
            <p>Initially, this data includes such things as your IP address, the date and time of your visit, and the
                page visited.
                If you are logged into your user account on that network, however, the network operator might assign the
                information
                collected about your visit to our site to your personal account. If you interact by clicking Like,
                Share, etc., this
                information can be stored your personal user account and possibly posted on the respective network. To
                prevent this,
                you need to log out of your social media account before clicking on the graphic. The various social
                media networks
                also offer settings that you can configure accordingly.</p>
            <p>The following social networks are integrated into our site by linked graphics:</p>
            <h4 class="h4 mt-4 mb-2">Facebook</h4>
            <p>Facebook Ireland Limited, 4 Grand Canal Square, Dublin 2, Ireland, a subsidiary of Facebook Inc., 1601 S.
                California
                Ave., Palo Alto, CA 94304, USA.</p>
            <p>Privacy Policy: https://www.facebook.com/policy.php</p>
            <h4 class="h4 mt-4 mb-2">twitter</h4>
            <p>Twitter Inc., 795 Folsom St., Suite 600, San Francisco, CA 94107, USA</p>
            <p>Privacy Policy: https://twitter.com/privacy</p>
            <h4 class="h4 mt-4 mb-2">YouTube</h4>
            <p>Google Ireland Limited, Gordon House, Barrow Street, Dublin 4, Irland, a subsidiary of Google LLC, 1600
                Amphitheatre
                Parkway, Mountain View, CA 94043 USA</p>
            <p>Privacy Policy: https://policies.google.com/privacy</p>
            <h4 class="h4 mt-4 mb-2">Instagram</h4>
            <p>Facebook Ireland Limited, 4 Grand Canal Square, Dublin 2, Ireland, a subsidiary of Facebook Inc., 1601 S.
                California
                Ave., Palo Alto, CA 94304, USA.</p>
            <p>Privacy Policy: https://help.instagram.com/519522125107875</p>
            <h4 class="h4 mt-4 mb-2">Facebook plug-in</h4>
            <p>Our website uses the plug-in of the Facebook social network. Facebook.com is a service provided by
                Facebook Inc.,
                1601 S. California Ave, Palo Alto, CA 94304, USA. In the EU, this service is also operated by Facebook
                Ireland
                Limited, 4 Grand Canal Square, Dublin 2, Ireland, hereinafter both referred to as “Facebook.”</p>
            <p>The legal basis is Art. 6 Para. 1 lit. f) GDPR. Our legitimate interest lies in improving the quality of
                our website.
            </p>
            <p>Further information about the possible plug-ins and their respective functions is available from Facebook
                at</p>
            <a target="_blank" href="https://developers.facebook.com/docs/plugins/">
                https://developers.facebook.com/docs/plugins/
            </a>
            <p>If the plug-in is stored on one of the pages you visit on our website, your browser will download an icon
                for the
                plug-in from Facebook’s servers in the USA. For technical reasons, it is necessary for Facebook to
                process your IP
                address. In addition, the date and time of your visit to our website will also be recorded.</p>
            <p>If you are logged in to Facebook while visiting one of our plugged-in websites, the information collected
                by the
                plug-in from your specific visit will be recognized by Facebook. The information collected may then be
                assigned to
                your personal account at Facebook. If, for example, you use the Facebook Like button, this information
                will be
                stored in your Facebook account and published on the Facebook platform. If you want to prevent this, you
                must either
                log out of Facebook before visiting our website or use an add-on for your browser to prevent the
                Facebook plug-in
                from loading.</p>
            <p>Further information about the collection and use of data as well as your rights and protection options in
                Facebook’s
                privacy policy found at</p>
            <a target="_blank" href="https://www.facebook.com/policy.php">
                https://www.facebook.com/policy.php
            </a>
            <h4 class="h4 mt-4 mb-2">Twitter plug-in</h4>
            <p>Our website uses the plug-in of the Twitter social network. The Twitter service is operated by Twitter
                Inc., 795
                Folsom St., Suite 600, San Francisco, CA 94107, USA (“Twitter”).</p>
            <p>The legal basis is Art. 6 Para. 1 lit. f) GDPR. Our legitimate interest lies in improving the quality of
                our website.
            </p>
            <p>If the plug-in is stored on one of the pages you visit on our website, your browser will download an icon
                for the
                plug-in from Twitter’s servers in the USA. For technical reasons, it is necessary for Twitter to process
                your IP
                address. In addition, the date and time of your visit to our website will also be recorded.</p>
            <p>If you are logged in to Twitter while visiting one of our plugged-in websites, the information collected
                by the
                plug-in from your specific visit will be recognized by Twitter. The information collected may then be
                assigned to
                your personal account at Twitter. If, for example, you use the Twitter Tweet button, this information
                will be stored
                in your Twitter account and may be published on the Twitter platform. To prevent this, you must either
                log out of
                Twitter before visiting our site or make the appropriate settings in your Twitter account.</p>
            <p>Further information about the collection and use of data as well as your rights and protection options in
                Twitter’s
                privacy policy found at</p>
            <a target="_blank" href="https://twitter.com/privacy">
                https://twitter.com/privacy
            </a>
            <h4 class="h4 mt-4 mb-2">Google+ plug-in</h4>
            <p>We use the plug-in of the Google+ social network on our website. Google+ is an online service provided by
                Google
                Ireland Limited, Gordon House, Barrow Street, Dublin 4, Irland (hereinafter: Google).</p>
            <p>The legal basis is Art. 6 Para. 1 lit. f) GDPR. Our legitimate interest lies in improving the quality of
                our website.
            </p>
            <p>Further information about the possible plug-ins and their respective functions is available from Google
                at</p>
            <a target="_blank" href="https://developers.google.com/+/web/">
                https://developers.google.com/+/web/
            </a>
            <p>If the plug-in is stored on one of the pages you visit on our website, your browser will download an icon
                for the
                plug-in from Google’s servers in the USA. For technical reasons, it is necessary for Google to process
                your IP
                address. In addition, the date and time of your visit to our website will also be recorded.</p>
            <p>If you are logged in to Google while visiting one of our plugged-in websites, the information collected
                by the
                plug-in from your specific visit will be recognized by Google. The information collected may then be
                assigned to
                your personal account at Google. If, for example, you use the +1 button, this information will be stored
                in your
                Google Account and may be published on the Google platform. To prevent this, you must either log out of
                Google
                before visiting our site or make the appropriate settings in your Google account.</p>
            <p>Further information about the collection and use of data as well as your rights and protection options in
                Google’s
                privacy policy found at</p>
            <a target="_blank" href="https://policies.google.com/privacy">
                https://policies.google.com/privacy
            </a>
            <h4 class="h4 mt-4 mb-2">Google Analytics</h4>
            <p>We use Google Analytics on our website. This is a web analytics service provided by Google Ireland
                Limited, Gordon
                House, Barrow Street, Dublin 4, Irland (hereinafter: Google).</p>
            <p>The Google Analytics service is used to analyze how our website is used. The legal basis is Art. 6 Para.
                1 lit. f)
                GDPR. Our legitimate interest lies in the analysis, optimization, and economic operation of our site.
            </p>
            <p>Usage and user-related information, such as IP address, place, time, or frequency of your visits to our
                website will
                be transmitted to a Google server in the United States and stored there. However, we use Google
                Analytics with the
                so-called anonymization function, whereby Google truncates the IP address within the EU or the EEA
                before it is
                transmitted to the US.</p>
            <p>The data collected in this way is in turn used by Google to provide us with an evaluation of visits to
                our website
                and what visitors do once there. This data can also be used to provide other services related to the use
                of our
                website and of the internet in general.</p>
            <p>Google states that it will not connect your IP address to other data. In addition, Google provides
                further
                information with regard to its data protection practices at</p>
            <a target="_blank" href="https://www.google.com/intl/de/policies/privacy/partners">
                https://www.google.com/intl/de/policies/privacy/partners
            </a>
            <p>including options you can exercise to prevent such use of your data.</p>
            <p>In addition, Google offers an opt-out add-on at</p>
            <a target="_blank" href="https://tools.google.com/dlpage/gaoptout?hl=en">
                https://tools.google.com/dlpage/gaoptout?hl=en
            </a>
            <p>in addition with further information. This add-on can be installed on the most popular browsers and
                offers you
                further control over the data that Google collects when you visit our website. The add-on informs Google
                Analytics’
                JavaScript (ga.js) that no information about the website visit should be transmitted to Google
                Analytics. However,
                this does not prevent information from being transmitted to us or to other web analytics services we may
                use as
                detailed herein.</p>
            <h4 class="h4 mt-4 mb-2">IONOS Tracking MYWEBSITE</h4>
            <p>We use MyWebsite on our website. This is a service of 1 & 1 IONOS SE, Elgendorfer Str. 57, 56410
                Montabaur,
                hereinafter referred to as “MyWebsite”.</p>
            <p>MyWebsite stores tracking cookies on your device via your internet browser, which are based on the
                Snowplow Analytics
                technology from Snowplow Analytics Limited, 32-38, Scrutton Street, London, United Kingdom.<br>With the
                help of
                these cookies, it can be evaluated e.g. how often you visit our website or which (sub) pages of our
                website are
                accessed. 1 & 1 IONOS SE states that no personal data will be recorded.</p>
            <p>The legal basis is Art. 6 para. 1 lit. f) GDPR. Our legitimate interest lies in the improvement and
                optimisation of our website.</p>
            <p>If you do not agree to this processing, you have the option of preventing the installation of cookies by
                making the
                appropriate settings in your browser. You can find more information on this under “Cookies” above.</p>
            <h4 class="h4 mt-4 mb-2">IONOS WebAnalytics</h4>
            <p>We use WebAnalytics on our website. This is an analysis service from 1 & 1 IONOS SE, Elgendorfer Str. 57,
                56410
                Montabaur, Germany, hereinafter referred to only as “WebAnalytics”, with which we can analyse the use of
                our
                website.</p>
            <p>For analysis, data is collected on the type and version of your internet browser, your operating system,
                the type of
                your device, the website from which you came to our site (referrer URL), the page(s) of our website that
                you visit
                or the files that you request, the date and time of the relevant access and the anonymized IP address of
                the
                internet connection from which the use of our website is made.</p>
            <p>The legal basis is Art. 6 para. 1 lit. f) GDPR. Our legitimate interest lies in the analysis,
                optimisation and
                improvement, as well as the economic operation of our website.</p>
            <h4 class="h4 mt-4 mb-2">Google reCAPTCHA</h4>
            <p>Our website uses Google reCAPTCHA to check and prevent automated servers (“bots”) from accessing and
                interacting with
                our website. This is a service provided by Google Ireland Limited, Gordon House, Barrow Street, Dublin
                4, Irland
                (hereinafter: Google).</p>
            <p>This service allows Google to determine from which website your request has been sent and from which IP
                address the
                reCAPTCHA input box has been used. In addition to your IP address, Google may collect other information
                necessary to
                provide and guarantee this service. </p>
            <p>The legal basis is Art. 6 Para. 1 lit. f) GDPR. Our legitimate interest lies in the security of our
                website and in
                the prevention of unwanted, automated access in the form of spam or similar.</p>
            <p>Google offers detailed information at</p>
            <a target="_blank" href="https://policies.google.com/privacy">
                https://policies.google.com/privacy
            </a>
            <p>concerning the general handling of your user data.</p>
            <h4 class="h4 mt-4 mb-2">GOOGLE Custom Search Engine (“CSE”)</h4>
            <p>For full-text search on the website, we use the Google Custom Search Engine (CSE). CSE is a service of
                Google Ireland
                Limited, Gordon House, Barrow Street, Dublin 4, Irland, hereinafter Google.</p>
            <p>CSE makes it possible to do a full-text search for content on our website. Access to this search function
                is via the
                Google Custom Search search box.</p>
            <p>The legal basis for this processing of data is Art. 6 para. 1 lit. f GDPR. Our legitimate interest is in
                the
                user-friendliness of the website.</p>
            <p>The feature is integrated into website without modification as a software module from Google.<br>If the
                search is
                activated by entering a search term, Google uses a plug-in to load the information you are looking for.
                At the same
                time, the search terms you enter and your IP address are transmitted to Google in order to perform the
                search and
                display the search results.</p>
            <p>If you are logged into your existing Google Account at the time of the search, Google may associate the
                collected
                information with your user profile.</p>
            <p>Google offers further information, in particular your options to prevent this use of your data, at the
                following
                links:</p>
            <p>https://policies.google.com/privacy<br>https://adssettings.google.com/authenticated</p>
            <h4 class="h4 mt-4 mb-2">Mapbox API</h4>
            <p>For directions. we use Mapbox API, a service of Mapbox Inc., 740 15th Street NW, 5th Floor, Washington,
                District of
                Columbia 20005, USA, hereinafter referred to as “Mapbox”.</p>
            <p>The legal basis for collecting and processing this information is Art. 6 Para. 1 lit. f) GDPR. Our
                legitimate
                interest lies in optimizing the functionality of our website.</p>
            <p>When you access one of our pages that includes the Mapbox service, Mapbox stores a cookie on your
                terminal device via
                your browser. The information generated by the cookie about your use of our app including your IP
                address is
                transmitted to a Mapbox server in the USA and stored there. This data is processed for the purpose of
                displaying the
                page or ensuring the functionality of the Mapbox service. Mapbox may share this information with third
                parties where
                required to do so by law or where the information is processed by third parties on behalf of Mapbox.</p>
            <p>The “Terms of Service” provided by Mapbox at https://www.mapbox.com/tos/#maps contain further information
                about the use of Mapbox and the data obtained by Mapbox.</p>
            <p>If you do not agree to this processing, you have the option of preventing the installation of cookies by
                making the
                appropriate settings in your browser. Further details can be found in the section about cookies above.
                However, it
                will then no longer be possible to use the Mapbox service via our website.</p>
            <p>In addition, Mapbox offers further information about how it collects and uses your data, your rights, and
                how to
                protect your privacy at the following link:</p>
            <a target="_blank" href="https://www.mapbox.com/privacy/">
                https://www.mapbox.com/privacy/
            </a>
            <h4 class="h4 mt-4 mb-2">YouTube</h4>
            <p>We use YouTube on our website. This is a video portal operated by YouTube LLC, 901 Cherry Ave, 94066 San
                Bruno, CA,
                USA, hereinafter referred to as “YouTube”.</p>
            <p>YouTube is a subsidiary of Google Ireland Limited, Gordon House, Barrow Street, Dublin 4, Irland,
                hereinafter
                referred to as “Google”.</p>
            <p>We use YouTube in its advanced privacy mode to show you videos. The legal basis is Art. 6 Para. 1 lit. f)
                GDPR. Our
                legitimate interest lies in improving the quality of our website. According to YouTube, the advanced
                privacy mode
                means that the data specified below will only be transmitted to the YouTube server if you actually start
                a video.
            </p>
            <p>Without this mode, a connection to the YouTube server in the USA will be established as soon as you
                access any of our
                webpages on which a YouTube video is embedded.</p>
            <p>This connection is required in order to be able to display the respective video on our website within
                your browser.
                YouTube will record and process at a minimum your IP address, the date and time the video was displayed,
                as well as
                the website you visited. In addition, a connection to the DoubleClick advertising network of Google is
                established.
            </p>
            <p>If you are logged in to YouTube when you access our site, YouTube will assign the connection information
                to your
                YouTube account. To prevent this, you must either log out of YouTube before visiting our site or make
                the
                appropriate settings in your YouTube account.</p>
            <p>For the purpose of functionality and analysis of usage behavior, YouTube permanently stores cookies on
                your device
                via your browser. If you do not agree to this processing, you have the option of preventing the
                installation of
                cookies by making the appropriate settings in your browser. Further details can be found in the section
                about
                cookies above.</p>
            <p>Further information about the collection and use of data as well as your rights and protection options in
                Google’s
                privacy policy found at</p>
            <a target="_blank" href="https://policies.google.com/privacy">
                https://policies.google.com/privacy
            </a>
            <h4 class="h4 mt-4 mb-2">CloudFlare</h4>
            <p>To secure our website and to optimize loading times, we use the CloudFlare CDN (content delivery
                network). This is a
                service of Cloudflare Inc., 101 Townsend Street, San Francisco, California 94107, USA, hereinafter
                referred to as
                “CloudFlare”.</p>
            <p>The legal basis for collecting and processing this information is Art. 6 Para. 1 lit. f) GDPR. Our
                legitimate
                interest lies in the secure operation of our website and in its optimization.</p>
            <p>If you access our website, your queries are forwarded to CloudFlare servers. Statistical access data
                about your visit
                to our website is collected and CloudFlare stores a cookie on your terminal device via your browser.
                Access data
                includes</p>
            <p>– your IP address;</p>
            <p>– the page(s) on our site that you access;</p>
            <p>– type and version of internet browser you are using;</p>
            <p>– your operating system;</p>
            <p>– the website from which you came prior to visiting our website (referrer URL);</p>
            <p>– your length of stay on our site; and </p>
            <p>– the frequency with which our pages are accessed.</p>
            <p>The data is used by CloudFlare for statistical evaluations of the accesses as well as for the security
                and
                optimization of the offer.</p>
            <p>If you do not agree to this processing, you have the option of preventing the installation of cookies by
                making the
                appropriate settings in your browser. Further details can be found in the section about cookies above.
            </p>
            <p>CloudFlare offers further information about its data collection and processing as well your rights and
                your options
                for protecting your privacy at this link:</p>
            <a target="_blank" href="https://www.cloudflare.com/privacypolicy/">
                https://www.cloudflare.com/privacypolicy/
            </a>
            <h4 class="h4 mt-4 mb-2">Google AdWords with Conversion Tracking</h4>
            <p>Our website uses Google AdWords and conversion tracking. This is a service provided by Google Ireland
                Limited, Gordon
                House, Barrow Street, Dublin 4, Irland (hereinafter: Google).</p>
            <p>We use conversion tracking to provide targeted promotion of our site. The legal basis is Art. 6 Para. 1
                lit. f) GDPR.
                Our legitimate interest lies in the analysis, optimization, and economic operation of our site.</p>
            <p>If you click on an ad placed by Google, the conversion tracking we use stores a cookie on your device.
                These
                so-called conversion cookies expire after 30 days and do not otherwise identify you personally.</p>
            <p>If the cookie is still valid and you visit a specific page of our website, both we and Google can
                evaluate that you
                clicked on one of our ads placed on Google and that you were then forwarded to our website.</p>
            <p>The data collected in this way is in turn used by Google to provide us with an evaluation of visits to
                our website
                and what visitors do once there. In addition, we receive information about the number of users who
                clicked on our
                advertisement(s) as well as about the pages on our site that are subsequently visited. Neither we nor
                third parties
                who also use Google AdWords will be able to identify you from this conversion tracking.</p>
            <p>You can also prevent or restrict the installation of cookies by making the appropriate settings in your
                browser.
                Likewise, you can use the browser to delete cookies that have already been stored. However, the steps
                and measures
                required vary, depending on the browser you use. If you have any questions, please use the help function
                or consult
                the documentation for your browser or contact its maker for support.</p>
            <p>In addition, Google provides further information with regard to its data protection practices at</p>
            <a target="_blank" href="https://services.google.com/sitestats/de.html">
                https://services.google.com/sitestats/de.html
            </a>
            <a target="_blank" href="https://www.google.com/policies/technologies/ads/">
                https://www.google.com/policies/technologies/ads/
            </a>
            <p>http://www.google.de/policies/privacy/</p>
            <p>in particular information on how you can prevent the use of your data.</p>
            <h4 class="h4 mt-4 mb-2">Google AdSense</h4>
            <p>We use Google AdSense on our website to integrate advertisements. This is a service provided by Google
                Ireland
                Limited, Gordon House, Barrow Street, Dublin 4, Irland (hereinafter: Google).</p>
            <p>Google AdSense stores cookies and web beacons on your device via your browser. This enables Google to
                analyze how you
                use our website. In addition to your IP address and the advertising formats displayed, the information
                thus
                collected will be transmitted to Google in the USA and stored there. Google may also share this
                information with
                third parties. Google states that it will not connect your IP address to other data.</p>
            <p>The legal basis is Art. 6 Para. 1 lit. f) GDPR. Our legitimate interest lies in the analysis,
                optimization, and
                economic operation of our site.</p>
            <p>If you do not agree to this processing, you have the option of preventing the installation of cookies by
                making the
                appropriate settings in your browser. Further details can be found in the section about cookies above.
            </p>
            <p>In addition, Google offers an opt-out add-on at</p>
            <a target="_blank" href="https://policies.google.com/privacy">
                https://policies.google.com/privacy
            </a>
            <a target="_blank" href="https://adssettings.google.com/authenticated">
                https://adssettings.google.com/authenticated
            </a>
            <p>in particular on options for preventing the use of data.</p>
            <h4 class="h4 mt-4 mb-2">Google Remarketing</h4>
            <p>We use the remarketing function on our website. This is a service provided by Google Ireland Limited,
                Gordon House,
                Barrow Street, Dublin 4, Irland (hereinafter: Google).</p>
            <p>We use this feature to deliver interest-based, personalized advertising on third-party websites that also
                participate
                in Google’s advertising network.</p>
            <p>The legal basis is Art. 6 Para. 1 lit. f) GDPR. Our legitimate interest lies in the analysis,
                optimization, and
                economic operation of our site.</p>
            <p>To allow this advertising service to function, Google stores a cookie with a sequence of numbers on your
                device via
                your browser when you visit our website. This cookie records both your visit and the use of our website
                in anonymous
                form. However, personal data will not be passed on. If you subsequently visit a third-party website that
                also uses
                the Google advertising network, advertising may appear that refers to our website or our offers there.
            </p>
            <p>To permanently disable this feature, Google provides a browser plugin for most common browsers at</p>
            <a target="_blank" href="https://www.google.com/settings/ads/plugin?hl=de">
                https://www.google.com/settings/ads/plugin?hl=de
            </a>
            <p>Likewise, the use of cookies from certain providers, e.g. via</p>
            <a target="_blank" href="https://www.youronlinechoices.com/uk/your-ad-choices">Your ad choices</a>
            <a target="_blank" href="https://www.youronlinechoices.com/uk/your-ad-choices/embed#?secret=WbUmEuX4mp">
                https://www.youronlinechoices.com/uk/your-ad-choices/embed#?secret=WbUmEuX4mp
            </a>
            <p>or</p>
            <p>http://www.networkadvertising.org/choices/</p>
            <p>can be deactivated by opt-out.</p>
            <p>Cross-device marketing allows Google to track your usage patterns across multiple devices, so you may see
                interest-based, personalized advertising even when you switch devices. However, this requires that you
                have agreed
                to link your browsing history to your existing Google account.</p>
            <p>Google offers more information about Google Remarketing at</p>
            <a target="_blank" href="https://www.google.com/privacy/ads/">
                https://www.google.com/privacy/ads/
            </a>
            <h4 class="h4 mt-4 mb-2">affilinet tracking cookies</h4>
            <p>We also advertise third-party offers and services on our website. If you enter into a contract with the
                third party
                after viewing our advertising for these third party offers, we will receive a commission for this
                referral.</p>
            <p>We use the affilinet tracking cookie to record this successful conversion. However, this cookie does not
                store any of
                your personal data. Only our identification number as the affiliate advertiser and the serial number of
                the
                advertising material you clicked on (e.g. a banner or a text link) are recorded. We need this
                information for the
                purpose of processing and/or receiving payment of our commissions.</p>
            <p>If you do not agree to this processing, you have the option of preventing the installation of cookies by
                making the
                appropriate settings in your browser. Further details can be found in the section about cookies above.
            </p>
            <h4 class="h4 mt-4 mb-2">MailChimp – Newsletter</h4>
            <p>We offer you the opportunity to register for our free newsletter via our website.</p>
            <p>We use MailChimp, a service of The Rocket Science Group, LLC, 512 Means Street, Suite 404, Atlanta, GA
                30318, USA,
                hereinafter referred to as “The Rocket Science Group”.</p>
            <p>In addition, the Rocket Science Group offers further information about its data protection practices at
            </p>
            <p>http://mailchimp.com/legal/privacy/</p>
            <p>If you register for our free newsletter, the data requested from you for this purpose, i.e. your email
                address and,
                optionally, your name and address, will be processed by The Rocket Science Group. In addition, your IP
                address and
                the date and time of your registration will be saved. During the registration process, your consent to
                receive this
                newsletter will be obtained together with a concrete description of the type of content it will offer
                and reference
                made to this privacy policy.</p>
            <p>The newsletter then sent out by The Rocket Science Group will also contain a tracking pixel called a web
                beacon. This
                pixel helps us evaluate whether and when you have read our newsletter and whether you have clicked any
                links
                contained therein. In addition to further technical data, such as data about your computer hardware and
                your IP
                address, the data processed will be stored so that we can optimize our newsletter and respond to the
                wishes of our
                readers. The data will therefore increase the quality and attractiveness of our newsletter.</p>
            <p>The legal basis for sending the newsletter and the analysis is Art. 6 Para. 1 lit. a) GDPR.</p>
            <p>You may revoke your prior consent to receive this newsletter under Art. 7 Para. 3 GDPR with future
                effect. All you
                have to do is inform us that you are revoking your consent or click on the unsubscribe link contained in
                each
                newsletter.</p>
            <h4 class="h4 mt-4 mb-2">Sendinblue</h4>
            <p>We offer you the opportunity to register for our free newsletter on our website.</p>
            <p>We use Sendinblue to send newsletters. Sendinblue is a service provided by the company Sendinblue GmbH,
                Köpenicker
                Str. 126, 10179 Berlin, hereinafter referred to as ” Sendinblue “.</p>
            <p>If you sign up to receive our newsletter, the data requested during the registration process (your email
                address)
                will be processed by Sendinblue. For this your IP address and the date of your registration will be
                saved along with
                the time. As a further part of the registration process, your consent to the sending of the newsletter
                will be
                obtained, the content will be described in concrete terms and reference made to this data protection
                declaration.
            </p>
            <p>Additionally at</p>
            <p>https://www.newsletter2go.de/datenschutz/<br>https://www.sendinblue.com/legal/privacypolicy/</p>
            <p>Informationen Newsletter-Empfänger – Sendinblue</p>
            <p>Sendinblue offers further data protection information.</p>
            <p>The newsletters sent by Sendinblue contain technologies by which we can analyse whether and when an email
                was opened
                and whether and which links contained in the newsletter were followed. We save this data in addition to
                the
                technical data (system data and IP address) so that the respective newsletter can be best tailored to
                your wishes
                and interests. The data thus collected is used to continuously improve the quality of our newsletters.
            </p>
            <p>The legal basis for sending the newsletter and the analysis is Art. 6 Para. 1 lit. a.) EU General Data
                Protection
                Regulation (GDPR).</p>
            <p>Consent to the newsletter being sent can be revoked at any time with future effect in accordance with
                Art. 7 Para. 3
                GDPR. To do this, you only have to inform us of your revocation or click the unsubscribe link contained
                in each
                newsletter.</p>
            <h4 class="h4 mt-4 mb-2">LiveChat</h4>
            <p>We use the LiveChat service on our website for analytics purposes and as our live chat system. This is a
                service of
                LiveChat Inc., 1 International Pl, STE 1400 Boston, MA 02110 – 2619, USA, which is operated in the
                European Union by
                LiveChat Software SA. ul. Zwycięska 47, 53 – 033 Wrocław, Poland, hereinafter referred to as “LiveChat”.
            </p>
            <p>The legal basis for collecting and processing this information is Art. 6 Para. 1 lit. f) GDPR. Our
                legitimate
                interest lies in the effective and direct support of our customers and interested parties as well as the
                statistical
                analysis of visitor behavior for the purpose of optimizing and operating our website economically.</p>
            <p>For statistical analysis of visitor behavior and operation of the live chat system, LiveChat stores a
                cookie on your
                terminal device via your browser. This cookie processes the anonymized data and creates a pseudonymized
                user
                profile. However, the data collected will not be used for your personal identification.</p>
            <p>If you do not agree to this processing, you have the option of preventing the installation of cookies by
                making the
                appropriate settings in your browser. Further details can be found in the section about cookies above.
            </p>
            <p>LiveChat offers further information about its data collection and processing as well your rights and your
                options for
                protecting your privacy at this link:</p>
            <a target="_blank" href="#">
                https://www.livechatinc.com/legal/privacy-policy/#main.
            </a>
            <h4 class="h4 mt-4 mb-2">mywebsite-editor.com – 1&1 IONOS SE</h4>
            <p>We use the mywebsite-editor.com service for the functionality of our website. This is a service of 1 & 1
                IONOS SE, Elgendorfer Str. 57, 56410 Montabaur, Germany, hereinafter referred to as “mywebsite-editor”.
            </p>
            <p>Due to the integration of mywebsite-editor, your internet browser will load a mandatory JavaScript code
                from the
                mywebsite-editor server to display the content of our website. This gives mywebsite-editor confirmation
                that
                our website has been accessed via your IP address. At the same time, a so-called session cookie is
                stored on
                your device via your internet browser.</p>
            <p>The legal basis for data processing is Art. 6 para. 1 lit. f) GDPR. Our legitimate interest lies in the
                presentation
                of a uniform and appealing presentation of our website.</p>
            <p>To totally prevent the execution of mywebsite-editor’s JavaScript code, you can install a so-called
                JavaScript
                blocker, such as noscript.net or ghostery.com. . You can also deactivate the execution of the Java
                Script code in
                the settings of your Internet browser.</p>
            <p>If you do not agree to the processing of cookies, you have the option of preventing the installation of
                cookies by
                making the appropriate settings in your browser. You can find more information on this under “Cookies”
                above.</p>
            <h4 class="h4 mt-4 mb-2">Klarna „CHECK-OUT“</h4>
            <p>To process orders through our online shop, we use the payment service of Klarna Bank AB, Sveavägen 46,
                111 34
                Stockholm, Sweden, hereinafter referred to as “Klarna”, on our website.</p>
            <p>For this purpose, we have integrated Klarna’s check-out into the final order page of our online shop.</p>
            <p>The legal basis is the fulfilment of the contract according to Art. 6 Para. 1 lit. b.) EU General Data
                Protection Regulation (GDPR). In addition, we have a legitimate interest in offering effective and
                secure payment
                options, so that another legal basis ensues from Art. 6 para. 1 lit f.) GDPR. </p>
            <p>By integrating Klarna, your internet browser loads the check-out page from a Klarna server. This means
                that the
                operating system you are using, type and version of your Internet browser, website from which the
                check-out has been
                requested, date and time of the call and the IP address are sent to Klarna – even without your
                interaction with the
                check-out page.</p>
            <p>As soon as you complete the order in our online shop, the data you have entered in the input fields of
                the check-out
                page will be processed by Klarna at your own responsibility in order to process the payment.</p>
            <p>With the offered payment methods “PayPal” and “Advance Payment”, processing without your further consent
                is limited
                to the transfer of the payment data to us or PayPal.</p>
            <p>With the offered payment methods of “Purchase on Account”, “Hire Purchase”, “Credit Card”, “Direct Debit”
                or
                “Immediate Payment”, the following personal data is processed by Klarna for the purpose of payment
                processing and
                for identity and credit checking:</p>
            <p>– Contact information such as names, addresses, date of birth, gender, email address, telephone number,
                mobile phone
                number, IP address, etc.</p>
            <p>– Information on the processing of the order, such as product type, product number, price, etc.</p>
            <p>– Payment information, such as debit and credit card data (card number, expiry date and CCV code),
                invoice data,
                account number, etc.</p>
            <p>If you choose the payment method “Purchase on Account” or “Hire Purchase”, Klarna collects and uses
                personal data and
                information about your previous payment behaviour to decide whether you will be granted the desired
                payment method.
                In addition, probability values for your future payment behaviour (so-called scoring) are used. Scoring
                is
                calculated on the basis of scientifically recognized mathematical and statistical methods.</p>
            <p>At
                <a target="_blank"
                    href="https://cdn.klarna.com/1.0/shared/content/policy/data/de_de/data_protection.pdf">https://cdn.klarna.com/1.0/shared/content/policy/data/de_de/data_protection.pdf</a>
            </p>
            <p>Klarna provides further information on the processing described above as well as the applicable data
                protection
                regulations.</p>


        </div>
    </div>
</section>