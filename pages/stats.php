<?php
require_once('../global_functions.php');
require_once('../connections/parameters.php');

try {
    $db = new dbWrapper_v3($hostname_my_gds_site, $username_my_gds_site, $password_my_gds_site, $database_my_gds_site, true);
    if (empty($db)) throw new Exception('No DB!');

    $memcached = new Cache(NULL, NULL, $useMemcache);


    echo '<h2>Test</h2>';

    echo '<p>Testing stuff</p>';

} catch (Exception $e) {
    echo formatExceptionHandling($e);
} finally {
    if (isset($memcached)) $memcached->close();
}