<?php

class Scheduler_JobController extends Yesup_Controller_Scheduler {

    protected $_job = null;

    public function init() {
        parent::init();
        $this->_job = Zend_Registry::get('job');
    }

    public function dispatch($action) {
        set_time_limit(0);
        ob_start();
        try {
            parent::dispatch($action);
        } catch (Exception $ex) {
            $this->_job->setException($ex);
            error_log('exception on dispatch cron job ' . $ex->getTraceAsString());
        }
        $text = ob_get_clean();
        $this->_job->setOutput($text);
        $this->_job->complete();
    }

    private function _generateReport($startDate, $endDate, $period, $method) {
        $report = array();
        if ($period == 'DATE') {
            $startDate = new Yesup_Date($startDate, Zend_Date::ISO_8601);
            for ($i = 0; $i < 7; $i++) {
                $report[$startDate->dbDateString()] = $this->$method($startDate->dbDateString(), $startDate->dbDateString());
                $startDate = $startDate->addDay(1);
            }
        }
        if ($period == 'WEEK') {
            $startDate = new Yesup_Date($startDate, Zend_Date::ISO_8601);
            $startDate = $startDate->setWeekday('sunday');
            for ($i = 0; $i < 8; $i++) {
                $endDate = clone $startDate;
                $endDate = $endDate->setWeekday('saturday');
                $report[$startDate->dbDateString()] = $this->$method($startDate->dbDateString(), $endDate->dbDateString());
                $startDate = $endDate->addDay(1);
            }
        }
        if ($period == 'MONTH') {
            $startDate = new Yesup_Date($startDate, Zend_Date::ISO_8601);
            $endDate = new Yesup_Date($endDate, Zend_Date::ISO_8601);
            while ($startDate->getMonthValue() != $endDate->getMonthValue()) {
                $startDate = $startDate->getFirstDayOfThisMonth();
                $endTempDate = clone $startDate;
                $endTempDate = $endTempDate->getLastDayOfThisMonth();
                $report[$startDate->dbDateString()] = $this->$method($startDate->dbDateString(), $endTempDate->dbDateString());
                $startDate = $endTempDate->addDay(1);
            }
        }
        $this->_printReport($report, $period, $method);
    }

    private function _csrReport($startDate, $endDate) {
        //Define num of followup
        $jobsAssignedCsrArray = array();

        //Get group
        $groups = Model_Group::getPagedGroups(true);
        $groupId = 0;
        foreach ($groups->fetchAll() as $group) {
            if ($group->getName() == 'CSR') {
                $groupId = $group->getId();
            }
        }
        if (!$groupId) {
            throw new Exception('Group not found');
        }
        $group = new Model_Group($groupId);

        //Get workflow id to pass into workflowcase
        $search = array('name' => 'New Account');
        $workflows = Model_Workflow::getPagedWorkflows($search)->fetchAll();
        if ($workflows) {
            $workflowId = $workflows[0]->getId();
        } else {
            throw new Exception('Workflow not found.');
        }
        $caseSearch = array(
            'CAST(`start_date` as DATE) >= ?' => $startDate,
            'CAST(`start_date` as DATE) <= ?' => $endDate,
            'workflow_id' => $workflowId,
        );
        $caseIds = array();
        $workflowCases = Model_WorkflowCase::getPagedWorkflowCases($caseSearch)->fetchAll();
        foreach ($workflowCases as $case) {
            $caseIds[$case->getId()] = '`case_id` = ' . $case->getId();
        }
        $admins = $group->getAdminsInGroup();
        $table = new Model_DbTable_WorkflowWorkitem();
        $sql = " SELECT SUM( CASE `status` WHEN 'in progress' THEN 1 ELSE 0 END ) AS progress," .
                "SUM( CASE `status` WHEN 'finished' THEN 1 ELSE 0 END ) AS finished " .
                "FROM  `workflowWorkitem`  WHERE `group_id` = $groupId AND " .
                "(CAST( `enabled_date` AS DATE ) >= '$startDate' AND CAST( `enabled_date` AS DATE ) <= '$endDate')";
        if ($caseIds) {
            $sql .= " AND ( " . implode(' OR ', $caseIds) . " ) ";
            $row = $table->getAdapter()->query($sql)->fetch();
            $totalJob = (($row['progress']) ? $row['progress'] : 0) + (($row['finished']) ? $row['finished'] : 0);
        } else {
            $totalJob = 0;
        }
        foreach ($admins as $admin) {
            $adminId = $admin->getId();
            $search = array(
                'group_id' => $groupId,
                'user_id' => $adminId,
                "(CAST( `enabled_date` AS DATE ) >= '$startDate' AND CAST( `enabled_date` AS DATE ) <= '$endDate')",                
            );
            if($caseIds) {
                $search['case_id'] = array_keys($caseIds); 
            } else {
                $search['case_id'] = 0;
            }
            $adminProgress = array();
            $adminFinished = array();
            foreach (Model_WorkflowWorkitem::getAllWorkItems($search) as $job) {
                $status = $job->getStatus();
                if ($status == 'in progress') {
                    $adminProgress[] = $job->getCaseId();
                } elseif ($status == 'finished') {
                    $adminFinished[] = $job->getCaseId();
                }
            }
            $jobsAssignedCsrArray[$admin->getDisplayText()] = array(
                'progress' => $this->_generateLink('csr_job_report', $adminProgress),
                'finished' => $this->_generateLink('csr_job_report', $adminFinished),
                'assign' => $this->_generateLink('csr_job_report', array_merge($adminFinished, $adminProgress)),
                'total' => $totalJob
            );
        }
        $reportArray = array('job' => $jobsAssignedCsrArray, 'detail' => $this->_csrDetailReport($startDate, $endDate));
        return $reportArray;
    }

    private function _csrDetailReport($startDate, $endDate) {
        $detailedCsrJobArray = array();

        //Get group
        $groups = Model_Group::getPagedGroups(true);
        $groupId = 0;
        foreach ($groups->fetchAll() as $group) {
            if ($group->getName() == 'CSR') {
                $groupId = $group->getId();
            }
        }
        if (!$groupId) {
            throw new Exception('Group not found');
        }
        $search = array(
            'group_id' => $groupId,
            "(CAST( `enabled_date` AS DATE ) >= '$startDate' AND CAST( `enabled_date` AS DATE ) <= '$endDate')",
        );
        $detailReports = Model_WorkflowWorkitem::getAllWorkItems($search, array('enabled_date', 'user_id'));
        foreach ($detailReports as $detailReport) {
            $admin = $detailReport->getUser();
            $adminName = ($admin) ? $admin->getDisplayText() : 'not assigned';
            $client = $detailReport->getClientUser();
            if($client) {
                $clientName = $client->getDisplayText();
            } else {
                $clientName = '';
            }
            $detailedCsrJobArray[] = array(
                'job_id' => $this->_generateLink('csr_detail_report', $detailReport->getCaseId()),
                'workflow' => $detailReport->getWorkflow()->getName(),
                'job' => $detailReport->getTransition()->getDescription(),
                'client' => $clientName,
                'assign_date' => $detailReport->getEnabledDate(),
                'due_date' => $detailReport->getDeadline(),
                'owner' => $adminName,
            );
        }
        return $detailedCsrJobArray;
    }

    private function _orderPaidReport($startDate, $endDate) {
        $workflow = 'Universal Server Order';
        $workflows = Model_Workflow::getPagedWorkflows(array('name' => $workflow, 'status' => 'active'))->fetchAll();
        if ($workflows) {
            $workflowId = $workflows[0]->getId();
        } else {
            throw new Exception('Cannot find workflow');
        }
        $groups = Model_Group::getPagedGroups(true);
        $groupId = 0;
        foreach ($groups->fetchAll() as $group) {
            if ($group->getName() == 'CSR') {
                $groupId = $group->getId();
            }
        }
        if (!$groupId) {
            throw new Exception('Group not found');
        }
        $report = array(
            'order_create' => 0,
            'order_paid_CSR_followup' => array(),
            'order_unpaid_CSR_followup' => array(),
        );
        //search cases where  startDate < case start date < endDate
        $caseSearch = array(
            'workflow_id' => $workflowId,
            'CAST(`start_date` as DATE) >= ?' => $startDate,
            'CAST(`start_date` as DATE) <= ?' => $endDate,
        );

        $cases = Model_WorkflowCase::getPagedWorkflowCases($caseSearch)->fetchAll();
        //foreach case 
        foreach ($cases as $case) {
            /* @var $case Model_WorkflowCase */
            $report['order_create'] += 1;
            $order = $case->getContextObject();
            $hasPaid = false;
            $csrFollowUp = false;
            //if order paid
            if ($order->getPaidDate()) {
                $hasPaid = true;
            }
            //if csr follow up
            if ($case->hasProcessByGroup($groupId)) {
                $csrFollowUp = true;
            }
            if ($hasPaid && $csrFollowUp) {
                $report['order_paid_CSR_followup'][] = $order->getId();
            }
            if (!$hasPaid && $csrFollowUp) {
                $report['order_unpaid_CSR_followup'][] = $order->getId();
            }
        }
        $report['order_paid_CSR_followup'] = $this->_generateLink('order_paid', $report['order_paid_CSR_followup']);
        $report['order_unpaid_CSR_followup'] = $this->_generateLink('order_paid', $report['order_unpaid_CSR_followup']);
        return $report;
    }

    /**
     * Generate report for account follow up.     
     * @param array $type $config = array('label'=>'Time Frame', 'selection'=>array('daily'=>'Daily','weekly'=>'Weekly', 'monthly'=>'Monthly'));
     * @param date  $date $config = array('label'=>'Date');   
     * @param array $report_type $config = array('label'=>'Report Type', 'selection'=>array('order_created'=>'New Account (CSR Follow up compare to Order Created)','order_paid'=>'Server Orders (CSR Follow up compare to Order Paid)', 'order_cancel' => 'Cancel Orders (CSR Follow up compare to Order Cancel)','csr_report'=> 'CSR Specific Report'));
     */
    public function accountFollowUpResultReportAction() {
        $type = $this->_getParam('type', 'daily');
        $dateString = $this->_getParam('date', null);
        $reportType = $this->_getParam('report_type', 'order_paid');
        switch ($type) {
            case 'daily':
                if ($dateString) {
                    $endDate = $dateString;
                    $startDate = new Yesup_Date($dateString, Zend_Date::ISO_8601);
                    $startDate->subWeek(1);
                    $startDate = $startDate->dbDateString();
                    $period = 'DATE';
                } else {
                    $endDate = Yesup_Date::now()->dbDateString();
                    $startDate = Yesup_Date::now();
                    $startDate->subWeek(1);
                    $startDate = $startDate->dbDateString();
                    $period = 'DATE';
                }
                break;
            case 'weekly':

                if ($dateString) {
                    $endDate = $dateString;
                    $startDate = new Yesup_Date($dateString, Zend_Date::ISO_8601);
                    $startDate->subWeek(8);
                    $startDate = $startDate->dbDateString();
                    $period = 'WEEK';
                } else {
                    $endDate = Yesup_Date::now()->dbDateString();
                    $startDate = Yesup_Date::now();
                    $startDate->subWeek(8);
                    $startDate = $startDate->dbDateString();
                    $period = 'WEEK';
                }
                break;
            case 'monthly':
                if ($dateString) {
                    $endDate = $dateString;
                    $startDate = new Yesup_Date($dateString, Zend_Date::ISO_8601);
                    $startDate->subMonth(6);
                    $startDate = $startDate->dbDateString();
                    $period = 'MONTH';
                } else {
                    $endDate = Yesup_Date::now();
                    $endDate = $endDate->getLastDayOfThisMonth();
                    $endDate = $endDate->dbDateString();
                    $startDate = Yesup_Date::now();
                    $startDate->subMonth(6);
                    $startDate = $startDate->getFirstDayOfThisMonth();
                    $startDate = $startDate->dbDateString();
                    $period = 'MONTH';
                }
                break;
            default:
                error_log('accountFollowUpResultReportAction should never reach here.');
                echo '';
                break;
        }
        if ($reportType == 'order_paid') {
            $this->_generateReport($startDate, $endDate, $period, '_orderPaidReport');
        } elseif ($reportType == 'order_created') {
            $this->_generateReport($startDate, $endDate, $period, '_newAccountReport');
        } elseif ($reportType == 'order_cancel') {
            $this->_generateReport($startDate, $endDate, $period, '_cancellationReport');
        } elseif ($reportType == 'csr_report') {
            $this->_generateReport($startDate, $endDate, $period, '_csrReport');
        }        
    }

    private function _printReport($report, $period, $method) {
        $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
        ///Sorting by date
        //Print in table
        $monthOption = Yesup_Date::now()->getMonthOptions();
        echo '<table>';
        echo '<tr>';
        if ($period == 'DATE') {
            echo '<td width=150><center>Date</center></td>';
        } elseif ($period == 'WEEK') {
            echo '<td width=200><center>Week</center></td>';
        } elseif ($period == 'MONTH') {
            echo '<td width=150><center>Month</center></td>';
        }

        if ($method == '_orderPaidReport') {
            echo '<td width=150><center>Order Created</center></td>';
            echo '<td width=150><center>Order Paid with CSR follow up</center></td>';
            echo '<td width=150><center>Order Unpaid with CSR follow up</center></td>';
            echo '</tr>';
        } elseif ($method == '_newAccountReport') {
            echo '<td width=150><center>New Account</center></td>';
            echo '<td width=180><center>Order Created with follow up</center></td>';
            echo '<td width=180><center>Order Not Created with follow up</center></td>';
            echo '</tr>';
        } elseif ($method == '_cancellationReport') {
            echo '<td width=150><center>Cancellation Order</center></td>';
            echo '<td width=200><center>Continue Cancel with CSR follow up</center></td>';
            echo '<td width=200><center>Retain with CSR follow up</center></td>';
            echo '</tr>';
        } elseif ($method == '_csrReport') {
            echo '<td width=150><center>CSR Member<center></td>';
            echo '<td width=150><center>Total jobs assigned to CSR</center></td>';
            echo '<td width=150><center>Number of jobs assigned to CSR member</center></td>';
            echo '<td width=200><center>Number of jobs in process</center></td>';
            echo '<td width=200><center>Number of jobs finished</center></td>';
            echo '</tr>';
            $detailReport = '';
            $detailReport.= '<table>';
            $detailReport.= '<tr>';
            if ($period == 'DATE') {
                $detailReport .= '<td width=150><center>Date</center></td>';
            } elseif ($period == 'WEEK') {
                $detailReport .= '<td width=200><center>Week</center></td>';
            } elseif ($period == 'MONTH') {
                $detailReport .= '<td width=150><center>Month<center></td>';
            }
            $detailReport .= '<td width=75><center>Job ID<center></td>';
            $detailReport .= '<td width=300><center>Workflow<center></td>';
            $detailReport .= '<td width=150><center>Jobs Name<center></td>';
            $detailReport .= '<td width=150><center>Client<center></td>';
            $detailReport .= '<td width=100><center>Assigned Date<center></td>';
            $detailReport .= '<td width=100><center>Due Date<center></td>';
            $detailReport .= '<td width=150><center>CSR Member<center></td>';
            $detailReport .= '</tr>';
        }
        foreach ($report as $key => $val) {

            if ($period == 'DATE') {
                $dateStr = $view->dateOnly(new Zend_Date($key, Zend_Date::ISO_8601));
            } elseif ($period == 'WEEK') {
                $startDate = new Yesup_Date($key, Zend_Date::ISO_8601);
                $endDate = new Yesup_Date($key, Zend_Date::ISO_8601);
                $endDate->addDay(6);
                $dateStr = $view->dateOnly($startDate) . ' - ' . $view->dateOnly($endDate);
            } elseif ($period == 'MONTH') {
                $startDate = new Yesup_Date($key, Zend_Date::ISO_8601);
                $dateStr = $monthOption[$startDate->getMonthValue()] . ' ' . $startDate->getYearValue();
            }
            if ($method == '_orderPaidReport') {
                echo '<tr>';
                echo '<td><center>' . $dateStr . '</center></td>';
                echo '<td><center>' . $val['order_create'] . '</center></td>';
                echo '<td><center>' . $val['order_paid_CSR_followup'] . '</center></td>';
                echo '<td><center>' . $val['order_unpaid_CSR_followup'] . '</center></td>';
                echo '</tr>';
            } elseif ($method == '_newAccountReport') {
                echo '<tr>';
                echo '<td><center>' . $dateStr . '</center></td>';
                echo '<td><center>' . $val['new_account'] . '</center></td>';
                echo '<td><center>' . $val['order_create_csr_followup'] . '</center></td>';
                echo '<td><center>' . $val['order_not_create_csr_followup'] . '</center></td>';
                echo '</tr>';
            } elseif ($method == '_cancellationReport') {
                echo '<tr>';
                echo '<td><center>' . $dateStr . '</center></td>';
                echo '<td><center>' . $val['cancellation_order'] . '</center></td>';
                echo '<td><center>' . $val['continue_cancel'] . '</center></td>';
                echo '<td><center>' . $val['retain'] . '</center></td>';
                echo '</tr>';
            } elseif ($method == '_csrReport') {
                foreach ($val['job'] as $key => $value) {
                    echo '<tr>';
                    echo '<td><center>' . $dateStr . '</center></td>';
                    echo '<td><center>' . $key . '</center></td>';
                    echo '<td><center>' . $value['total'] . '</center></td>';
                    echo '<td><center>' . $value['assign'] . '</center></td>';
                    echo '<td><center>' . $value['progress'] . '</center></td>';
                    echo '<td><center>' . $value['finished'] . '</center></td>';
                    echo '</tr>';
                }
                echo '<tr><td colspan="6"><br/></td></tr>';
                foreach ($val['detail'] as $key => $value) {
                    $assignDate = new Yesup_Date($value['assign_date'], Zend_Date::ISO_8601);
                    $dueDate = new Yesup_Date($value['due_date'], Zend_Date::ISO_8601);

                    $detailReport .= '<tr>';
                    $detailReport .= '<td><center>' . $dateStr . '</center></td>';
                    $detailReport .= '<td><center>' . $value['job_id'] . '</center></td>';
                    $detailReport .= '<td><center>' . $value['workflow'] . '</center></td>';
                    $detailReport .= '<td><center>' . $value['job'] . '</center></td>';
                    $detailReport .= '<td><center>' . $value['client'] . '</center></td>';
                    $detailReport .= '<td><center>' . $view->dateOnly($assignDate) . '</center></td>';
                    $detailReport .= '<td><center>' . $view->dateOnly($dueDate) . '</center></td>';
                    $detailReport .= '<td><center>' . $value['owner'] . '</center></td>';
                    $detailReport .= '</tr>';
                }               
            }
        }
        echo '</table>';
        if ($method == "_csrReport") {
            echo $detailReport . '</table>';
        }
    }

    private function _newAccountReport($startDate, $endDate) {
        //Define num of followup
        $orderCreatedFollowUp = array();
        $orderNotCreatedFollowUp = array();
        //Get group
        $groups = Model_Group::getPagedGroups(true);
        $groupId = 0;
        foreach ($groups->fetchAll() as $group) {
            if ($group->getName() == 'CSR') {
                $groupId = $group->getId();
            }
        }
        if (!$groupId) {
            throw new Exception('Group not found');
        }
        //Get workflow id to pass into workflowcase
        $search = array('status' => 'active', 'name' => 'New Account');
        $workflows = Model_Workflow::getPagedWorkflows($search)->fetchAll();
        if ($workflows) {
            $workflowId = $workflows[0]->getId();
        } else {
            throw new Exception('Workflow not found.');
        }

        //Get workflowcases which associate with New Account workflow within date range.
        $caseSearch = array(
            'CAST(`start_date` as DATE) >= ?' => $startDate,
            'CAST(`start_date` as DATE) <= ?' => $endDate,
            'workflow_id' => $workflowId,
        );
        $workflowCases = Model_WorkflowCase::getPagedWorkflowCases($caseSearch)->fetchAll();
        foreach ($workflowCases as $case) {
            /* @var $case Model_WorkflowCase */
            /* @var $user Model_User */
            $user = $case->getContextObject();
            $hasOrder = false;
            $csrFollowUp = false;
            if ($user->hasEverOrdered()) {
                $hasOrder = true;
            }
            //Function check workflowitems associate with workflowcaseId and get workflowtransitionId
            //Check in workflowtransition table if its associate with CSR groupID
            if ($case->hasProcessByGroup($groupId)) {
                $csrFollowUp = true;
            }
            if ($hasOrder && $csrFollowUp) {
                $orderCreatedFollowUp[] = $user->getId();
            } elseif ($hasOrder == false && $csrFollowUp) {
                $orderNotCreatedFollowUp[] = $user->getId();
            }
        }

        return array(
            'new_account' => count($workflowCases),
            'order_create_csr_followup' => $this->_generateLink('order_created', $orderCreatedFollowUp),
            'order_not_create_csr_followup' => $this->_generateLink('order_created', $orderNotCreatedFollowUp));
    }

    private function _cancellationReport($startDate, $endDate) {
        //Define num of followup
        $orderCancelledFollowUp = array();
        $orderNotCancelledFollowUp = array();
        //Get group
        $groups = Model_Group::getPagedGroups(true);
        $groupId = 0;
        foreach ($groups->fetchAll() as $group) {
            if ($group->getName() == 'CSR') {
                $groupId = $group->getId();
            }
        }
        if (!$groupId) {
            throw new Exception('Group not found');
        }
        //Get workflow id to pass into workflowcase
        $search = array('status' => 'active', 'name' => 'Server Cancellation Order');
        $workflows = Model_Workflow::getPagedWorkflows($search)->fetchAll();
        if ($workflows) {
            $workflowId = $workflows[0]->getId();
        } else {
            throw new Exception('Workflow not found.');
        }

        //Get workflowcases which associate with New Account workflow within date range.
        $caseSearch = array(
            'CAST(`start_date` as DATE) >= ?' => $startDate,
            'CAST(`start_date` as DATE) <= ?' => $endDate,
            'workflow_id' => $workflowId,
        );
        $workflowCases = Model_WorkflowCase::getPagedWorkflowCases($caseSearch)->fetchAll();
        foreach ($workflowCases as $case) {
            /* @var $case Model_WorkflowCase */
            /* @var $order Model_Order */
            $order = $case->getContextObject();
            $hasCancelled = '';
            $csrFollowUp = false;
            if ($order->getStatus() == 'delivered') {
                $hasCancelled = 'delivered';
            }
            if ($order->getStatus() == 'canceled') {
                $hasCancelled = 'canceled';
            }
            //Function check workflowitems associate with workflowcaseId and get workflowtransitionId
            //Check in workflowtransition table if its associate with CSR groupID
            if ($case->hasProcessByGroup($groupId)) {
                $csrFollowUp = true;
            }
            if ($hasCancelled == 'delivered' && $csrFollowUp) {
                $orderCancelledFollowUp[] = $order->getId();
            } elseif ($hasCancelled == 'canceled' && $csrFollowUp) {
                $orderNotCancelledFollowUp[] = $order->getId();
            }
        }
        return array(
            'cancellation_order' => count($workflowCases),
            'continue_cancel' => $this->_generateLink('order_cancel', $orderCancelledFollowUp),
            'retain' => $this->_generateLink('order_cancel', $orderNotCancelledFollowUp));
    }

    private function _generateLink($type, $ids) {
        $link = array();
        if ($type == 'order_created') {
            if ($ids) {
                $link = '<a href="/admin/user/list?user_id=' . implode(',', $ids) . '">' . count($ids) . '</a>';
            } else {
                $link = '0';
            }
        }
        if ($type == 'order_paid' || $type == 'order_cancel') {
            if ($ids) {
                $link = '<a href="/admin/order/list?order_id=' . implode(',', $ids) . '">' . count($ids) . '</a>';
            } else {
                $link = '0';
            }
        }
        if ($type == 'csr_detail_report') {
            if ($ids) {
                $link = '<a href="/admin/job/case-detail/id/' . $ids . '">' . $ids . '</a>';
            } else {
                $link = '0';
            }
        }

        if ($type == 'csr_job_report') {
            if ($ids) {
                $link = '<a href="/admin/job/all/id/' . implode(',', $ids) . '">' . count($ids) . '</a>';
            } else {
                $link = '0';
            }
        }
        return $link;
    }

}

