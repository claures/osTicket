<?php
/**
 * Created by IntelliJ IDEA.
 * User: claures
 * Date: 31/08/18
 * Time: 09:28
 */

require('staff.inc.php');

$nav->setTabActive('useroverview');

$inc = 'mxvpUserOw.inc.php';

require_once(STAFFINC_DIR.'header.inc.php');
require_once(STAFFINC_DIR.$inc);
require_once(STAFFINC_DIR.'footer.inc.php');