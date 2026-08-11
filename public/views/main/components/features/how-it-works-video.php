<style>
.step-number {
  position: relative;
  width: var(--si-steps-number-size);
  height: var(--si-steps-number-size);
  flex-shrink: 0;
  padding-top: calc((var(--si-steps-number-size) - var(--si-steps-number-inner-size)) * 0.5);
  padding-left: calc((var(--si-steps-number-size) - var(--si-steps-number-inner-size)) * 0.5);
  border-radius: var(--si-steps-number-border-radius);
  background-color: var(--si-steps-number-bg);
  color: var(--si-steps-number-color);
  font-size: var(--si-steps-number-font-size);
  font-weight: 800;
  z-index: 2;
}
:root {
    --gradient-color-1: rgba(255, 255, 255, 0.5);
    --gradient-color-2: rgba(0, 0, 0, 0.5);
    --gradient-direction: 45deg;
}

.step-number .step-number-inner {
    display: flex;
    align-items: center;
    justify-content: center;
    width: var(--si-steps-number-inner-size);
    height: var(--si-steps-number-inner-size);
    border-radius: var(--si-steps-number-border-radius);
    background-color: var(--si-steps-number-inner-bg);
    box-shadow: var(--gradient-box-shadow);
}

.gradient-box-shadow {
    --gradient-box-shadow: 10px 10px 15px 5px 
        linear-gradient(
            var(--gradient-direction),
            var(--gradient-color-1),
            var(--gradient-color-2)
        );
}

</style>


<!-- How it works (Steps + Video) -->
<section class="container">
    <div class="text-center pb-4 pb-md-0 mb-2 mb-md-5 mx-auto" style="max-width: 530px;">
        <h2 class="h1">How Does It Work?</h2>
        <p class="mb-0">What happens after the checkout</p>
    </div>

    <!-- Steps -->
    <div class="steps steps-sm steps-horizontal-md steps-center pb-5 mb-md-2 mb-lg-3">
        <div class="step">
        <div class="step-number">
            <div class="step-number-inner">1</div>
        </div>
        <div class="step-body">
            <h3 class="h5 mb-2">Customize Your Boost</h3>
            <p class="mb-0">Use our user-friendly configurator to customize your boost according to your preferences.</p>
        </div>
        </div>
        <div class="step">
        <div class="step-number">
            <div class="step-number-inner">2</div>
        </div>
        <div class="step-body">
            <h3 class="h5 mb-3">Register & Checkout</h3>
            <p class="mb-0">Create an account to access the Order Dashboard and complete your purchase securely.</p>
        </div>
        </div>
        <div class="step">
        <div class="step-number">
            <div class="step-number-inner">3</div>
        </div>
        <div class="step-body">
            <h3 class="h5 mb-3">Enjoy The Service</h3>
            <p class="mb-0">After placing your order, a dedicated booster will be assigned to you. You can communicate directly with your booster throughout the process.</p>
        </div>
        </div>
        <div class="step">
        <div class="step-number">
            <div class="step-number-inner">4</div>
        </div>
        <div class="step-body">
            <h3 class="h5 mb-3">Enjoy Your New Rank</h3>
            <p class="mb-0">Your booster will complete your order promptly. We value your feedback, so please consider leaving a review to share your experience with our service.</p>
        </div>
        </div>
    </div>
</section> 

<!--      <div class="bg-secondary position-relative rounded-3 overflow-hidden px-4 px-sm-5">
        <div class="position-absolute top-50 start-50 w-75 h-75 translate-middle d-flex align-items-center justify-content-center zindex-5">
        <a href="https://www.youtube.com/watch?v=wODsNtortYw" class="btn btn-video btn-icon btn-xl bg-white stretched-link" data-bs-toggle="video">
            <i class="bx bx-play"></i>
        </a>
        </div>
        <div class="pt-4 mt-sm-3 px-3 px-sm-5 mx-md-5">
        <img src="<?=ASSET_URL?>/origin/main/img/landing/saas-3/video-cover.png" width="786" class="rellax d-block mx-auto mt-lg-4" alt="Card" data-rellax-percentage="0.5" data-rellax-speed="1.1" data-disable-parallax-down="lg">
        </div> 
    </div>
</section>  -->