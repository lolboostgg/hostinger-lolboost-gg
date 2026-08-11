    <style>
        .border-left-red {
            border-left: 5px solid #dc2626;
        }
        .border-left-green {
            border-left: 5px solid #16a34a;
        }
        .champion-img {
            width: 62px;
            height: 62px;
        }
        .spell-icon {
            width: 30px;
            height: 30px;
            margin: 0 2px;
        }
        .item-icon {
            width: 30px;
            height: 30px;
            border: 1px solid #f3f6ff;
            border-radius: 50%;
        }
        .hidden-item-icon {
            width: 30px;
            height: 30px;
            border: 1px solid #f3f6ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .player-row {
            border-top: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            margin-bottom: 10px;
        }
        .text-strong {
            font-weight: bold;
        }
        .rank-color-diamond {
            color: #5b77e0;
        }
        .rank-color-platinum {
            color: #52a09b;
        }
        .rank-color-gold {
            color: #d7893e;
        }
        .item-grid {
            display: grid;
            grid-template-columns: repeat(7, 30px);
            gap: 5px;
        }
        @media (max-width: 768px) {
            .champion-img {
                width: 36px;
                height: 36px;
            }
            .spell-icon {
                width: 18px;
                height: 18px;
                margin: 0 1px;
            }
            .item-icon,
            .hidden-item-icon {
                width: 18px;
                height: 18px;
                border: 1px solid #f3f6ff;
                border-radius: 50%;
            }
            .player-row {
                margin-bottom: 5px;
            }
            .item-grid {
                gap: 2px;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-3">
        <h2 class="text-center">Recent Games</h2>
        <p class="text-center">Just take a look at our recently finished orders and see the results for yourself.</p>
        <div id="player-stats-container">
            <!-- Dynamically inserted player rows will appear here -->
        </div>
    </div>

    <script>
        const players = [
            {
                name: "Tardis",
                queue: "Solo Queue",
                kda: "6.38",
                played: "1 minute ago",
                rank: "Diamond IV",
                rankColor: "rank-color-diamond",
                championImg: "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/champion/Fiora.png",
                spells: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerFlash.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerTeleport.png"
                ],
                items: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3074.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3047.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3340.png",
                ],
                borderColor: 0 // 0 für grün
            },
            {
                name: "Melina",
                queue: "Duo Queue",
                kda: "7.45",
                played: "4 minutes ago",
                rank: "Platinum II",
                rankColor: "rank-color-platinum",
                championImg: "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/champion/Lux.png",
                spells: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerBarrier.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerFlash.png"
                ],
                items: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3020.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/4628.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3340.png",
                ],
                borderColor: 0 // 0 für grün
            },
            {
                name: "Majd",
                queue: "Solo Queue",
                kda: "5.12",
                played: "8 minutes ago",
                rank: "Diamond IV",
                rankColor: "rank-color-diamond",
                championImg: "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/champion/Nidalee.png",
                spells: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerFlash.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerSmite.png"
                ],
                items: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3100.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3020.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3364.png",
                ],
                borderColor: 0 // 0 für grün
            },
            {
                name: "Xervoras",
                queue: "Solo Queue",
                kda: "7.20",
                played: "10 minutes ago",
                rank: "Platinum I",
                rankColor: "rank-color-platinum",
                championImg: "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/champion/Tristana.png",
                spells: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerFlash.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerDot.png"
                ],
                items: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/6672.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3006.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3363.png"
                ],
                borderColor: 1 // 1 für rot
            },
            {
                name: "Fxbi",
                queue: "Duo Queue",
                kda: "4.50",
                played: "13 minutes ago",
                rank: "Gold III",
                rankColor: "rank-color-gold",
                championImg: "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/champion/Hecarim.png",
                spells: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerSmite.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerHaste.png"
                ],
                items: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/6692.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3071.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3364.png",
                ],
                borderColor: 0 // 0 für grün
            },
            {
                name: "HaderrQ",
                queue: "Solo Queue",
                kda: "4.60",
                played: "16 minutes ago",
                rank: "Diamond I",
                rankColor: "rank-color-diamond",
                championImg: "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/champion/Taliyah.png",
                spells: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerDot.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerFlash.png"
                ],
                items: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3020.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/6653.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3340.png",
                ],
                borderColor: 0 // 0 für grün
            },
            {
                name: "Fylinxx",
                queue: "Duo Queue",
                kda: "4.10",
                played: "20 minutes ago",
                rank: "Diamond III",
                rankColor: "rank-color-diamond",
                championImg: "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/champion/Camille.png",
                spells: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerTeleport.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerFlash.png"
                ],
                items: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3078.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3047.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3340.png",
                ],
                borderColor: 1 // 1 für grün
            },
            {
                name: "Cres",
                queue: "Solo Queue",
                kda: "2.80",
                played: "25 minutes ago",
                rank: "Gold I",
                rankColor: "rank-color-gold",
                championImg: "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/champion/LeeSin.png",
                spells: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerFlash.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerSmite.png"
                ],
                items: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/6692.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3111.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3340.png",
                ],
                borderColor: 0 // 0 für grün
            },
            {
                name: "Zweise",
                queue: "Duo Queue",
                kda: "6.50",
                played: "32 minutes ago",
                rank: "Platinum IV",
                rankColor: "rank-color-platinum",
                championImg: "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/champion/Irelia.png",
                spells: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerTeleport.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerFlash.png"
                ],
                items: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3153.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3111.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3340.png",
                ],
                borderColor: 0 // 0 für grün
            },
            {
                name: "Fxbi",
                queue: "Solo Queue",
                kda: "4.20",
                played: "38 minutes ago",
                rank: "Gold II",
                rankColor: "rank-color-gold",
                championImg: "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/champion/Hecarim.png",
                spells: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerSmite.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerHaste.png"
                ],
                items: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3071.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/6692.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3364.png",
                ],
                borderColor: 0 // 0 für grün
            },
            {
                name: "Melina",
                queue: "Duo Queue",
                kda: "8.45",
                played: "45 minutes ago",
                rank: "Platinum II",
                rankColor: "rank-color-platinum",
                championImg: "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/champion/Lux.png",
                spells: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerBarrier.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerFlash.png"
                ],
                items: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/4628.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3020.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3363.png",
                ],
                borderColor: 0 // 0 für grün
            },
            {
                name: "Majd",
                queue: "Solo Queue",
                kda: "5.12",
                played: "52 minutes ago",
                rank: "Diamond I",
                rankColor: "rank-color-diamond",
                championImg: "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/champion/Nidalee.png",
                spells: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerFlash.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerSmite.png"
                ],
                items: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3020.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3100.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3364.png",
                ],
                borderColor: 0 // 0 für grün
            },
            {
                name: "Toxtune",
                queue: "Solo Queue",
                kda: "7.20",
                played: "60 minutes ago",
                rank: "Gold I",
                rankColor: "rank-color-gold",
                championImg: "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/champion/Evelynn.png",
                spells: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerFlash.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerSmite.png"
                ],
                items: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/223100.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/1082.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3364.png",
                ],
                borderColor: 0 // 0 für grün
            },
            {
                name: "Majd",
                queue: "Solo Queue",
                kda: "5.12",
                played: "52 minutes ago",
                rank: "Diamond I",
                rankColor: "rank-color-diamond",
                championImg: "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/champion/Nidalee.png",
                spells: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerFlash.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/spell/SummonerSmite.png"
                ],
                items: [
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3020.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3100.png",
                    "https://ddragon.leagueoflegends.com/cdn/14.11.1/img/item/3364.png",
                ],
                borderColor: 0 // 0 für grün
            },
        ];

        const playerStatsContainer = document.getElementById("player-stats-container");

        function createPlayerRow(player) {
            const row = document.createElement("div");

            // Entscheide die Border-Klasse basierend auf borderColor
            let borderClass;
            if (player.borderColor === 0) {
                borderClass = 'border-left-green';
            } else {
                borderClass = 'border-left-red';
            }

            row.className = `row player-row ${borderClass} p-2 mb-0`;

            row.innerHTML = `
                <div class="col-auto">
                    <img src="${player.championImg}" alt="Champion" class="champion-img">
                </div>
                <div class="col-auto d-flex flex-column justify-content-center">
                    <div class="d-flex">
                        <img src="${player.spells[0]}" alt="Blitz" class="spell-icon">
                        <img src="${player.spells[1]}" alt="Zerschmettern" class="spell-icon">
                    </div>
                </div>
                <div class="col-auto d-flex flex-column justify-content-center">
                    <strong>${player.name}</strong>  <span>${player.queue}</span>
                </div>
                <div class="col d-flex flex-column justify-content-center text-center">
                    <span><span class="text-strong">KDA Ratio:</span> ${player.kda}</span>
                    <span><span class="text-strong">Played:</span> ${player.played}</span>
                    <span><span class="text-strong">Rank:</span> <span class="${player.rankColor}">${player.rank}</span></span>
                </div>
                <div class="col-auto d-flex justify-content-end align-items-center">
                    <div class="item-grid">
                        <img src="${player.items[0]}" alt="Item" class="item-icon">
                        <img src="${player.items[1]}" alt="Item" class="item-icon">
                        <img src="${player.items[2]}" alt="Item" class="item-icon">
                        <div class="hidden-item-icon"><i class="fas fa-eye-slash"></i></div>
                        <div class="hidden-item-icon"><i class="fas fa-eye-slash"></i></div>
                        <div class="hidden-item-icon"><i class="fas fa-eye-slash"></i></div>
                        <div class="hidden-item-icon"><i class="fas fa-eye-slash"></i></div>
                    </div>
                </div>
            `;

            return row;
        }

        function displayPlayers() {
            playerStatsContainer.innerHTML = "";
            const numPlayersToShow = window.innerWidth <= 768 ? 2 : 5;
            for (let i = 0; i < Math.min(numPlayersToShow, players.length); i++) {
                const playerRow = createPlayerRow(players[i]);
                playerStatsContainer.appendChild(playerRow);
            }
        }

        function cyclePlayers() {
            players.push(players.shift());
            displayPlayers();
        }

        displayPlayers();
        setInterval(cyclePlayers, 3000);
    </script>

    