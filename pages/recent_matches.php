<?php
require_once('../global_functions.php');
require_once('../global_variables.php');
require_once('../connections/parameters.php');

try {
    if (!isset($_SESSION)) {
        session_start();
    }

    $db = new dbWrapper_v3($hostname_my_gds_site, $username_my_gds_site, $password_my_gds_site, $database_my_gds_site, true);
    if (empty($db)) throw new Exception('No DB!');


    $memcached = new Cache(NULL, NULL, $useMemcache);

    echo '<h2>Recent Matches</h2>';

    if (!checkLogin()) {
        //throw new Exception('Not logged in!');
        $userID64 = '76561197989020883';
        echo '<p>Showing games for jimmydorry, as you are not logged in.</p>';
    } else {
        $userID64 = $_SESSION['user_id64'];

        $recentMatches = cached_query(
            'recent_matches_uid' . $userID64,
            'SELECT
                    mmrm.`steamID64`,
                    mmrm.`matchID`,
                    lmr.`matchResultName`,
                    mmrm.`matchHeroID`,
                    mmrm.`mmrSolo`,
                    mmrm.`mmrParty`,
                    mmrm.`mmrChange`,
                    mmrm.`badgeProgress`,
                    lb.`badgeDisplayedName`,
                    mmrm.`badgeDisplayedPip`,
                    lms.`matchSearchTypeName`,
                    lmm.`matchModeName`,
                    mmrm.`matchTeamChallengeToken`,
                    mmrm.`matchSeason`,
                    mmrm.`matchCallibration`,
                    mmrm.`matchGOSUCheaters`,
                    mmrm.`matchGOSUCheatersDesc`,
                    mmrm.`matchNotes`,
                    mmrm.`matchDateRecorded`
                FROM `mmr_matches` mmrm
                LEFT JOIN `lookup_match_results` lmr ON mmrm.`matchResultID` = lmr.`matchResultID`
                LEFT JOIN `lookup_badges` lb ON mmrm.`badgeDisplayedID` = lb.`badgeDisplayedID`
                LEFT JOIN `lookup_match_searches` lms ON mmrm.`matchSearchTypeID` = lms.`matchSearchTypeID`
                LEFT JOIN `lookup_match_modes` lmm ON mmrm.`matchModeID` = lmm.`matchModeID`
                WHERE mmrm.`steamID64` = ? AND mmrm.`matchCallibration` IS NULL
                ORDER BY mmrm.`matchDateRecorded` DESC;',
            's',
            $userID64,
            1
        );

        if (empty($recentMatches)) {
            $userID64 = '76561197989020883';

            echo '<p>Hard-coded for jimmydorry, because you have no matches added yet.</p>';
        }
    }

    if (empty($recentMatches)) {
        $recentMatches = cached_query(
            'recent_matches_uid' . $userID64,
            'SELECT
                    mmrm.`steamID64`,
                    mmrm.`matchID`,
                    lmr.`matchResultName`,
                    mmrm.`matchHeroID`,
                    mmrm.`mmrSolo`,
                    mmrm.`mmrParty`,
                    mmrm.`mmrChange`,
                    mmrm.`badgeProgress`,
                    lb.`badgeDisplayedName`,
                    mmrm.`badgeDisplayedPip`,
                    lms.`matchSearchTypeName`,
                    lmm.`matchModeName`,
                    mmrm.`matchTeamChallengeToken`,
                    mmrm.`matchSeason`,
                    mmrm.`matchCallibration`,
                    mmrm.`matchGOSUCheaters`,
                    mmrm.`matchGOSUCheatersDesc`,
                    mmrm.`matchNotes`,
                    mmrm.`matchDateRecorded`
                FROM `mmr_matches` mmrm
                LEFT JOIN `lookup_match_results` lmr ON mmrm.`matchResultID` = lmr.`matchResultID`
                LEFT JOIN `lookup_badges` lb ON mmrm.`badgeDisplayedID` = lb.`badgeDisplayedID`
                LEFT JOIN `lookup_match_searches` lms ON mmrm.`matchSearchTypeID` = lms.`matchSearchTypeID`
                LEFT JOIN `lookup_match_modes` lmm ON mmrm.`matchModeID` = lmm.`matchModeID`
                WHERE mmrm.`steamID64` = ? AND mmrm.`matchCallibration` IS NULL
                ORDER BY mmrm.`matchDateRecorded` DESC;',
            's',
            $userID64,
            1
        );
        if (empty($recentMatches)) throw new Exception("No matches to display!");
    }

    echo "<table class='recentMatches'>
        <tr>
            <th colspan='4'>Match</th>
            <th colspan='3'>MMR</th>
            <th colspan='2'>Badge</th>
            <th colspan='2'>Parameters</th>
            <th rowspan='2' colspan='2'>GOSU.AI Cheaters</th>
            <th rowspan='2'>Date</th>
        </tr>
        <tr>
            <th>ID</th>
            <th>Result</th>
            <th colspan='2'>Champion</th>
            <th>Solo</th>
            <th>Party</th>
            <th>Change</th>
            <th>Displayed</th>
            <th>Progress</th>
            <th>Type</th>
            <th>Mode</th>
        </tr>
    ";
    foreach ($recentMatches as $key => $value) {
        switch ($value['matchGOSUCheaters']) {
            case 1:
                $matchGOSUCheaters = 'Yes';
                break;
            case 0:
                $matchGOSUCheaters = 'No';
                break;
            default:
                $matchGOSUCheaters = '??';
                break;
        }

        if ($value['matchResultName'] == 'Loss') {
            $matchResultColour = ' class="red text-centre"';
        } else if ($value['matchResultName'] == 'Win') {
            $matchResultColour = ' class="green text-centre"';
        } else {
            $matchResultColour = ' class="text-centre"';
        }

        if ($value['matchGOSUCheaters'] == 1) {
            $matchGOSUCheatersColour = ' class="red text-centre"';
        } else {
            $matchGOSUCheatersColour = ' class="text-centre"';
        }

        if ($value['mmrChange'] > 25) {
            $matchMMRChangeColour = ' class="dark_green text-centre"';
        } else if ($value['mmrChange'] < -25) {
            $matchMMRChangeColour = ' class="dark_red text-centre"';
        } else if ($value['mmrChange'] > 0) {
            $matchMMRChangeColour = ' class="green text-centre"';
        } else if ($value['mmrChange'] < 0) {
            $matchMMRChangeColour = ' class="red text-centre"';
        } else {
            $matchMMRChangeColour = ' class="text-centre"';
        }

        $matchDate = relative_time_v3($value['matchDateRecorded']);

        !empty($value['matchHeroID']) && is_numeric($value['matchHeroID'])
            ? $heroArray = $heroes[$value['matchHeroID']]
            : $heroArray = $heroes[0];

        $heroIMG = $CDN_image . '/images/heroes/' . $heroArray['pic'] . '.png';

        echo "<tr>
                <td class='text-centre'>{$value['matchID']}</td>
                <td{$matchResultColour}>{$value['matchResultName']}</td>
                <td class='text-centre heroImage'><img src='{$heroIMG}' height='21' ></td>
                <td class='text-left'>{$heroArray['name_formatted']}</td>
                <td class='text-right'>{$value['mmrSolo']}</td>
                <td class='text-right'>{$value['mmrParty']}</td>
                <td{$matchMMRChangeColour}>{$value['mmrChange']}</td>
                <td class='text-centre'>{$value['badgeDisplayedName']} {$value['badgeDisplayedPip']}</td>
                <td class='text-centre'>{$value['badgeProgress']} %</td>
                <td class='text-centre'>{$value['matchSearchTypeName']}</td>
                <td class='text-centre'>{$value['matchModeName']}</td>
                <td{$matchGOSUCheatersColour}>{$matchGOSUCheaters}</td>
                <td>{$value['matchGOSUCheatersDesc']}</td>
                <td class='text-right'>{$matchDate}</td>
            </tr>";
    }
    echo "</table>";

} catch (Exception $e) {
    echo formatExceptionHandling($e);
} finally {
    if (isset($memcached)) $memcached->close();
}