<?php
/**
 * Created by IntelliJ IDEA.
 * User: claures
 * Date: 31/08/18
 * Time: 09:28
 */
require('staff.inc.php');

$tableTitle = '';
//Let's create our table array
$displayUsers = array();

$statusIdArr = array();
/** @var TicketStatus $openStatus */
$openStatus = TicketStatus::lookup(array('name' => 'Open'));
/** @var TicketStatus $waitStatus */
$waitStatus = TicketStatus::lookup(array('name' => 'Waiting'));

if (isset($openStatus)) $statusIdArr[] = $openStatus->getId();
if (isset($waitStatus)) $statusIdArr[] = $waitStatus->getId();
$statusIdQuery = implode(',', $statusIdArr);

$nav->setTabActive('useroverview');
switch ($_REQUEST['type']) {
    case 'team':
        $nav->setActiveSubMenu(2);
        $tableTitle = 'Team';
        $teamIds = array(0 => 'No Team');
        $teamIds += Team::getTeams();
        foreach ($teamIds as $teamId => $teamName) {
            $tCount = 0;
            $sql = "SELECT COUNT(*) FROM " . TABLE_PREFIX . "ticket WHERE status_id IN ($statusIdQuery) AND team_id = $teamId";
            if (!($res = db_query($sql)))
                Http::response(500, 'Unable to lookup Teams');
            if ($row = db_fetch_row($res)) {
                $tCount = $row[0];
            }
            $displayUsers[] = array(
                'uid' => $teamId,
                'name' => $teamName,
                'noTicket' => $tCount,
                'type' => 'team'
            );
        }
        break;
    case 'dep':
        $nav->setActiveSubMenu(3);
        $tableTitle = 'Department';
        $deptIds = Dept::getDepartments();
        foreach ($deptIds as $depId => $deptName) {
            $tCount = 0;
            $sql = "SELECT COUNT(*) FROM " . TABLE_PREFIX . "ticket WHERE status_id IN ($statusIdQuery) AND dept_id = $depId";
            if (!($res = db_query($sql)))
                Http::response(500, 'Unable to lookup files');
            if ($row = db_fetch_row($res)) {
                $tCount = $row[0];
            }
            $displayUsers[] = array(
                'uid' => $depId,
                'name' => $deptName,
                'noTicket' => $tCount,
                'type' => 'dept'
            );
        }
        break;
    case
    'user':
    default:
        $_users = Staff::getStaffMembers();
        foreach ($_users as $uid => $user) {
            $tCount = 0;
            $sql = "SELECT COUNT(*) FROM " . TABLE_PREFIX . "ticket WHERE status_id IN ($statusIdQuery) AND staff_id = $uid";
            if (!($res = db_query($sql)))
                Http::response(500, 'Unable to lookup files');
            if ($row = db_fetch_row($res)) {
                $tCount = $row[0];
            }
            if ((isset($_REQUEST['showAll']) && $_REQUEST['showAll']) || $tCount > 0) {
                $displayUsers[] = array(
                    'uid' => $uid,
                    'name' => $user->name,
                    'noTicket' => $tCount,
                    'type' => 'user'
                );
            }
        }
        $tableTitle = 'Agent';
        $nav->setActiveSubMenu(1);
        break;
}
$inc = 'mxvpUserOw.inc.php';

require_once(STAFFINC_DIR . 'header.inc.php');
require_once(STAFFINC_DIR . $inc);
require_once(STAFFINC_DIR . 'footer.inc.php');