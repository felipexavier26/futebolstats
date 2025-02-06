<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classificação e Jogos de Campeonatos</title>

    <script type="module" src="https://widgets.api-sports.io/2.0.3/widgets.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container py-5">
        <h2 class="text-center mb-4">Escolha um Campeonato</h2>

        <form method="GET" class="d-flex justify-content-center mb-4">
            <select name="league" onchange="this.form.submit()" class="form-select w-50">
                <option value="71" <?php echo isset($_GET['league']) && $_GET['league'] == '71' ? 'selected' : ''; ?>>Campeonato Brasileiro</option>
                <option value="39" <?php echo isset($_GET['league']) && $_GET['league'] == '39' ? 'selected' : ''; ?>>Premier League</option>
                <option value="140" <?php echo isset($_GET['league']) && $_GET['league'] == '140' ? 'selected' : ''; ?>>La Liga</option>
            </select>
        </form>

        <?php
        $apiKey = "6a40a913a13dad0fc002b698872756e4";
        $leagueId = isset($_GET['league']) ? $_GET['league'] : '71';
        $season = "2023";

        function fetchApiData($url, $apiKey)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "x-apisports-key: $apiKey",
                "Content-Type: application/json"
            ]);
            $response = curl_exec($ch);
            curl_close($ch);
            return json_decode($response, true);
        }
        ?>

        <h3 class="mb-3 text-center">Classificação</h3>
        <div id="standingsContainer" class="mb-5">
            <div id="wg-api-football-standings"
                data-host="v3.football.api-sports.io"
                data-key="<?php echo $apiKey; ?>"
                data-league="<?php echo $leagueId; ?>"
                data-season="<?php echo $season; ?>"
                data-show-logos="true">
            </div>
        </div>

        <div id="lastResultsContainer">
            <?php
            $urlResults = "https://v3.football.api-sports.io/fixtures?league=$leagueId&season=$season&last=5";
            $resultsData = fetchApiData($urlResults, $apiKey);

            if (isset($resultsData['response']) && count($resultsData['response']) > 0) {
                foreach ($resultsData['response'] as $result) {
                    $homeTeam = $result['teams']['home']['name'];
                    $awayTeam = $result['teams']['away']['name'];
                    $scoreHome = $result['goals']['home'];
                    $scoreAway = $result['goals']['away'];

                    echo "
                    <div class='card mb-3'>
                        <div class='card-body'>
                            <h5 class='card-title'>$homeTeam $scoreHome x $scoreAway $awayTeam</h5>
                        </div>
                    </div>";
                }
            } else {
                echo "<p class='text-center'>Não há resultados recentes disponíveis.</p>";
            }
            ?>
        </div>

        <h3 class="mb-3 text-center">Buscar Jogos do Time</h3>
        <form method="GET" class="d-flex justify-content-center mb-4">
            <input type="text" name="team" class="form-control w-50" placeholder="Digite o nome do time">
            <button type="submit" class="btn btn-primary ms-2">Buscar</button>
        </form>

        <div id="teamResultsContainer">
            <?php
            if (!empty($_GET['team'])) {
                $teamName = $_GET['team'];
                $urlTeam = "https://v3.football.api-sports.io/teams?search=" . urlencode($teamName);
                $teamData = fetchApiData($urlTeam, $apiKey);

                if (isset($teamData['response'][0]['team']['id'])) {
                    $teamId = $teamData['response'][0]['team']['id'];

                    $urlTeamGames = "https://v3.football.api-sports.io/fixtures?team=$teamId&season=$season";
                    $teamGames = fetchApiData($urlTeamGames, $apiKey);

                    if (isset($teamGames['response']) && count($teamGames['response']) > 0) {
                        echo "<h4>Jogos Programados:</h4>";
                        foreach ($teamGames['response'] as $game) {
                            $homeTeam = $game['teams']['home']['name'];
                            $awayTeam = $game['teams']['away']['name'];
                            $dateTime = new DateTime($game['fixture']['date']);
                            $formattedDate = $dateTime->format('d/m/Y H:i');
                            $stadium = isset($game['fixture']['venue']['name']) ? $game['fixture']['venue']['name'] : 'Estádio não disponível';
                            echo "
                            <div class='card mb-3'>
                                <div class='card-body'>
                                    <h5 class='card-title'>$homeTeam vs $awayTeam</h5>
                                    <p><strong>Data e Hora:</strong> $formattedDate</p>
                                    <p><strong>Estádio:</strong> $stadium</p>
                                </div>
                            </div>";
                        }
                    }

                    $urlLastResults = "https://v3.football.api-sports.io/fixtures?team=$teamId&season=$season&last=5";
                    $lastResultsData = fetchApiData($urlLastResults, $apiKey);
                    if (isset($lastResultsData['response']) && count($lastResultsData['response']) > 0) {
                        echo "<h4>Últimos Resultados:</h4>";
                        foreach ($lastResultsData['response'] as $result) {
                            $homeTeam = $result['teams']['home']['name'];
                            $awayTeam = $result['teams']['away']['name'];
                            $scoreHome = $result['goals']['home'];
                            $scoreAway = $result['goals']['away'];

                            echo "
                            <div class='card mb-3'>
                                <div class='card-body'>
                                    <h5 class='card-title'>$homeTeam $scoreHome x $scoreAway $awayTeam</h5>
                                </div>
                            </div>";
                        }
                    } else {
                        echo "<p class='text-center'>Nenhum resultado encontrado para este time.</p>";
                    }
                } else {
                    echo "<p class='text-center'>Time não encontrado.</p>";
                }
            }
            ?>
        </div>

        <div id="wg-api-football-games"
            data-host="v3.football.api-sports.io"
            data-key="<?php echo $apiKey; ?>"
            data-league="<?php echo $leagueId; ?>"
            data-season="<?php echo $season; ?>"
            data-theme=""
            data-refresh="15"
            data-show-toolbar="true"
            data-show-errors="false"
            data-show-logos="true"
            data-modal-game="true"
            data-modal-standings="true"
            data-modal-show-logos="true">
        </div>

    </div>
</body>

</html>