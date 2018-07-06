<?php
/*
    Module: naplo
*/

        // Nyelvi konstansok
    if (file_exists("lang/$lang/module-naplo/share/ertekeles.php")) {
        require_once("lang/$lang/module-naplo/share/ertekeles.php");
    } elseif (file_exists('lang/'._DEFAULT_LANG.'/module-naplo/share/ertekeles.php')) {
        require_once('lang/'._DEFAULT_LANG.'/module-naplo/share/ertekeles.php');
    }

    if (!is_array($DICSERET_FOKOZATOK)) $DICSERET_FOKOZATOK = array(
        'SEMMI',
        'SZAKTANARI_DICSERET',
        'OSZTALYFONOKI_DICSERET',
        'IGAZGATOI_DICSERET',
        'NEVELOTESTULETI_DICSERET'
    );

    if (!is_array($FEGYELMI_FOKOZATOK)) $FEGYELMI_FOKOZATOK = array(
        'SEMMI',
        'SZAKTANARI_FIGYELMEZTETES',
        'SZOBELI_OSZTALYFONOKI_FIGYELMEZTETES',
        'OSZTALYFONOKI_FIGYELMEZTETES',
        'OSZTALYFONOKI_INTO',
        'OSZTALYFONOKI_ROVO',
        'IGAZGATOI_FIGYELMEZTETO',
        'IGAZGATOI_INTO',
        'IGAZGATOI_ROVO',
        'NEVELOTESTULETI_FIGYELMEZTETES',
        'NEVELOTESTULETI_INTES',
        'NEVELOTESTULETI_MEGROVAS'
    );

    // A fegyelmi fokozatok adott nyelvű megnevezései
    for ($i = 0; $i < count($FEGYELMI_FOKOZATOK); $i++)
        if (defined('_'.$FEGYELMI_FOKOZATOK[$i]))
            $FEGYELMI_FOKOZATOK[$i] = constant('_'.$FEGYELMI_FOKOZATOK[$i]);
    // A dicséret fokozatok adott nyelvű megnevezései
    for ($i = 0; $i < count($DICSERET_FOKOZATOK); $i++)
        if (defined('_'.$DICSERET_FOKOZATOK[$i]))
            $DICSERET_FOKOZATOK[$i] = constant('_'.$DICSERET_FOKOZATOK[$i]);

    if (!is_array($HIANYZASI_FOKOZATOK)) $HIANYZASI_FOKOZATOK = array(
        0 => 0,  // semmi
        1 => 2,  // szóbeli osztályfőnöki figyelmeztetés
        2 => 3,  // osztályfőnöki figyelmeztetés
        3 => 4,  // osztályfőnöki intő
        4 => 5,  // osztályfőnöki rovó
        5 => 6,  // igazgatói figyelmeztető (szülő értesítése)
        6 => 0,  // semmi
        7 => 7,  // igazgatói intő
        8 => 0,  // semmi
        9 => 8,  // igazgatói rovó
        10 => 0, // igazgatói rovó
        11 => 9  // fegyelmi eljárás
    );

?>
