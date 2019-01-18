<?php
require_once('../global_functions.php');
require_once('../global_functions_v2.php');
require_once('../global_variables.php');
require_once('../connections/parameters.php');

try {
    if (!isset($_SESSION)) {
        session_start();
    }

    $db = new dbWrapper_v3($hostname_my_gds_site, $username_my_gds_site, $password_my_gds_site, $database_my_gds_site, true);
    if (empty($db)) throw new Exception('No DB!');

    $memcached = new Cache(NULL, NULL, $useMemcache);

    if (!checkLogin()) throw new Exception('Not logged in!');
    $userID64 = $_SESSION['user_id64'];

    $steamID = new SteamID($userID64 = $_SESSION['user_id64']);
    $steamID32 = $steamID->getsteamID32();
    $steamID64 = $steamID->getSteamID64();

    $webAPI = new dota2_webapi_v2($api_key1);

    $lastMatchSQL = cached_query(
        'mygds_last_match2_' . $steamID64,
        'SELECT MAX(`matchID`) as `matchID` FROM `mmr_matches` WHERE `steamID64` = ?;',
        's',
        array(
            $steamID64
        ),
        1
    );
    if (empty($lastMatchSQL)) {
        $lastMatch = 0;
    } else {
        $lastMatch = $lastMatchSQL[0]['matchID'];
    }

    $recentMatches = $memcached->get('mygds_GetMatchHistory_' . $steamID32);
    if (empty($recentMatches)) {
        $recentMatches = $webAPI->GetMatchHistory($steamID32);
        $memcached->set('mygds_GetMatchHistory_' . $steamID32, $recentMatches, 10 * 60);
    }

    $rankedMatches = array();
    foreach ($recentMatches['result']['matches'] as $key => $value) {
        if ($value['match_id'] <= $lastMatch) {
            //echo $value['match_id'] . ' vs. ' . $lastMatch;
            break;
        }
        if ($value['lobby_type'] == 7) { //GRAB ONLY MATCHES THAT ARE RANKED
            foreach ($value['players'] as $key2 => $value2) {
                if ($value2['account_id'] == $steamID32) {
                    $rankedMatches[$value['match_id']] = $value2['hero_id'];
                    $heroName = $heroes[$value2['hero_id']]['name_formatted'];
                    echo "Added match: {$value['match_id']} || {$heroName}";
                }
            }
        }
    }

    //https://api.steampowered.com/IDOTA2Match_570/GetMatchDetails/v1/?key=___KEY___&format=json&match_id=4185073260


} catch (Exception $e) {
    echo formatExceptionHandling($e);
} finally {
    if (isset($memcached)) $memcached->close();
}
