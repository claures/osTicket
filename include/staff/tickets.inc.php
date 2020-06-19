<?php
$search = SavedSearch::create();
$tickets = TicketModel::objects();
$clear_button = false;
$view_all_tickets = $date_header = $date_col = false;

/** @var TicketStatus $waitingStatus */
$waitingStatus = TicketStatus::lookup(array('name' => 'Waiting'));

// Make sure the cdata materialized view is available
TicketForm::ensureDynamicDataView();

// Figure out REFRESH url — which might not be accurate after posting a
// response
list($path,) = explode('?', $_SERVER['REQUEST_URI'], 2);
$args = array();
parse_str($_SERVER['QUERY_STRING'], $args);

// Remove commands from query
unset($args['id']);
if ($args['a'] !== 'search') {
	unset($args['a']);
}

$refresh_url = $path . '?' . http_build_query($args);

$sort_options = array(
	'priority,updated' => __('Priority + Most Recently Updated'),
	'updated' => __('Most Recently Updated'),
	'priority,created' => __('Priority + Most Recently Created'),
	'due' => __('Due Date'),
	'priority,due' => __('Priority + Due Date'),
	'number' => __('Ticket Number'),
	'answered' => __('Most Recently Answered'),
	'closed' => __('Most Recently Closed'),
	'hot' => __('Longest Thread'),
	'relevance' => __('Relevance'),
);

// Queues columns

$queue_columns = array(
	'number' => array(
		'heading' => __('Number'),
	),
	'date' => array(
		'heading' => __('Date Created'),
		'sort_col' => 'created',
	),
	'subject' => array(
		'heading' => __('Subject'),
		'sort_col' => 'cdata__subject',
	),
	'name' => array(
		'heading' => __('From'),
		'sort_col' => 'user__name',
	),
	'status' => array(
		'heading' => __('Status'),
		'sort_col' => 'status_id',
	),
	'priority' => array(
		'heading' => __('Priority'),
		'sort_col' => 'cdata__:priority__priority_urgency',
	),
	'assignee' => array(
		'heading' => __('Agent'),
	),
	'dept' => array(
		'heading' => __('Department'),
		'sort_col' => 'dept__name',
	),
	'team' => array(
		'heading' => __('Team'),
	),
);

$use_subquery = true;

// Figure out the queue we're viewing
$queue_key = sprintf('::Q:%s', ObjectModel::OBJECT_TYPE_TICKET);
$queue_name = $_SESSION[$queue_key] ?: '';

$unassignedUID = Staff::getIdByUsername('unassigned');

switch ($queue_name) {
	case 'closed':
		$status = 'closed';
		$results_type = __('Closed Tickets');
		$showassigned = true; //closed by.
		$queue_sort_options = array('closed', 'priority,due', 'due',
			'priority,updated', 'priority,created', 'answered', 'number', 'hot');
		break;
	case 'overdue':
		$status = 'open';
		$results_type = __('Overdue Tickets');
		$tickets->filter(array('isoverdue' => 1));
		$queue_sort_options = array('updated', 'priority,updated', 'priority,due', 'due',
			'answered', 'priority,created', 'number', 'hot');
		break;
	case 'assigned':
		$status = 'open';
		$staffId = $thisstaff->getId();
		$results_type = __('My Groups Tickets');
		$filter = Q::any(array(
			'staff_id' => $thisstaff->getId()
		));
		if ($teams = array_filter($thisstaff->getTeams())) {
			$filter->add(Q::any(array('team_id__in' => $teams)));
		}
		$tickets->filter($filter);
		$queue_sort_options = array('updated', 'priority,updated',
			'priority,created', 'priority,due', 'due', 'answered', 'number',
			'hot');
		break;
	case 'answered':
		$status = 'open';
		$showanswered = true;
		$results_type = __('Answered Tickets');
		$tickets->filter(array('isanswered' => 1));
		$queue_sort_options = array('updated', 'priority,updated', 'answered',
			'priority,created', 'priority,due', 'due', 'number', 'hot');
		break;

	case 'waiting':
		$status = 'open';
		$results_type = __('Open Tickets');
		$tickets->filter(array('status_id' => $waitingStatus->getId()));
		$queue_sort_options = array('updated', 'priority,updated',
			'priority,due', 'due', 'priority,created', 'answered', 'number',
			'hot');
		break;

	case 'unassigned':
		$status = 'open';
		$staffId = $thisstaff->getId();
		$results_type = __('Unassigned Tickets');
		$tickets->filter(Q::any(array(
			Q::all(array('staff_id' => 0, 'team_id' => 0)),
			Q::all(array('staff_id' => $unassignedUID)),
		)));
		$queue_sort_options = array('updated', 'priority,updated',
			'priority,created', 'priority,due', 'due', 'answered', 'number',
			'hot');
		break;
	case 'nopid':
		$status = 'open';
		$results_type = __('Unassigned to profile');
	/*	$domainBlacklist = array('smartcall.be', 'mixvoip.net', 'mixvoip.com', 'ipfix.be');
		$notLikeEmail = array();
		foreach ($domainBlacklist as $domain){
		    $notLikeEmail[] = " 'user__address__like' '%$domain' ";
        }
        $notLikeEmail = implode(' AND ',$notLikeEmail);

		$sql = 'SELECT T1.ticket_id FROM ' . TICKET_TABLE . ' T1 ,ost_ticket__cdata T2  , ost_user_email U'

			. ' WHERE (T2.profile_id = "" OR T2.profile_id like "%;%" )'

			. ' AND T1.ticket_id = T2.ticket_id'

            . ' AND U.user_id = T1.user_id AND '.$notLikeEmail

			. ' AND T1.lastupdate > "2020-01-01 00:00:00"'

            .' AND  T1.status_id IN (1,7)'

			. ' ORDER BY T1.created';

/*
		if (($res = db_query($sql)) && db_num_rows($res)) {

			while ($ticketId = db_fetch_row($res)) {
				$arrTicket[] = $ticketId[0];
			}
		}
*/
		$tickets->filter(array(Q::any(array(
			'cdata__profile_id__like' => '%;%',
			'cdata__profile_id' => '',
            ))
		));
		$tickets->filter(
			 Q::not(array('user__emails__address__endswith' => 'mixvoip.net')),
		);
		$tickets->filter(Q::not(array('user__address__endswith' => 'mixvoip.com')));
		$tickets->filter(Q::not(array('user__address__endswith' => 'smartcall.be')));
		$tickets->filter(Q::not(array('user__address__endswith' => 'ipfix.be')));
		$queue_sort_options = array('updated', 'priority,updated',
			'priority,created', 'priority,due', 'due', 'answered', 'number',
			'hot');
		break;
	//Our custom filters
	case 'mxvp':
		if (!isset($_REQUEST['mxvptype'])) {
			if (!isset($_REQUEST['mxvpid'])) {
				$user_id = $ost->session->backend->data->ht['user_id'];
				$mxvpuser = Staff::lookup($user_id);
				if (isset($mxvpuser)) {
					$_REQUEST['mxvpid'] = reset($mxvpuser->getDepts());
				} else {
					$_REQUEST['mxvpid'] = 1;
				}
			}
			$_REQUEST['mxvptype'] = 'dept';
		}
		switch ($_REQUEST['mxvptype']) {
			//Department filters:
			case 'dept':
				$status = 'open';
				$depts = Dept::getDepartments();
				$results_type = __('Department :: ') . $depts[$_REQUEST['mxvpid']];
				$tickets->filter(Q::any(array(
					'dept_id' => $_REQUEST['mxvpid'],
				)));
				$queue_sort_options = array('updated', 'priority,updated',
					'priority,created', 'priority,due', 'due', 'answered', 'number',
					'hot');
				break;
			case 'user':
				$status = 'open';
				/** @var Staff $_staff */
				$_staff = Staff::lookup(array('staff_id' => $_REQUEST['mxvpid']));
				$results_type = __('Agent :: ') . $_staff->getName();
				$tickets->filter(Q::any(array(
					'staff_id' => $_REQUEST['mxvpid'],
				)));
				$queue_sort_options = array('updated', 'priority,updated',
					'priority,created', 'priority,due', 'due', 'answered', 'number',
					'hot');
				break;
			case 'team':
				$status = 'open';
				if ($_REQUEST['mxvpid'] == 0) {
					$_teamName = 'No Team';
				} else {
					$_teamName = Team::lookup(array('team_id' => $_REQUEST['mxvpid']))->getName();
				}
				$results_type = __('Team :: ') . $_teamName;
				$tickets->filter(Q::any(array(
					'team_id' => $_REQUEST['mxvpid'],
				)));
				$queue_sort_options = array('updated', 'priority,updated',
					'priority,created', 'priority,due', 'due', 'answered', 'number',
					'hot');
				break;
		}
		break;

	default:
	case 'search':
		$queue_sort_options = array('updated', 'priority,updated', 'priority,created',
			'priority,due', 'due', 'answered',
			'closed', 'number', 'hot');
		// Consider basic search
		if ($_REQUEST['query']) {
			$results_type = __('Search Results');
			// Use an index if possible
			if ($_REQUEST['search-type'] == 'typeahead') {
				if (Validator::is_email($_REQUEST['query'])) {
					$tickets = $tickets->filter(array(
						'user__emails__address' => $_REQUEST['query'],
					));
				} elseif ($_REQUEST['query']) {
					$tickets = $tickets->filter(array(
						'number' => $_REQUEST['query'],
					));
				}
			} elseif (isset($_REQUEST['query'])
				&& ($q = trim($_REQUEST['query']))
				&& strlen($q) > 2
			) {
				// [Search] click, consider keywords
				$__tickets = $ost->searcher->find($q, $tickets);
				if (!count($__tickets) && preg_match('`\w$`u', $q)) {
					// Do wildcard search if no hits
					$__tickets = $ost->searcher->find($q . '*', $tickets);
				}
				$tickets = $__tickets;
				$has_relevance = true;
			}
			// Clear sticky search queue
			unset($_SESSION[$queue_key]);
			break;
		} // Apply user filter
        elseif (isset($_GET['uid']) && ($user = User::lookup($_GET['uid']))) {
			$tickets->filter(array('user__id' => $_GET['uid']));
			$results_type = sprintf(
				'%s — %s',
				__('Search Results'),
				$user->getName()
			);
			if (isset($_GET['status'])) {
				$status = $_GET['status'];
			}
			// Don't apply normal open ticket
			break;
		} elseif (isset($_GET['orgid']) && ($org = Organization::lookup($_GET['orgid']))) {
			$tickets->filter(array('user__org_id' => $_GET['orgid']));
			$results_type = sprintf(
				'%s — %s',
				__('Search Results'),
				$org->getName()
			);
			if (isset($_GET['status'])) {
				$status = $_GET['status'];
			}
			// Don't apply normal open ticket
			break;
		} elseif (isset($_SESSION['advsearch'])) {
			$form = $search->getFormFromSession('advsearch');
			$tickets = $search->mangleQuerySet($tickets, $form);
			$view_all_tickets = $thisstaff->hasPerm(SearchBackend::PERM_EVERYTHING);
			$results_type = __('Advanced Search')
				. '<a class="action-button" style="font-size: 15px;" href="?clear_filter"><i style="top:0" class="icon-ban-circle"></i> <em>' . __('clear') . '</em></a>';
			foreach ($form->getFields() as $sf) {
				if ($sf->get('name') == 'keywords' && $sf->getClean()) {
					$has_relevance = true;
					break;
				}
			}
			break;
		}
	// Fall-through and show open tickets
	// no break
	case 'open':
		$status = 'open';
		$queue_name = $queue_name ?: 'open';
		$results_type = __('Open Tickets');
		$tickets->filter(Q::not(array('status_id' => $waitingStatus->getId())));
		if (!$cfg->showAnsweredTickets()) {
			$tickets->filter(array('isanswered' => 0));
		}
		$queue_sort_options = array('updated', 'priority,updated',
			'priority,due', 'due', 'priority,created', 'answered', 'number',
			'hot');
		break;
}

// Open queues _except_ assigned should respect showAssignedTickets()
// settings
if ($status != 'closed' && $queue_name != 'assigned') {
	$hideassigned = ($cfg && !$cfg->showAssignedTickets()) && !$thisstaff->showAssignedTickets();
	$showassigned = !$hideassigned;
	if ($queue_name == 'open' && $hideassigned) {
		$tickets->filter(array('staff_id' => 0, 'team_id' => 0));
	}
}

// Apply primary ticket status
if ($status) {
	$tickets->filter(array('status__state' => $status));
}



// Impose visibility constraints
// ------------------------------------------------------------
if (!$view_all_tickets) {
	// -- Open and assigned to me
	$assigned = Q::any(array(
		'staff_id' => $thisstaff->getId(),
	));
	// -- Open and assigned to a team of mine
	if ($teams = array_filter($thisstaff->getTeams())) {
		$assigned->add(array('team_id__in' => $teams));
	}

	$visibility = Q::any(new Q(array('status__state' => 'open', $assigned)));

	// -- Routed to a department of mine
	if (!$thisstaff->showAssignedOnly() && ($depts = $thisstaff->getDepts())) {
		$visibility->add(array('dept_id__in' => $depts));
	}

	$tickets->filter(Q::any($visibility));
}

// TODO :: Apply requested quick filter

// Apply requested pagination
$page = ($_GET['p'] && is_numeric($_GET['p'])) ? $_GET['p'] : 1;
$count = $tickets->count();
$pageNav = new Pagenate($count, $page, PAGE_LIMIT);
$pageNav->setURL('tickets.php', $args);
$tickets = $pageNav->paginate($tickets);

// Apply requested sorting
$queue_sort_key = sprintf(':Q%s:%s:sort', ObjectModel::OBJECT_TYPE_TICKET, $queue_name);

// If relevance is available, use it as the default
if ($has_relevance) {
	array_unshift($queue_sort_options, 'relevance');
} elseif ($_SESSION[$queue_sort_key][0] == 'relevance') {
	unset($_SESSION[$queue_sort_key]);
}

if (isset($_GET['sort'])) {
	$_SESSION[$queue_sort_key] = array($_GET['sort'], $_GET['dir']);
} elseif (!isset($_SESSION[$queue_sort_key])) {
	$_SESSION[$queue_sort_key] = array($queue_sort_options[0], 0);
}

list($sort_cols, $sort_dir) = $_SESSION[$queue_sort_key];
$orm_dir = $sort_dir ? QuerySet::ASC : QuerySet::DESC;
$orm_dir_r = $sort_dir ? QuerySet::DESC : QuerySet::ASC;

switch ($sort_cols) {
	case 'number':
		$queue_columns['number']['sort_dir'] = $sort_dir;
		$tickets->extra(array(
			'order_by' => array(
				array(SqlExpression::times(new SqlField('number'), 1), $orm_dir)
			)
		));
		break;

	case 'priority,created':
		$tickets->order_by(($sort_dir ? '-' : '') . 'cdata__:priority__priority_urgency');
	// Fall through to columns for `created`
	// no break
	case 'created':
		$queue_columns['date']['heading'] = __('Date Created');
		$queue_columns['date']['sort_col'] = $date_col = 'created';
		$tickets->values('created');
		$tickets->order_by($sort_dir ? 'created' : '-created');
		break;

	case 'priority,due':
		$tickets->order_by('cdata__:priority__priority_urgency', $orm_dir_r);
	// Fall through to add in due date filter
	// no break
	case 'due':
		$queue_columns['date']['heading'] = __('Due Date');
		$queue_columns['date']['sort'] = 'due';
		$queue_columns['date']['sort_col'] = $date_col = 'est_duedate';
		$tickets->values('est_duedate');
		$tickets->order_by(SqlFunction::COALESCE(new SqlField('est_duedate'), 'zzz'), $orm_dir_r);
		break;

	case 'closed':
		$queue_columns['date']['heading'] = __('Date Closed');
		$queue_columns['date']['sort'] = $sort_cols;
		$queue_columns['date']['sort_col'] = $date_col = 'closed';
		$queue_columns['date']['sort_dir'] = $sort_dir;
		$tickets->values('closed');
		$tickets->order_by('closed', $orm_dir);
		break;

	case 'answered':
		$queue_columns['date']['heading'] = __('Last Response');
		$queue_columns['date']['sort'] = $sort_cols;
		$queue_columns['date']['sort_col'] = $date_col = 'thread__lastresponse';
		$queue_columns['date']['sort_dir'] = $sort_dir;
		$date_fallback = '<em class="faded">' . __('unanswered') . '</em>';
		$tickets->order_by('thread__lastresponse', $orm_dir);
		$tickets->values('thread__lastresponse');
		break;

	case 'hot':
		$tickets->order_by('thread_count', $orm_dir);
		$tickets->annotate(array(
			'thread_count' => SqlAggregate::COUNT('thread__entries'),
		));
		break;

	case 'relevance':
		$tickets->order_by(new SqlCode('__relevance__'), $orm_dir);
		break;

	case 'assignee':
		$tickets->order_by('staff__lastname', $orm_dir);
		$tickets->order_by('staff__firstname', $orm_dir);
		$tickets->order_by('team__name', $orm_dir);
		$queue_columns['assignee']['sort_dir'] = $sort_dir;
		break;

	default:
		if ($sort_cols && isset($queue_columns[$sort_cols])) {
			$queue_columns[$sort_cols]['sort_dir'] = $sort_dir;
			if (isset($queue_columns[$sort_cols]['sort_col'])) {
				$sort_cols = $queue_columns[$sort_cols]['sort_col'];
			}
			$tickets->order_by($sort_cols, $orm_dir);
			break;
		}

	// no break
	case 'priority,updated':
		$tickets->order_by('cdata__:priority__priority_urgency', $orm_dir_r);
	// Fall through for columns defined for `updated`
	// no break
	case 'updated':
		$queue_columns['date']['heading'] = __('Last Updated');
		$queue_columns['date']['sort'] = $sort_cols;
		$queue_columns['date']['sort_col'] = $date_col = 'lastupdate';
		$tickets->order_by('lastupdate', $orm_dir);
		break;
}

if (in_array($sort_cols, array('created', 'due', 'updated'))) {
	$queue_columns['date']['sort_dir'] = $sort_dir;
}

// Rewrite $tickets to use a nested query, which will include the LIMIT part
// in order to speed the result
$orig_tickets = clone $tickets;
$tickets2 = TicketModel::objects();
$tickets2->values = $tickets->values;
$tickets2->filter(array('ticket_id__in' => $tickets->values_flat('ticket_id')));

// Transfer the order_by from the original tickets
$tickets2->order_by($orig_tickets->getSortFields());

$tickets = $tickets2;

// Save the query to the session for exporting
$_SESSION[':Q:tickets'] = $tickets;

TicketForm::ensureDynamicDataView();

// Select pertinent columns
// ------------------------------------------------------------
if(isset($_GET['debug']) && $_GET['debug'] == 1) {
	$tickets->values(
		'lock__staff_id',
		'staff_id',
		'isoverdue',
		'team_id',
		'ticket_id',
		'number',
		'cdata__subject',
		'user__default_email__address',
		'source',
		'cdata__:priority__priority_color',
		'cdata__:priority__priority_desc',
		'cdata__profile_id',
		'status_id',
		'status__name',
		'status__state',
		'dept_id',
		'dept__name',
		'user__name',
		'lastupdate',
		'isanswered',
		'staff__firstname',
		'staff__lastname',
		'team__name'
	);
}else {
	$tickets->values(
		'lock__staff_id',
		'staff_id',
		'isoverdue',
		'team_id',
		'ticket_id',
		'number',
		'cdata__subject',
		'user__default_email__address',
		'source',
		'cdata__:priority__priority_color',
		'cdata__:priority__priority_desc',
		'status_id',
		'status__name',
		'status__state',
		'dept_id',
		'dept__name',
		'user__name',
		'lastupdate',
		'isanswered',
		'staff__firstname',
		'staff__lastname',
		'team__name'
	);
}

// Add in annotations
$tickets->annotate(array(
	'collab_count' => TicketThread::objects()
		->filter(array('ticket__ticket_id' => new SqlField('ticket_id', 1)))
		->aggregate(array('count' => SqlAggregate::COUNT('collaborators__id'))),
	'attachment_count' => TicketThread::objects()
		->filter(array('ticket__ticket_id' => new SqlField('ticket_id', 1)))
		->filter(array('entries__attachments__inline' => 0))
		->aggregate(array('count' => SqlAggregate::COUNT('entries__attachments__id'))),
	'thread_count' => TicketThread::objects()
		->filter(array('ticket__ticket_id' => new SqlField('ticket_id', 1)))
		->exclude(array('entries__flags__hasbit' => ThreadEntry::FLAG_HIDDEN))
		->aggregate(array('count' => SqlAggregate::COUNT('entries__id'))),
));

// Make sure we're only getting active locks
$tickets->constrain(array('lock' => array(
	'lock__expire__gt' => SqlFunction::NOW())));

?>

<!-- SEARCH FORM START -->
<div id='basic_search'>
    <div class="pull-right" style="height:25px">
        <span class="valign-helper"></span>
		<?php
		require STAFFINC_DIR . 'templates/queue-sort.tmpl.php';
		?>
    </div>
    <form action="tickets.php" method="get" onsubmit="javascript:
  $.pjax({
    url:$(this).attr('action') + '?' + $(this).serialize(),
    container:'#pjax-container',
    timeout: 2000
  });
return false;">
        <input type="hidden" name="a" value="search">
        <input type="hidden" name="search-type" value=""/>
        <div class="attached input">
            <input type="text" class="basic-search" data-url="ajax.php/tickets/lookup" name="query"
                   autofocus size="30" value="<?php echo Format::htmlchars($_REQUEST['query'], true); ?>"
                   autocomplete="off" autocorrect="off" autocapitalize="off">
            <button type="submit" class="attached button"><i class="icon-search"></i>
            </button>
        </div>
        <a href="#" onclick="javascript:
        $.dialog('ajax.php/tickets/search', 201);"
        >[<?php echo __('advanced'); ?>]</a>
        <i class="help-tip icon-question-sign" href="#advanced"></i>
    </form>
</div>
<!-- SEARCH FORM END -->
<div class="clear"></div>
<div style="margin-bottom:20px; padding-top:5px;">
    <div class="sticky bar opaque">
        <div class="content">
            <div class="pull-left flush-left">
                <h2><a href="<?php echo $refresh_url; ?>"
                       title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo
						$results_type; ?></a></h2><a
                        href='tickets.php?status=mxvp&mxvptype=user&mxvpid=<?php echo $thisstaff->getId(); ?>'>My
                    tickets</a>
            </div>
            <div class="pull-right flush-right">
				<?php
				if ($count) {
					Ticket::agentActions($thisstaff, array('status' => $status));
				} ?>
            </div>
        </div>
    </div>
</div>
<div class="clear"></div>
<form action="tickets.php" method="POST" name='tickets' id="tickets">
	<?php csrf_token(); ?>
    <input type="hidden" name="a" value="mass_process">
    <input type="hidden" name="do" id="action" value="">
    <input type="hidden" name="status" value="<?php echo
	Format::htmlchars($_REQUEST['status'], true); ?>">
    <table class="list table table-hover table-bordered table-sm">
        <thead>
        <tr>
			<?php
			if ($thisstaff->canManageTickets()) { ?>
                <th max-width="1.5em">&nbsp;</th>
			<?php } ?>

			<?php
			$colNum = 10;
			// Swap some columns based on the queue.
			if ($queue_name == 'assigned') {
				$queue_columns['team']['heading'] = __('Team / User');
			}
			if ($showassigned) {
				//unset($queue_columns['dept']);
				if (!strcasecmp($status, 'closed')) {
					$queue_columns['assignee']['heading'] = __('Closed By');
				} else {
					$queue_columns['assignee']['heading'] = __('Assigned To');
				}
			} else {
				unset($queue_columns['assignee']);
			}
			/*if ($search && !$status)
				unset($queue_columns['priority']);
			else
				unset($queue_columns['status']);
			*/

			//Hide Department when we are in it.
			if (isset($_REQUEST['mxvptype'])) {
				switch ($_REQUEST['mxvptype']) {
					//Department filters:
					case 'dept':
						unset($queue_columns['dept']);
						$colNum--;
						break;
					/*case 'team':
						unset($queue_columns['team']);
						$colNum--;
					break;
					case 'user':
						unset($queue_columns['assignee']);
						$colNum--;
						break;
					*/
				}
			}
			// Query string
			unset($args['sort'], $args['dir'], $args['_pjax']);
			$qstr = Http::build_query($args);
			// Show headers
			foreach ($queue_columns as $k => $column) {
				echo sprintf(
					'<th %s><a href="?sort=%s&dir=%s&%s"
                        class="%s">%s</a></th>',
					$column['width'],
					$column['sort'] ?: $k,
					$column['sort_dir'] ? 0 : 1,
					$qstr,
					isset($column['sort_dir'])
						? ($column['sort_dir'] ? 'asc' : 'desc') : '',
					$column['heading']
				);
			}
			?>
        </tr>
        </thead>
        <tbody>
		<?php
		// Setup Subject field for display
		$subject_field = TicketForm::getInstance()->getField('subject');
		$class = 'row1';
		$total = 0;
		$ids = ($errors && $_POST['tids'] && is_array($_POST['tids'])) ? $_POST['tids'] : null;
		foreach ($tickets as $T) {
		    if(isset($_GET['debug']) && $_GET['debug'] == 1)var_dump($T);
			$teamName = Team::getLocalById($T['team_id'], 'name', $T['team__name']);
			$agentName = new AgentsName($T['staff__firstname'] . ' ' . $T['staff__lastname']);
			//f($T['staff_id'] == $thisstaff->getId()) $agentName .= " (Me)";
			if (!isset($teamName) && $queue_name == 'assigned') { //On the my ticket view show myself as bold if no team
				$teamName = "<b>$agentName</b>";
			} elseif (!isset($teamName)) {
				$teamName = '';
			} elseif ($queue_name == 'assigned' && !empty(trim($agentName)) && $T['staff_id'] != $unassignedUID) {
				$teamName .= ' / ' . $agentName;
			}
			if ($T['staff_id'] == $thisstaff->getId()) {
				$teamName = "<b>$teamName<b/>";
			}
			$total += 1;
			$tag = $T['staff_id'] ? 'assigned' : 'openticket';
			$flag = null;
			if ($T['lock__staff_id'] && $T['lock__staff_id'] != $thisstaff->getId()) {
				$flag = 'locked';
			} elseif ($T['isoverdue']) {
				$flag = 'overdue';
			}

			$lc = '';
			if ($showassigned) {
				if ($T['staff_id']) {
					$lc = $agentName;
				} elseif ($T['team_id']) {
					$lc = Team::getLocalById($T['team_id'], 'name', $T['team__name']);
				}
			} else {
				$lc = Dept::getLocalById($T['dept_id'], 'name', $T['dept__name']);
			}
			$tid = $T['number'];
			$subject = $subject_field->display($subject_field->to_php($T['cdata__subject']));
			$threadcount = $T['thread_count'];
			if (!strcasecmp($T['status__state'], 'open') && !$T['isanswered'] && !$T['lock__staff_id']) {
				$tid = sprintf('<b>%s</b>', $tid);
			}

			$statusClass = '';
			if (strtolower($T['status__name']) === 'closed' || strtolower($T['status__name']) === 'resolved') {
				$statusClass = 'status_closed';
			} elseif ($T['isanswered']) {
				$statusClass = 'status_answered';
			} elseif ($T['isoverdue']) {
				$statusClass = 'status_overdue';
			} elseif (strtolower($T['status__name']) === 'spam') {
				$statusClass = 'status_spam';
			} elseif (strtolower($T['status__name']) === 'open') {
				$statusClass = 'status_open';
			} ?>
            <tr id="<?php echo $T['ticket_id']; ?>" class="<?= $statusClass ?>">
				<?php if ($thisstaff->canManageTickets()) {
					$sel = false;
					if ($ids && in_array($T['ticket_id'], $ids)) {
						$sel = true;
					} ?>
                    <td align="center" class="nohover">
                        <input class="ckb" type="checkbox" name="tids[]"
                               value="<?php echo $T['ticket_id']; ?>" <?php echo $sel ? 'checked="checked"' : ''; ?>>
                        <?php if(isset($_GET['status']) && $_GET['status'] == 'nopid') { ?>
                        <a class="assignToprofile" href="#tickets/<?php echo $T['ticket_id']; ?>/assign/profile" data-redirect="tickets.php?status=pid"><?php echo __('Assign'); ?></a>&nbsp;&nbsp;
                        <?php } ?>
                    </td>
					<?php
				} ?>
                <td title="<?php echo $T['user__default_email__address']; ?>" nowrap>
                    <a class="Icon <?php echo strtolower($T['source']); ?>Ticket preview"
                       title="Preview Ticket"
                       href="tickets.php?id=<?php echo $T['ticket_id']; ?>"
                       data-preview="#tickets/<?php echo $T['ticket_id']; ?>/preview"
                    ><?php echo $tid; ?></a></td>
                <td align="center" nowrap>
					<?php
					echo Format::datetime($T[$date_col ?: 'lastupdate']) ?: $date_fallback; ?>
                </td>
                <td>
                    <div
                            class="<?php if ($flag) { ?>Icon <?php echo $flag; ?>Ticket <?php } ?>link truncate"
						<?php if ($flag) { ?> title="<?php echo ucfirst($flag); ?> Ticket" <?php } ?>
                            href="tickets.php?id=<?php echo $T['ticket_id']; ?>"><?php echo $subject; ?></div>
					<?php
					if ($T['attachment_count']) {
						echo '<i class="small icon-paperclip icon-flip-horizontal" data-toggle="tooltip" title="'
							. $T['attachment_count'] . '"></i>';
					}
					if ($threadcount > 1) { ?>
                        <span class="pull-right faded-more"><i class="icon-comments-alt"></i>
                            <small><?php echo $threadcount; ?></small>
                        </span>
					<?php } ?>
                </td>
                <td nowrap>
                    <div class="ticket-subject"><?php
						if ($T['collab_count']) {
							echo '<span class="pull-right faded-more" data-toggle="tooltip" title="'
								. $T['collab_count'] . '"><i class="icon-group"></i></span>';
						} ?><span class="truncate" style=""><?php
							$un = new UsersName($T['user__name']);
							echo Format::htmlchars($un) . ' &lt;' . Format::htmlchars($T['user__default_email__address']) . '&gt;'; ?></span>
                    </div>
                </td>
				<?php
				$displaystatus = TicketStatus::getLocalById($T['status_id'], 'value', $T['status__name']);
				if ($T['isanswered'] && $T['status_id'] < 2) {
					$displaystatus = 'Answered';
				}
				if (!strcasecmp($T['status__state'], 'open')) {
					$displaystatus = "<b>$displaystatus</b>";
				}
				echo "<td>$displaystatus</td>"; ?>
                <td class="nohover ticketPrio" align="center"
                    style="background-color:<?php echo $T['cdata__:priority__priority_color']; ?>;">
					<?php echo $T['cdata__:priority__priority_desc']; ?></td>
                <td nowrap><span class="truncate" style="max-width: 169px"><?php
						echo ($T['staff_id'] == $thisstaff->getId()) ? '<b>' . Format::htmlchars($lc) . '</b>' : Format::htmlchars($lc); ?></span>
                </td>
				<?php if (isset($queue_columns['dept']) && $showassigned) {
					/**@var $_dept Dept */
					$_dept = Dept::lookup($T['dept_id']);
					$lc2 = $_dept->getFullName();
					//$lc2 = Dept::getLocalById($T['dept_id'], 'name', $T['dept__name']);
					?>
                    <td nowrap><span class="truncate" style="max-width: 169px">
                        <?php echo Format::htmlchars($lc2); ?></span>
                    </td>
					<?php
				} ?>
                <td nowrap>
					<?= $teamName ?>
                </td>
            </tr>
			<?php
		} //end of foreach
		if (!$total) {
			$ferror = __('There are no tickets matching your criteria.');
		}
		?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="<?= $colNum ?>">
				<?php if ($total && $thisstaff->canManageTickets()) { ?>
					<?php echo __('Select'); ?>:&nbsp;
                    <a id="selectAll" href="#ckb"><?php echo __('All'); ?></a>&nbsp;&nbsp;
                    <a id="selectNone" href="#ckb"><?php echo __('None'); ?></a>&nbsp;&nbsp;
                    <a id="selectToggle" href="#ckb"><?php echo __('Toggle'); ?></a>&nbsp;&nbsp;
				<?php } else {
					echo '<i>';
					echo $ferror ? Format::htmlchars($ferror) : __('Query returned 0 results.');
					echo '</i>';
				} ?>
            </td>
        </tr>
        </tfoot>
    </table>
	<?php
	if ($total > 0) { //if we actually had any tickets returned.
	?>
    <div>
        <span class="faded pull-right"><?php echo $pageNav->showing(); ?></span>
		<?php
		echo __('Page') . ':' . $pageNav->getPageLinks() . '&nbsp;';
		echo sprintf(
			'<a class="export-csv no-pjax" href="?%s">%s</a>',
			Http::build_query(array(
				'a' => 'export', 'h' => $hash,
				'status' => $_REQUEST['status'])),
			__('Export')
		);
		echo '&nbsp;<i class="help-tip icon-question-sign" href="#export"></i></div>';
		} ?>
</form>
</div>

<div style="display:none;" class="dialog" id="confirm-action">
    <h3><?php echo __('Please Confirm'); ?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="mark_overdue-confirm">
		<?php echo __('Are you sure you want to flag the selected tickets as <font color="red"><b>overdue</b></font>?'); ?>
    </p>
    <div><?php echo __('Please confirm to continue.'); ?></div>
    <hr style="margin-top:1em"/>
    <p class="full-width">
        <span class="buttons pull-left">
            <input type="button" value="<?php echo __('No, Cancel'); ?>" class="close">
        </span>
        <span class="buttons pull-right">
            <input type="button" value="<?php echo __('Yes, Do it!'); ?>" class="confirm">
        </span>
    </p>
    <div class="clear"></div>
</div>
<script type="text/javascript">
    $(function () {
        $('[data-toggle=tooltip]').tooltip();
    });
</script>

