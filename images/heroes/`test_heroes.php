<?php
try {
    //require_once('../../connections/parameters.php');
    //require_once('../../global_functions.php');
    require_once('../../global_variables.php');

    $heroImagesArray = $heroes;

    echo "<h2>Normal Picture Test</h2>";

    $countNormalPicsMissing = 0;
    foreach ($heroImagesArray as $key => $value) {
        if (!is_file("./{$value['pic']}.png")) {
            $countNormalPicsMissing++;
            echo "<img src='./{$value['pic']}.png' />{$value['name_formatted']}<br />";
        }
    }

    if ($countNormalPicsMissing > 0) {
        echo "<strong>We have {$countNormalPicsMissing} missing or incorrectly named hero images!</strong>";
    } else {
        echo "<strong>All hero images available and correctly named!</strong>";
    }

    echo "<hr />";

    echo "<h2>Retro Picture Test</h2>";

    $properArray = array();
    foreach ($heroImagesArray as $key => $value) {
        $properArray[$key]['name_raw'] = $value['name_raw'];
        $properArray[$key]['name_formatted'] = $value['name_formatted'];
        $properArray[$key]['pic'] = $value['pic'];

        $picLocation = null;
        $imageEcho = '';
        $imageEcho .= "<img src='./{$value['pic']}.png' />";
        for ($i = 1; $i <= 10; $i++) {
            if (is_file("./archive/{$value['pic']}-old-{$i}.png")) {
                $picLocation = "archive/{$value['pic']}-old-{$i}";
                $properArray[$key]['pic_' . $i] = $picLocation;


                $imageEcho .= "<img src='./{$picLocation}.png' />";
            } else if (!empty($picLocation)) {
                $properArray[$key]['pic_retro'] = $picLocation;
            }
        }
        if (!empty($picLocation)) {
            echo "{$imageEcho} {$value['name_formatted']}<br />";
        }
    }

    echo "<hr />";

    echo "<h2>Updated Hero Image Array</h2>";

    if (!empty($properArray)) {
        echo '$heroes = array(<br />';
        foreach ($properArray as $key => $value) {
            echo "{$key} => array(<br />";

            if (!empty($value)) {
                foreach ($value as $key2 => $value2) {
                    echo '"' . $key2 . '" => "' . $value2 . '",<br />';
                }
            }

            echo "),<br />";
        }
        echo ');';
    }

} catch (Exception $e) {
    echo formatExceptionHandling($e);
}