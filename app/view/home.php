<?php
/**
 *  Project: KanFF
 *  File: home.php firstpage of the website
 *  Author: Samuel Roland
 *  Creation date: 25.05.2020
 */
$title = "Dashboard";
ob_start();
?>
<h1><?= $title ?></h1>
<?php printPageWIPTextInfo(); ?>

<?php
require ".const.php";
//INFO dev zone: to force displaydebug without debug mode, write displaydebug($var, true, true);
if ($dev == true) { //dev zone

    //Benoit en haut
    echo "<h4>=============Séparation des zones===============</h4>";
    //Samuel en bas

    echo "Tests calculateProgressionOfProjects()";
    $progressionsByProject = calculateProgressionOfProjects(getAllProjects(), getAllWorks(), getAllTasks());
    displaydebug($progressionsByProject, true, true);
}
$contenttype = "large";
$content = ob_get_clean();
require "gabarit.php";
?>
