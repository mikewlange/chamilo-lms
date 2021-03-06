<?php
/* For licensing terms, see /license.txt */
/**
 * Responses to AJAX calls
 */

require_once __DIR__.'/../global.inc.php';

$action = isset($_REQUEST['a']) ? $_REQUEST['a'] : null;

api_block_anonymous_users();

Skill::isAllowed(api_get_user_id());

$skill = new Skill();
$gradebook = new Gradebook();
$skillGradeBook = new SkillRelGradebook();
$userId = api_get_user_id();

switch ($action) {
    case 'add':
        if (api_is_platform_admin() || api_is_drh()) {
            if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
                $skill_id = $skill->edit($_REQUEST);
            } else {
                $skill_id = $skill->add($_REQUEST);
            }
        }
        echo $skill_id;
        break;
    case 'delete_skill':
        if (api_is_platform_admin() || api_is_drh()) {
            echo $skill->delete($_REQUEST['skill_id']);
        }
        break;
    case 'find_skills':
        $skills = $skill->find('all', ['where' => ['name LIKE %?% '=>$_REQUEST['q']]]);
        $return_skills = [[
            'items' => []
        ]];
        foreach ($skills as $skill) {
            $return_skills['items'][] = [
                'id' => $skill['id'],
                'text' => $skill['name']
            ];
        }
        header('Content-Type: application/json');
        echo json_encode($return_skills);
        break;
    case 'get_gradebooks':
        $gradebooks = $gradebook_list = $gradebook->get_all();
        $gradebook_list = [];
        //Only course gradebook with certificate
        if (!empty($gradebooks)) {
            foreach ($gradebooks as $gradebook) {
                if ($gradebook['parent_id'] == 0 &&
                    !empty($gradebook['certif_min_score']) &&
                    !empty($gradebook['document_id'])
                ) {
                    $gradebook_list[] = $gradebook;
                }
            }
        }
        echo json_encode($gradebook_list);
        break;
    case 'find_gradebooks':
        $gradebooks = $gradebook->find('all', ['where' => ['name LIKE %?% ' => $_REQUEST['tag']]]);
        $return = [];
        foreach ($gradebooks as $item) {
            $item['key'] = $item['name'];
            $item['value'] = $item['id'];
            $return[] = $item;
        }
        echo json_encode($return);
        break;
    case 'get_course_info_popup':
        $course_info = api_get_course_info($_REQUEST['code']);
        $courses = CourseManager::processHotCourseItem(
            [
                ['c_id' => $course_info['real_id']]
            ]
        );
        Display::display_no_header();
        Display::$global_template->assign('hot_courses', $courses);
        $template = Display::$global_template->get_template('layout/hot_course_item_popup.tpl');
        echo Display::$global_template->fetch($template);
        break;
    case 'gradebook_exists':
        $data = $gradebook->get($_REQUEST['gradebook_id']);
        if (!empty($data)) {
            echo 1;
        } else {
            echo 0;
        }
        break;
    case 'get_skills_by_profile':
        $skill_rel_profile = new SkillRelProfile();
        $profile_id = isset($_REQUEST['profile_id']) ? $_REQUEST['profile_id'] : null;
        $skills = $skill_rel_profile->getSkillsByProfile($profile_id);
        echo json_encode($skills);
        break;
    case 'get_saved_profiles':
        $skill_profile = new SkillProfile();
        $profiles = $skill_profile->get_all();
        Display::display_no_header();
        Display::$global_template->assign('profiles', $profiles);
        $template = Display::$global_template->get_template('skill/profile_item.tpl');
        echo Display::$global_template->fetch($template);
        break;
    case 'get_skills':
        $load_user_data = isset($_REQUEST['load_user_data']) ? $_REQUEST['load_user_data'] : null;
        $id = intval($_REQUEST['id']);
        $skills = $skill->get_all($load_user_data, false, $id);
        echo json_encode($skills);
        break;
    case 'get_skill_info':
        $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
        $skill_info = $skill->getSkillInfo($id);
        echo json_encode($skill_info);
        break;
    case 'get_skill_course_info':
        $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
        $skill_info = $skill->getSkillInfo($id);
        $courses = $skill->getCoursesBySkill($id);
        $sessions = $skill->getSessionsBySkill($id);
        $html = '';
        if (!empty($courses) || !empty($sessions)) {
            Display::display_no_header();
            Display::$global_template->assign('skill', $skill_info);
            Display::$global_template->assign('courses', $courses);
            Display::$global_template->assign('sessions', $sessions);
            $template = Display::$global_template->get_template('skill/skill_info.tpl');
            $html = Display::$global_template->fetch($template);
        }
        echo $html;
        break;
    case 'get_skills_tree_json':
        header('Content-Type: application/json');
        $userId = isset($_REQUEST['load_user']) && $_REQUEST['load_user'] == 1 ? api_get_user_id() : 0;
        $skill_id = isset($_REQUEST['skill_id']) ? intval($_REQUEST['skill_id']) : 0;
        $depth = isset($_REQUEST['main_depth']) ? intval($_REQUEST['main_depth']) : 2;
        $all = $skill->getSkillsTreeToJson($userId, $skill_id, false, $depth);
        echo $all;
        break;
    case 'get_user_skill':
        $skillId = isset($_REQUEST['profile_id']) ? intval($_REQUEST['profile_id']) : 0;
        $skill = $skill->userHasSkill($userId, $skillId);
        if ($skill) {
            echo 1;
        } else {
            echo 0;
        }
        break;
    case 'get_all_user_skills':
        $skills = $skill->getUserSkills($userId, true);
        echo json_encode($skills);
        break;
    case 'get_user_skills':
        $skills = $skill->getUserSkills($userId, true);
        Display::display_no_header();
        Display::$global_template->assign('skills', $skills);
        $template = Display::$global_template->get_template('skill/user_skills.tpl');
        echo Display::$global_template->fetch($template);
        break;
    case 'get_gradebook_info':
        $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null;
        $info = $gradebook->get($id);
        echo json_encode($info);
        break;
    case 'load_children':
        $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null;
        $load_user_data = isset($_REQUEST['load_user_data']) ? $_REQUEST['load_user_data'] : null;
        $skills = $skill->getChildren($id, $load_user_data);
        $return = [];
        foreach ($skills as $skill) {
            if (isset($skill['data']) && !empty($skill['data'])) {
                $return[$skill['data']['id']] = [
                    'id'    => $skill['data']['id'],
                    'name'  => $skill['data']['name'],
                    'passed'=> $skill['data']['passed']
                ];
            }
        }
        $success = true;
        if (empty($return)) {
            $success = false;
        }

        $result = [
            'success' => $success,
            'data' => $return
        ];
        echo json_encode($result);
        break;
    case 'load_direct_parents':
        $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null;
        $skills = $skill->getDirectParents($id);
        $return = [];
        foreach ($skills as $skill) {
            $return [$skill['data']['id']] = [
                'id'        => $skill['data']['id'],
                'parent_id' => $skill['data']['parent_id'],
                'name'      => $skill['data']['name']
            ];
        }
        echo json_encode($return);
        break;
    case 'profile_matches':
        $skill_rel_user = new SkillRelUser();
        $skills = (!empty($_REQUEST['skill_id']) ? $_REQUEST['skill_id'] : []);
        $total_skills_to_search = $skills;
        $users  = $skill_rel_user->getUserBySkills($skills);
        $user_list = [];
        $count_skills = count($skills);
        $ordered_user_list = null;

        if (!empty($users)) {
            foreach ($users as $user) {
                $user_info = api_get_user_info($user['user_id']);
                $user_list[$user['user_id']]['user'] = $user_info;
                $my_user_skills = $skill_rel_user->getUserSkills($user['user_id']);

                $user_skill_list = [];
                foreach ($my_user_skills as $skill_item) {
                    $user_skill_list[] = $skill_item['skill_id'];
                }

                $user_skills = [];
                $found_counts = 0;

                foreach ($skills as $skill_id) {
                    $found = false;
                    if (in_array($skill_id, $user_skill_list)) {
                        $found = true;
                        $found_counts++;
                        $user_skills[$skill_id] = ['skill_id' => $skill_id, 'found' => $found];
                    }
                }

                foreach ($my_user_skills as $my_skill) {
                    if (!isset($user_skills[$my_skill['skill_id']])) {
                        $user_skills[$my_skill['skill_id']] = [
                            'skill_id' => $my_skill['skill_id'],
                            'found' => false
                        ];
                    }
                    $total_skills_to_search[$my_skill['skill_id']] = $my_skill['skill_id'];
                }
                $user_list[$user['user_id']]['skills'] = $user_skills;
                $user_list[$user['user_id']]['total_found_skills'] = $found_counts;
            }

            foreach ($user_list as $user_id => $user_data) {
                $ordered_user_list[$user_data['total_found_skills']][] = $user_data;
            }

            if (!empty($ordered_user_list)) {
                krsort($ordered_user_list);
            }
        }

        Display::display_no_header();
        Display::$global_template->assign('order_user_list', $ordered_user_list);
        Display::$global_template->assign('total_search_skills', $count_skills);

        $skill_list = [];
        if (!empty($total_skills_to_search)) {
            $total_skills_to_search = $skill->getSkillsInfo($total_skills_to_search);
            foreach ($total_skills_to_search as $skill_info) {
                $skill_list[$skill_info['id']] = $skill_info;
            }
        }

        Display::$global_template->assign('skill_list', $skill_list);
        $template = Display::$global_template->get_template('skill/profile.tpl');
        echo Display::$global_template->fetch($template);
        break;
    case 'delete_gradebook_from_skill':
    case 'remove_skill':
        if (api_is_platform_admin() || api_is_drh()) {
            if (!empty($_REQUEST['skill_id']) && !empty($_REQUEST['gradebook_id'])) {
                $skill_item = $skillGradeBook->getSkillInfo(
                    $_REQUEST['skill_id'],
                    $_REQUEST['gradebook_id']
                );
                if (!empty($skill_item)) {
                    $skillGradeBook->delete($skill_item['id']);
                    echo 1;
                } else {
                    echo 0;
                }
            } else {
                echo 0;
            }
        }
        break;
    case 'get_profile':
        $skillRelProfile = new SkillRelProfile();
        $profileId = isset($_REQUEST['profile_id']) ? intval($_REQUEST['profile_id']) : null;
        $profile = $skillRelProfile->getProfileInfo($profileId);
        echo json_encode($profile);
        break;
    case 'save_profile':
        if (api_is_platform_admin() || api_is_drh()) {
            $skill_profile = new SkillProfile();
            $params = $_REQUEST;
            $params['skills'] = isset($params['skill_id']) ? $params['skill_id'] : null;
            $profileId = isset($_REQUEST['profile']) ? intval($_REQUEST['profile']) : null;
            if ($profileId > 0) {
                $skill_profile->updateProfileInfo(
                    $profileId,
                    $params['name'],
                    $params['description']
                );
                $skill_data = 1;
            } else {
                $skill_data = $skill_profile->save($params);
            }
            if (!empty($skill_data)) {
                echo 1;
            } else {
                echo 0;
            }
        }
        break;
    case 'delete_profile':
        if (api_is_platform_admin() || api_is_drh()) {
            $profileId = $_REQUEST['profile'];
            $skillProfile = new SkillProfile();
            $isDeleted = $skillProfile->delete($profileId);

            echo json_encode([
                'status' => $isDeleted
            ]);
        }
        break;
    case 'skill_exists':
        $skill_data = $skill->get($_REQUEST['skill_id']);
        if (!empty($skill_data)) {
            echo 1;
        } else {
            echo 0;
        }
        break;
    case 'search_skills':
        $skills = $skill->find(
            'all',
            [
                'where' => ['name LIKE %?% ' => $_REQUEST['q']]
            ]
        );
        $returnSkills = [];

        foreach ($skills as $skill) {
            $returnSkills[] = [
                'id' => $skill['id'],
                'text' => $skill['name']
            ];
        }

        echo json_encode([
            'items' => $returnSkills
        ]);
        break;
    case 'update_skill_rel_user':
        $allowSkillInTools = api_get_configuration_value('allow_skill_rel_items');
        if (empty($allowSkillInTools)) {
            exit;
        }

        if (!api_is_allowed_to_edit()) {
            exit;
        }

        $creatorId = api_get_user_id();
        $typeId = isset($_REQUEST['type_id']) ? (int) $_REQUEST['type_id'] : 0;
        $itemId = isset($_REQUEST['item_id']) ? (int) $_REQUEST['item_id'] : 0;
        $skillId = isset($_REQUEST['skill_id']) ? (int) $_REQUEST['skill_id'] : 0;
        $userId = isset($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : 0;
        $courseId = isset($_REQUEST['course_id']) ? (int) $_REQUEST['course_id'] : 0;
        $sessionId = isset($_REQUEST['session_id']) ? (int) $_REQUEST['session_id'] : 0;

        if (!empty($typeId) && !empty($itemId) && !empty($skillId) && !empty($userId) && !empty($courseId)) {
            $em = Database::getManager();
            $user = api_get_user_entity($userId);
            $skill = $em->getRepository('ChamiloCoreBundle:Skill')->find($skillId);
            if (empty($user) || empty($skill)) {
                exit;
            }
            $course = api_get_course_entity($courseId);
            if (empty($course)) {
                exit;
            }

            $session = $em->getRepository('ChamiloCoreBundle:Session')->find($sessionId);

            /** @var \Chamilo\SkillBundle\Entity\SkillRelItem $skillRelItem */
            $skillRelItem = $em->getRepository('ChamiloSkillBundle:SkillRelItem')->findOneBy(
                ['itemId' => $itemId, 'itemType' => $typeId, 'skill' => $skillId]
            );

            if ($skillRelItem) {
                $criteria = [
                    'user' => $userId,
                    'skillRelItem' => $skillRelItem
                ];
                $skillRelItemRelUser = $em->getRepository('ChamiloSkillBundle:SkillRelItemRelUser')->findOneBy($criteria);
                if ($skillRelItemRelUser) {
                    $em->remove($skillRelItemRelUser);
                    $em->flush();
                    $skillRelItemRelUser = null;
                } else {
                    $skillRelItemRelUser = new Chamilo\SkillBundle\Entity\SkillRelItemRelUser();
                    $skillRelItemRelUser
                        ->setUser($user)
                        ->setSkillRelItem($skillRelItem)
                        ->setCreatedBy($creatorId)
                        ->setUpdatedBy($creatorId)
                    ;
                    $em->persist($skillRelItemRelUser);
                    $em->flush();
                }
            }
            echo Skill::getUserSkillStatusLabel($skillRelItem, $skillRelItemRelUser, false);
        }
        break;
    default:
        echo '';
}
exit;
