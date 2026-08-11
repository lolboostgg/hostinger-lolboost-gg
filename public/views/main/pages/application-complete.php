<?= $this->layout('main/layouts/main', ['meta' => $meta]) ?>
<?= $this->insert('main/components/heroes/three', ['title' => 'Application Submitted', 'lead' => '', 'banner' => 'Contact.gif']) ?>
<section class="container1 py-5">
    <div class="row pb-5 justify-content-center text-center">
        <div class="col-lg-8">
            <h1 class="display-2 mb-4">Booster Application Submitted! 🎉</h1>
            <p class="fs-xl pb-4 mb-4">
                Thank you for your interest in becoming a booster! Your application has been successfully submitted.
                Our team will review your application and get back to you shortly. In the meantime, feel free to
                explore our community and resources.
            </p>
        </div>
    </div>
</section>