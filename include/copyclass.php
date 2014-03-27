<?php

class Copy extends Job {

    public $folding;
    public $stapling;
    public $cutting;
    public $laminate;
    public $collate;
    public $holePunch;
    public $earliest;
    public $view;

    public function __construct($username) {
        parent::__construct($username);

        $this->username = $username;
    }

    public function get_earliest() {
        $hour = date("G");
        $minute = date("i");
        $stamp = time();

        if ($hour >= 13)
            $stamp += 122400 - ($hour * 60 * 60); //(33*60*60) - G*60*60
        elseif ($hour < 8)
            $stamp += (11 - $hour) * 60 * 60;
        else
            $stamp += 5 * 60 * 60;

        if (date("N", $stamp) > 5)
            $stamp += 24 * 60 * 60;
        if (date("N", $stamp) > 5)
            $stamp += 24 * 60 * 60;

        $earliest = date("Y", $stamp) . ",";
        $earliest .= date("n", $stamp) - 1;
        $earliest .= date(",j,G", $stamp);
        $earliest .= ",0,0,0";
        $this->earliest = $earliest;
    }
    
    public function serialize() {
        parent::serialize();
        //Copy class values
        $this->preSerialArray['folding'] = $this->folding;
        $this->preSerialArray['stapling'] = $this->stapling;
        $this->preSerialArray['cutting'] = $this->cutting;
        $this->preSerialArray['laminate'] = $this->laminate;
        $this->preSerialArray['collate'] = $this->collate;
        $this->preSerialArray['holePunch'] = $this->holePunch;
        $this->serial = serialize($this->preSerialArray);
    }
    
    public function un_serialize($serialData) {
        $values = unserialize($serialData);
        if ($values != FALSE) {
            foreach($values as $aVariable => $aValue){
                $this->{$aVariable} = $aValue; 
            }
        }
        
    }

    public function store_job() {
        parent::store_job();
        if (isset($this->jobId) && $this->jobId != NULL) {
            //Fold, staple, cut, laminate, collate, holepunch
            $insertCopy = "INSERT INTO " . $this->tablePrefix . "copy ";
            $insertCopy .= "(id, folding, stapling, cutting, laminate, [collate], holepunch) ";
            $insertCopy .= "VALUES ";
            $insertCopy .= "('$this->jobId', '$this->folding', '$this->stapling', ";
            $insertCopy .= "'$this->cutting', '$this->laminate', '$this->collate', '$this->holePunch')";

            $res = $this->sql->exec($insertCopy);
            if (PEAR::isError($res)) {
                syslog(LOG_ERR, $res->getMessage() . " - " . $insertCopy);
            }
        }
    }
    
    public function update_job() {
        parent::update_job();
        if (isset($this->jobId) && $this->jobId != NULL) {
            $updateCopy = "UPDATE " . $this->tablePrefix . "copy ";
            $updateCopy .= "SET folding = '$this->folding', stapling = '$this->stapling', ";
            $updateCopy .= "cutting = '$this->cutting', laminate = '$this->laminate', ";
            $updateCopy .= "[collate] = '$this->collate', holepunch = '$this->holePunch' ";
            $updateCopy .= "WHERE id = $this->jobId";
            
            $res = $this->sql->exec($updateCopy);
            if (PEAR::isError($res)) {
                syslog(LOG_ERR, $res->getMessage() . " - " . $updateCopy);
            }
        }
    }
    
    public function email_job() {
        include_once("templates/copy_email.php");
        $toaddr = $this->username . '@northcentral.edu';
        $fromaddr = "mailcent@northcentral.edu";
        mail($toaddr, "Order ID: $this->jobId", $msg, "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\nFrom: mailcent@northcentral.edu\n");
        mail($fromaddr, "Order ID: $this->jobId", $msg, "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\nFrom: $toaddr\n");        
    }
    
    public function view_job() {
        include_once("./templates/copy_view.php");
        $this->view = $msg;
    }
    
    public function get_job_by_id($jobid) {
        if (!is_numeric($jobid)) {
            return False;
        } else {
            parent::get_job_by_id($jobid);
            $query = "SELECT * FROM " . $this->tablePrefix ."copy WHERE id = '$jobid'";
            $res = $this->sql->query($query);
            if (PEAR::isError($res)) {
                syslog(LOG_ERR, $res->getMessage() . " - " . $query);
            } else {
                $row = $res->fetchRow();
                $this->folding = $row['folding'];
                $this->stapling = $row['stapling'];
                $this->cutting = $row['cutting'];
                $this->laminate = $row['laminate'];
                $this->collate = $row['collate'];
                $this->holePunch = $row['holepunch'];
                
            }
        
        }
    }
    
    public function calculate_cost() {
        $priceColor = .15;
        $priceLaminate8x11 = .5;
        $priceLaminate11x17 = 1;
        $priceBlackWhite = .05;
        $runningTotal = 0;
        $totalPages = $this->quantity * $this->pageCount;
        $studentOrgs = array("SIFE", "Delta Kappa", "SMB", "Praise Gathering", "NCSA");
        
        if ($this->departmentCharge != "Personal Coppies") {     
            if ($this->colorInk == 'Yes')
                $runningTotal += $totalPages * $priceColor;
            if ($this->laminate == 'Yes') {
                if ($this->paperSize == '8 1/2 x 11')
                    $runningTotal += $totalPages * $priceLaminate8x11;
                else 
                    $runningTotal += $totalPages * $priceLaminate11x17;
            }
        }
        
        if (in_array($this->departmentCharge, $studentOrgs) && $this->colorInk == 'No') {
                $runningTotal += $totalPages * $priceBlackWhite;
        }
        $this->cost = $runningTotal;
    }
}

?>
