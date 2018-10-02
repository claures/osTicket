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


//Let's get the Department's where the user is member


//Let's create our table array
$displayUsers = array();

var_dump($_users);

foreach ($_users as $uid => $user) {
    $displayUsers[] = array(
        'uid' => $uid,
        'name' => $user->name,
        'noTicket' => 5
    );

}
var_dump($displayUsers);

//var_dump($thisstaff);
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
        echo "<td>{$agent['name']}</td>";
        echo "<td>{$agent['noTicket']}</td>";
        echo "</tr>";
    }
    ?>
    </tbody>
</table>