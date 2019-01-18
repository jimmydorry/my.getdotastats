<?php
try {
    require_once("./connections/parameters.php");
    require_once("./global_functions.php");
    require_once("./global_variables.php");

    if (!isset($_SESSION)) {
        session_start();
    }

    $db = new dbWrapper_v3($hostname_my_gds_site, $username_my_gds_site, $password_my_gds_site, $database_my_gds_site, true);
    if (empty($db)) throw new Exception('No DB!');


    $memcached = new Cache(NULL, NULL, $useMemcache);

    checkLogin();

    $adminCheck = !empty($_SESSION['user_id64'])
        ? adminCheck($_SESSION['user_id64'], 'admin')
        : false;

    $feedCheck = !empty($_SESSION['user_id64'])
        ? adminCheck($_SESSION['user_id64'], 'animufeed')
        : false;

    $emailCheck = !empty($_SESSION['user_id64'])
        ? adminCheck($_SESSION['user_id64'], 'email')
        : false;
} catch (Exception $e) {
    $message = 'Caught Exception -- ' . basename($e->getFile()) . ':' . $e->getLine() . '<br /><br />' . $e->getMessage();
    echo bootstrapMessage('Oh Snap', $message, 'danger');
} finally {
    if (!empty($memcached)) {
        $memcached->close();
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta charset="utf-8">
    <meta content="text/html">
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="stylesheet" href="<?= $path_css_site_full ?>">
    <title>GetDotaStats - Dota 2 Statistics</title>
    <script type="text/javascript" src="<?= $path_lib_jQuery_full ?>"></script>
    <script type="text/javascript" src="<?= $path_lib_siteJS_full ?>"></script>
</head>

<body>
<div class="navbarContainer">
    <ul>
        <li class="navbarLogo">
            <a href="#recent_matches" class="nav-clickable"><img height="34px" src="<?= $imageCDN ?>/images/getdotastats_logo_v5_1_small.png"
                 alt="site logo"/></a>
        </li>
        <li class="navbarLink"><a href="#add_match">Add Match</a></li>
        <!--<li class="navbarLink"><a href="#stats">Stats</a></li>-->
        <li class="navbarLink"><a href="#recent_matches">Recent Matches</a></li>

        <?php if (empty($_SESSION['user_id64'])) { ?>
            <li class="navbarLogin"><a href="./auth/?login"><img
                        src="<?= $CDN_generic ?>/auth/assets/images/steam_small.png"
                        alt="Sign in with Steam"/></a></li>
        <?php
        } else {
            $image = empty($_SESSION['user_avatar'])
                ? $_SESSION['user_id64']
                : '<img width="20px" height="20px" src="' . $_SESSION['user_avatar'] . '" />';

            $username = !empty($_SESSION['user_name'])
                ? $_SESSION['user_name']
                : '';

            echo "<li class='navbarAvatar'>{$image}</li>";
            echo "<li class='username'> <a>{$username}</a></li>";
            echo "<li class='navbarLink'> <a href='./auth/?logout'>Logout</a></li>";
        } ?>

        <li>&nbsp;</li>
        <li class="navbarLink navbarRefresh">
            <a id="nav-refresh-holder" class="nav-refresh" href="#recent_matches" title="Refresh page">Refresh</a>
        </li>
    </ul>
</div>

<div class="container">
    <div class="text-center">
        <div id="loading">
            <img id="loading_spinner1" src="<?= $CDN_generic ?>/images/spinner_v2.gif" alt="loading"/>
        </div>
    </div>
</div>

<div id="main_content" class="col-sm-12"></div>

<div id="footer">
    <div class="container">
        <p class="text-muted">Site built by <a href="https://steamcommunity.com/id/jimmydorry/"
                                                           target="_blank">jimmydorry</a>. Dota 2 is a registered trademark
            of Valve Corporation. Powered by
            Steam.

            <a href="//steamcommunity.com/groups/getdotastats" target="_blank" class="steam-group-button"><span
                    class="steam-group-icon"></span> <span class="steam-group-label">Steam Group</span></a>
        </p>
    </div>
</div>

<script type="text/javascript" src="<?= $path_lib_highcharts_full ?>"></script>
</body>
</html>
