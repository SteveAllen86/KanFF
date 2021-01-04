<?php
/**
 *  Project: KanFF
 *  File: projectsControler.php controler functions for the projects
 *  Author: Samuel Roland
 *  Creation date: 22.07.2020
 */

require_once "model/projectsModel.php";

// Display the page groups
function projects($option)
{
    switch ($option) {
        case 1:
            $projects = getAllProjectsVisible($_SESSION['user']['id']);
            $description = "Tous les projets actuels (non-archivés) du collectif qui sont visibles pour vous.";
            break;
        case 2:
            $projects = getAllProjectsContributed($_SESSION['user']['id']);
            $description = "Tous les projets auxquels vous avez contribué dans ce collectif et qui sont visibles pour vous. (Contribué signifie techniquement que vous avez effectué au moins une tâche).";
            break;
        case 3:
            $projects = getAllArchivedProjects($_SESSION['user']['id']);
            $description = "Tous les projets archivés du collectif qui sont visibles pour vous. Ils ont été archivés parce qu'il n'était plus d'actualité (et qu'ils étaient terminés, abandonnés ou annulés).";
            break;
    }

    $groups = indexAnArrayById(getAll("groups"));
    foreach ($projects as $key => $project) {
        $participates = getByCondition("participate", ["id" => $project['id']], "participate.project_id=:id and participate.state in (2, 3) order by participate.state desc", true);
        foreach ($participates as $key2 => $participate) {
            $participates[$key2]['group'] = $groups[$participate['group_id']];
        }
        $projects[$key]['participate'] = $participates;
    }

    //TODO: fix bug with substrText() after specialCharsConvertFromAnArray() ...
    //$fieldsToConvert = ["name", "description", "start", "end", "state", "value", "effort", "visible", "project_id", "creator_id", "creation_date"];
    //$projects = specialCharsConvertFromAnArray($projects, $fieldsToConvert);

    require_once "view/projects.php";
}

// Display the page create a project or create the project (depends on the data sent)
function createAProject($data)
{
    if (empty($data) == false) {
        $error = false;

        //Check length of name, description and goal
        $newProject['name'] = trimIt($data['name']);
        $newProject['description'] = trimIt($data['description']);
        $newProject['goal'] = trimIt($data['goal']);

        //Check length of name, description and goal
        if (areAreAllEqualTo(true, [chkLength($newProject['name'], 70), chkLength($newProject['description'], 1000), chkLength($newProject['goal'], 1000)]) == false) {
            $error = 10;
        }

        //Check that the user is in the given group
        if (isMemberInAGroup($_SESSION['user']['id'], $data['group_id']) == false) {
            $error = 10;
        }

        if (checkUserPassword($_SESSION['user']['id'], $data['password']) == false) {
            $error = 8;
        }
        unset($newProject['password']);

        // Default values (not in the form)
        $newProject['archived'] = 0;
        $newProject['logbook_content'] = "Non défini";
        $newProject['responsible_id'] = null;
        $newProject['state'] = PROJECT_STATE_UNDERREFLECTION;

        //Convert checkbox values to tinyint
        $newProject['visible'] = chkToTinyint($newProject['visible']);
        $newProject['logbook_visible'] = chkToTinyint($newProject['logbook_visible']);
        $newProject['goal'] = chkToTinyint($newProject['goal']);

        //Verify start and end date
        $newProject['start'] = timeToDT($data['start']);
        if ($newProject['start'] == false) {
            $error = 10;
        }

        $newProject['end'] = null;  //default value
        if ($data['end'] != "") {
            $newProject['end'] = strtotime($data['end']);
            $newProject['end'] = timeToDT($newProject['end']);
            if ($newProject['end'] == false) {
                $error = 10;
            }
        }
        //Check that end date are bigger than start date
        if ($newProject['start'] >= $newProject['end']) {
            $error = 10;
        }

        //Then depending on errors or on success:
        if ($error != false) {
            flshmsg($error);
            $groups = getAllGroupsByUser($_SESSION['user']['id']);
            require "view/createAProject.php";  //view values sent inserted
        } else {
            createOne("projects", $newProject);
            flshmsg(9);
            require "view/projects.php";
        }
    } else {
        $groups = getAllGroupsByUser($_SESSION['user']['id']);
        require_once "view/createAProject.php";
    }

}


function projectDetails($id, $option)
{
    if ($option == null) {
        $option = 2;
    }
    //TODO: check visibility of the project and if isMember
    $project = getOneProject($id);
    if (empty($project) == false) {
        $users = getAllUsers();
        $groups = getAllGroupsByProject($id);
        $logs = getAllLogs($project['id']);
        foreach ($logs as $key => $log) {
            $logs[$key]['user'] = $users[$log['user_id']];
        }
    }
    require_once "view/project.php";
}

function kanban($id, $opt)
{
    $isInsideTheProject = isAUserInsideAProject($id, $_SESSION['user']['id']);
    $users = getAllUsers();
    $project = getOneProject($id);
    $works = indexAnArrayById(getAllWorksByProject($id));
    $tasks = getAllTasksByProject($id);
    foreach ($tasks as $task) {
        $task['responsible'] = $users[$task['responsible_id']];
        $works[$task['work_id']]['tasks'][] = $task;
    }

    $totalEffort = 0;
    $totalValue = 0;
    $providedEffort = 0;
    $generatedValue = 0;
    foreach ($works as $key => $work) {
        $totalEffort += $work['effort'];
        $totalValue += $work['value'];
        if ($work['state'] == WORK_STATE_DONE) {
            $providedEffort += $work['effort'];
            $generatedValue += $work['value'];
        }
        $works[$key]['hasWritingRightOnTasks'] = hasWritingRightOnTasksOfAWork($isInsideTheProject, $work);
        if ($isInsideTheProject != true) {  //if is not inside the project, the filter apply, else no filter
            if ($work['visible'] != 1) {    //unset the work is not visible
                unset($works[$key]);
            }
        }
    }
    displaydebug($isInsideTheProject);
    displaydebug($works);

    $project['works'] = $works;

    require_once "view/kanban.php";
}

//return true or false, if the user is inside the project (inside groups that participate to the project)
function isAUserInsideAProject($projectid, $userid)
{
    $result = getGroupsParticipatingToAProjectByMember($projectid, $userid);
    if (count($result) >= 1) {
        return true;
    } else {
        return false;
    }
}


?>