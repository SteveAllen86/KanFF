<?php
/**
 *  Project: KanFF
 *  File: projectsModel.php functions model for the projects
 *  Author: Benoit Pierrehumbert
 *  Creation date: 24.09.2020
 */

//Get one projects with his id
function getOneProject($id)
{
    return getOne("projects", $id);
}

//Get all projects
function getAllProjects()
{
    return getAll("projects");

}

//Get one projects whith conditions
function getOneByConditionProject($conditions, $params)
{
    return getByCondition("projects", $params, $conditions, false);
}

//Get more than one projects whith conditions
function getAllByConditionProjects($conditions, $params)
{
    return getByCondition("projects", $params, $conditions, true);
}

//Create projects
function createProjects($projects)
{
    createOne("projects", $projects);
}

//Update one projects with his id
function updateProjects($projects, $id)
{
    updateOne("projects", $id, $projects);
}

//Delete one projects with his id
function deleteProjects($id)
{
    deleteOne("projects", $id);
}

//Get all the projects in which a group participates, for a person that is a member of this group or not (can see invisible projects only if is a member)
function getAllProjectsByGroup($id, $isMember)
{
    $query = "SELECT projects.* FROM projects
INNER join participate ON projects.id = participate.project_id
INNER join `groups` ON `groups`.id = participate.group_id
WHERE `groups`.id = :id" . (($isMember == false) ? " AND projects.visible = 1;" : ";");
    $params = ["id" => $id];
    return Query($query, $params, true);
}

//Get all the projects in which a group participates, for a person that is a member of this group or not (can see invisible projects only if is a member)
function getAllGroupsByProject($id)
{
    $query = "SELECT `groups`.*, COUNT(`join`.user_id) AS nbmembers, participate.`start` AS participate_since FROM projects
INNER join participate ON projects.id = participate.project_id
INNER join `groups` ON `groups`.id = participate.group_id
INNER join `join` ON `join`.group_id = `groups`.id
WHERE `projects`.id = :id AND participate.state IN (" . implode(", ", [PARTICIPATE_STATE_INVITATION_ACCEPTED, PARTICIPATE_STATE_CREATOR]) . ") AND `join`.state IN (" . implode(", ", [JOIN_STATE_APPROVED, JOIN_STATE_INVITATION_ACCEPTED]) . ")
GROUP BY `groups`.id
ORDER BY participate.`start`";
    $params = ["id" => $id];
    return Query($query, $params, true);
}

//Get all visible groups by the logged user's id
function getAllProjectsVisible($id)
{
    //Strategy: to get all visible projects: get visible projects (visible = 1) + all projects where you are inside (then group by projects.name to remove duplicate)
    $baseQuery = "SELECT (projects.importance * 2 + projects.urgency) as priority, projects.* FROM projects 
INNER	join participate ON projects.id = participate.project_id
INNER	join `groups` ON `groups`.id = participate.group_id
INNER join `join` ON `join`.group_id = `groups`.id
INNER	join users ON users.id = `join`.user_id
WHERE	(users.id =:id AND users.state != 0 AND `join`.state IN (" . implode(", ", [JOIN_STATE_INVITATION_ACCEPTED, JOIN_STATE_APPROVED]) . ") AND participate.state IN (" . PARTICIPATE_STATE_INVITATION_ACCEPTED . "," . PARTICIPATE_STATE_CREATOR . ") OR projects.visible = 1)";

    //Get all projects visible for given userid, where category (in the view) are In run or On break, ordered by priority first.
    $query = $baseQuery . " AND projects.state NOT IN (" . implode(",", [PROJECT_STATE_ABANDONNED, PROJECT_STATE_CANCELLED, PROJECT_STATE_DONE]) . ")
GROUP BY  projects.name
ORDER BY priority desc, importance desc, urgency DESC, end, start DESC;";
    $params = ["id" => $id];
    $projectsPriorityFirst = Query($query, $params, true);

    //Get all projects visible for given userid, where category (in the view) are Done or Others, ordered by end date first. (There is no notion of priority if the projects are done).
    $query = $baseQuery . " AND projects.state IN (" . implode(",", [PROJECT_STATE_ABANDONNED, PROJECT_STATE_CANCELLED, PROJECT_STATE_DONE]) . ")
GROUP BY  projects.name
ORDER BY end DESC, importance DESC, start DESC;";
    $params = ["id" => $id];
    $projectsCompletionFirst = Query($query, $params, true);

    return array_merge($projectsPriorityFirst, $projectsCompletionFirst);   //merge the 2 arrays (the order will be intact, because it's logically separated after)
}

//Get all projects where the logged user has finish a task
function getAllProjectsContributed($id)
{
    //Get all projects contributed without any order (it's only to get ids)
    $query = "SELECT (projects.importance * 2 + projects.urgency) as priority, projects.* FROM	projects
INNER join works ON works.project_id = projects.id
INNER	join tasks ON tasks.work_id = works.id
WHERE	tasks.responsible_id = :id AND tasks.state = :state
GROUP BY projects.name;";
    $params = ["id" => $id, "state" => TASK_STATE_DONE];
    $projectsContributed = Query($query, $params, true);

    //Remove projects not contributed (where $project['id'] is not in the list of ids)
    $idsOfProjectsContributed = array_column($projectsContributed, "id");   //extract ids in 2D array
    $projectsVisible = getAllProjectsVisible($id);
    foreach ($projectsVisible as $key => $project) {
        if (in_array($project['id'], $idsOfProjectsContributed) == false) {
            unset($projectsVisible[$key]);
        }
    }
    return $projectsVisible;
}

function getAllArchivedProjects($id)
{
    $projects = getAllProjectsVisible($id);
    foreach ($projects as $key => $project) {
        if ($project['archived'] != 1) {
            unset($projects[$key]);
        }
    }
    return $projects;
}

//Get all groups participations to a project where the groups are joined by the member
function getGroupsParticipatingToAProjectByMember($projectid, $userid)
{
    $query = "SELECT participate.* FROM projects
INNER join participate ON participate.project_id = projects.id
INNER join `groups` ON participate.group_id = `groups`.id
INNER join `join` ON `join`.group_id = `groups`.id
INNER join users ON `join`.user_id = users.id
WHERE users.id = :userid AND participate.state IN (" . PARTICIPATE_STATE_INVITATION_ACCEPTED . "," . PARTICIPATE_STATE_CREATOR . ") AND `join`.state IN (" . implode(", ", [JOIN_STATE_INVITATION_ACCEPTED, JOIN_STATE_APPROVED]) . ") AND projects.id = :projectid";

    $params = ["userid" => $userid, "projectid" => $projectid];
    return Query($query, $params, true);
}

//Get all users id where users are in a given project
function getAllUsersIdInsideAProject($projectid)
{
    $query = "SELECT distinct users.id FROM users
INNER join `join` ON `join`.user_id = users.id
INNER join `groups` ON `join`.group_id = `groups`.id
INNER join participate ON participate.group_id = `groups`.id
INNER join projects ON participate.project_id = projects.id
WHERE participate.state IN (" . PARTICIPATE_STATE_INVITATION_ACCEPTED . "," . PARTICIPATE_STATE_CREATOR . ") AND `join`.state IN (" . implode(", ", [JOIN_STATE_INVITATION_ACCEPTED, JOIN_STATE_APPROVED]) . ") AND projects.id = :id AND users.state != 0
ORDER BY users.id";

    $params = ["id" => $projectid];
    $items = Query($query, $params, true);
    $ids = [];
    foreach ($items as $item) {
        $ids[] = $item['id'];
    }
    return $ids;
}

//Get all projects id where the given user is inside them
function getAllProjectsIdWhereUserIsInside($userid)
{
    $query = "SELECT DISTINCT projects.id FROM users
INNER join `join` ON `join`.user_id = users.id
INNER join `groups` ON `join`.group_id = `groups`.id
INNER join participate ON participate.group_id = `groups`.id
INNER join projects ON participate.project_id = projects.id
WHERE participate.state IN (" . PARTICIPATE_STATE_INVITATION_ACCEPTED . "," . PARTICIPATE_STATE_CREATOR . ") AND `join`.state IN (" . implode(", ", [JOIN_STATE_INVITATION_ACCEPTED, JOIN_STATE_APPROVED]) . ") AND users.id = :id AND users.state != 0    
ORDER BY projects.id";

    $params = ["id" => $userid];
    $items = Query($query, $params, true);
    $ids = [];
    foreach ($items as $item) {
        $ids[] = $item['id'];
    }
    return $ids;
}

//Get project's id by task's id
function getProjectIdByTask($idTask)
{
    $query = "SELECT projects.id AS project_id,works.state AS work_state	FROM projects
INNER join 	works ON	projects.id = works.project_id
INNER	join tasks ON works.id = tasks.work_id
WHERE	tasks.id = :id;";
    $params = ["id" => $idTask];
    $result = Query($query, $params, false);
    return $result['project_id'];
}

?>
