<?php
require_once('../MDB2/MDB2.php');
require_once "copyclass.php";
require_once "pressclass.php";
require_once "perfectclass.php";
require_once "spiralclass.php";
require_once "tapeclass.php";

class Job {

    public $jobId;         //unique job # set by SQL autoincrement
    public $jobType = "copy";       //Type of job, one of copy, pressbind, perfectbind
    public $jobName;       //User defined name for job
    public $submitDate;    //Date submitted on, auto generated by SQL when record is stored
    public $dueDate;       //Date user would like it completed
    public $quantity;      //Number of copies
    public $duplex;    //Single or Double sided, Yes or No
    public $transport = "Deliver";     //Deliver or pickup
    public $printedBy;     //Who completed the job
    public $stored;        //Where the finished product was placed
    public $username;      //User who submitted the job
    public $phone;         //Phone # of user who submitted job
    public $departmentDeliver;    //Department to deliver to
    public $departmentCharge;   //Department to charge for job
    public $confidential;       //Tests or other information that students shouldn't see.
    public $departmentList = array();
    public $departmentDeliverable = array();
    public $departmentChargeDefault; //Users default charge department
    public $departmentDeliverDefault; //Users default deliver department
    public $filename;      //The actual filename that was uploaded
    public $randname;      //Random name we assigned the file to protect from overwrite
    public $comments;      //Any user comments for the job - Optional
    public $account;       //Account # to charge - Optional
    public $colorInk;      //Should the document be printed in color
    public $pageCount;     //Number of pages in document excluding cover if uploaded seperate
    public $paperSize = "8 1/2 X 11";     //all jobs are printed on paper, usually standard size sheet
    public $paperType;     //Paper type used for the document itself, not the cover
    public $paperColor;    //Paper color used for the document itself, not the cover
    public $paperSizes = array();
    public $paperTypes = array();
    public $paperColors = array();
    public $paperCombinations = array();
    public $status;        //The status of the job, one of Pending, Completed, Deleted
    public $flag;
    public $jobList = array();        //Stores the results of get_jobs_by_username or get_open_jobs or get_completed_jobs
    public $serial;
    public $preSerialArray = array();
    public $update;
    public $cost;
    public $uploadDir = "/files/directory";
    private $dbServer = "mysql1.freehosting.com";
    private $dbUser = "anythin8_edoc";
    private $dbPass = "vU07h67zsf";
    private $database = "anythin8_edoc";
    private $dbType = "mysql";
    protected $tablePrefix = "doccenter-";
    public $sql;
    public $isAdmin = FALSE;

    public function __construct($username) {
        $this->username = $username;
        $this->db_connect();
        $this->get_departments();
        $this->get_departments_deliverable();
        $this->get_default_department_charge();
        $this->get_default_department_deliver();
        $this->is_administrator();
        
    }
    
    public function db_connect()
    {
        $dsn = array (
            'phptype' => $this->dbType,
            'username' => $this->dbUser,
            'password' => $this->dbPass,
            'hostspec' => $this->dbServer,
            'database' => $this->database,
        );
        
        $options = array();
        $this->sql =& MDB2::connect($dsn, $options);
        if (PEAR::isError($this->sql)) {
            die($this->sql->getMessage()." unable to connect to database ".$this->database);
        }
        $this->sql->setFetchMode(MDB2_FETCHMODE_ASSOC);
    }
     
    public function get_job_type_by_id($jobid) {
        if (!is_numeric($jobid)) {
            return False;
        } else {
            $query = "SELECT jobtype FROM " . 
                    $this->tablePrefix ."job WHERE jobid = '$jobid'";
            $res = $this->sql->query($query);
            if (PEAR::isError($res)) {
                syslog(LOG_ERR, $res->getMessage() . " - " . $query);
            } else {
                $row = $res->fetchRow();
                $this->jobType = $row['jobtype'];
            }
        }
    }
    
    public function get_job_by_username() {
        if (! isset($this->username)) {
            return;
        }
        $query = "SELECT TOP 10 jobid, jobtype, jobname, duedate, status FROM ".
                $this->tablePrefix."job WHERE username = '$this->username' ORDER BY jobid DESC";
        $res = $this->sql->query($query);
        if (PEAR::isError($res)) {
            syslog(LOG_ERR, $res->getMessage() . " - " . $query);
        } else {
            while ($row = $res->fetchRow()) {
                $this->jobList[] = $row;
            }
        }
        
    }
    
    public function get_open_jobs() {
        $query = "SELECT jobid, jobtype, jobname, duedate, status, username, confidential FROM ".
                $this->tablePrefix."job WHERE flag = 'Open' ORDER BY duedate ASC";
        $res = $this->sql->query($query);
        if (PEAR::isError($res)) {
            syslog(LOG_ERR, $res->getMessage() . " - " . $query);
        } else {
            while ($row = $res->fetchRow()) {
                $this->jobList[] = $row;
            }
        }
    }
    
    public function get_closed_jobs() {
        $epoch36DaysAgo = time() - (36*24*60*60);
        
        $query = "SELECT jobid, jobtype, jobname, completedate, printedby, stored, username, status FROM " . 
                $this->tablePrefix."job WHERE flag = 'Complete' OR flag = 'Cancel' " .
                "AND completedate > '".date('Y-m-d', $epoch36DaysAgo)."' " .
                "ORDER BY completedate DESC";
        $res = $this->sql->query($query);
        if (PEAR::isError($res)) {
            syslog(LOG_ERR, $res->getMessage() . " - " . $query);
        } else {
            while ($row = $res->fetchRow()) {
                $this->jobList[] = $row;
            }
        }
    }
    public function is_administrator() {
        $query = "SELECT * from unet_mc_admin WHERE username = '$this->username'";
        $res = $this->sql->query($query);
        if (PEAR::isError($res)) {
            $this->isAdmin = FALSE;
            syslog(LOG_ERR, $res->getMessage() . " - " . $query);
        } else {
            $row = $res->fetchRow();
            if (isset($row) && is_array($row) && $row['user_index'] > 0)
                $this->isAdmin = TRUE;
        }
    }
    
    public function get_departments() {
        $res =& $this->sql->query("SELECT * FROM unet_mc_dept ORDER BY department ASC");
        if (PEAR::isError($res)) {
            echo $res->getMessage();
            syslog(LOG_ERR, $res->getMessage() . " - " .$query);
        } else {
            while ($row = $res->fetchRow()) {
                $this->departmentList[] = $row['department'];
            }
        }
    }
    
    public function get_departments_deliverable() {
        $res =& $this->sql->query("SELECT 'department' FROM unet_mc_dept WHERE deliverable='1'");
        while ($row = $res->fetchRow()) {
            $this->departmentDeliverable[] = $row['department'];
        }
    }
    
    public function add_department($departmentname) {
        $addQuery = "INSERT INTO unet_mc_dept (department) VALUES ('$departmentname')";
        $res = $this->sql->exec($addQuery);
        if (PEAR::isError($res)) {
            syslog(LOG_ERR, $res->getMessage() . " - " . $addQuery);
        }
    }
    
    public function delete_department($departmentname) {
        $deleteQuery = "DELETE FROM unet_mc_dept WHERE department = '$departmentname'";
        $res = $this->sql->exec($deleteQuery);
        if (PEAR::isError($res)) {
            syslog(LOG_ERR, $res->getMessage() . " - " . $deleteQuery);
        }
    }
    
    public function get_default_department_charge() {
        $res =& $this->sql->query("SELECT departmentcharge FROM " . 
                $this->tablePrefix . "job WHERE username = '" . 
                $this->username . "' AND default = 'checked' ORDER BY submitdate DESC LIMIT 1"  );
        if (PEAR::isError($res)) {
            return;
        }
        $row = $res->fetchRow();
        if ($row != NULL) {
            $this->departmentChargeDefault = $row['departmentcharge'];
        }
    }
    
    public function get_default_department_deliver() {
        $res =& $this->sql->query("SELECT departmentdeliver FROM " . 
                $this->tablePrefix . "job WHERE username = '" . 
                $this->username . "' AND default = 'checked' ORDER BY submitdate DESC LIMIT 1"  );
        if (PEAR::isError($res)) {
            return;
        }
        $row = $res->fetchRow();
        if ($row != NULL) {
            $this->departmentDeliverDefault = $row['departmentdeliver'];
        }
    }

    public function add_admin($username) {
        $add_user = "INSERT INTO unet_mc_admin (username) VALUES ('$username')";
        $res = $this->sql->query($add_user);
        if (PEAR::isError($res)){
            syslog(LOG_ERR, $res->getMessage() . " - " . $add_user);
        }
    }
    
    public function delete_admin($username) {
        $delete_user = "DELETE FROM unet_mc_admin WHERE username = '$username'";
        $res = $this->sql->exec($delete_user);
        if (PEAR::isError($res)){
            syslog(LOG_ERR, $res->getMessage() . " - " . $delete_user);
        }
    }
    
    public function get_admins() {
        $admins = array();
        $get_admins = "SELECT username FROM unet_mc_admin";
        $res = $this->sql->query($get_admins);
        if (PEAR::isError($res)) {
            syslog(LOG_ERR, $res->getMessage() . " - ". $get_admins);
            $admins[] = "ERROR - see logs";
        } else {
          while ($row = $res->fetchRow()) {
              $admins[] = $row['username'];
          }  
        }
        return $admins;
    }
    
    public function get_paper_type_by_id($id) {
        $query = "SELECT papertype FROM unet_mc_papertype WHERE papertype_index = '$id'";
        $res =& $this->sql->query($query);
        if (PEAR::isError($res)){
            syslog(LOG_ERR, $res->getMessage() . " - " . $query);
            $this->paperType = "ERROR jobclass GetPaperTypeById";
        } else {
            $row = $res->fetchRow();
            $this->paperType = $row['papertype'];
        }
    }
    public function get_paper_size_by_id($id) {
        $query = "SELECT papersize FROM unet_mc_papersize WHERE papersize_index = '$id'";
        $res =& $this->sql->query($query);
        if (PEAR::isError($res)){
            syslog(LOG_ERR, $res->getMessage() . " - " . $query);
            $this->paperSize = "ERROR jobclass GetPaperSizeById";
        } else {
            $row = $res->fetchRow();
            $this->paperSize = $row['papersize'];
        }
    }
    public function get_paper_color_by_id($id) {
        $query = "SELECT papercolor FROM unet_mc_papercolor WHERE papercolor_index = '$id'";
        $res =& $this->sql->query($query);
        if (PEAR::isError($res)){
            syslog(LOG_ERR, $res->getMessage() . " - " . $query);
            $this->paperColor = "ERROR jobclass GetPaperColorById";
        } else {
            $row = $res->fetchRow();
            $this->paperColor = $row['papercolor'];
        }
    }
    
    public function get_all_paper_colors() {
        $query = "SELECT * FROM unet_mc_papercolor";
        $res =& $this->sql->query($query);
        if (PEAR::isError($res)){
            syslog(LOG_ERR, $res->getMessage() . " - " . $query);
            $this->paperColors[] = "ERROR - see logs";
        } else {
            while($row = $res->fetchRow()) {
                $this->paperColors[] = $row;
            }
        }
    }
    
    public function get_all_paper_types() {
                $query = "SELECT * FROM unet_mc_papertype";
        $res =& $this->sql->query($query);
        if (PEAR::isError($res)){
            syslog(LOG_ERR, $res->getMessage() . " - " . $query);
            $this->paperTypes[] = "ERROR - see logs";
        } else {
            while($row = $res->fetchRow()) {
                $this->paperTypes[] = $row;
            }
        }
    }
    
    public function get_all_paper_sizes() {
        $query = "SELECT * FROM unet_mc_papersize";
        $res =& $this->sql->query($query);
        if (PEAR::isError($res)){
            syslog(LOG_ERR, $res->getMessage() . " - " . $query);
            $this->paperSizes[] = "ERROR - see logs";
        } else {
            while($row = $res->fetchRow()){
                $this->paperSizes[] = $row;
            }
        }
    }
    
    public function get_all_paper_combinations() {
        $query = "SELECT paper_index, papercolor, papertype, papersize FROM unet_mc_paper p ";
        $query .= "JOIN unet_mc_papercolor c ON c.papercolor_index = p.paper_color ";
        $query .= "JOIN unet_mc_papertype t on t.papertype_index = p.paper_type ";
        $query .= "JOIN unet_mc_papersize s on s.papersize_index = p.paper_size ";
        $res =& $this->sql->query($query);
         if (PEAR::isError($res)){
            syslog(LOG_ERR, $res->getMessage() . " - " . $query);
            $this->paperCombinations[] = "ERROR - see logs";
        } else {
            while($row = $res->fetchRow()){
                $this->paperCombinations[] = $row;
            }
        }
    }
    
    public function delete_paper_combination($id) {
        $deleteQuery = "DELETE FROM unet_mc_paper WHERE paper_index = '$id'";
        $res = $this->sql->exec($deleteQuery);
            if (PEAR::isError($res)) {
                syslog(LOG_ERR, $res->getMessage() . " - " . $deleteQuery);
            }
    }
    
    public function add_paper_color($color) {
        $addQuery = "INSERT INTO unet_mc_papercolor (papercolor) VALUES ('$color')";
        $res = $this->sql->exec($addQuery);
        if (PEAR::isError($res)) {
            syslog(LOG_ERR, $res->getMessage() . " - " . $addQuery);
        }
    }
    
    public function delete_paper_color($color) {
        //Deletes a paper color from the database
        //Technically this should do some additional checking to see if this 
        //papercolor_index is in use in the paper table
        $deleteQuery = "DELETE FROM unet_mc_papercolor WHERE papercolor = '$color'";
        $res = $this->sql->exec($deleteQuery);
            if (PEAR::isError($res)) {
                syslog(LOG_ERR, $res->getMessage() . " - " . $deleteQuery);
            }
    }
    
    public function add_paper_size($size) {
        $addQuery = "INSERT INTO unet_mc_papersize (papersize) VALUES ('$size')";
        $res = $this->sql->exec($addQuery);
        if (PEAR::isError($res)) {
            syslog(LOG_ERR, $res->getMessage() . " - " . $addQuery);
        }
    }
    
    public function delete_paper_size($size) {
        //Deletes a paper size from the database
        //Technically this should do some additional checking to see if this
        //papersize_index is in use in the paper table
        $deleteQuery = "DELETE FROM unet_mc_papersize WHERE papersize = '$size'";
        $res = $this->sql->exec($deleteQuery);
            if (PEAR::isError($res)) {
                syslog(LOG_ERR, $res->getMessage() . " - " . $deleteQuery);
            }
    }
    
    public function add_paper_type($type) {
        $addQuery = "INSERT INTO unet_mc_papertype (papertype) VALUES ('$type')";
        $res = $this->sql->exec($addQuery);
        if (PEAR::isError($res)) {
            syslog(LOG_ERR, $res->getMessage() . " - " . $addQuery);
        }
    }
    
    public function delete_paper_type($type) {
        //Deletes a paper type from the database
        //Technically this should do some additional checking to see if this
        //papertype_index is in use in the paper table
        $deleteQuery = "DELETE from unet_mc_papertype WHERE papertype = '$type'";
        $res = $this->sql->exec($deleteQuery);
            if (PEAR::isError($res)) {
                ssylog(LOG_ERR, $res->getMessage() . " - " . $deleteQuery);
            }
    }
    
    public function add_paper_combination($size, $type, $color) {
        $addQuery = "INSERT INTO unet_mc_paper (paper_size, paper_type, paper_color) ";
        $addQuery .= "VALUES ('$size', '$type', '$color')";
        $res = $this->sql->exec($addQuery);
        if (PEAR::isError($res)) {
            syslog(LOG_ERR, $res->getMessage() . " - " . $addQuery);
        }
    }
    
    public function store_job() {   
        $childClass = get_class($this);
        $escapedJobName = $this->sql->escape($this->jobName);
        $escapedFileName = $this->sql->escape($this->filename);
        $escapedComments = $this->sql->escape($this->comments);
        $insertQuery = "INSERT INTO " . $this->tablePrefix . "job (jobtype, jobname, username, ";
        $insertQuery .= "submitdate, duedate, phone, departmentcharge, departmentdeliver, confidential, account, ";
        $insertQuery .= "filename, randomname, quantity, duplex, comments, colorink, pagecount, ";
        $insertQuery .= "papersize, papertype, papercolor, transport, status, flag, printedby, stored, cost) VALUES (";
        $insertQuery .= "'$childClass', '$escapedJobName', '$this->username', '$this->submitDate', ";
        $insertQuery .= "'$this->dueDate', '$this->phone', '$this->departmentCharge', '$this->departmentDeliver', ";
        $insertQuery .= "'$this->confidential', '$this->account', '$escapedFileName', '$this->randname', '$this->quantity', ";
        $insertQuery .= "'$this->duplex', '$escapedComments', '$this->colorInk', '$this->pageCount', ";
        $insertQuery .= "'$this->paperSize', '$this->paperType', '$this->paperColor', '$this->transport', ";
        $insertQuery .= "'$this->status', '$this->flag', '$this->printedBy', '$this->stored', '$this->cost')";

        $res = $this->sql->exec($insertQuery);
        if (PEAR::isError($res)) {
            syslog(LOG_ERR, $res->getMessage() . " - " . $insertQuery);
        } else {
            $this->jobId = $this->sql->lastInsertID($this->tablePrefix."job", 'jobid');
            if(PEAR::isError($this->jobId)){
                die($this->jobID->getMessage());
            }
        }
    }
    
    //Update the job information
    public function update_job() {
        if (isset($this->jobId) && isset($this->update) && $this->jobId != '0' && $this->update == '1') {
            //$childClass = get_class($this);
            $escapedJobName = $this->sql->escape($this->jobName);
           
            $escapedComments = $this->sql->escape($this->comments);
            $insertQuery = "UPDATE " . $this->tablePrefix . "job SET jobname = '$escapedJobName', ";
            $insertQuery .= "duedate = '$this->dueDate', phone = '$this->phone', departmentcharge = '$this->departmentCharge', ";
            $insertQuery .= "departmentdeliver = '$this->departmentDeliver', confidential = '$this->confidential', ";
            $insertQuery .= "account = '$this->account', quantity = '$this->quantity', duplex = '$this->duplex', ";
            $insertQuery .= "comments = '$escapedComments', colorink = '$this->colorInk', pagecount = '$this->pageCount', ";
            $insertQuery .= "papersize = '$this->paperSize', papertype = '$this->paperType', papercolor = '$this->paperColor', ";
            $insertQuery .= "transport = '$this->transport', status = 'Updated by $this->username', cost = '$this->cost' ";
            $insertQuery .= "WHERE jobid = $this->jobId";


            $res = $this->sql->exec($insertQuery);
            if (PEAR::isError($res)) {
                syslog(LOG_ERR, $res->getMessage() . " - " . $insertQuery);
            }
        }
    }
    
    
    public function serialize() {
        $values = array();
        $this->preSerialArray['jobId'] = $this->jobId;
        $this->preSerialArray['jobName'] = $this->jobName;
        $this->preSerialArray['submitDate'] = $this->submitDate;
        $this->preSerialArray['dueDate'] = $this->dueDate;
        $this->preSerialArray['quantity'] = $this->quantity;
        $this->preSerialArray['duplex'] = $this->duplex;
        $this->preSerialArray['transport'] = $this->transport;
        $this->preSerialArray['confidential'] = $this->confidential;
        $this->preSerialArray['phone'] = $this->phone;
        $this->preSerialArray['departmentDeliver'] = $this->departmentDeliver;
        $this->preSerialArray['departmentCharge'] = $this->departmentCharge;
        $this->preSerialArray['filename'] = $this->filename;
        $this->preSerialArray['randname'] = $this->randname;
        $this->preSerialArray['comments'] = $this->comments;
        $this->preSerialArray['account'] = $this->account;
        $this->preSerialArray['colorInk'] = $this->colorInk;
        $this->preSerialArray['pageCount'] = $this->pageCount;
        $this->preSerialArray['paperSize'] = $this->paperSize;
        $this->preSerialArray['paperType'] = $this->paperType;
        $this->preSerialArray['paperColor'] = $this->paperColor;
        $this->preSerialArray['status'] = $this->status;
        $this->preSerialArray['update'] = $this->update;
    }
    
    public function get_job_by_id($jobid) {
        if (!is_numeric($jobid)) {
            return False;
        } else {
            $query = "SELECT * FROM " . $this->tablePrefix ."job WHERE jobid = '$jobid'";
            $res = $this->sql->query($query);
            if (PEAR::isError($res)) {
                syslog(LOG_ERR, $res->getMessage() . " - " . $query);
            } else {
                $row = $res->fetchRow();
                $this->jobId = $row['jobid'];
                $this->jobType = $row['jobtype'];
                $this->jobName = $row['jobname'];
                $this->username = $row['username'];
                $this->submitDate = $row['submitdate'];
                $this->dueDate = $row['duedate'];
                $this->phone = $row['phone'];
                $this->departmentCharge = $row['departmentcharge'];
                $this->departmentDeliver = $row['departmentdeliver'];
                $this->confidential = $row['confidential'];
                $this->account = $row['account'];
                $this->filename = $row['filename'];
                $this->randname = $row['randomname'];
                $this->quantity = $row['quantity'];
                $this->duplex = $row['duplex'];
                $this->comments = $row['comments'];
                $this->colorInk = $row['colorink'];
                $this->pageCount = $row['pagecount'];
                $this->paperSize = $row['papersize'];
                $this->paperType = $row['papertype'];
                $this->paperColor = $row['papercolor'];
                $this->transport = $row['transport'];
                $this->status = $row['status'];
                $this->flag = $row['flag'];
                $this->stored = $row['stored'];
                $this->printedBy = $row['printedby'];
            }
        }
    }
    
    function cancel($uname) {
        if (isset($this->jobId) && isset($this->username)) {
            $query = "UPDATE " . $this->tablePrefix . "job SET status = 'Canceled by $uname', ";
            $query .= "flag = 'Cancel', ";
            $query .= "completedate = '" . strftime("%G-%m-%d %H:%M") . "', ";
            $query .= "printedby = '" . $uname ."' ";
            $query .= "WHERE jobid = '$this->jobId'";
            $res = $this->sql->query($query);
            if (PEAR::isError($res)) {
                syslog(LOG_ERR, $res->getMessage() . " - " . $query);
            }
        }
    }
    
    function complete() {
        if (isset($this->jobId)) {
            $query = "UPDATE ".$this->tablePrefix."job SET ";
            $query .= "status = 'Completed by $this->username', flag = 'Complete', ";
            $query .= "completedate = '" . strftime("%G-%m-%d %H:%M") ."' WHERE jobid = '$this->jobId'";
            $res = $this->sql->exec($query);
            if (PEAR::isError($res)) {
                syslog(LOG_ERR, $res->getMessage() . " - " . $query);
            }
            //Need something here to email the requestor letting them know their job has been completed.
            //**This is now handeled in admin.php by calling the specific jobs email_job function with a different subject.
            //$msg = "This job has been completed";
            //$toaddr = $this->username . '@northcentral.edu';
            //$fromaddr = "mailcent@northcentral.edu";
            //mail($toaddr, "Job: $this->jobId is complete", $msg, "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\nFrom: mailcent@northcentral.edu\n");
        }
    }
    
    function printed() {
        if (isset($this->jobId) && isset($this->stored) && isset($this->printedBy)) {
            $query = "UPDATE ".$this->tablePrefix."job SET printedby = '$this->printedBy', ";
            $query .= "stored = '$this->stored', status = 'Printed', flag = 'Open', ";
            $query .= "completedate = '" . strftime("%G-%m-%d %H:%M") ."' WHERE jobid = '$this->jobId'";
            $res = $this->sql->query($query);
            if (PEAR::isError($res)) {
                syslog(LOG_ERR, $res->getMessage() . " - " . $query);
            }
        }
    }
}
?>
