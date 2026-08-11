<section class="container pt-5">
    <div class="container">
        <div class="row">
            <div class="col text-center">
                <h2><?= t('Current Orders in Progress') ?></h2>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="slider-container">
                    <div class="swiper-container">

                        <div class="swiper-wrapper" id="slider-container">
                        </div>
                    </div>
                </div>
            </div>
</section>

<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script>
    const rankMapping = {
        "Bronze": 2,
        "Silver": 3,
        "Gold": 4,
        "Platinum": 5,
        "Emerald": 6,
        "Diamond": 7,
        "Master": 8
    };

    const boosterLogos = {
        Fxbi: "<?= ASSET_URL ?>/core/main/img/icons/lol-flat-black.png",
        Melina: "<?= ASSET_URL ?>/core/main/img/icons/lol-flat-black.png",
        Tardis: "<?= ASSET_URL ?>/core/main/img/icons/lol-flat-black.png",
        Trap40: "<?= ASSET_URL ?>/core/main/img/icons/lol-flat-black.png",
        Xervoras: "<?= ASSET_URL ?>/core/main/img/icons/lol-flat-black.png",
        Daksider: "<?= ASSET_URL ?>/core/main/img/icons/lol-flat-black.png",
        NoName: "<?= ASSET_URL ?>/core/main/img/icons/lol-flat-black.png",
        Fylinxx: "<?= ASSET_URL ?>/core/main/img/icons/lol-flat-black.png",
        JerzyHook: "<?= ASSET_URL ?>/core/main/img/icons/lol-flat-black.png",
        crewdark: "<?= ASSET_URL ?>/core/main/img/icons/lol-flat-black.png",
        TheEiyuu: "<?= ASSET_URL ?>/core/main/img/icons/lol-flat-black.png",
        Hakaisu: "<?= ASSET_URL ?>/core/main/img/icons/lol-flat-black.png",
        hogerr: "<?= ASSET_URL ?>/core/main/img/icons/lol-flat-black.png",
        Hedwig: "<?= ASSET_URL ?>/core/main/img/icons/lol-flat-black.png",
        Stathis: "<?= ASSET_URL ?>/core/main/img/icons/lol-flat-black.png",
        Liszka: "<?= ASSET_URL ?>/core/main/img/icons/lol-flat-black.png",
        zera: "<?= ASSET_URL ?>/core/main/img/icons/lol-flat-black.png"

    };

    const getRandomColor = () => {
        const colors = ['#8800FF', '#00FF00', '#fb0081'];
        return colors[Math.floor(Math.random() * colors.length)];
    };


    const cardsData = [
        { initial: "Bronze I", target: "Master", current: "Diamond IV", progress: 70, booster: "Fxbi", boosterClass: "fxbi" },
        { initial: "Gold II", target: "Emerald II", current: "Platinum IV", progress: 25, booster: "Xervoras", boosterClass: "xervoras" },
        { initial: "Silver II", target: "Diamond III", current: "Platinum I", progress: 72, booster: "Melina", boosterClass: "melina" },
        { initial: "Bronze III", target: "Platinum I", current: "Gold IV", progress: 55, booster: "Trap40", boosterClass: "melina" },
        { initial: "Gold IV", target: "Diamond IV", current: "Platinum III", progress: 45, booster: "JerzyHook", boosterClass: "fxbi" },
        { initial: "Silver I", target: "Diamond I", current: "Gold II", progress: 30, booster: "NoName", boosterClass: "melina" },
        { initial: "Gold III", target: "Emerald I", current: "Platinum II", progress: 60, booster: "Daksider", boosterClass: "fxbi" },
        { initial: "Bronze II", target: "Gold III", current: "Silver III", progress: 40, booster: "Fylinxx", boosterClass: "xervoras" },
        { initial: "Silver IV", target: "Platinum II", current: "Gold I", progress: 50, booster: "crewdark", boosterClass: "melina" },
        { initial: "Gold I", target: "Master", current: "Diamond III", progress: 80, booster: "Stathis", boosterClass: "fxbi" },
        { initial: "Bronze IV", target: "Emerald III", current: "Silver IV", progress: 20, booster: "Hedwig", boosterClass: "xervoras" },
        { initial: "Silver III", target: "Diamond II", current: "Gold IV", progress: 35, booster: "TheEiyuu", boosterClass: "melina" },
        { initial: "Gold II", target: "Platinum III", current: "Platinum IV", progress: 75, booster: "Hakaisu", boosterClass: "fxbi" },
        { initial: "Gold I", target: "Diamond II", current: "Platinum I", progress: 68, booster: "hogerr", boosterClass: "xervoras" },
        { initial: "Silver II", target: "Gold I", current: "Silver I", progress: 28, booster: "Tardis", boosterClass: "melina" },
        { initial: "Bronze II", target: "Silver IV", current: "Bronze III", progress: 34, booster: "Trap40", boosterClass: "fxbi" },
        { initial: "Gold III", target: "Emerald IV", current: "Platinum III", progress: 61, booster: "Xervoras", boosterClass: "xervoras" },
        { initial: "Silver IV", target: "Diamond I", current: "Gold III", progress: 47, booster: "Melina", boosterClass: "melina" },
        { initial: "Bronze I", target: "Gold II", current: "Silver II", progress: 53, booster: "Tardis", boosterClass: "fxbi" },
        { initial: "Silver III", target: "Emerald II", current: "Gold IV", progress: 65, booster: "Liszka", boosterClass: "xervoras" },
        { initial: "Gold IV", target: "Diamond III", current: "Platinum II", progress: 78, booster: "Trap40", boosterClass: "melina" },
        { initial: "Bronze I", target: "Emerald III", current: "Silver IV", progress: 25, booster: "zera", boosterClass: "fxbi" },
        { initial: "Silver II", target: "Diamond IV", current: "Gold III", progress: 40, booster: "Liszka", boosterClass: "xervoras" },
        { initial: "Gold I", target: "Master", current: "Diamond II", progress: 82, booster: "crewdark", boosterClass: "melina" },
        { initial: "Bronze III", target: "Platinum II", current: "Silver II", progress: 30, booster: "hogerr", boosterClass: "fxbi" },
        { initial: "Silver I", target: "Diamond III", current: "Gold I", progress: 52, booster: "Stathis", boosterClass: "xervoras" },
        { initial: "Gold IV", target: "Emerald I", current: "Platinum IV", progress: 75, booster: "Melina", boosterClass: "melina" },
        { initial: "Bronze IV", target: "Gold I", current: "Silver III", progress: 23, booster: "Xervoras", boosterClass: "fxbi" },
        { initial: "Silver III", target: "Platinum I", current: "Gold II", progress: 56, booster: "JerzyHook", boosterClass: "xervoras" },
        { initial: "Gold II", target: "Diamond II", current: "Platinum III", progress: 67, booster: "Trap40", boosterClass: "melina" },
        { initial: "Bronze II", target: "Emerald II", current: "Silver I", progress: 31, booster: "Fxbi", boosterClass: "fxbi" },
        { initial: "Silver IV", target: "Gold II", current: "Silver III", progress: 44, booster: "Tardis", boosterClass: "xervoras" },
    ];

    function getRankImage(rank) {
        const rankName = rank.split(" ")[0];
        const rankNumber = rankMapping[rankName];
        return `<?= ASSET_URL ?>/core/main/img/lol/ranks/mini/${rankNumber}.png`;
    }

    function generateCard(cardData) {
        return `
                 <div class="swiper-slide">
            <div class="card mx-2 text-left mt-3 ps-2">
                <div class="card-header w-100">
                    <div class="progress-text">Progress</div>
                    <div class="progress mb-1" style="height: 5px;">
                        <div class="progress-bar" role="progressbar" style="width: ${cardData.progress}%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="text-center progress-text">${cardData.progress}%</div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span style="margin-left: 10px;" class="custom-bold">${cardData.initial}</span>
                        <div class="d-flex justify-content-center align-items-center">
                            <i class="fa-light fa-angles-right"></i>
                        </div>
                        <span style="margin-right: 10px;" class="custom-bold">${cardData.target}</span>
                    </div>
                </div>
                <div class="card-body">
                    <h6 class="rank-title">Initial Rank</h6>
                    <p class="mt-2 mb-1 rank-detail"><img src="${getRankImage(cardData.initial)}" class="rank-logo" alt="Rank"> ${cardData.initial}</p>
                    <h6 class="rank-title">Target Rank</h6>
                    <p class="mt-2 mb-1 rank-detail"><img src="${getRankImage(cardData.target)}" class="rank-logo" alt="Rank"> ${cardData.target}</p>
                    <h6 class="rank-title">Current Rank</h6>
                    <p class="mt-2 mb-1 rank-detail"><img src="${getRankImage(cardData.current)}" class="rank-logo" alt="Rank"> ${cardData.current}</p>
                </div>
                <div class="d-flex align-items-center booster-info">
                    <img src="${boosterLogos[cardData.booster]}" alt="${cardData.booster}" class="booster-logo">
                    <div class="custom-bold">Booster</div>
                    <div class="${cardData.boosterClass}">${cardData.booster}</div>
                </div>
            </div>
        </div>
            `;
    }

    const cardContainer = document.getElementById('slider-container');
    cardsData.forEach(cardData => {
        cardContainer.innerHTML += generateCard(cardData);
    });

    const swiper = new Swiper('.swiper-container', {
        slidesPerView: 1,
        spaceBetween: 10,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        autoplay: {
            delay: 3000, // 2 seconds
        },
        breakpoints: {
            400: {
                slidesPerView: 1,
            },
            650: {
                slidesPerView: 2,
            },
            1000: {
                slidesPerView: 2,
            },
            1200: {
                slidesPerView: 3,
            },
        },
    });

</script>
<div class="container">
    <!-- Inhalt des oberen Containers -->
</div>
<div class="spacer"></div>
<div class="container">
    <!-- Inhalt des unteren Containers -->
</div>

</script>
</body>

</html>

<style>
    .progress-text {
        font-size: 14px;
        font-weight: bold;
        margin-left: 10px;
    }

    .rank-logo {
        width: 30px;
        height: 30px;
    }

    .booster-logo {
        width: 20px;
        height: 20px;
        margin-right: 5px;
    }

    .booster-info {
        margin-left: 10px;
        margin-top: 10px;
    }

    .custom-bold {
        font-weight: bold;
    }
</style>