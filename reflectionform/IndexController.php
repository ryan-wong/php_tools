<?php

class Scheduler_IndexController extends Yesup_Controller_Scheduler {
    
    public function indexAction() {
        $items = Model_Cron::getAllActiveCronItems();
        $i = 0;
        foreach ( $items as $item ) {
            /* @var $item Model_Cron */
            if ( $item->isRunnableNow() ) {
                // run this item in other process
                
                // first we make a unique key to pass the object to runner by apc
                $i = $i + 1;
                $key = 'cron'.time().'i'.$i;
                apc_store($key, $item, 60);
                
                // trigger the runner page
                $errno = 0;
                $fp = fsockopen('127.0.0.1', 80, $errno);
                if ( $fp ) {
                    // send http request
                    $crlf = "\r\n";
                    fwrite($fp, 'GET /scheduler/index/runner/key/'.$key.' HTTP/1.1'.$crlf);
                    fwrite($fp, 'Host: '.$_SERVER['HTTP_HOST'].$crlf);
                    fwrite($fp, 'User-Agent: local scheduler'.$crlf);
                    fwrite($fp, 'Connection: Close'.$crlf.$crlf);
                    fflush($fp);
                    fclose($fp);
                } else {
                    error_log('Could not open socket to local httpd with error '.$errno);
                }
            }
        }
    }
    
    public function runnerAction() {
        $key = $this->_getParam('key');
        if ( ! $key ) {
            error_log('runner called with key');
            exit();
        }
        $jobItem = apc_fetch($key);
        /* @var $jobItem Model_Cron */
        if ( ! $jobItem ) {
            error_log('Can not get job object with key: '.$key);
            exit();
        }
        
        Zend_Registry::set('job', $jobItem);
        
        $this->_forward(
                $jobItem->getAction(), 
                $jobItem->getController(),
                $jobItem->getModule(),
                $jobItem->getParametersArray()
                );
    }
}
