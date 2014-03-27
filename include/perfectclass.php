<?php

class Perfect extends Job {

    public $docSize;            //small, medium, large
    public $coverColorInk;      //print cover in color ink
    public $coverName;          //cover filename
    public $coverRandName;      //cover file random name
    public $earliest;
    public $view;

    public function __construct($username) {
        parent::__construct($username);
    }

    public function getEarliest() {
	$stamp = time() + (24 * 60 * 60); //Add 24 hours first
        $hour = date("G", $stamp); //Then get the hour stamp in 24 hour time
        syslog(1, $hour);
	if ( $hour >= 16 ) //If it's after hours
		$stamp += (32 - $hour) * 60 * 60; //32 - current = # hours to add to get 8AM
	elseif ( $hour < 8 && $hour > 0 ) //Else it's early morning
		$stamp += (8 - $hour) * 60 * 60; //8 - current = # hours to add to get 8AM
	if ( date("N", $stamp ) > 5) //If it's the weekend
		$stamp += 24 * 60 * 60; //add 24 hours
	if ( date("N", $stamp) > 5 ) //If still weekend
		$stamp += 24 * 60 * 60; //add 24 hours.

	$earliest .= date("Y", $stamp) . ",";
	$earliest .= date("n", $stamp) - 1;
	$earliest .= date(",j,G", $stamp);
	$earliest .= ",0,0,0";
        $this->earliest = $earliest;
    }
    public function serialize() {
        parent::serialize();
        //Press class variables
        $this->preSerialArray['docSize'] = $this->docSize;
        $this->preSerialArray['coverColorInk'] = $this->coverColorInk;
        $this->preSerialArray['coverName'] = $this->coverName;
        $this->preSerialArray['coverRandName'] = $this->coverRandName;
        $this->serial = serialize($this->preSerialArray);
    }
    function un_serialize($serialData) {
        $values = unserialize($serialData);
        if ($values != FALSE) {
            foreach($values as $aVariable => $aValue){
                $this->{$aVariable} = $aValue; 
            }
        }

    }
    function store_job() {
        parent::store_job();
        if (isset($this->jobId) && $this->jobId != NULL) {
            //docSize, coverColorInk, coverName, coverRandName
            $insertPress = "INSERT INTO " . $this->tablePrefix . "perfect ";
            $insertPress .= "(id, docsize, covercolorink, covername, coverrandname)";
            $insertPress .= " VALUES ";
            $insertPress .= "('$this->jobId', '$this->docSize', '$this->coverColorInk', ";
            $insertPress .= "'$this->coverName', '$this->coverRandName')";

            $res = $this->sql->exec($insertPress);
            if (PEAR::isError($res)) {
                syslog(LOG_ERR, $res->getMessage() . " - " . $insertPress);
            }
        }
    }
    
    
    function update_job() {
        parent::update_job();
        if (isset($this->jobId) && $this->jobId != NULL) {
            $updatePerfect = "UPDATE " . $this->tablePrefix . "perfect ";
            $updatePerfect .= "SET docsize = '$this->docSize', covercolorink = '$this->coverColorInk' ";
            $updatePerfect .= "WHERE id = $this->jobId";
            
            $res = $this->sql->exec($updatePerfect);
            if (PEAR::isError($res)) {
                syslog(LOG_ERR, $res->getMessage() . " - " . $updatePerfect);
            }
        }
    }
    
    
    function email_job() {
        include_once("templates/perfect_email.php");
        $toaddr = $this->username . '@northcentral.edu';
        $fromaddr = "mailcent@northcentral.edu";
        mail($toaddr, "Order ID: $this->jobId", $msg, "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\nFrom: mailcent@northcentral.edu\n");
        mail($fromaddr, "Order ID: $this->jobId", $msg, "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\nFrom: $toaddr\n");        
    }
    
    function view_job() {
        include_once("./templates/perfect_view.php");
        $this->view = $msg;
    }
    
    public function get_job_by_id($jobid) {
        if (!is_numeric($jobid)) {
            return False;
        } else {
            parent::get_job_by_id($jobid);
            $query = "SELECT * FROM " . $this->tablePrefix ."perfect WHERE id = '$jobid'";
            $res = $this->sql->query($query);
            if (PEAR::isError($res)) {
                syslog(LOG_ERR, $res->getMessage() . " - " . $query);
            } else {
                $row = $res->fetchRow();
                $this->docSize = $row['docsize'];
                $this->coverColorInk = $row['covercolorink'];
                $this->coverName = $row['covername'];
                $this->coverRandName = $row['coverrandname'];
            }
        }
    }
    
    public function calculate_cost() {
        $runningTotal = 0;
        if($this->departmentCharge != "Personal Copies") {
           if ($this->paperType = "Perforated") {
               if ($this->pageCount > 30)
                   $runningTotal = 5;
               if ($this->pageCount > 50)
                   $runningTotal = 6.5;
               if ($this->pageCount > 100)
                   $runningTotal = 8.5;
               if ($this->pageCount > 150)
                   $runningTotal = 10.5;
               if ($this->pageCount > 200)
                   $runningTotal = 12.5;
               if ($this->pageCount > 250)
                   $runningTotal = 14.5;
           } else {
               if ($this->pageCount > 30)
                   $runningTotal = 4;
               if ($this->pageCount > 50)
                   $runningTotal = 5.5;
               if ($this->pageCount > 100)
                   $runningTotal = 7;
               if ($this->pageCount > 150)
                   $runningTotal = 8.5;
               if ($this->pageCount > 200)
                   $runningTotal = 10;
               if ($this->pageCount > 250)
                   $runningTotal = 12.5;
           }
               
        }
        $this->cost = $runningTotal;
        
    }
}

?>
