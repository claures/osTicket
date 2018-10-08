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
if(!$_REQUEST['type'] || $_REQUEST['type'] == 'user'){
    if(isset($_REQUEST['showAll']) && $_REQUEST['showAll'] == 1){
        $sAllT = 'Hide All';
        $sAllV = 0;
    }else{
        $sAllT = 'Show All';
        $sAllV = 1;
    }
echo '<a href="?type=user&showAll='.$sAllV.'">'.$sAllT.'</a>';
}
?>
<table class="list table table-bordered table-sm table-hover">
    <thead>
    <tr>
        <th><?=$tableTitle ?></th>
        <th>Ticket Count</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($displayUsers as $agent) {
        echo '<tr>';
        echo "<td><a href='tickets.php?status=mxvp&mxvptype={$agent['type']}&mxvpid={$agent['uid']}'>{$agent['name']}</a></td>";
        echo "<td>{$agent['noTicket']}</td>";
        echo "</tr>";
    }
    ?>
    </tbody>
</table>