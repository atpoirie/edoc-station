<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of spiral
 *
 * @author atpoirie
 */
class Press extends Job {
    public $earliest;
    public $cutting;
    public $bindColor;
    public $bindSide;
    public $frontPlastic;
    public $frontPresentation;
    public $frontColorInk;
    public $frontPaperType;
    public $frontPaperColor;
    public $backPlastic;
    public $backPresentation;
    public $backBlankPage;
    public $backPaperType;
    public $backPaperColor;
    public $defaultPaperType = array();
    public $defaultPaperColor = array();
    public $view;
    
    public function __construct($username) {
        parent::__construct($username);
        $this->paperSize = '8 1/2 x 11';
        $this->getDefaultPaperType();
    }
    
    public function getEarliest() {	
	$stamp = time() + (24 * 60 * 60);
        $hour = date("G", $stamp);
	if ( $hour >= 16 )
		$stamp += (32 - $hour) * 60 * 60;
	elseif ( $hour < 8 && $hour > 0 )
		$stamp += (8 - $hour) * 60 * 60;
	if ( date("N", $stamp ) > 5)
		$stamp += 24 * 60 * 60;
	if ( date("N", $stamp) > 5 )
		$stamp += 24 * 60 * 60;

	$earliest .= date("Y", $stamp) . ",";
	$earliest .= date("n", $stamp) - 1;
	$earliest .= date(",j,G", $stamp);
	$earliest .= ",0,0,0";
        $this->earliest = $earliest;
    }
    
    public function getDefaultPaperType() {
        $query = "SELECT * FROM unet_mc_papertype WHERE pressbind IS NOT NULL ORDER BY pressbind";
        $res = $this->sql->query($query);
        $first = TRUE;
        if (PEAR::isError($res)) {
            syslog(LOG_ERR, $res->getMessage() . " - " . $query);
            return;
        } elseif ($res->numRows() == 0) {
            $this->defaultPaperType[] = "papertype not defined";
        } else {
            while($row = $res->fetchRow()) {
                if ($first) {
                    $defaultType = $row['papertype_index'];
                    $first = FALSE;
                }
                $this->defaultPaperType[] = $row['papertype'];
            }
        }
        $res->free();
        if (isset($defaultType)) {
            $query = "SELECT c.papercolor FROM unet_mc_paper p 
                INNER JOIN unet_mc_papertype t ON t.papertype_index = p.paper_type AND t.pressbind = '1'
                INNER JOIN unet_mc_papersize s ON s.papersize_index = p.paper_size AND s.pressbind = '1'
                INNER JOIN unet_mc_papercolor c ON c.papercolor_index = p.paper_color";
            $res = $this->sql->query($query);
            if (PEAR::isError($res)) {
                syslog(LOG_ERR, $res->getMessage() . " - " . $query);
                $this->defaultPaperColor[] = "papercolor not defined";
                return;
            } else {
                while ($color = $res->fetchRow()) {
                    $this->defaultPaperColor[] = $color['papercolor'];
                }
            }
        }
    }
    
    public function getPaperColor($paperType) {
        if ($paperType == "") {
            die();
        }
        
        unset($this->defaultPaperColor);
        
        $query = "SELECT papertype_index FROM unet_mc_papertype WHERE papertype = '$paperType'";
        $paperTypeIndex = $this->sql->query($query);
        if (PEAR::isError($paperTypeIndex)) {
            syslog(LOG_ERR, $paperTypeIndex->getMessage() . " - " . $query);
        }
        $typeIndex = $paperTypeIndex->fetchRow();
        
        $query = "SELECT papersize_index FROM unet_mc_papersize WHERE pressbind = '1'";
        $paperSizeIndex = $this->sql->query($query);
        if (PEAR::isError($paperSizeIndex)) {
            syslog(LOG_ERR, $paperSizeIndex->getMessage() . " - " . $query);
        }
        $sizeIndex = $paperSizeIndex->fetchRow();
        
        $query = "SELECT c.papercolor FROM unet_mc_paper p
            INNER JOIN unet_mc_papercolor c ON p.paper_color = c.papercolor_index
            WHERE p.paper_size = '".$sizeIndex['papersize_index']. "' 
            AND p.paper_type = '".$typeIndex['papertype_index']."'";
        $paperColors = $this->sql->query($query);
        if (PEAR::isError($paperColors)) {
            syslog(LOG_ERR, $paperColors->getMessage() . " - " . $query);
        }
        while ( $color = $paperColors->fetchRow()) {
            $this->defaultPaperColor[] = $color['papercolor'];
        }
    }
    
    public function serialize() {
        parent::serialize();
        //Press class variables
        $this->preSerialArray['cutting'] = $this->cutting;
        $this->preSerialArray['bindColor'] = $this->bindColor;
        $this->preSerialArray['bindSide'] = $this->bindSide;
        $this->preSerialArray['frontPlastic'] = $this->frontPlastic;
        $this->preSerialArray['frontPresentation'] = $this->frontPresentation;
        $this->preSerialArray['frontColorInk'] = $this->frontColorInk;
        $this->preSerialArray['frontPaperType'] = $this->frontPaperType;
        $this->preSerialArray['frontPaperColor'] = $this->frontPaperColor;
        $this->preSerialArray['backPlastic'] = $this->backPlastic;
        $this->preSerialArray['backPresentation'] = $this->backPresentation;
        $this->preSerialArray['backBlankPage'] = $this->backBlankPage;
        $this->preSerialArray['backPaperType'] = $this->backPaperType;
        $this->preSerialArray['backPaperColor'] = $this->backPaperColor;
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
            //cutting, bind color, bind side, front plastic, front presentation
            //front color ink, front paper type, front paper color
            //back plastic, back presentation, back blank page, back paper type, back paper color
            $insertPress = "INSERT INTO " . $this->tablePrefix . "press ";
            $insertPress .= "(id, cutting, bindcolor, bindside, frontplastic, frontpresentation, ";
            $insertPress .= "frontcolorink, frontpapertype, frontpapercolor, backplastic, ";
            $insertPress .= "backpresentation, backblankpage, backpapertype, backpapercolor)";
            $insertPress .= " VALUES ";
            $insertPress .= "('$this->jobId', '$this->cutting', '$this->bindColor', ";
            $insertPress .= "'$this->bindSide', '$this->frontPlastic', '$this->frontPresentation', ";
            $insertPress .= "'$this->frontColorInk', '$this->frontPaperType', '$this->frontPaperColor', ";
            $insertPress .= "'$this->backPlastic', '$this->backPresentation', '$this->backBlankPage', ";
            $insertPress .= "'$this->backPaperType', '$this->backPaperColor')";

            $res = $this->sql->exec($insertPress);
            if (PEAR::isError($res)) {
                syslog(LOG_ERR, $res->getMessage() . " - " . $insertPress);
            }
        }
    }
    
    function update_job() {
        parent::update_job();
        if (isset($this->jobId) && $this->jobId != NULL) {
            $updatePress = "UPDATE " . $this->tablePrefix . "press ";
            $updatePress .= "SET cutting = '$this->cutting', bindcolor = '$this->bindColor', ";
            $updatePress .= "bindside = '$this->bindSide', frontplastic = '$this->frontPlastic', ";
            $updatePress .= "frontpresentation = '$this->frontPresentation', frontcolorink = '$this->frontColorInk', ";
            $updatePress .= "frontpapertype = '$this->frontPaperType', frontpapercolor = '$this->frontPaperColor', ";
            $updatePress .= "backplastic = '$this->backPlastic', backpresentation = '$this->backPresentation', ";
            $updatePress .= "backblankpage = '$this->backBlankPage', backpapertype = '$this->backPaperType', backpapercolor = '$this->backPaperColor' ";
            $updatePress .= "WHERE id = $this->jobId";
            
            $res = $this->sql->exec($updatePress);
            if (PEAR::isError($res)) {
                syslog(LOG_ERR, $res->getMessage() . " - " . $updatePress);
            }
        }
    }
    
    function email_job() {
        include_once("templates/press_email.php");
        $toaddr = $this->username . '@northcentral.edu';
        $fromaddr = "mailcent@northcentral.edu";
        mail($toaddr, "Order ID: $this->jobId", $msg, "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\nFrom: mailcent@northcentral.edu\n");
        mail($fromaddr, "Order ID: $this->jobId", $msg, "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\nFrom: $toaddr\n");        
    }
    
    function view_job() {
        include_once("./templates/press_view.php");
        $this->view = $msg;
    }
    
    public function get_job_by_id($jobid) {
        if (!is_numeric($jobid)) {
            return False;
        } else {
            parent::get_job_by_id($jobid);
            $query = "SELECT * FROM " . $this->tablePrefix ."press WHERE id = '$jobid'";
            $res = $this->sql->query($query);
            if (PEAR::isError($res)) {
                syslog(LOG_ERR, $res->getMessage() . " - " . $query);
            } else {
                $row = $res->fetchRow();
                $this->cutting = $row['cutting'];
                $this->bindColor = $row['bindcolor'];
                $this->bindSide = $row['bindside'];
                $this->frontPlastic = $row['frontplastic'];
                $this->frontPresentation = $row['frontpresentation'];
                $this->frontColorInk = $row['frontcolorink'];
                $this->frontPaperType = $row['frontpapertype'];
                $this->frontPaperColor = $row['frontpapercolor'];
                $this->backPlastic = $row['backplastic'];
                $this->backPresentation = $row['backpresentation'];
                $this->backBlankPage = $row['backblankpage'];
                $this->backPaperType = $row['backpapertype'];
                $this->backPaperColor = $row['backpapercolor'];
            }
        
        }
    }
    
    public function calculate_cost() {
        $priceBlackWhite = .05;
        $runningTotal = 0;
        $priceColor = .15;
        $studentOrgs = array("SIFE", "Delta Kappa", "SMB", "Praise Gathering", "NCSA");
        if($this->departmentCharge != 'Personal Copies'){ 
            if(in_array($this->departmentCharge, $studentOrgs) && $this->colorInk == 'No')
                    $runningTotal += $this->pageCount * $this->quantity * $priceBlackWhite;
            if ($this->bindSide == '11 inch') {
                if ($this->pageCount <= 250)
                    $runningTotal += 1.50 * $this->quantity;
                else
                    $runningTotal += 2 * $this->quantity;
            } else
                $runningTotal += $this->quantity;
            if ($this->colorInk == 'Yes')
                $runningTotal += $this->pageCount * $this->quantity * $priceColor;
            elseif ($this->frontColorInk == 'Yes')
                $runningTotal += .15 * $this->quantity;
            if ($this->frontPresentation == 'Yes')
                $runningTotal += .25 * $this->quantity;
            if ($this->backPresentation == 'Yes')
                $runningTotal += .25 * $this->quantity;
            

            $this->cost = $runningTotal;
        }
    }
}

?>
