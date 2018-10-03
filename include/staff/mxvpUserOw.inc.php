<?php
/**
 * Created by IntelliJ IDEA.
 * User: claures
 * Date: 31/08/18
 * Time: 09:32
 */
/**
 * @var Staff $thisstaff
 */
require_once(INCLUDE_DIR . 'class.ticket.php');
require_once(INCLUDE_DIR . 'class.dept.php');

$_users = array();

//if($thisstaff->isAdmin()){
$_users = Staff::getStaffMembers();
//}

//Let's create our table array
$displayUsers = array();

$statusIdArr = array();
/** @var TicketStatus $openStatus */
$openStatus = TicketStatus::lookup(array('name' => 'Open'));
/** @var TicketStatus $waitStatus */
$waitStatus = TicketStatus::lookup(array('name' => 'Waiting'));

if (isset($openStatus)) $statusIdArr[] = $openStatus->getId();
if (isset($waitStatus)) $statusIdArr[] = $waitStatus->getId();
$statusIdQuery = implode(',',$statusIdArr);

foreach ($_users as $uid => $user) {
    $tCount = 0;
    $sql = "SELECT COUNT(*) FROM " . TABLE_PREFIX . "ticket WHERE status_id IN ($statusIdQuery) AND staff_id = $uid";
    if (!($res = db_query($sql)))
        Http::response(500, 'Unable to lookup files');
    if($row = db_fetch_row($res)){
        $tCount = $row[0];
    }
    $displayUsers[] = array(
        'uid' => $uid,
        'name' => $user->name,
        'noTicket' => $tCount
    );
}

?>
<table class="list table table-bordered table-sm table-hover">
    <thead>
    <tr>
        <th>Agent</th>
        <th>Ticket Count</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($displayUsers as $agent) {
        echo '<tr>';
        echo "<td><a href='tickets.php?status=mxvp&mxvptype=user&mxvpid={$agent['uid']}'>{$agent['name']}</a></td>";
        echo "<td>{$agent['noTicket']}</td>";
        echo "</tr>";
    }
    ?>
    </tbody>
</table>