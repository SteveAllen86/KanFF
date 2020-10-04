﻿<?php
$versions = getVersionsApp();
$action = $_GET['action'];
$instanceinfos = getInstanceInfos();
require ".const.php";
?>

<!DOCTYPE HTML>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title><?= $title; ?></title>

    <!-- Bootstrap files -->
    <link href="node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="node_modules/bootstrap/dist/css/bootstrap-grid.min.css" rel="stylesheet">
    <link href="node_modules/bootstrap/dist/css/bootstrap-reboot.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">

    <!-- Jquery files -->
    <script src="node_modules/jquery/dist/jquery.min.js"></script>
    <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

    <!-- CSS files -->
    <link href="css/styles.css" rel="stylesheet">
    <link href="css/groups.css" rel="stylesheet">
    <!-- Js files  -->
    <script src="js/global.js"></script>
    <script src="js/groups.js"></script>
    <script src="js/users.js"></script>

</head>
<body>
<?php
//EasterEgg:
echo "<!-- ----------------------------------------------
            Save together the planet !

              ,,ggddY\"\"\"Ybbgg,,
          ,agd888b,_ \"Y8, ___`\"\"Ybga,
       ,gdP\"\"88888888baa,.\"\"8b    \"888g,
     ,dP\"     ]888888888P'  \"Y     `888Yb,
   ,dP\"      ,88888888P\"  db,       \"8P\"\"Yb,
  ,8\"       ,888888888b, d8888a           \"8,
 ,8'        d88888888888,88P\"' a,          `8,
,8'         88888888888888PP\"  \"\"           `8,
d'          I88888888888P\"                   `b
8           `8\"88P\"\"Y8P'                      8
8            Y 8[  _ \"                        8
8              \"Y8d8b  \"Y a                   8
8                 `\"\"8d,   __                 8
Y,                    `\"8bd888b,             ,P
`8,                     ,d8888888baaa       ,8'
 `8,                    888888888888'      ,8'
  `8a                   \"8888888888I      a8'
   `Yba                  `Y8888888P'    adP'
     \"Yba                 `888888P'   adY\"
       `\"Yba,             d8888P\" ,adP\"'
          `\"Y8baa,      ,d888P,ad8P\"'
               ``\"\"YYba8888P\"\"''

----------------------------------------------- -->";
?>

<!-- The full header -->
<header class="bg-header <?= ($debug == false) ? "header-fixed" : "" //the header is not fixed in debug mode because else devs can't see var_dump() results printed under the menu.                       ?>">

    <!-- Zone Logo with logo image + version texts -->
    <div class="divZoneLogo flexdiv">
        <div class="divLogo lineheigthsmall">
            <img src="view/medias/logos/KanFF_Logo.svg" alt="logo KanFF" class="logo clickable cursorpointer"
                 data-href="/">
            <div class="divVersion ">
                <span class="versiontext "><?= $versions[count($versions) - 1]['version'] ?></span>
                <span class="versiontext alignright"><em>le <?= date("d.m.Y", strtotime($versions[count($versions) - 1]['date'])) ?></em></span>
            </div>
        </div>
        <div data-href="?action=about"
             class="flex-3 collectivename flexdiv overflow-hidden borderleftorange borderrightorange clickable <?= ($action == "about") ? 'active' : '' //button active or not                       ?>">
            <div class="align-items-center flexdiv"><?= $instanceinfos['collective']['name'] ?></div>
        </div>
    </div>

    <!-- Zone User with user firstname+lastname and circle with initials, the dropdown and the bell -->
    <?php if (isset($_SESSION['user'])) { //if user is logged?>
        <div class="borderleftorange user row justify-content-end flexdiv borderrightorange">
            <!-- The bell and the fullname-->
            <div class="pr-3 box-verticalaligncenter"><img src="view/medias/icons/bell.png" class="bell"
                                                           alt="bell icon">
            </div>
            <div class="fullname alignright pr-2 justify-content-end box-verticalaligncenter">
                <?= $_SESSION['user']['firstname'] ?> <?= $_SESSION['user']['lastname'] ?>
            </div>

            <!-- Circle for user initials and dropdown -->
            <div class="box-alignright pr-4 nomargin">
                <div class="usericon">
                    <div class="dropdown">
                        <form action="">
                            <!-- form tag ? -> thanks to https://stackoverflow.com/questions/25089297/avoid-dropdown-menu-close-on-click-inside#answer-34216265 -->
                            <!-- The circle -->
                            <div class="circle-usericon cursorpointer <?= (checkAdmin()) ? "innerbordercircle" : "" ?>"
                                 data-toggle="dropdown" aria-expanded="false">
                                <p class="marginauto"><?= $_SESSION['user']['initials'] ?></p>
                            </div>
                            <!-- The dropdown -->
                            <div class="divDropDown dropdown-menu yellowheader" style="">
                                <div>
                                    <strong>Mes informations</strong><br>
                                    <?= $_SESSION['user']['username'] ?><br>
                                    <span title="<?= $_SESSION['user']['email'] ?>"><?= substrText($_SESSION['user']['email'], 32, true) ?></span><br>
                                    <?= $_SESSION['user']['phonenumber'] ?><br>
                                    <br>
                                    <p>Inscription: <?= DTToHumanDate($_SESSION['user']['inscription']) ?></p>
                                    <p><strong>Statut <br></strong><em><?= $_SESSION['user']['status'] ?></em><img
                                                src="view/medias/icons/modify.png"
                                                alt="modify icon"
                                                class="yellowdarkonhover icon-small justify-content-end">
                                    </p>
                                    <p><strong>Etat:</strong> <?= convertUserState($_SESSION['user']['state']) ?></p>
                                    <?= ($_SESSION['user']['on_break'] == 1) ? "<p class='lightbluelogo p-2'><strong>En pause</strong></p>" : "" ?>
                                    <!-- The 2 buttons for "my account" and "logout" -->
                                    <div class="clickable cursorpointer yellowdarkonhover"
                                         data-href="/?action=editAccount">
                                        <img src="view/medias/icons/settings.png" alt="settings icon"
                                             class="icon-small">
                                        <strong>Mon compte</strong></div>
                                    <div class="clickable cursorpointer yellowdarkonhover fullwidth"
                                         data-href="/?action=logout">
                                        <img src="view/medias/icons/logout.png" alt="settings icon" class="icon-small">
                                        <strong>Déconnexion</strong>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php } else { //if user is not logged display only a login button?>
        <div class="user row col-2 borderleftorange txtblack">
            <div class="col-10 box-verticalaligncenter header-height">
                <a href="/?action=login"><span class="btn btn-info">Connexion</span></a>
            </div>
        </div>
    <?php } ?>

    <!-- Zone Menu -->
    <div class="menu">
        <ul>
            <?php
            if (isset($_SESSION['user'])){  //display the buttons only if the user is logged
            ?>
            <li><a class="<?= ($action == null) ? 'active' : '' ?>" href="/">Dashboard</a></li>
            <li><a class="<?= ($action == "tasks") ? 'active' : '' ?>" href="/?action=tasks">Tâches</a></li>
            <li><a class="<?= ($action == "projects") ? 'active' : '' ?>" href="/?action=projects">Projets</a></li>
            <li><a class="<?= ($action == "groups") ? 'active' : '' ?>" href="/?action=groups">Groupes</a></li>
            <li><a class="<?= ($action == "members") ? 'active' : '' ?>" href="/?action=members">Membres</a></li>
            <li><a class="<?= ($action == "calendar") ? 'active' : '' ?>" href="/?action=calendar">Calendrier</a>
            </li>
        </ul>
        <?php
        }
        ?>
    </div>
</header>

<!-- Flashmessage div if the flashmessage is set-->
<div class="margintopforheader"><?php
    $msg = flashMessage(true); //get flashmessage with Html included
    echo $msg;  //$msg that store the message can now be displayed. the value of $msg isn't lost (useful for appbody).
    ?></div>
<!-- Zone appbody with the content of the view generated -->
<?php
//Depending on the content type choosed in the view, the appbody will change. 3 types are available: full, large, restrict.
switch ($contenttype) {
    case "full":
        ?>
        <div class="appbody <?= ($msg != "") ? "" : "margintopforheader" ?> p-1"><?= $content; ?></div><?php
        break;
    case "large":
        ?>
        <div class="appbody <?= ($msg != "") ? "" : "margintopforheader" ?> p-3"><?= $content; ?></div><?php
        break;
    case "restricted":
        ?>
        <div class="flexdiv <?= ($msg != "") ? "" : "margintopforheader" ?> justify-content-center">
            <div class="appbody appbodyrestrict col-lg-7 col-md-8 col-sm-11 marginauto p-3"><?= $content; ?></div>
        </div>
        <?php
        break;
    default:
        ?>
        <div class="appbody p-1">$contenttype must be correctly defined...</div>
        <?php
        break;
}
?>

</div>
</body>
</html>
