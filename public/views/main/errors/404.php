<?= $this->layout('main/layouts/blank', ['meta' => $meta]) ?>

<style>
.err-wrapper {
    min-height: 100vh;
    background: #0a0d1f;
    position: relative;
    overflow: hidden;
    font-family: inherit;
}
#err-canvas {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 0;
}
/* Subtle radial glow in center like the homepage */
.err-glow {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 700px;
    height: 700px;
    background: radial-gradient(ellipse at center, rgba(99,85,255,0.18) 0%, transparent 70%);
    pointer-events: none;
    z-index: 0;
}
.err-inner {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    text-align: center;
    padding: 2rem 1rem;
}
.err-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 2rem;
    color: #6c63ff;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
}
.err-eyebrow::before,
.err-eyebrow::after {
    content: '';
    display: block;
    width: 32px;
    height: 2px;
    background: #6c63ff;
    border-radius: 2px;
}
.err-num {
    font-size: clamp(100px, 22vw, 180px);
    font-weight: 900;
    line-height: 1;
    background: linear-gradient(135deg, #818cf8 0%, #6c63ff 40%, #a855f7 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display: inline-block;
    margin-bottom: 0.25rem;
    animation: err-float 5s ease-in-out infinite;
    filter: drop-shadow(0 0 40px rgba(108,99,255,0.4));
}
@keyframes err-float {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-12px); }
}
.err-title {
    font-size: clamp(20px, 3vw, 32px);
    font-weight: 700;
    color: #fff;
    margin-bottom: 0.75rem;
}
.err-sub {
    font-size: 15px;
    color: rgba(255,255,255,0.4);
    max-width: 380px;
    line-height: 1.65;
    margin-bottom: 2.5rem;
}
.err-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #6c63ff, #a855f7);
    border: none;
    border-radius: 50px;
    padding: 14px 32px;
    font-size: 15px;
    font-weight: 700;
    color: #fff;
    text-decoration: none;
    transition: opacity .2s, transform .15s;
    box-shadow: 0 4px 24px rgba(108,99,255,0.35);
}
.err-btn:hover {
    color: #fff;
    opacity: .88;
    transform: translateY(-2px);
    text-decoration: none;
}
</style>

<main class="err-wrapper">

    <canvas id="err-canvas"></canvas>
    <div class="err-glow"></div>

    <div class="err-inner">

        <div class="err-eyebrow">Page not found</div>

        <div class="err-num">404</div>

        <h1 class="visually-hidden">404</h1>
        <h2 class="err-title">You got lost on the rift.</h2>
        <p class="err-sub">The page you are looking for is not available.</p>

        <a href="<?= BASE_URL ?>" class="err-btn">
            <i class="fa-duotone fa-home"></i>
            Go to Homepage
        </a>

    </div>

</main>

<script>
(function () {
    var c = document.getElementById('err-canvas');
    var ctx = c.getContext('2d');
    var stars = [];

    function resize() {
        c.width  = window.innerWidth;
        c.height = window.innerHeight;
    }
    resize();
    window.addEventListener('resize', resize);

    for (var i = 0; i < 220; i++) {
        stars.push({
            x: Math.random(),
            y: Math.random(),
            r: Math.random() * 1.2 + 0.2,
            speed: Math.random() * 0.003 + 0.001,
            phase: Math.random() * Math.PI * 2
        });
    }

    function draw() {
        ctx.clearRect(0, 0, c.width, c.height);
        var t = Date.now() / 1000;
        for (var i = 0; i < stars.length; i++) {
            var s = stars[i];
            var alpha = 0.2 + 0.5 * Math.abs(Math.sin(t * s.speed * 10 + s.phase));
            ctx.beginPath();
            ctx.arc(s.x * c.width, s.y * c.height, s.r, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(160,150,255,' + alpha + ')';
            ctx.fill();
        }
        requestAnimationFrame(draw);
    }
    draw();
})();
</script>
