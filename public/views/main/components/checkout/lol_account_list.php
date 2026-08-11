<div class="d-flex flex-wrap justify-content-start gap-2 align-items-center">
    <ul class="list-unstyled ms-3">
        <?php if ($data['delivery_type'] == 'instant'): ?>
            <li>
                <i class="fas fa-badge-check text-primary me-2 mb-2"></i>
                Ready to play in seconds
            </li>
            <li>
                <i class="fas fa-badge-check text-primary me-2 mb-2"></i>
                Full access (email & password changeable)
            </li>
            <li>
                <i class="fas fa-badge-check text-primary me-2 mb-2"></i>
                Free warranty and support
            </li>
        <?php else: ?>
            <li>
                <i class="fas fa-badge-check text-primary me-2 mb-2"></i>
                Secure manual delivery process
            </li>
            <li>
                <i class="fas fa-badge-check text-primary me-2 mb-2"></i>
                Claim via Live Chat for fastest access
            </li>
            <li>
                <i class="fas fa-badge-check text-primary me-2 mb-2"></i>
                Login details also sent to your email within 60 minutes
            </li>
        <?php endif; ?>
    </ul>
</div>