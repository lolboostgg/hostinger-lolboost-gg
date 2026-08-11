<?= $this->layout('website/layouts/checkout', ['meta' => $meta, 'bodyClass' => 'checkout ' . (CLIENT_DATA == false ? 'checkout-guest' : 'checkout-authenticated')]) ?>

<?php
$paypal_limit_cents = 3000;
$paypal_available = true;
?>


<style>
.trustpilot-banner--chip {
    display: flex;
    justify-content: center;
    margin-top: 14px;
    text-decoration: none;
    width: 100%;
}

.trustpilot-banner--chip:hover,
.trustpilot-banner--chip:active,
.trustpilot-banner--chip:visited {
    text-decoration: none;
    color: inherit;
}

.tpBadge--summary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 10px 16px;
    min-height: 46px;
    width: auto;
    max-width: 100%;
    border-radius: 999px;
    background: rgba(255, 255, 255, .05);
    border: 1px solid rgba(255, 255, 255, .10);
    box-shadow: 0 8px 24px rgba(0, 0, 0, .14);
    transition: transform .16s ease, border-color .16s ease, background .16s ease;
}

.trustpilot-banner--chip:hover .tpBadge--summary {
    transform: translateY(-1px);
    border-color: rgba(124, 107, 255, .28);
    background: rgba(124, 107, 255, .10);
}

.tpBadge__excellent {
    font-weight: 800;
    color: rgba(255, 255, 255, .96);
    font-size: 14px;
    line-height: 1;
    white-space: nowrap;
}

.tpBadge__stars {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    flex-shrink: 0;
}

.tpBadge__stars i {
    font-size: 12px;
    color: #f5c542;
}

.tpBadge__reviews {
    color: rgba(255, 255, 255, .78);
    font-weight: 700;
    font-size: 13px;
    line-height: 1;
    white-space: nowrap;
}

.tpBadge__tpIcon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #00b67a;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    line-height: 1;
    flex-shrink: 0;
}

@media (max-width: 768px) {
    .trustpilot-banner--chip {
        margin-top: 16px;
    }

    .tpBadge--summary {
        min-height: 50px;
        padding: 12px 16px;
        gap: 10px;
        border-radius: 999px;
    }

    .tpBadge__excellent {
        font-size: 14px;
    }

    .tpBadge__reviews {
        font-size: 13px;
    }

    .tpBadge__stars i {
        font-size: 12px;
    }

    .tpBadge__tpIcon {
        width: 30px;
        height: 30px;
        font-size: 15px;
    }
}


.payment-methods label.payment-method-disabled {
    display: block;
    cursor: not-allowed;
}

.payment-methods label.payment-method-disabled .method-btn {
    opacity: 0.55;
    filter: grayscale(0.15);
    cursor: not-allowed;
    border: 1px dashed rgba(255, 255, 255, 0.14);
}

.payment-methods label.payment-method-disabled .custom-radio {
    pointer-events: none;
}

.payment-method-unavailable-note {
    margin-top: 8px;
    font-size: 12px;
    line-height: 1.45;
    color: rgba(255, 255, 255, 0.72);
}

.payment-method-unavailable-note strong {
    color: #fff;
}

html.refund-modal-open,
body.refund-modal-open {
    overflow: hidden !important;
    touch-action: none;
    overscroll-behavior: none;
}

.refund-confirm-modal {
    position: fixed;
    inset: 0;
    z-index: 999999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 24px;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.refund-confirm-modal.is-open {
    display: flex;
}

.refund-confirm-modal__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(4, 6, 20, 0.78);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

.refund-confirm-modal__dialog {
    position: relative;
    width: min(580px, calc(100% - 32px));
    max-width: 580px;
    max-height: min(760px, calc(100vh - 48px));
    overflow-y: auto;
    margin: 0 auto;
    background:
        radial-gradient(circle at top right, rgba(109, 93, 252, 0.22), transparent 34%),
        linear-gradient(180deg, #17193d 0%, #10122d 100%);
    border: 1px solid rgba(123, 108, 255, .24);
    border-radius: 24px;
    padding: 28px;
    color: #fff;
    box-shadow: 0 24px 80px rgba(0, 0, 0, .50);
    z-index: 2;
    -webkit-overflow-scrolling: touch;
}

.refund-confirm-modal__header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 14px;
}

.refund-confirm-modal__header-icon {
    width: 48px;
    height: 48px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(123, 108, 255, 0.28), rgba(97, 114, 255, 0.14));
    border: 1px solid rgba(152, 138, 255, 0.34);
    color: #b6abff;
    font-size: 18px;
    flex-shrink: 0;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.08), 0 10px 24px rgba(76, 63, 180, 0.18);
}

.refund-confirm-modal__title {
    margin: 0;
    font-size: 28px;
    font-weight: 800;
    color: rgba(255, 255, 255, .98);
    letter-spacing: -0.02em;
}

.refund-confirm-modal__intro {
    margin: 0 0 18px;
    line-height: 1.6;
    color: rgba(255, 255, 255, .82);
    font-size: 15px;
}

.refund-warning {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 15px 16px;
    border-radius: 16px;
    background: linear-gradient(180deg, rgba(255, 191, 90, 0.14), rgba(255, 191, 90, 0.06));
    border: 1px solid rgba(255, 191, 90, 0.24);
    margin-bottom: 18px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
}

.refund-warning--simple {
    margin-bottom: 16px;
}

.refund-warning__icon {
    width: 30px;
    height: 30px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 191, 90, 0.16);
    color: #ffcf70;
    font-size: 13px;
    line-height: 1;
    margin-top: 1px;
    flex-shrink: 0;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.06);
}

.refund-warning__text {
    color: rgba(255, 247, 229, 0.96);
    font-size: 14px;
    line-height: 1.55;
    font-weight: 700;
}

.refund-confirm-checkbox {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-top: 16px;
    padding: 18px 18px 18px 16px;
    border-radius: 18px;
    background: linear-gradient(180deg, rgba(255, 255, 255, .07), rgba(255, 255, 255, .04));
    border: 1px solid rgba(255, 255, 255, .10);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
    cursor: pointer;
    transition: all .2s ease;
}

.refund-confirm-checkbox:hover {
    border-color: rgba(123, 108, 255, 0.28);
    background: linear-gradient(180deg, rgba(255, 255, 255, .08), rgba(255, 255, 255, .05));
}

.refund-confirm-checkbox input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.refund-confirm-checkbox__box {
    width: 22px;
    height: 22px;
    margin-top: 2px;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.22);
    background: rgba(255, 255, 255, 0.04);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all .2s ease;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
}

.refund-confirm-checkbox__box i {
    font-size: 11px;
    color: #ffffff;
    opacity: 0;
    transform: scale(0.7);
    transition: all .18s ease;
}

.refund-confirm-checkbox input:checked + .refund-confirm-checkbox__box {
    background: linear-gradient(135deg, #6d5dfc 0%, #8b5cf6 100%);
    border-color: rgba(139, 92, 246, 0.8);
    box-shadow: 0 10px 20px rgba(109, 93, 252, 0.24);
}

.refund-confirm-checkbox input:checked + .refund-confirm-checkbox__box i {
    opacity: 1;
    transform: scale(1);
}

.refund-confirm-checkbox span {
    font-size: 14px;
    line-height: 1.6;
    color: rgba(255, 255, 255, .94);
    font-weight: 600;
}

.refund-confirm-modal__saved {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 12px;
    font-size: 12px;
    color: rgba(255, 255, 255, .62);
}

.refund-confirm-modal__saved i {
    color: rgba(182, 171, 255, 0.9);
}

.refund-confirm-modal__close {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 42px;
    height: 42px;
    border: 0;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, .07);
    color: rgba(255, 255, 255, .78);
    cursor: pointer;
    transition: all .2s ease;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
}

.refund-confirm-modal__close:hover {
    background: rgba(255, 255, 255, .12);
    color: #fff;
    transform: translateY(-1px);
}

.refund-confirm-modal__actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
}

.refund-confirm-modal__actions .refund-confirm-modal__btn {
    min-width: 130px;
}

.refund-confirm-modal__btn {
    border: 0;
    border-radius: 16px;
    min-height: 50px;
    padding: 0 22px;
    font-weight: 800;
    cursor: pointer;
    transition: all .2s ease;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.05);
}

.refund-confirm-modal__btn--secondary {
    background: rgba(255, 255, 255, .08);
    color: #fff;
}

.refund-confirm-modal__btn--secondary:hover {
    background: rgba(255, 255, 255, .14);
    transform: translateY(-1px);
}

.refund-confirm-modal__btn--primary {
    background: linear-gradient(90deg, #6d5dfc 0%, #8b5cf6 100%);
    color: #fff;
}

.refund-confirm-modal__btn--primary:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 10px 24px rgba(109, 93, 252, 0.30);
}

.refund-confirm-modal__btn--primary:disabled {
    opacity: .45;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    html.refund-modal-open,
    body.refund-modal-open {
        height: 100%;
    }

    .refund-confirm-modal {
        align-items: flex-end;
        justify-content: center;
        padding:
            max(12px, env(safe-area-inset-top))
            12px
            max(12px, env(safe-area-inset-bottom))
            12px;
        width: 100vw;
        height: 100vh;
        height: 100dvh;
        min-height: 100vh;
        min-height: 100dvh;
    }

    .refund-confirm-modal__dialog {
        width: 100%;
        max-width: 100%;
        max-height: calc(100dvh - env(safe-area-inset-top) - env(safe-area-inset-bottom) - 24px);
        border-radius: 20px;
        padding: 18px 16px;
    }

    .refund-confirm-modal__header {
        gap: 12px;
        margin-bottom: 10px;
        padding-right: 44px;
    }

    .refund-confirm-modal__header-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        font-size: 16px;
    }

    .refund-confirm-modal__title {
        font-size: 20px;
    }

    .refund-confirm-modal__intro {
        font-size: 14px;
        margin-bottom: 14px;
    }

    .refund-warning {
        padding: 12px 14px;
        margin-bottom: 14px;
    }

    .refund-warning__icon {
        width: 24px;
        height: 24px;
        font-size: 11px;
    }

    .refund-confirm-checkbox {
        margin-top: 14px;
        padding: 14px;
        gap: 12px;
    }

    .refund-confirm-checkbox__box {
        width: 20px;
        height: 20px;
        border-radius: 7px;
    }

    .refund-confirm-checkbox span:last-child {
        font-size: 13px;
        line-height: 1.5;
    }

    .refund-confirm-modal__close {
        top: 12px;
        right: 12px;
        width: 38px;
        height: 38px;
        border-radius: 12px;
    }

    .refund-confirm-modal__actions {
        margin-top: 18px;
    }

    .refund-confirm-modal__actions {
        flex-direction: column;
    }

    .refund-confirm-modal__btn {
        width: 100%;
    }
}

@supports not (height: 100dvh) {
    @media (max-width: 768px) {
        .refund-confirm-modal {
            height: 100vh;
            min-height: 100vh;
        }

        .refund-confirm-modal__dialog {
            max-height: calc(100vh - 24px);
        }
    }
}


/* =========================================================
   Checkout clarity redesign — cleaner sections, better spacing
   ========================================================= */
.checkout {
  position: relative;
  isolation: isolate;
  overflow-x: hidden;
  overflow-y: visible;
  min-height: 100vh;
  background:
    radial-gradient(circle at 82% 78%, rgba(95, 120, 255, .10), transparent 32%),
    radial-gradient(circle at 26% 92%, rgba(93, 180, 132, .09), transparent 28%),
    linear-gradient(180deg, #111022 0%, #0b0b18 56%, #090b16 100%);
}

.checkout::before,
.checkout::after {
  content: '';
  position: fixed;
  inset: 0;
  pointer-events: none;
  z-index: -1;
}

.checkout::before {
  background:
    radial-gradient(115% 82% at 104% 20%, transparent 0 35%, rgba(255,255,255,.035) 35.4% 35.9%, transparent 36.3%),
    radial-gradient(100% 76% at 102% 22%, transparent 0 46%, rgba(255,255,255,.026) 46.3% 46.8%, transparent 47.2%),
    radial-gradient(88% 68% at 102% 24%, transparent 0 58%, rgba(255,255,255,.018) 58.2% 58.7%, transparent 59.1%),
    linear-gradient(110deg, rgba(255,255,255,.025), transparent 22% 100%);
  opacity: .95;
}

.checkout::after {
  background:
    radial-gradient(circle at 34% 100%, rgba(94, 139, 120, .18), transparent 28%),
    radial-gradient(circle at 76% 12%, rgba(91, 86, 255, .18), transparent 20%),
    radial-gradient(circle at 84% 24%, rgba(92, 94, 255, .08), transparent 30%);
  filter: blur(14px);
  opacity: .85;
}

/* prevent the decorative background from blocking page scroll */
html,
body.checkout {
  overflow-y: auto !important;
  min-height: 100% !important;
}


.checkout .header {
  width: min(92vw, 1180px);
  margin: clamp(150px, 11vw, 210px) auto clamp(44px, 4vw, 68px) !important;
  text-align: center;
}

.checkout .header h1 {
  font-size: clamp(42px, 3.3vw, 66px) !important;
  line-height: .95 !important;
  letter-spacing: .015em;
  margin-bottom: 18px;
}

.checkout .header h5 {
  font-size: clamp(14px, .95vw, 17px) !important;
  color: rgba(255,255,255,.76);
  line-height: 1.55 !important;
}

.checkout .checkout-steps {
  margin: 22px auto 0;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.checkout .checkout-step {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  border-radius: 999px;
  background: rgba(99,102,241,.10);
  border: 1px solid rgba(99,102,241,.24);
  color: rgba(226,232,255,.86);
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .055em;
}

.checkout .checkout-step i { color: #8b8cf5; font-size: 12px; }
.checkout .checkout-step.is-active {
  background: linear-gradient(135deg, rgba(99,102,241,.34), rgba(124,58,237,.22));
  border-color: rgba(154,150,255,.50);
  color: #fff;
  box-shadow: 0 12px 34px rgba(99,102,241,.18);
}

.checkout .main-content {
  width: min(92vw, 1320px);
  margin: 0 auto clamp(72px, 6vw, 110px) !important;
  padding-bottom: 0 !important;
  display: grid;
  grid-template-columns: minmax(360px, .92fr) minmax(420px, 1.08fr) !important;
  gap: clamp(22px, 2vw, 34px) !important;
  align-items: start;
}

.checkout .card {
  padding: clamp(24px, 1.8vw, 34px) !important;
  border-radius: 28px !important;
  background:
    linear-gradient(180deg, rgba(255,255,255,.035), rgba(255,255,255,.012)),
    rgba(13,12,29,.82) !important;
  border: 1px solid rgba(99,102,241,.16) !important;
  box-shadow: 0 24px 80px rgba(0,0,0,.22), inset 0 1px 0 rgba(255,255,255,.04);
}

.checkout .main-content .right .card {
  position: sticky;
  top: clamp(92px, 7vw, 132px);
}

.checkout .main-content .left h3,
.checkout .main-content .right h3 {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0 0 18px !important;
  padding-bottom: 14px;
  border-bottom: 1px solid rgba(255,255,255,.08);
  color: rgba(255,255,255,.96);
  font-size: clamp(18px, 1.1vw, 22px) !important;
  font-weight: 800 !important;
  line-height: 1.25;
}

.checkout .main-content .left h3:not(:first-child) {
  margin-top: 28px !important;
}

.checkout .main-content .left h3::before,
.checkout .main-content .right h3::before {
  content: '';
  width: 4px;
  height: 18px;
  border-radius: 999px;
  background: linear-gradient(180deg, #8b8cf5, #6366f1);
  box-shadow: 0 0 18px rgba(99,102,241,.75);
}

.checkout .main-content .right h3 img {
  width: 22px !important;
  height: auto;
  opacity: .9;
}
.checkout .main-content .right h3::before { content: none; }

.checkout .main-content .left .buttons {
  gap: 12px !important;
  margin-bottom: 28px !important;
}

.checkout .main-content .left .buttons .btn {
  min-height: 48px;
  padding: 12px 16px !important;
  border-radius: 14px !important;
  background: rgba(10,10,24,.72) !important;
  border: 1px solid rgba(99,102,241,.22) !important;
  box-shadow: none;
  font-size: 14px !important;
}
.checkout .main-content .left .buttons .btn:hover {
  background: rgba(99,102,241,.16) !important;
  border-color: rgba(139,140,245,.45) !important;
}
.checkout .main-content .left .buttons .btn::after { content: none !important; }
.checkout .main-content .left .buttons .btn img { width: 15px !important; }

.checkout .main-content .left .payment-methods {
  gap: 12px !important;
  display: grid;
}

.checkout .main-content .left .payment-methods label { display: block; }
.checkout .main-content .left .payment-methods .method-btn {
  min-height: 64px;
  padding: 14px 16px !important;
  border-radius: 16px !important;
  background: rgba(6,6,18,.72) !important;
  border: 1px solid rgba(255,255,255,.07);
  transition: transform .16s ease, border-color .16s ease, background .16s ease, box-shadow .16s ease;
}
.checkout .main-content .left .payment-methods .method-btn:hover {
  transform: translateY(-1px);
  border-color: rgba(139,140,245,.32);
  background: rgba(99,102,241,.08) !important;
}
.checkout .main-content .left .payment-methods .custom-radio:checked + .method-btn {
  background: linear-gradient(135deg, rgba(99,102,241,.18), rgba(99,102,241,.08)) !important;
  border-color: rgba(139,140,245,.55) !important;
  box-shadow: 0 16px 38px rgba(99,102,241,.12), inset 0 1px 0 rgba(255,255,255,.05);
}
.checkout .main-content .left .payment-methods .method-btn .checkmark {
  gap: 14px !important;
  font-size: 15px !important;
  font-weight: 800;
}
.checkout .main-content .left .payment-methods .method-btn .checkmark span {
  width: 20px !important;
  height: 20px !important;
  border-width: 1px !important;
  flex-shrink: 0;
}
.checkout .main-content .left .payment-methods .method-btn img {
  max-height: 24px !important;
  height: auto !important;
}
.checkout .main-content .left .payment-methods .method-btn > img {
  max-width: 210px;
  object-fit: contain;
}
.checkout .payment-method-unavailable-note {
  margin: 8px 4px 0 !important;
  color: rgba(255,255,255,.55) !important;
}

.checkout .summary .rank-box {
  margin: 0 0 18px !important;
  padding: 18px 20px !important;
  border-radius: 20px !important;
  background:
    radial-gradient(circle at 50% 0%, rgba(99,102,241,.14), transparent 56%),
    rgba(6,6,18,.78) !important;
  border: 1px solid rgba(255,255,255,.07);
}
.checkout .summary .rank-box .from img,
.checkout .summary .rank-box .to img {
  height: 42px !important;
  margin-bottom: 6px !important;
}
.checkout .summary .rank-box .title,
.checkout .summary .rank-box .game,
.checkout .summary .rank-box .count {
  font-size: 14px !important;
  font-weight: 800 !important;
}
.checkout .summary .rank-box small { color: rgba(255,255,255,.58) !important; }
.checkout .summary .rank-box > img { width: 18px !important; opacity: .55; }

.checkout .summary .order-options {
  gap: 10px !important;
  margin: 18px 0 22px !important;
}
.checkout .summary .order-options .option {
  display: flex !important;
  flex-direction: row !important;
  align-items: center !important;
  justify-content: space-between !important;
  gap: 14px;
  padding: 12px 14px;
  border-radius: 14px;
  background: rgba(255,255,255,.035);
  border: 1px solid rgba(255,255,255,.065);
  text-align: left;
}
.checkout .summary .order-options .option .title,
.checkout .summary .order-options .option .value {
  font-size: 14px !important;
  line-height: 1.35;
}
.checkout .summary .order-options .option .title {
  color: rgba(255,255,255,.82);
  font-weight: 700;
  text-align: left;
  flex: 0 0 auto;
}
.checkout .summary .order-options .option .value {
  color: rgba(165,168,255,.95) !important;
  font-weight: 800;
  text-align: right;
  margin-left: auto;
}
.checkout .summary .order-options .option .title img {
  width: 16px !important;
  height: 16px !important;
}

.checkout .summary #lbbp-v5,
.checkout .summary textarea,
.checkout .summary .discount-input,
.checkout .summary .discount-applied {
  border-radius: 16px !important;
}
.checkout .summary textarea {
  background: rgba(6,6,18,.76) !important;
  border: 1px solid rgba(255,255,255,.06) !important;
}
.checkout .main-content .right .summary .discount-input {
  margin: 18px 0 14px !important;
  min-height: 54px;
  background: rgba(6,6,18,.76) !important;
  border: 1px solid rgba(255,255,255,.07);
}
.checkout .main-content .right .summary .discount-input input {
  width: 100%;
  font-size: 14px !important;
  padding: 14px 16px !important;
}
.checkout .main-content .right .summary .discount-input button {
  margin-right: 6px;
  padding: 10px 16px !important;
  border-radius: 12px !important;
  font-weight: 800;
}
.checkout .main-content .right .summary .discount-applied {
  margin: 20px 0 !important;
  padding: 14px 16px !important;
  background: rgba(34,197,94,.08) !important;
  border-color: rgba(34,197,94,.25) !important;
  color: #86efac;
  font-weight: 800;
}

.checkout .main-content .right .summary .totals-section {
  margin-top: 18px;
  padding: 22px 0 18px !important;
  border-top: 1px solid rgba(255,255,255,.09) !important;
}
.checkout .main-content .right .summary .totals-section .item {
  padding: 6px 0;
}
.checkout .main-content .right .summary .totals-section .item .label {
  font-size: 15px !important;
  color: rgba(255,255,255,.64) !important;
}
.checkout .main-content .right .summary .totals-section .item .value,
.checkout .main-content .right .summary .totals-section .item #total-text {
  font-size: clamp(24px, 1.7vw, 32px) !important;
  color: #fff !important;
  font-weight: 900 !important;
}
.checkout .main-content .right .summary #complete_payment {
  min-height: 58px;
  padding: 15px 22px !important;
  border-radius: 18px !important;
  font-size: 16px !important;
  font-weight: 900 !important;
  background: linear-gradient(135deg, #6366f1, #7c3aed) !important;
  box-shadow: 0 18px 42px rgba(99,102,241,.24);
  transition: transform .16s ease, box-shadow .16s ease;
}
.checkout .main-content .right .summary #complete_payment:hover {
  transform: translateY(-1px);
  box-shadow: 0 22px 48px rgba(99,102,241,.32);
}
.checkout .main-content .right .summary .checkout-trust-line {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  margin-top: 12px;
  font-size: 12.5px;
  font-weight: 600;
  color: rgba(255,255,255,.5);
  letter-spacing: .01em;
}
.checkout .main-content .right .summary .checkout-trust-line i {
  color: rgba(139,140,245,.75);
  font-size: 12px;
}
.checkout .main-content .left .payment-methods .badge-primary[title] {
  cursor: help;
}

.checkout .trustpilot-banner--chip { margin-top: 18px !important; }
.checkout .tpBadge--summary {
  min-height: 42px !important;
  padding: 9px 14px !important;
  background: rgba(255,255,255,.04) !important;
  border-color: rgba(255,255,255,.08) !important;
}

@media (max-width: 980px) {
  .checkout .header {
    margin: 116px auto 34px !important;
    padding: 0 20px;
  }
  .checkout .main-content {
    width: calc(100% - 32px);
    grid-template-columns: 1fr !important;
    gap: 18px !important;
  }
  .checkout .main-content .right .card { position: static; }
  .checkout .card { border-radius: 22px !important; padding: 20px !important; }
  .checkout .main-content .left .buttons { flex-direction: column; }
  .checkout .main-content .left .payment-methods .method-btn > img { max-width: 160px; }
}

@media (max-width: 560px) {
  .checkout .header h1 { font-size: 34px !important; }
  .checkout .checkout-steps { justify-content: flex-start; }
  .checkout .checkout-step { font-size: 10px; padding: 7px 10px; }
  .checkout .main-content { width: calc(100% - 24px); }
  .checkout .summary .rank-box { padding: 14px !important; }
  .checkout .summary .order-options .option {
    align-items: flex-start !important;
    gap: 8px;
    flex-direction: column;
  }
  .checkout .summary .order-options .option .value { text-align: left; }
  .checkout .main-content .right .summary .discount-input { flex-direction: column; padding: 10px; }
  .checkout .main-content .right .summary .discount-input button { width: 100%; margin: 0; }
}



/* =========================================================
   Checkout mobile polish: no empty coin rows, better steps,
   cleaner disabled Coinbase method
   ========================================================= */
.checkout .summary .order-options .option:empty,
.checkout .summary .order-options .coins-list:empty {
  display: none !important;
  padding: 0 !important;
  margin: 0 !important;
  border: 0 !important;
  background: transparent !important;
}

.checkout .main-content .left .payment-methods .method-btn.third {
  gap: 14px;
}

.checkout .main-content .left .payment-methods .method-btn.third > img {
  max-width: 170px;
  opacity: .58;
  filter: saturate(.75) brightness(.9);
}

.checkout .main-content .left .payment-methods label.payment-method-disabled .method-btn {
  background: rgba(6,6,18,.52) !important;
  border-color: rgba(255,255,255,.055) !important;
  box-shadow: none !important;
}

.checkout .main-content .left .payment-methods label.payment-method-disabled .method-btn .checkmark {
  min-width: 0;
}

.checkout .main-content .left .payment-methods label.payment-method-disabled .badge,
.checkout .main-content .left .payment-methods label.payment-method-disabled .badge-primary {
  border: 1px solid rgba(255,255,255,.12) !important;
  background: rgba(255,255,255,.065) !important;
  color: rgba(255,255,255,.62) !important;
  font-size: 11px !important;
  line-height: 1 !important;
  padding: 6px 9px !important;
  border-radius: 999px !important;
  white-space: nowrap;
}

.checkout .payment-method-unavailable-note {
  padding-left: 4px;
  line-height: 1.45 !important;
}

@media (max-width: 560px) {
  .checkout .header {
    margin: 90px auto 22px !important;
    padding: 0 16px !important;
  }

  .checkout .header h1 {
    font-size: clamp(30px, 8.8vw, 38px) !important;
    line-height: .92 !important;
    margin-bottom: 10px !important;
  }

  .checkout .header h5 {
    font-size: 12px !important;
    line-height: 1.4 !important;
    margin-bottom: 0 !important;
  }

  .checkout .checkout-steps {
    width: min(100%, 340px);
    margin: 16px auto 0 !important;
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 7px !important;
    justify-content: center !important;
    align-items: stretch !important;
  }

  .checkout .checkout-step {
    min-width: 0;
    justify-content: center;
    text-align: center;
    flex-direction: column;
    gap: 5px !important;
    padding: 9px 5px !important;
    border-radius: 13px !important;
    font-size: 9px !important;
    line-height: 1.05 !important;
    letter-spacing: .045em !important;
    white-space: normal !important;
  }

  .checkout .checkout-step i {
    font-size: 12px !important;
  }

  .checkout .main-content {
    width: calc(100% - 14px) !important;
    gap: 12px !important;
  }

  .checkout .card {
    padding: 16px 14px !important;
    border-radius: 18px !important;
  }

  .checkout .main-content .left h3,
  .checkout .main-content .right h3 {
    font-size: 14px !important;
    margin-bottom: 12px !important;
    padding-bottom: 10px !important;
    gap: 8px !important;
  }

  .checkout .main-content .left h3:not(:first-child) {
    margin-top: 20px !important;
  }

  .checkout .main-content .left .buttons {
    gap: 9px !important;
    margin-bottom: 18px !important;
  }

  .checkout .main-content .left .buttons .btn {
    min-height: 42px !important;
    padding: 10px 12px !important;
    border-radius: 12px !important;
    font-size: 11px !important;
  }

  .checkout .main-content .left .payment-methods {
    gap: 9px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn {
    min-height: 54px !important;
    padding: 11px 12px !important;
    border-radius: 14px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn .checkmark {
    gap: 10px !important;
    font-size: 12px !important;
    min-width: 0;
  }

  .checkout .main-content .left .payment-methods .method-btn .checkmark span {
    width: 17px !important;
    height: 17px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn img {
    max-height: 18px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn > img {
    max-width: 104px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn.third {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
  }

  .checkout .main-content .left .payment-methods .method-btn.third > img[alt="crypto logos"] {
    display: none !important;
  }

  .checkout .main-content .left .payment-methods label.payment-method-disabled .badge,
  .checkout .main-content .left .payment-methods label.payment-method-disabled .badge-primary {
    margin-left: auto;
    font-size: 9px !important;
    padding: 5px 7px !important;
  }

  .checkout .payment-method-unavailable-note {
    margin: 7px 0 0 !important;
    padding: 0 2px !important;
    font-size: 10px !important;
    line-height: 1.35 !important;
  }

  .checkout .summary .rank-box {
    padding: 12px !important;
    border-radius: 16px !important;
  }

  .checkout .summary .rank-box .from img,
  .checkout .summary .rank-box .to img {
    height: 32px !important;
  }

  .checkout .summary .rank-box .title,
  .checkout .summary .rank-box .game,
  .checkout .summary .rank-box .count {
    font-size: 11px !important;
  }

  .checkout .summary .rank-box small {
    font-size: 9px !important;
  }

  .checkout .summary .order-options {
    gap: 8px !important;
    margin: 14px 0 16px !important;
  }

  .checkout .summary .order-options .option {
    padding: 11px 12px !important;
    border-radius: 13px !important;
  }

  .checkout .summary .order-options .option .title,
  .checkout .summary .order-options .option .value {
    font-size: 12px !important;
  }

  .checkout .summary .order-options .option .title img {
    width: 14px !important;
    height: 14px !important;
  }

  .checkout .main-content .right .summary #complete_payment {
    min-height: 52px !important;
    border-radius: 15px !important;
    font-size: 14px !important;
  }
}


/* =========================================================
   Checkout desktop update: larger left-side checkout elements
   ========================================================= */
@media (min-width: 981px) {
  .checkout .main-content {
    width: min(94vw, 1500px);
    grid-template-columns: minmax(620px, 1.16fr) minmax(500px, 1fr) !important;
    gap: clamp(28px, 2.4vw, 44px) !important;
  }

  .checkout .main-content .left .card {
    padding: clamp(34px, 2.5vw, 48px) !important;
    border-radius: 30px !important;
  }

  .checkout .main-content .left h3 {
    gap: 14px !important;
    margin-bottom: 26px !important;
    padding-bottom: 20px !important;
    font-size: clamp(28px, 1.9vw, 36px) !important;
    line-height: 1.12 !important;
  }

  .checkout .main-content .left h3:not(:first-child) {
    margin-top: 42px !important;
  }

  .checkout .main-content .left h3::before {
    width: 6px;
    height: 32px;
  }

  .checkout .main-content .left .buttons {
    gap: 18px !important;
    margin-bottom: 42px !important;
  }

  .checkout .main-content .left .buttons .btn {
    min-height: 72px !important;
    padding: 20px 28px !important;
    border-radius: 20px !important;
    font-size: 21px !important;
    font-weight: 900 !important;
    gap: 14px !important;
  }

  .checkout .main-content .left .buttons .btn img {
    width: 24px !important;
    max-height: 24px !important;
  }

  .checkout .main-content .left .payment-methods {
    gap: 18px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn {
    min-height: 104px !important;
    padding: 24px 28px !important;
    border-radius: 24px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn .checkmark {
    gap: 24px !important;
    font-size: 21px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn .checkmark span {
    width: 34px !important;
    height: 34px !important;
    border-width: 2px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn img {
    max-height: 40px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn > img {
    max-width: 350px !important;
    max-height: 36px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn.first .checkmark img {
    max-height: 52px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn:not(.first) .checkmark img {
    max-height: 48px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn.third > img {
    max-width: 310px !important;
    max-height: 34px !important;
  }

  .checkout .main-content .left .payment-methods .badge,
  .checkout .main-content .left .payment-methods .badge-primary {
    padding: 12px 18px !important;
    border-radius: 12px !important;
    font-size: 16px !important;
    font-weight: 900 !important;
  }

  .checkout .payment-method-unavailable-note {
    margin: 20px 8px 0 !important;
    font-size: 16px !important;
    line-height: 1.55 !important;
    color: rgba(255, 255, 255, .76) !important;
  }
}



/* Coinbase unavailable note: subtle and shown on hover */
.checkout .main-content .left .payment-methods label.payment-method-disabled {
  position: relative;
}

.checkout .payment-method-unavailable-note {
  display: flex !important;
  align-items: center !important;
  gap: 10px !important;
  margin: 10px 6px 0 !important;
  padding: 10px 12px !important;
  border: 1px solid rgba(255,255,255,.06) !important;
  border-radius: 14px !important;
  background: rgba(11, 11, 27, .78) !important;
  color: rgba(255,255,255,.64) !important;
  box-shadow: none !important;
  opacity: 0 !important;
  max-height: 0 !important;
  overflow: hidden !important;
  transform: translateY(-6px) !important;
  transition: opacity .18s ease, transform .18s ease, max-height .18s ease, margin .18s ease, padding .18s ease !important;
  pointer-events: none !important;
}

.checkout .main-content .left .payment-methods label.payment-method-disabled:hover .payment-method-unavailable-note {
  opacity: 1 !important;
  max-height: 90px !important;
  transform: translateY(0) !important;
  margin-top: 12px !important;
}

.checkout .payment-method-unavailable-note .note-icon {
  width: 28px !important;
  height: 28px !important;
  min-width: 28px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  border-radius: 999px !important;
  background: rgba(118, 111, 255, .12) !important;
  color: rgba(157, 151, 255, .95) !important;
  font-size: 12px !important;
}

.checkout .payment-method-unavailable-note .note-copy {
  min-width: 0;
}

.checkout .payment-method-unavailable-note .note-copy span {
  display: block !important;
  color: rgba(255,255,255,.84) !important;
  font-size: 12px !important;
  line-height: 1.25 !important;
  font-weight: 800 !important;
  margin-bottom: 2px !important;
}

.checkout .payment-method-unavailable-note .note-copy p {
  margin: 0 !important;
  color: rgba(255,255,255,.58) !important;
  font-size: 11px !important;
  line-height: 1.35 !important;
}

@media (min-width: 1180px) {
  .checkout .payment-method-unavailable-note {
    margin-left: 8px !important;
    margin-right: 8px !important;
  }

  .checkout .payment-method-unavailable-note .note-icon {
    width: 30px !important;
    height: 30px !important;
    min-width: 30px !important;
    font-size: 13px !important;
  }

  .checkout .payment-method-unavailable-note .note-copy span {
    font-size: 13px !important;
  }

  .checkout .payment-method-unavailable-note .note-copy p {
    font-size: 11.5px !important;
  }
}

@media (max-width: 560px) {
  .checkout .payment-method-unavailable-note {
    display: none !important;
  }
}

/* Bigger right-side checkout benefit rows */
.checkout .summary .checkout-benefit-row {
  gap: 14px !important;
  padding: 10px 0 !important;
  min-height: 0 !important;
}

.checkout .summary .checkout-benefit-row i,
.checkout .summary .checkout-benefit-row svg,
.checkout .summary .checkout-benefit-row img,
.checkout .summary .checkout-benefit-row .icon {
  width: 20px !important;
  height: 20px !important;
  min-width: 20px !important;
  font-size: 20px !important;
}

.checkout .summary .checkout-benefit-row,
.checkout .summary .checkout-benefit-row span,
.checkout .summary .checkout-benefit-row p,
.checkout .summary .checkout-benefit-row div {
  font-size: 17px !important;
  line-height: 1.4 !important;
}

@media (min-width: 1180px) {
  .checkout .summary .checkout-benefit-row {
    gap: 16px !important;
    padding: 12px 0 !important;
  }

  .checkout .summary .checkout-benefit-row i,
  .checkout .summary .checkout-benefit-row svg,
  .checkout .summary .checkout-benefit-row img,
  .checkout .summary .checkout-benefit-row .icon {
    width: 22px !important;
    height: 22px !important;
    min-width: 22px !important;
    font-size: 22px !important;
  }

  .checkout .summary .checkout-benefit-row,
  .checkout .summary .checkout-benefit-row span,
  .checkout .summary .checkout-benefit-row p,
  .checkout .summary .checkout-benefit-row div {
    font-size: 18px !important;
  }
}


/* Bigger Order Summary heading + selected account copy */
.checkout .main-content .right .card > h3 {
  display: flex !important;
  align-items: center !important;
  gap: 14px !important;
  font-size: clamp(24px, 1.75vw, 34px) !important;
  line-height: 1.2 !important;
  font-weight: 900 !important;
  margin-bottom: 28px !important;
}

.checkout .main-content .right .card > h3 img {
  width: 30px !important;
  height: 30px !important;
  min-width: 30px !important;
  object-fit: contain !important;
}

.checkout .checkout-account-copy,
.checkout .summary .checkout-account-copy {
  display: flex !important;
  flex-direction: column !important;
  gap: 6px !important;
}

.checkout .checkout-account-label,
.checkout .summary .checkout-account-label {
  font-size: 14px !important;
  line-height: 1.25 !important;
  font-weight: 900 !important;
  letter-spacing: .065em !important;
  text-transform: uppercase !important;
  color: rgba(255, 255, 255, .58) !important;
}

.checkout .checkout-account-title,
.checkout .summary .checkout-account-title {
  font-size: 18px !important;
  line-height: 1.35 !important;
  font-weight: 900 !important;
  color: #fff !important;
}

@media (min-width: 1180px) {
  .checkout .main-content .right .card > h3 {
    font-size: 32px !important;
    gap: 16px !important;
    margin-bottom: 32px !important;
  }

  .checkout .main-content .right .card > h3 img {
    width: 34px !important;
    height: 34px !important;
    min-width: 34px !important;
  }

  .checkout .checkout-account-label,
  .checkout .summary .checkout-account-label {
    font-size: 15px !important;
  }

  .checkout .checkout-account-title,
  .checkout .summary .checkout-account-title {
    font-size: 20px !important;
  }
}

@media (max-width: 560px) {
  .checkout .main-content .right .card > h3 {
    font-size: 22px !important;
    gap: 10px !important;
  }

  .checkout .main-content .right .card > h3 img {
    width: 24px !important;
    height: 24px !important;
    min-width: 24px !important;
  }

  .checkout .checkout-account-label,
  .checkout .summary .checkout-account-label {
    font-size: 11px !important;
  }

  .checkout .checkout-account-title,
  .checkout .summary .checkout-account-title {
    font-size: 15px !important;
  }
}


/* Mobile checkout polish: buttons + Stripe row */
@media (max-width: 560px) {
  .checkout .main-content {
    width: min(94vw, 430px) !important;
    gap: 18px !important;
  }

  .checkout .card {
    padding: 18px 14px !important;
    border-radius: 22px !important;
  }

  .checkout .main-content .left .buttons {
    display: grid !important;
    grid-template-columns: 1fr !important;
    gap: 12px !important;
    margin: 14px 0 22px !important;
  }

  .checkout .main-content .left .buttons .btn {
    width: 100% !important;
    min-height: 50px !important;
    padding: 14px 16px !important;
    justify-content: center !important;
    gap: 10px !important;
    border-radius: 14px !important;
    font-size: 14px !important;
    line-height: 1.2 !important;
    font-weight: 800 !important;
  }

  .checkout .main-content .left .buttons .btn i,
  .checkout .main-content .left .buttons .btn svg {
    font-size: 18px !important;
    width: 18px !important;
    height: 18px !important;
  }

  .checkout .main-content .left .payment-methods {
    gap: 12px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn {
    min-height: 82px !important;
    padding: 18px 16px !important;
    border-radius: 20px !important;
    display: flex !important;
    align-items: center !important;
    gap: 14px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn .checkmark {
    width: 26px !important;
    height: 26px !important;
    min-width: 26px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn > img,
  .checkout .main-content .left .payment-methods .method-btn .payment-logo,
  .checkout .main-content .left .payment-methods .method-btn img[alt*="stripe" i],
  .checkout .main-content .left .payment-methods .method-btn img[src*="stripe" i] {
    width: auto !important;
    height: 28px !important;
    max-width: 120px !important;
    object-fit: contain !important;
  }

  .checkout .main-content .left .payment-methods .method-btn .payment-icons,
  .checkout .main-content .left .payment-methods .method-btn .cards,
  .checkout .main-content .left .payment-methods .method-btn .icons {
    margin-left: auto !important;
    display: flex !important;
    align-items: center !important;
    gap: 4px !important;
    flex-wrap: nowrap !important;
  }

  .checkout .main-content .left .payment-methods .method-btn .payment-icons img,
  .checkout .main-content .left .payment-methods .method-btn .cards img,
  .checkout .main-content .left .payment-methods .method-btn .icons img {
    height: 14px !important;
    width: auto !important;
  }

  .checkout .main-content .left .payment-methods label:first-child .method-btn,
  .checkout .main-content .left .payment-methods .method-btn:has(img[alt*="stripe" i]),
  .checkout .main-content .left .payment-methods .method-btn:has(img[src*="stripe" i]) {
    min-height: 88px !important;
  }

  .checkout .main-content .left .payment-methods label:first-child .method-btn > img,
  .checkout .main-content .left .payment-methods label:first-child .method-btn img[alt*="stripe" i],
  .checkout .main-content .left .payment-methods label:first-child .method-btn img[src*="stripe" i] {
    height: 30px !important;
    max-width: 130px !important;
  }
}

@media (max-width: 390px) {
  .checkout .main-content .left .payment-methods .method-btn {
    padding: 16px 14px !important;
    gap: 12px !important;
  }

  .checkout .main-content .left .payment-methods label:first-child .method-btn > img,
  .checkout .main-content .left .payment-methods label:first-child .method-btn img[alt*="stripe" i],
  .checkout .main-content .left .payment-methods label:first-child .method-btn img[src*="stripe" i] {
    height: 28px !important;
    max-width: 118px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn .payment-icons img,
  .checkout .main-content .left .payment-methods .method-btn .cards img,
  .checkout .main-content .left .payment-methods .method-btn .icons img {
    height: 12px !important;
  }
}


/* Mobile: make PayPal and Coinbase rows/logo sizes match Stripe better */
@media (max-width: 560px) {
  .checkout .main-content .left .payment-methods .method-btn .checkmark {
    display: flex !important;
    align-items: center !important;
    min-width: 0 !important;
    width: auto !important;
    flex-wrap: nowrap !important;
  }

  .checkout .main-content .left .payment-methods .method-btn .checkmark img[alt*="PayPal" i],
  .checkout .main-content .left .payment-methods .method-btn .checkmark img[src*="paypal" i] {
    max-height: 30px !important;
    height: 30px !important;
    width: auto !important;
    max-width: 110px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn.third .checkmark img[alt*="Coinbase" i],
  .checkout .main-content .left .payment-methods .method-btn.third .checkmark img[src*="coinbase" i] {
    max-height: 24px !important;
    height: 24px !important;
    width: auto !important;
    max-width: 104px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn:not(.first) {
    min-height: 84px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn:not(.first) .checkmark {
    gap: 12px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn:not(.first) .checkmark span {
    width: 22px !important;
    height: 22px !important;
    min-width: 22px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn:not(.first) .badge,
  .checkout .main-content .left .payment-methods .method-btn:not(.first) .badge-primary {
    padding: 8px 10px !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    margin-left: 6px !important;
    line-height: 1.15 !important;
    border-radius: 10px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn.third > img {
    max-width: 120px !important;
    max-height: 20px !important;
    width: auto !important;
    opacity: .6 !important;
  }
}

@media (max-width: 390px) {
  .checkout .main-content .left .payment-methods .method-btn .checkmark img[alt*="PayPal" i],
  .checkout .main-content .left .payment-methods .method-btn .checkmark img[src*="paypal" i] {
    max-height: 28px !important;
    height: 28px !important;
    max-width: 100px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn.third .checkmark img[alt*="Coinbase" i],
  .checkout .main-content .left .payment-methods .method-btn.third .checkmark img[src*="coinbase" i] {
    max-height: 22px !important;
    height: 22px !important;
    max-width: 96px !important;
  }

  .checkout .main-content .left .payment-methods .method-btn:not(.first) .badge,
  .checkout .main-content .left .payment-methods .method-btn:not(.first) .badge-primary {
    padding: 7px 9px !important;
    font-size: 12px !important;
  }
}

/* Checkout summary v2 */
.checkout .checkout-summary-v2__card {
  padding: 0 !important;
  overflow: hidden;
  border-radius: 28px !important;
  border: 1px solid rgba(139, 140, 245, .20) !important;
  background:
    radial-gradient(circle at 100% 0%, rgba(124, 58, 237, .16), transparent 34%),
    linear-gradient(180deg, rgba(17, 18, 40, .98), rgba(9, 10, 25, .98)) !important;
  box-shadow: 0 28px 80px rgba(0, 0, 0, .34) !important;
}
.checkout .checkout-summary-v2__card > h3 {
  margin: 0 !important;
  padding: 22px 24px !important;
  border-bottom: 1px solid rgba(255, 255, 255, .08);
  background: rgba(255, 255, 255, .018);
  font-size: 17px !important;
}
.checkout .checkout-summary-v2__card > h3 img {
  width: 19px !important;
  padding: 0 !important;
}
.checkout .checkout-summary-v2__card > .summary {
  padding: 22px 24px 24px;
}
.checkout .checkout-summary-v2 .order-options {
  display: grid !important;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px !important;
  margin: 0 0 20px !important;
}
.checkout .checkout-summary-v2 .order-options .option {
  min-height: 72px;
  padding: 13px 14px !important;
  display: flex !important;
  flex-direction: column;
  align-items: flex-start !important;
  justify-content: center !important;
  gap: 5px !important;
  border-radius: 15px !important;
  background: rgba(255, 255, 255, .035) !important;
  border: 1px solid rgba(255, 255, 255, .07) !important;
}
.checkout .checkout-summary-v2 .order-options .option .title {
  color: rgba(255, 255, 255, .43) !important;
  font-size: 10px !important;
  font-weight: 800 !important;
  letter-spacing: .075em;
  text-transform: uppercase;
}
.checkout .checkout-summary-v2 .order-options .option .value {
  width: 100%;
  color: rgba(255, 255, 255, .94) !important;
  font-size: 13px !important;
  line-height: 1.3;
  text-align: left !important;
  overflow-wrap: anywhere;
}
.checkout .checkout-summary-v2 .order-options .option:first-child {
  grid-column: 1 / -1;
  min-height: 66px;
  background: linear-gradient(135deg, rgba(99, 102, 241, .13), rgba(124, 58, 237, .08)) !important;
  border-color: rgba(139, 140, 245, .20) !important;
}
.checkout .checkout-summary-v2 .egirl-session-list-note {
  margin: 4px 0 20px;
  padding: 14px;
  border-radius: 15px;
  background: rgba(255, 255, 255, .025);
  border: 1px solid rgba(255, 255, 255, .065);
}
.checkout .checkout-summary-v2 .egirl-session-list-note label { margin-bottom: 9px; }
.checkout .checkout-summary-v2 .egirl-session-note-box {
  padding: 0;
  border: 0;
  background: transparent;
}
.checkout .checkout-summary-v2 .totals-section {
  margin-top: 4px !important;
  padding: 18px 0 12px !important;
}
.checkout .checkout-summary-v2 .totals-section .item {
  min-height: 46px;
  padding: 8px 0 !important;
}
.checkout .checkout-summary-v2 .totals-section .checkout-total-row {
  align-items: flex-end;
}
.checkout .checkout-summary-v2 .totals-section .checkout-total-row .label {
  font-size: 12px !important;
  font-weight: 800;
  letter-spacing: .05em;
  text-transform: uppercase;
}
.checkout .checkout-summary-v2 .totals-section .checkout-total-row .value {
  font-size: clamp(30px, 3vw, 42px) !important;
  letter-spacing: -.04em;
}
.checkout .checkout-summary-v2 #complete_payment {
  margin-top: 8px;
  min-height: 60px !important;
  border-radius: 16px !important;
  background: linear-gradient(100deg, #5b63f5, #8b3df0) !important;
  box-shadow: 0 18px 40px rgba(102, 72, 235, .26) !important;
}
@media (max-width: 560px) {
  .checkout .checkout-summary-v2__card > h3 { padding: 18px !important; }
  .checkout .checkout-summary-v2__card > .summary { padding: 18px !important; }
  .checkout .checkout-summary-v2 .order-options { grid-template-columns: 1fr; }
  .checkout .checkout-summary-v2 .order-options .option:first-child { grid-column: auto; }
  .checkout .checkout-summary-v2 .order-options .option { min-height: 62px; }
}

</style>

<div class="header">
    <h1><?= t('Order Checkout') ?></h1>
    <h5><?= t('One more step! Complete your payment.') ?></h5>
    <div class="checkout-steps" aria-label="Checkout progress">
        <span class="checkout-step"><i class="fa-solid fa-cart-shopping"></i><?= t('Order checked') ?></span>
        <span class="checkout-step is-active"><i class="fa-solid fa-credit-card"></i><?= t('Payment') ?></span>
        <span class="checkout-step"><i class="fa-solid fa-shield-check"></i><?= t('Secure delivery') ?></span>
    </div>
</div>

<form action="<?= AJAX_URL ?>" class="checkout-form ajax-form">
    <input type="hidden" name="action" value="client_checkout">
    <input type="hidden" name="invoice_uuid" value="<?= $invoice['uuid'] ?>">
    <input type="hidden" name="paypal_refund_confirmed" id="paypal_refund_confirmed" value="0">
    <div class="main-content">
        <div class="left">
            <div class="card">
                <?php if (CLIENT_DATA == false): ?>
                    <section class="checkout-email-card" aria-labelledby="checkout-email-title">
                        <label id="checkout-email-title" for="checkout_guest_email"><?= t('Email address') ?></label>
                        <div class="checkout-email-card__row">
                            <div class="checkout-email-card__control">
                                <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                                <input type="email" id="checkout_guest_email" autocomplete="email" placeholder="<?= t('you@example.com') ?>">
                            </div>
                            <button type="button" id="checkout-email-continue" aria-label="<?= t('Continue') ?>">
                                <span class="checkout-email-button-label"><?= t('Continue') ?></span>
                                <span class="checkout-email-button-loading" hidden><span class="loader"></span></span>
                                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="checkout-email-card__error" id="checkout-email-error" role="alert" hidden></div>
                        <button type="button" class="checkout-email-card__login" id="checkout-login-link" onclick="if(window.lbOpenAuthModal){window.lbOpenAuthModal();}">
                            <?= t('Already have an account? Log in') ?>
                        </button>
                    </section>
                <?php else: ?>
                    <div class="checkout-customer-ready">
                        <span class="checkout-customer-ready__check"><i class="fa-solid fa-check"></i></span>
                        <span>
                            <strong><?= t('Order updates will be sent to') ?></strong>
                            <small><?= htmlspecialchars((string)(CLIENT_DATA['email'] ?? CLIENT_DATA['username']), ENT_QUOTES, 'UTF-8') ?></small>
                        </span>
                        <?php if (stripos((string)(CLIENT_DATA['username'] ?? ''), 'Guest#') === 0): ?>
                            <button type="button" class="checkout-change-email" id="checkout-change-email"><?= t('Change') ?></button>
                        <?php endif; ?>
                    </div>
                    <?php if (stripos((string)(CLIENT_DATA['username'] ?? ''), 'Guest#') === 0): ?>
                        <div class="checkout-email-editor" id="checkout-email-editor" hidden>
                            <label for="checkout_new_email"><?= t('New email address') ?></label>
                            <div class="checkout-email-editor__row">
                                <input type="email" id="checkout_new_email" autocomplete="email" value="<?= htmlspecialchars((string)(CLIENT_DATA['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <button type="button" id="checkout-save-email"><?= t('Save') ?></button>
                                <button type="button" id="checkout-cancel-email"><?= t('Cancel') ?></button>
                            </div>
                            <div class="checkout-email-step__error" id="checkout-change-email-error" role="alert" hidden></div>
                            <button type="button" class="checkout-email-login" id="checkout-change-email-login" hidden onclick="if(window.lbOpenAuthModal){window.lbOpenAuthModal();}">
                                <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
                                <?= t('Log in to this account') ?>
                            </button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <h3><?= t('Select your Payment Processor') ?></h3>

                <div class="payment-methods">
                    <label>
                        <input type="radio" name="processor" value="stripe" class="custom-radio" checked>
                        <div class="method-btn first">
                            <div class="checkmark">
                                <span></span>

                                <img src="<?= ASSET_URL ?>/core/main/img/checkout/stripe.svg" alt="Stripe">
                            </div>
                            <img src="<?= ASSET_URL ?>/website/images/checkout/payment-cards.webp" alt="payment cards">
                        </div>
                    </label>
                    <label id="paypal-method">
                        <input type="radio" name="processor" value="stripe_paypal" class="custom-radio">
                        <div class="method-btn">
                            <div class="checkmark">
                                <span></span>

                                <img src="<?= ASSET_URL ?>/core/main/img/checkout/paypal.svg" alt="PayPal">

                                <div class="badge badge-primary" title="<?= t('PayPal charges an additional processing fee') ?>"><?= t('10% Fee') ?></div>
                            </div>
                        </div>
                    </label>
                    <label class="payment-method-disabled" aria-disabled="true" title="<?= t('Currently unavailable') ?>">
                        <input type="radio" name="processor" value="coinbase" class="custom-radio" disabled>
                        <div class="method-btn third">
                            <div class="checkmark">
                                <span></span>

                                <img src="<?= ASSET_URL ?>/core/main/img/checkout/coinbase.svg" alt="Coinbase">

                                <div class="badge badge-primary"><?= t('Unavailable') ?></div>
                            </div>
                            <img src="<?= ASSET_URL ?>/website/images/checkout/crypto-logos.webp" alt="crypto logos">
                        </div>
                        <div class="payment-method-unavailable-note">
                            <div class="note-icon" aria-hidden="true">
                                <i class="fa-solid fa-comment-dots"></i>
                            </div>
                            <div class="note-copy">
                                <span><?= t('Coinbase unavailable') ?></span>
                                <p><?= t('Manual crypto payment is possible via live chat.') ?></p>
                            </div>
                        </div>
                    </label>
                </div>

                <?php if (CLIENT_DATA): ?>
                    <?php if ($invoice['order_type'] !== 'tip'): ?>
                        <h3><?= t('LB Coins') ?></h3>

                        <div class="payment-methods">
                            <label>
                                <input type="hidden" name="points_used" value="0">
                                <input type="checkbox" class="custom-radio" id="use_points" value="1" name="points_used"
                                    <?= $invoice['coins_used'] != 0.00 ? 'checked' : '' ?>>
                                <div class="method-btn">
                                    <div class="checkmark">
                                        <span></span>

                                        <img src="<?= ASSET_URL ?>/core/main/img/coin.png"><?= t('Use LB Coins') ?></div>
                                </div>
                            </label>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            </div>
            <a href="https://www.trustpilot.com/review/lolboost.gg" target="_blank" rel="noopener noreferrer" class="checkout-trustpilot" aria-label="LoLBoost reviews on Trustpilot">
                <span class="checkout-trustpilot__excellent"><?= t('Excellent') ?></span>
                <span class="checkout-trustpilot__stars" aria-hidden="true">
                    <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                </span>
                <strong>4.9 out of 5</strong>
                <span class="checkout-trustpilot__brand"><i class="fa-solid fa-star" aria-hidden="true"></i> Trustpilot</span>
            </a>
        </div>
        <div class="right checkout-summary-v2">
            <div class="card checkout-summary-v2__card">
                <h3>
                    <img src="<?= ASSET_URL ?>/website/images/checkout/order-summary.svg" alt="order_summary" />
                    <?php if ($invoice['order_type'] == 'tip'): ?>
                        <?= t('Thank You') ?> 😊
                    <?php elseif ($invoice['order_type'] == 'invoice'): ?>
                        <?= t('Invoice Summary') ?>
                    <?php else: ?>
                        <?= t('Your Order') ?>
                    <?php endif; ?>
                </h3>

                <div class="summary">
                    <?= $this->insert('website/components/checkout/' . $invoice['order_type'], ['data' => $data]) ?>

                    <?php if ($invoice['order_type'] != 'tip' && $invoice['order_type'] != 'invoice' && $invoice['order_type'] != 'egirl_session' && (int)($data['form_id'] ?? 0) !== 26): ?>
                        <?php if (!empty($invoice['discount_id']) && $invoice['discount_id'] > 0): ?>
                            <div class="discount-applied"><?= t('Great! Your discount code is now active. 🎉') ?></div>
                        <?php else: ?>
                            <div class="discount-input" id="discount-input">
                                <input type="text" placeholder="Enter Discount Code" name="discount_code" id="discount_code">
                                <button class="apply-discount" id="apply_discount"><?= t('Apply') ?></button>
                            </div>
                            <div id="discount_alert">
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="totals-section">
                        <div class="item checkout-subtotal-row" id="checkout-subtotal-row" hidden>
                            <div class="label"><?= t('Subtotal') ?></div>
                            <div class="value" id="checkout-subtotal-text"><?= util_format_currency_display($invoice['currency']) ?><?= util_format_price_display($invoice['total_price']) ?></div>
                        </div>
                        <div class="item paypal-fee-row" id="paypal-fee-row" hidden>
                            <div class="label">
                                <i class="fa-brands fa-paypal" aria-hidden="true"></i><?= t('PayPal fee') ?> <small>· 10%</small>
                            </div>
                            <div class="value" id="paypal-fee-text">+<?= util_format_currency_display($invoice['currency']) ?>0.00</div>
                        </div>
                        <div class="item checkout-total-row">
                            <div class="label">
                                <img src="<?= ASSET_URL ?>/website/images/checkout/coins.svg" alt="total" /><?= t('Total Price') ?></div>
                            <div class="value" id="total-text">
                                <?= util_format_currency_display($invoice['currency']) ?><?= util_format_price_display($invoice['total_price']) ?>
                            </div>
                        </div>
                        <?php if (CLIENT_DATA && $invoice['order_type'] != 'tip'): ?>
                            <div class="item">
                                <div class="label"><?= t('Cashback') ?></div>
                                <div class="value">
                                    <img src="<?= ASSET_URL ?>/core/main/img/coin.png" alt="coins" />
                                    <?php
                                    $client_rank = db_get_row('loyalty_ranks', ['id' => CLIENT_DATA['loyalty_rank_id']]);
                                    $cashback_percentage = $client_rank['cashback'];

                                    $coins_earned = ($invoice['total_price'] * $cashback_percentage) / 100;

                                    echo number_format($coins_earned / 100, 2);
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn" id="complete_payment" <?= CLIENT_DATA == false ? 'aria-disabled="true"' : '' ?>>
                        <span class="indicator-label"><?= CLIENT_DATA == false ? t('Enter email to continue') : t('Pay securely now') ?></span>
                        <span class="indicator-progress">
                            <span class="loader"></span>
                        </span>
                    </button>

                    <div class="checkout-trust-line">
                        <i class="fa-solid fa-lock" aria-hidden="true"></i>
                        <span><?= t('SSL encrypted &amp; secure payment') ?></span>
                    </div>

                </div>
            </div>
        </div>
    </div>
</form>


<div class="refund-confirm-modal" id="refundConfirmModal">
    <div class="refund-confirm-modal__backdrop"></div>
    <div class="refund-confirm-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="refundConfirmTitle">
        <button type="button" class="refund-confirm-modal__close" id="refundConfirmClose" aria-label="<?= t('Close') ?>">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="refund-confirm-modal__header">
            <div class="refund-confirm-modal__header-icon">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h3 class="refund-confirm-modal__title" id="refundConfirmTitle"><?= t('Refund confirmation') ?></h3>
        </div>

        <p class="refund-confirm-modal__intro">
            <?= t('Please confirm that you understand our refund policy before paying with PayPal.') ?>
        </p>

        <div class="refund-warning refund-warning--simple">
            <div class="refund-warning__icon">
                <i class="fa-solid fa-circle-info"></i>
            </div>
            <div class="refund-warning__text">
                <?= t('Once your order is completed, refunds are not possible.') ?>
            </div>
        </div>

        <div class="refund-confirm-modal__content">
            <label class="refund-confirm-checkbox">
                <input type="checkbox" id="refund_policy_acknowledged">
                <span class="refund-confirm-checkbox__box">
                    <i class="fa-solid fa-check"></i>
                </span>
                <span>
                    <?= t('I understand that once my order is completed, I cannot request a refund.') ?>
                </span>
            </label>

            <div class="refund-confirm-modal__saved">
                <i class="fa-solid fa-lock"></i> <?= t('This confirmation will be saved with your order.') ?>
            </div>
        </div>

        <div class="refund-confirm-modal__actions">
            <button type="button" class="refund-confirm-modal__btn refund-confirm-modal__btn--primary" id="refundConfirmAccept" disabled><?= t('Confirm') ?></button>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script type="text/javascript">
    const paypalLimitCents = <?= (int) $paypal_limit_cents ?>;
    const checkoutCurrencySymbol = <?= json_encode(util_format_currency_display($invoice['currency'])) ?>;
    let checkoutBaseTotalCents = <?= (int) round($invoice['total_price']) ?>;
    let paypalConfirmationAccepted = false;
    let pendingPaymentSubmit = false;

    <?php if (CLIENT_DATA == false): ?>
    (function () {
        const emailInput = document.getElementById('checkout_guest_email');
        const continueButton = document.getElementById('checkout-email-continue');
        const errorBox = document.getElementById('checkout-email-error');

        if (!emailInput || !continueButton || !errorBox) return;

        function setGuestCheckoutLoading(isLoading) {
            continueButton.disabled = isLoading;
            emailInput.disabled = isLoading;
            continueButton.querySelector('.checkout-email-button-label').hidden = isLoading;
            continueButton.querySelector('.checkout-email-button-loading').hidden = !isLoading;
        }

        function showGuestCheckoutError(message) {
            errorBox.textContent = message;
            errorBox.hidden = false;
            emailInput.setAttribute('aria-invalid', 'true');
            emailInput.focus();
        }

        function parseGuestCheckoutResponse(rawResponse) {
            if (rawResponse && typeof rawResponse === 'object') return rawResponse;

            const raw = String(rawResponse || '').replace(/^\uFEFF/, '').trim();
            if (!raw) return null;

            try {
                return JSON.parse(raw);
            } catch (error) {
                // Some PHP hosts prepend notices/warnings to an otherwise valid JSON response.
                const jsonStart = raw.indexOf('{');
                const jsonEnd = raw.lastIndexOf('}');
                if (jsonStart !== -1 && jsonEnd > jsonStart) {
                    try {
                        return JSON.parse(raw.slice(jsonStart, jsonEnd + 1));
                    } catch (nestedError) {
                        return null;
                    }
                }
                return null;
            }
        }

        function handleGuestCheckoutResponse(response) {
            const validationMessage = response && response.validationErrors
                ? (response.validationErrors.email || Object.values(response.validationErrors)[0])
                : null;

            if (validationMessage) {
                const existingEmail = String(validationMessage).toLowerCase().includes('already used');
                showGuestCheckoutError(existingEmail
                    ? '<?= addslashes(t('This email is already registered. Please log in instead.')) ?>'
                    : validationMessage);
                setGuestCheckoutLoading(false);
                return;
            }

            if (response && response.redirectUrl) {
                window.location.href = response.redirectUrl;
                return;
            }

            if (response && response.refreshPage) {
                window.location.reload();
                return;
            }

            showGuestCheckoutError('<?= addslashes(t('The server did not complete the guest checkout. Please try again.')) ?>');
            setGuestCheckoutLoading(false);
        }

        function continueGuestCheckout() {
            const email = emailInput.value.trim();
            errorBox.hidden = true;
            emailInput.removeAttribute('aria-invalid');

            if (!/^\S+@\S+\.\S+$/.test(email)) {
                showGuestCheckoutError('<?= addslashes(t('Please enter a valid email address.')) ?>');
                return;
            }

            setGuestCheckoutLoading(true);

            $.ajax({
                url: '<?= AJAX_URL ?>',
                type: 'POST',
                dataType: 'text',
                data: {
                    action: 'auth_client_guest_login',
                    email: email,
                    tos: 1,
                    redirectUrl: window.location.pathname + window.location.search
                }
            }).done(function (rawResponse) {
                const response = parseGuestCheckoutResponse(rawResponse);
                if (!response) {
                    showGuestCheckoutError('<?= addslashes(t('The checkout server returned an unreadable response. Please contact support.')) ?>');
                    setGuestCheckoutLoading(false);
                    return;
                }
                handleGuestCheckoutResponse(response);
            }).fail(function (xhr, textStatus) {
                const response = parseGuestCheckoutResponse(
                    xhr && (xhr.responseJSON || xhr.responseText) ? (xhr.responseJSON || xhr.responseText) : null
                );
                const validationMessage = response && response.validationErrors
                    ? (response.validationErrors.email || Object.values(response.validationErrors)[0])
                    : null;
                const serverMessage = response && response.sendToast ? response.sendToast.message : null;

                if (validationMessage) {
                    const existingEmail = String(validationMessage).toLowerCase().includes('already used');
                    showGuestCheckoutError(existingEmail
                        ? '<?= addslashes(t('This email is already registered. Please log in instead.')) ?>'
                        : validationMessage);
                } else if (xhr && xhr.status === 429) {
                    showGuestCheckoutError(serverMessage || '<?= addslashes(t('Too many attempts. Please wait a moment and try again.')) ?>');
                } else if (textStatus === 'parsererror') {
                    showGuestCheckoutError('<?= addslashes(t('The server returned an invalid response. Please reload the page and try again.')) ?>');
                } else {
                    showGuestCheckoutError('<?= addslashes(t('The checkout service is currently unavailable. Please try again in a moment.')) ?>');
                }
                setGuestCheckoutLoading(false);
            });
        }

        continueButton.addEventListener('click', continueGuestCheckout);
        emailInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                continueGuestCheckout();
            }
        });

        window.lbFocusGuestCheckoutEmail = function () {
            emailInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            window.setTimeout(function () {
                emailInput.focus({ preventScroll: true });
                emailInput.closest('.checkout-email-card__control').classList.add('needs-email');
                window.setTimeout(function () {
                    emailInput.closest('.checkout-email-card__control').classList.remove('needs-email');
                }, 1200);
            }, 350);
        };

        const paymentButton = document.getElementById('complete_payment');
        if (paymentButton) {
            paymentButton.addEventListener('click', function (event) {
                event.preventDefault();
                window.lbFocusGuestCheckoutEmail();
            });
        }
    })();
    <?php endif; ?>

    <?php if (CLIENT_DATA && stripos((string)(CLIENT_DATA['username'] ?? ''), 'Guest#') === 0): ?>
    (function () {
        const openButton = document.getElementById('checkout-change-email');
        const editor = document.getElementById('checkout-email-editor');
        const emailInput = document.getElementById('checkout_new_email');
        const saveButton = document.getElementById('checkout-save-email');
        const cancelButton = document.getElementById('checkout-cancel-email');
        const errorBox = document.getElementById('checkout-change-email-error');
        const loginButton = document.getElementById('checkout-change-email-login');

        if (!openButton || !editor || !emailInput || !saveButton || !cancelButton || !errorBox || !loginButton) return;

        function closeEmailEditor() {
            editor.hidden = true;
            errorBox.hidden = true;
            loginButton.hidden = true;
            openButton.hidden = false;
        }

        openButton.addEventListener('click', function () {
            openButton.hidden = true;
            editor.hidden = false;
            emailInput.focus();
            emailInput.select();
        });

        cancelButton.addEventListener('click', closeEmailEditor);

        function parseEmailUpdateResponse(rawResponse) {
            if (rawResponse && typeof rawResponse === 'object') return rawResponse;

            const raw = String(rawResponse || '').replace(/^\uFEFF/, '').trim();
            if (!raw) return null;

            try {
                return JSON.parse(raw);
            } catch (error) {
                const jsonStart = raw.indexOf('{');
                const jsonEnd = raw.lastIndexOf('}');
                if (jsonStart !== -1 && jsonEnd > jsonStart) {
                    try {
                        return JSON.parse(raw.slice(jsonStart, jsonEnd + 1));
                    } catch (nestedError) {
                        return null;
                    }
                }
                return null;
            }
        }

        function resetEmailUpdateButton() {
            saveButton.disabled = false;
            saveButton.textContent = '<?= addslashes(t('Save')) ?>';
        }

        function showEmailUpdateError(message) {
            errorBox.textContent = message;
            errorBox.hidden = false;
            loginButton.hidden = !String(message || '').toLowerCase().includes('already used');
            resetEmailUpdateButton();
        }

        function saveGuestEmail() {
            if (saveButton.disabled) return;

            const email = emailInput.value.trim();
            errorBox.hidden = true;
            loginButton.hidden = true;

            if (!/^\S+@\S+\.\S+$/.test(email)) {
                errorBox.textContent = '<?= addslashes(t('Please enter a valid email address.')) ?>';
                errorBox.hidden = false;
                return;
            }

            saveButton.disabled = true;
            saveButton.textContent = '<?= addslashes(t('Saving...')) ?>';

            $.ajax({
                url: '<?= AJAX_URL ?>',
                type: 'POST',
                dataType: 'text',
                data: {
                    action: 'auth_client_guest_email_update',
                    email: email
                }
            }).done(function (rawResponse) {
                const response = parseEmailUpdateResponse(rawResponse);
                if (!response) {
                    showEmailUpdateError('<?= addslashes(t('The checkout server returned an unreadable response. Please contact support.')) ?>');
                    return;
                }

                const validationMessage = response && response.validationErrors
                    ? (response.validationErrors.email || Object.values(response.validationErrors)[0])
                    : null;

                if (validationMessage) {
                    showEmailUpdateError(validationMessage);
                    return;
                }

                if (response.refreshPage || response.sendToast) {
                    window.location.reload();
                    return;
                }

                showEmailUpdateError('<?= addslashes(t('The server did not save the email address. Please try again.')) ?>');
            }).fail(function (xhr) {
                const response = parseEmailUpdateResponse(
                    xhr && (xhr.responseJSON || xhr.responseText) ? (xhr.responseJSON || xhr.responseText) : null
                );
                const validationMessage = response && response.validationErrors
                    ? (response.validationErrors.email || Object.values(response.validationErrors)[0])
                    : null;
                const serverMessage = response && response.sendToast ? response.sendToast.message : null;

                if (validationMessage) {
                    showEmailUpdateError(validationMessage);
                } else if (xhr && xhr.status === 429) {
                    showEmailUpdateError(serverMessage || '<?= addslashes(t('Too many attempts. Please wait a moment and try again.')) ?>');
                } else {
                    showEmailUpdateError(serverMessage || '<?= addslashes(t('The checkout service is currently unavailable. Please try again in a moment.')) ?>');
                }
            });
        }

        saveButton.addEventListener('click', saveGuestEmail);
        emailInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                event.stopPropagation();
                saveGuestEmail();
            }
        });
    })();
    <?php endif; ?>

    function getSelectedProcessor() {
        return $('input[name="processor"]:checked').val();
    }

    function getDisplayedCheckoutTotalCents() {
        return getSelectedProcessor() === 'stripe_paypal'
            ? Math.round(checkoutBaseTotalCents * 1.10)
            : checkoutBaseTotalCents;
    }

    function renderCheckoutTotal() {
        const isPaypal = getSelectedProcessor() === 'stripe_paypal';
        const paymentFeeCents = isPaypal ? getDisplayedCheckoutTotalCents() - checkoutBaseTotalCents : 0;

        $('#paypal-fee-row').prop('hidden', !isPaypal);
        $('#checkout-subtotal-row').prop('hidden', !isPaypal);
        $('#checkout-subtotal-text').text(checkoutCurrencySymbol + (checkoutBaseTotalCents / 100).toFixed(2));
        $('#paypal-fee-text').text('+' + checkoutCurrencySymbol + (paymentFeeCents / 100).toFixed(2));
        $('#total-text').text(checkoutCurrencySymbol + (getDisplayedCheckoutTotalCents() / 100).toFixed(2));
    }

    function getCurrentTotalAmountCents() {
        return checkoutBaseTotalCents;
    }

    function requiresPaypalConfirmation() {
        return getSelectedProcessor() === 'stripe_paypal' && getCurrentTotalAmountCents() >= paypalLimitCents;
    }

    function resetPaypalConfirmation() {
        paypalConfirmationAccepted = false;
        pendingPaymentSubmit = false;
        $('#paypal_refund_confirmed').val('0');
        $('#refund_policy_acknowledged').prop('checked', false);
        $('#refundConfirmAccept').prop('disabled', true);
    }

    function openRefundConfirmModal() {
        const modal = document.getElementById('refundConfirmModal');
        const dialog = modal ? modal.querySelector('.refund-confirm-modal__dialog') : null;
        const paypalMethod = document.getElementById('paypal-method');

        if (!modal || !dialog || !paypalMethod) return;

        modal.classList.add('is-open');
        modal.style.display = 'block';

        // On narrow viewports there isn't enough room to anchor the popover next to
        // the payment method without it overlapping other content, so fall back to
        // the standard centered modal-with-backdrop instead.
        const canAnchor = window.innerWidth > 700;

        if (!canAnchor) {
            modal.classList.remove('is-anchored');
            dialog.style.left = '';
            dialog.style.top = '';
            return;
        }

        modal.classList.add('is-anchored');

        requestAnimationFrame(function () {
            const anchorRect = paypalMethod.getBoundingClientRect();
            const dialogRect = dialog.getBoundingClientRect();
            const viewportGap = 12;
            const left = Math.min(
                Math.max(viewportGap, anchorRect.left + ((anchorRect.width - dialogRect.width) / 2)),
                window.innerWidth - dialogRect.width - viewportGap
            );
            let top = anchorRect.top - dialogRect.height - 10;

            if (top < viewportGap) {
                top = Math.min(anchorRect.bottom + 10, window.innerHeight - dialogRect.height - viewportGap);
            }

            dialog.style.left = Math.round(left) + 'px';
            dialog.style.top = Math.round(Math.max(viewportGap, top)) + 'px';
        });
    }

    function closeRefundConfirmModal() {
        const modal = document.getElementById('refundConfirmModal');
        const dialog = modal ? modal.querySelector('.refund-confirm-modal__dialog') : null;
        if (!modal) return;

        modal.classList.remove('is-open', 'is-anchored');
        modal.style.display = 'none';
        if (dialog) {
            dialog.style.left = '';
            dialog.style.top = '';
        }
    }

    $('#refund_policy_acknowledged').on('change', function () {
        $('#refundConfirmAccept').prop('disabled', !this.checked);
    });

    $('#refundConfirmClose, .refund-confirm-modal__backdrop').on('click', function () {
        resetPaypalConfirmation();
        $('input[name="processor"][value="stripe"]').prop('checked', true).trigger('change');
        closeRefundConfirmModal();
    });

    $('#refundConfirmAccept').on('click', function () {
        paypalConfirmationAccepted = true;
        $('#paypal_refund_confirmed').val('1');
        pendingPaymentSubmit = false;
        closeRefundConfirmModal();
    });

    $('label.payment-method-disabled').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    $('input[name="processor"]').on('change', function () {
        if (getSelectedProcessor() === 'coinbase') {
            $('input[name="processor"][value="stripe"]').prop('checked', true);
            return;
        }

        if (getSelectedProcessor() === 'stripe_paypal') {
            renderCheckoutTotal();
            if (getCurrentTotalAmountCents() >= paypalLimitCents && !paypalConfirmationAccepted) {
                pendingPaymentSubmit = false;
                openRefundConfirmModal();
            }
        } else {
            renderCheckoutTotal();
            resetPaypalConfirmation();
            closeRefundConfirmModal();
        }
    });

    $('#use_points').on('change', function () {
        let checkbox = $(this);

        $.ajax({
            url: '<?= AJAX_URL ?>',
            type: 'POST',
            data: {
                action: 'checkout_coins_toggle',
                invoice_id: '<?= $uuid ?>',
                use_coins: checkbox.is(':checked')
            },
            beforeSend: function () {
                $('#complete_payment').attr('data-indicator', 'on');
                $('#complete_payment').prop('disabled', true);
            },
            success: function (response) {
                response = JSON.parse(response);
                checkoutBaseTotalCents = Math.round(parseFloat(response.total_price));
                renderCheckoutTotal();

                if (<?= CLIENT_DATA ? 'true' : 'false' ?>) {
                    const cashbackPercentage =
                        <?= CLIENT_DATA ? (CLIENT_DATA['loyalty_rank_id'] ? 'parseFloat(' . json_encode(db_get_row('loyalty_ranks', ['id' => CLIENT_DATA['loyalty_rank_id']])['cashback']) . ')' : '0') : '0' ?>;
                    const coinsEarned = (response.total_price * cashbackPercentage) / 100;
                }

                $('.coins-list').remove();

                if (checkbox.is(':checked')) {
                    $('.order-options').append(`
                        <div class="option coins-list">
                            <div class="title">
                                <img src="<?= ASSET_URL ?>/core/main/img/coin.png">
                                LB Coins Spent
                            </div>
                            <div class="value">
                                ${response.coins_used}
                            </div>
                        </div>
                    `);
                }

                if (!requiresPaypalConfirmation()) {
                    resetPaypalConfirmation();
                }

                $('#complete_payment').removeAttr('data-indicator');
                $('#complete_payment').prop('disabled', false);
            }
        });
    });

    var finalCheckoutSubmitting = false;

    function showCheckoutSubmitOverlay() {
        document.documentElement.classList.add('checkout-submit-lock');
        document.body.classList.add('checkout-submit-lock');
        var overlay = document.getElementById('checkout-submit-overlay');
        if (overlay) {
            overlay.classList.add('is-active');
            overlay.setAttribute('aria-hidden', 'false');
        }
    }

    function hideCheckoutSubmitOverlay() {
        document.documentElement.classList.remove('checkout-submit-lock');
        document.body.classList.remove('checkout-submit-lock');
        var overlay = document.getElementById('checkout-submit-overlay');
        if (overlay) {
            overlay.classList.remove('is-active');
            overlay.setAttribute('aria-hidden', 'true');
        }
    }

    window.addEventListener('pageshow', function () {
        finalCheckoutSubmitting = false;
        hideCheckoutSubmitOverlay();
        $('#complete_payment').removeAttr('data-indicator');
        $('#complete_payment').prop('disabled', false);
    });

    $('.checkout-form').on('submit', function (e) {
        <?php if (CLIENT_DATA == false): ?>
        e.preventDefault();
        if (window.lbFocusGuestCheckoutEmail) window.lbFocusGuestCheckoutEmail();
        return false;
        <?php endif; ?>

        if (requiresPaypalConfirmation() && !paypalConfirmationAccepted) {
            e.preventDefault();
            openRefundConfirmModal();
            return false;
        }

        if (finalCheckoutSubmitting) {
            return true;
        }

        finalCheckoutSubmitting = true;
        $('#complete_payment').attr('data-indicator', 'on');
        $('#complete_payment').prop('disabled', true);
        showCheckoutSubmitOverlay();

        return true;
    });

    $(document).ajaxComplete(function () {
        finalCheckoutSubmitting = false;
        hideCheckoutSubmitOverlay();
        $('#complete_payment').removeAttr('data-indicator');
        $('#complete_payment').prop('disabled', false);
    });

    $(document).ajaxError(function () {
        finalCheckoutSubmitting = false;
        hideCheckoutSubmitOverlay();
        $('#complete_payment').removeAttr('data-indicator');
        $('#complete_payment').prop('disabled', false);
    });

    $("#apply_discount").on('click', function () {
        var discount_code = $("#discount_code").val();

        $.ajax({
            url: '<?= AJAX_URL ?>',
            type: 'POST',
            data: {
                action: 'client_discount_invoice',
                discount_code: discount_code,
                invoice_id: <?= $invoice['id'] ?>,
            },
            beforeSend: function () {
                $("#apply_discount").text('Applying...');
                $("#apply_discount").prop('disabled', true);
            },
            success: function (response) {
                $("#apply_discount").text('Apply');
                $("#apply_discount").prop('disabled', false);

                response = JSON.parse(response);

                if (response.discount_status) {
                    $('#discount_alert').text(response.discount_msg);
                    $('#discount_alert').removeClass('error');
                    $('#discount_alert').addClass('success');
                    location.reload();
                } else if (response.discount_msg != null) {
                    $('#discount_alert').text(response.discount_msg);
                    $('#discount_alert').removeClass('success');
                    $('#discount_alert').addClass('error');
                } else {
                    $('#discount_alert').text('');
                }
            }
        });
    });

</script>

<div id="checkout-submit-overlay" aria-hidden="true">
    <div class="checkout-submit-overlay__box">
        <div class="checkout-submit-overlay__spinner"></div>
        <div class="checkout-submit-overlay__text"><?= t('Processing payment...') ?></div>
    </div>
</div>

<style>
#checkout-submit-overlay {
    position: fixed;
    inset: 0;
    display: none;
    align-items: center;
    justify-content: center;
    background: #070812;
    z-index: 2147483647;
}
#checkout-submit-overlay.is-active {
    display: flex;
}
html.checkout-submit-lock,
body.checkout-submit-lock {
    overflow: hidden !important;
    background: #070812 !important;
}
body.checkout-submit-lock .header,
body.checkout-submit-lock .checkout-form,
body.checkout-submit-lock .refund-confirm-modal {
    opacity: 0 !important;
    visibility: hidden !important;
    pointer-events: none !important;
}
body.checkout-submit-lock #checkout-submit-overlay {
    opacity: 1 !important;
    visibility: visible !important;
    pointer-events: auto !important;
}
.checkout-submit-overlay__box {
    min-width: 220px;
    padding: 20px 24px;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,.1);
    background: rgba(18, 19, 40, .96);
    box-shadow: 0 20px 60px rgba(0,0,0,.35);
    text-align: center;
}
.checkout-submit-overlay__spinner {
    width: 40px;
    height: 40px;
    margin: 0 auto 12px;
    border-radius: 50%;
    border: 3px solid rgba(255,255,255,.18);
    border-top-color: #7867ff;
    animation: checkoutSubmitSpin .8s linear infinite;
}
.checkout-submit-overlay__text {
    color: #fff;
    font-size: 14px;
    font-weight: 600;
}
@keyframes checkoutSubmitSpin {
    to { transform: rotate(360deg); }
}


</style>
<?= $this->end() ?>


<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-65TVKVNEEW"></script>
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'G-65TVKVNEEW');

    var sessionBooster = sessionStorage.getItem('booster');
    var boosterInput = document.getElementById('booster_id');

    if (sessionBooster && boosterInput) {
        boosterInput.value = sessionBooster;
    }

    document.getElementById('complete_payment').addEventListener('click', function () {
        if (getSelectedProcessor() !== 'stripe_paypal') {
            resetPaypalConfirmation();
        }

        // Do not clear the selected booster before checkout has actually finished.
        // Clearing it on click can lead to inconsistent UI state when the payment flow is still loading or fails.
    });
</script>
