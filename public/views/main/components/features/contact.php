<section class="container pb-5 pt-3">
    <div class="row justify-content-center">
        <div class="col-xxl-8 col-xl-9 col-lg-10 pt-sm-2 pt-md-5">
            <div class="d-sm-flex">
                <div class="d-flex flex-column w-sm-50 border-0 bg-transparent text-center px-sm-1 px-md-5 pb-3 pb-sm-0 mb-4 mb-sm-0">
                    <div class="card-body p-0">
                        <div class="d-inline-block bg-secondary text-primary rounded-circle fs-3 lh-1 p-3 mb-3">
                            <i class="fa-duotone fa-messages"></i>
                        </div>
                        <p class="pb-2 pb-sm-3 mb-3">Feel free to drop us a line. We will respond as soon as possible.</p>
                    </div>
                    <div class="card-footer border-0 p-0">
    <a href="javascript:void(0)" 
       class="btn btn-lg btn-primary"
       onclick="
          if (typeof Tawk_API !== 'undefined') {
              Tawk_API.maximize();
          } else if (typeof tidioChatApi !== 'undefined') {
              tidioChatApi.open();
          } else {
              alert('Chat system not loaded.');
          }
       ">
       <i class="fas fa-comments me-2"></i> Live Chat
    </a>
</div>
                </div>
                <div class="vr text-border d-none d-sm-inline-block m-4"></div>
                <div class="d-flex flex-column w-sm-50 border-0 bg-transparent text-center px-sm-1 px-md-5 pb-3 pb-sm-0 mb-4 mb-sm-0">
                    <div class="card-body p-0">
                        <div class="d-inline-block bg-secondary text-primary rounded-circle fs-3 lh-1 p-3 mb-3">
                            <i class="fa-brands fa-discord"></i>
                        </div>
                        <p class="pb-2 pb-sm-3 mb-3">Join our discord community.<br> We are more active in there.</p>
                    </div>
                    <div class="card-footer border-0 p-0">
                        <a href="<?= BASE_URL ?>/discord" class="btn btn-lg btn-primary">Join Discord</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>