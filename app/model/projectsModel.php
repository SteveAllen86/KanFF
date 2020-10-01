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
    require getOne("projects", $id);
}

//Get all projects
function getAllProjects()
{
    return getAll("projects");

}

//Get one projects whith conditions
function getOneByConditionProject($criterions, $params)
{
    return getByCondition("projects", $params, $criterions, false);
}

//Get more than one projects whith conditions
function getAllByConditionProjects($criterions, $params)
{
    return getByCondition("projects", $params, $criterions, true);
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

//Get all visible groups by the logged user's id
function getAllProjectsVisible($id){
    $query = "SELECT projects.* FROM projects 
INNER	join participate ON projects.id = participate.project_id
INNER	join `groups` ON `groups`.id = participate.group_id
INNER join `join` ON join.group_id = `groups`.id
INNER	join users ON users.id = join.user_id
WHERE	users.id = :id
UNION	
SELECT projects.* FROM projects 
INNER	join participate ON projects.id = participate.project_id
INNER	join `groups` ON `groups`.id = participate.group_id
INNER join `join` ON join.group_id = `groups`.id
WHERE	projects.visible = 1;";
    $params = ["id" => $id];
    return Query($query, $params, true);
}

//Get all projects where the logged user has finish a task
function getAllProjectsContributed($id){
    $query = "SELECT projects.* FROM	projects
INNER join works ON works.project_id = projects.id
INNER	join tasks ON tasks.work_id = works.id
WHERE	tasks.user_id = :id AND tasks.state = :state
GROUP BY projects.name;";
    $params = ["id" => $id,"state" => TASK_STATE_DONE];
    return Query($query, $params, true);
}

?>