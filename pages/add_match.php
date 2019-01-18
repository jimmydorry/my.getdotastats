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

    echo '<h2>Add a Match</h2>';

    if (!checkLogin()) throw new Exception('Not logged in!');
    $userID64 = $_SESSION['user_id64'];

    echo "<p>All fields to be filled out after match is finished.</p>";

    $last_match_details_SQL = $db->q(
        'SELECT
                mmrm.`mmrSolo`,
                mmrm.`mmrParty`,
                mmrm.`badgeProgress`,
                mmrm.`badgeDisplayedID`,
                mmrm.`badgeDisplayedPip`,
                mmrm.`matchSearchTypeID`,
                mmrm.`matchModeID`
            FROM `mmr_matches` mmrm
            WHERE
              mmrm.`steamID64` = ?
              AND mmrm.`matchID` = (
                SELECT
                  MAX(mmrm2.`matchID`)
                FROM `mmr_matches` mmrm2
                WHERE mmrm2.`steamID64` = ?
              )
            GROUP BY mmrm.`steamID64`;',
        'ss',
        array($userID64, $userID64)
    );
    $last_match_details_SQL = empty($last_match_details_SQL)
        ? array(
            'mmrSolo' => 0,
            'mmrParty' => 0,
            'badgeProgress' => 0,
            'badgeDisplayedID' => 0,
            'badgeDisplayedPip' => 0,
            'matchSearchTypeID' => 0,
            'matchModeID' => 0,
        )
        : $last_match_details_SQL[0];

    $match_badge_displayed_SQL = $db->q(
        'SELECT
            lb.`badgeDisplayedID`,
            lb.`badgeDisplayedName`
            FROM `lookup_badges` lb
            ORDER BY lb.`badgeDisplayedID` DESC;'
    );

    if (!empty($match_badge_displayed_SQL)) {
        $match_badge_displayed = '';
        foreach ($match_badge_displayed_SQL as $key => $value) {
            $isSelected = $value['badgeDisplayedID'] == $last_match_details_SQL['badgeDisplayedID']
                ? ' selected="selected"'
                : '';
            $match_badge_displayed .= '<option value="' . $value['badgeDisplayedID'] . '"' . $isSelected . '>' . $value['badgeDisplayedName'] . '</option>';
        }
    }

    $match_mode_SQL = $db->q(
        'SELECT
            lmm.`matchModeID`,
            lmm.`matchModeName`
            FROM `lookup_match_modes` lmm
            ORDER BY lmm.`matchModeID`;'
    );

    if (!empty($match_mode_SQL)) {
        $match_mode = '';
        foreach ($match_mode_SQL as $key => $value) {
            $isSelected = $value['matchModeID'] == $last_match_details_SQL['matchModeID']
                ? ' selected="selected"'
                : '';
            $match_mode .= '<option value="' . $value['matchModeID'] . '"' . $isSelected . '>' . $value['matchModeName'] . '</option>';
        }
    }

    $heroes_played_SQL = $db->q(
        'SELECT
              mmrm.`matchID`,
              mmrm.`matchHeroID`,
              MAX(mmrm.`matchDateRecorded`) as `matchDateRecorded`
            FROM `mmr_matches` mmrm
            WHERE `steamID64` = ?
            GROUP BY `matchHeroID`
            ORDER BY 3 DESC;',
        's',
        array($userID64)
    );
    $hero_options_array = array();
    if (!empty($heroes_played_SQL)) {
        foreach ($heroes_played_SQL as $key => $value) {
            $hero_options_array[$value['matchHeroID']] = $heroes[$value['matchHeroID']]['name_formatted'];
        }
    }

    if (empty($heroes)) throw new Exception('No heroes list to select from!');
    $hero_options = '';

    if (!empty($hero_options_array)) {
        foreach ($heroes as $key => $value) {
            if (!array_key_exists($key, $hero_options_array)) {
                $hero_options_array[$key] = $value['name_formatted'];
            }
        }
        foreach ($hero_options_array as $key => $value) {
            $hero_options .= '<option value="' . $key . '">' . $value . '</option>';
        }
    } else {
        foreach ($heroes as $key => $value) {
            $hero_options .= '<option value="' . $key . '">' . $value['name_formatted'] . '</option>';
        }
    }

    echo "<form id='addMatch'>";
    echo "<table>
            <!--<tr>
                <th class='text-left'>Result</th>
                <td>
                    <select name='match_result' required>
                        <option value=''>--- Select ---</option>
                        <option value='1'>Loss</option>
                        <option value='2'>Win</option>
                    </select>
                </td>
            </tr>-->
            <tr>
                <th class='text-left'>MatchID</th>
                <td><input name='match_id' type='number' required></td>
            </tr>
            <tr>
                <th class='text-left'>Hero</th>
                <td>
                    <select name='match_hero' required>
                        <option value=''>--- Select ---</option>
                        {$hero_options}
                    </select>
                </td>
            </tr>
            <tr>
                <th class='text-left'>Solo MMR</th>
                <td><input name='match_solo_mmr' type='text' value='{$last_match_details_SQL["mmrSolo"]}' required></td>
            </tr>
            <tr>
                <th class='text-left'>Party MMR</th>
                <td><input name='match_party_mmr' type='text' value='{$last_match_details_SQL["mmrParty"]}' required></td>
            </tr>
            <tr>
                <th class='text-left'>Displayed Badge</th>
                <td>
                    <select name='match_badge_displayed' required>
                        <option value=''>--- Select ---</option>
                        {$match_badge_displayed}
                    </select>
                </td>
            </tr>
            <tr>
                <th class='text-left'>Badge Pip</th>
                <td><input name='match_badge_pip' type='number' value='{$last_match_details_SQL["badgeDisplayedPip"]}' required></td>
            </tr>
            <tr>
                <th class='text-left'>Badge Progress</th>
                <td><input name='match_badge_progress' type='number' value='{$last_match_details_SQL["badgeProgress"]}' required></td>
            </tr>
            <tr>
                <th class='text-left'>Match Mode</th>
                <td>
                    <select name='match_mode' required>
                        <option value=''>--- Select ---</option>
                        {$match_mode}
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan='2' class='text-centre'>
                    <input type='submit' value='Add Match'>
                </td>
            </tr>
        </table>";

    echo "<input type='hidden' name='old_solo_mmr' value='{$last_match_details_SQL["mmrSolo"]}'>";
    echo "<input type='hidden' name='old_party_mmr' value='{$last_match_details_SQL["mmrParty"]}'>";

    echo "</form>";

    echo "<br />";

    echo "<span id='result'></span>";


} catch (Exception $e) {
    echo formatExceptionHandling($e);
} finally {
    if (isset($memcached)) $memcached->close();
}

?>

<script type="application/javascript">
    $("#addMatch").submit(function (event) {
        event.preventDefault();

        $.post("./pages/add_match_process.php", $("#addMatch").serialize(), function (data) {
            $("#addMatch :input").each(function () {
                $(this).val('');
            });

            $('#result').html(data);

            if (data.includes('Success')) {
                //loadPage(document.getElementById("nav-refresh-holder").getAttribute("href"));
                loadPage('#recent_matches');
            }
        }, 'text');
    });
</script>