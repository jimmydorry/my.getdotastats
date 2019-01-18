<?php
try {
    require_once('../global_functions.php');
    require_once('../connections/parameters.php');

    if (!isset($_SESSION)) {
        session_start();
    }

    $db = new dbWrapper_v3($hostname_my_gds_site, $username_my_gds_site, $password_my_gds_site, $database_my_gds_site, true);
    if (empty($db)) throw new Exception('No DB!');

    $memcached = new Cache(NULL, NULL, $useMemcache);

    if (!checkLogin()) throw new Exception('Not logged in!');
    $userID64 = $_SESSION['user_id64'];

    if (
        //!empty($_POST['match_result']) &&
        !empty($_POST['match_id']) &&
        isset($_POST['match_solo_mmr']) && is_numeric($_POST['match_solo_mmr']) &&
        isset($_POST['match_party_mmr']) && is_numeric($_POST['match_party_mmr']) &&
        !empty($_POST['match_badge_displayed']) &&
        isset($_POST['match_badge_pip']) && is_numeric($_POST['match_badge_pip']) &&
        isset($_POST['match_badge_progress']) && is_numeric($_POST['match_badge_progress']) &&
        //!empty($_POST['match_search_type']) &&
        !empty($_POST['match_hero']) &&
        !empty($_POST['match_mode']) &&

        isset($_POST['old_solo_mmr']) && is_numeric($_POST['old_solo_mmr']) &&
        isset($_POST['old_party_mmr']) && is_numeric($_POST['old_party_mmr'])
    ) {
        //$match_result = $db->escape($_POST['match_result']);
        $match_id = $db->escape($_POST['match_id']);
        $match_solo_mmr = $db->escape($_POST['match_solo_mmr']);
        $match_party_mmr = $db->escape($_POST['match_party_mmr']);
        $match_badge_displayed = $db->escape($_POST['match_badge_displayed']);
        $match_badge_pip = $db->escape($_POST['match_badge_pip']);
        $match_badge_progress = $db->escape($_POST['match_badge_progress']);
        $match_hero = $db->escape($_POST['match_hero']);
        //$match_search_type = $db->escape($_POST['match_search_type']);
        $match_mode = $db->escape($_POST['match_mode']);

        $old_solo_mmr = $db->escape($_POST['old_solo_mmr']);
        $old_party_mmr = $db->escape($_POST['old_party_mmr']);

        if ($match_solo_mmr != $old_solo_mmr) {
            $matchChange = $match_solo_mmr - $old_solo_mmr;
        } else if ($match_party_mmr != $old_party_mmr) {
            $matchChange = $match_party_mmr - $old_party_mmr;
        } else {
            $matchChange = 0;
        }

        if ($match_solo_mmr > $old_solo_mmr) {
            $match_result = 2;
            $search_type = 1;
        } else if ($match_party_mmr > $old_party_mmr) {
            $match_result = 2;
            $search_type = 2;
        } else if ($match_solo_mmr < $old_solo_mmr) {
            $match_result = 1;
            $search_type = 1;
        } else if ($match_party_mmr < $old_party_mmr) {
            $match_result = 1;
            $search_type = 2;
        } else {
            $match_result = 4;
            $search_type = 3;
        }

        $insertSQL = $db->q(
            'INSERT INTO `mmr_matches`
                (
                    `steamID64`,
                    `matchID`,
                    `matchHeroID`,
                    `matchResultID`,
                    `mmrSolo`,
                    `mmrParty`,
                    `mmrChange`,
                    `badgeProgress`,
                    `badgeDisplayedID`,
                    `badgeDisplayedPip`,
                    `matchSearchTypeID`,
                    `matchModeID`,
                    `matchDateRecorded`
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL);',
            'sssiiiiiiiii',
            array(
                $userID64,
                $match_id,
                $match_hero,
                $match_result,
                $match_solo_mmr,
                $match_party_mmr,
                $matchChange,
                $match_badge_progress,
                $match_badge_displayed,
                $match_badge_pip,
                $search_type,
                $match_mode
            )
        );

        if ($insertSQL) {
            echo bootstrapMessage('Oh Snap', 'Insert Success!', 'success');
        } else {
            echo bootstrapMessage('Oh Snap', 'Insert Failure!');
        }
    } else {
        echo bootstrapMessage('Oh Snap', 'One or more of the required variables are missing or empty!');

        echo "<br />";

        var_dump($_POST);

    }
} catch (Exception $e) {
    echo $e->getMessage();
}