<style>
    .white-link {
        color: #ffffff !important;
        text-decoration: underline !important;
    }
</style>


<?= $this->layout('emails/layouts/main', ['preheader' => $data['preheader']]) ?>

<?= $title('GG! Your order is complete 🔥') ?>

<?= $p("We appreciate your trust in lolboost.gg.") ?>

<?= $img($data['file_url']) ?>

<?= $p("Happy with your experience? Drop us a quick review, it helps us grow and keeps the quality top-tier for you and others!") ?>

<table width="100%" cellpadding="0" cellspacing="0" border="0"
    style="font-family:'Montserrat',sans-serif; max-width: 600px; margin: 30px auto 10px;">
    <tr>
        <td style="padding: 0 20px;">
            <p style="font-size:18px; font-weight:600; margin-bottom: 10px;">⭐ How did we do? </p>

            <?php
            $starImages = [
                5 => 'https://i.gyazo.com/2781cab48af81c1df7bec7b9a31f20f7.png',
                4 => 'https://i.gyazo.com/27d7e319b91b0e286600b3b1917b22bf.png',
                3 => 'https://i.gyazo.com/573a4441a0b3ec6ba471377ad500c267.png',
                2 => 'https://i.gyazo.com/fd1d94ea44d8886e00827127773de99b.png',
                1 => 'https://i.gyazo.com/ce7ea5def2b9ca74f086e6bb5d9efc0f.png',
            ];

            $trustpilotBaseUrl = 'https://www.trustpilot.com/evaluate/lolboost.gg?stars=';

            foreach ($starImages as $rating => $url) {
                $link = $trustpilotBaseUrl . $rating;
                echo '<p style="margin: 8px 0;">
                  <a href="' . $link . '" target="_blank" style="text-decoration:none; color: inherit;">
                    <img src="' . $url . '" alt="' . $rating . ' star rating" width="200" style="max-height: 36px; border: 0; line-height: 100%; outline: none; text-decoration: none; display: block; vertical-align: middle;">
                  </a>
                </p>';
            }
            ?>
        </td>
    </tr>
</table>

<?= $p('If you need help, we’re here for you via <a class="white-link" href="https://lolboost.gg/contact#open-chat" target="_blank" rel="noopener noreferrer">Website Live-Chat</a> or on our <a class="white-link" href="https://lolboost.gg/discord" target="_blank" rel="noopener noreferrer">Discord Community Server</a>.') ?>


<?= $p('Best regards,<br>The LBGG Team') ?>