<?php
include_once("/var/authscripts/ncu_auth.inc");
$msg = '
<HTML>
<HEAD>

</HEAD>
<BODY>
<table width="525px" style="border:solid 1px #b84702">
<tr width="525px">
<td colspan="2"></td>
</tr>
';
$msg .= "<tr><td><strong>Job ID</strong></td><td>$this->jobId</td></tr>\n";
$msg .= "<tr><td><strong>Submitted By</strong></td><td>".ncu_getdisplayname($this->username)."</td></tr>\n";
$msg .= "<tr><td><strong>Job Type</strong></td><td>$this->jobType</td></tr>\n";
$msg .= "<tr><td><strong>Phone Number</strong></td><td>$this->phone</td></tr>\n";
$msg .= "<tr><td><strong>Job Name</strong></td><td>$this->jobName</td></tr>\n";
$msg .= "<tr><td><strong>Charge to</strong></td><td>$this->departmentCharge</td></tr>\n";
if (isset($this->account) && $this->account != "") {
    $msg .= "<tr><td><strong>Account Number</strong></td><td>$this->account</td></tr>\n";
}
$msg .= "<tr><td><strong>File</strong></td><td><a href='download.php?jobid=$this->jobId'>$this->filename</a></td></tr>\n";
$msg .= "<tr><td><strong>Number of Pages</strong></td><td>$this->pageCount</td></tr>\n";
$msg .= "<tr><td><strong>Quantity</strong></td><td>$this->quantity</td></tr>\n";
$msg .= "<tr><td><strong>Date Due</strong></td><td>$this->dueDate</td></tr>\n";
$msg .= '<tr height="6"></tr>';
$msg .= '<tr height="2" bgcolor="#b84702"><td height="2" colspan="2"></td></tr>';
$msg .= '<tr height="6"></tr>';
$msg .= "<tr><td><h3>Delivery Mode</h3></td><td></td></tr>\n";
if ($this->transport == 'Deliver') {
    $msg .= "<tr><td><strong>Delivery</strong></td><td>To $this->departmentDeliver on rounds</td></tr>\n";
} else {
    $msg .= "<tr><td><strong>Pick-Up</strong></td><td>from Copy and Mailing Services</td></tr>\n";
}
$msg .= "<tr><td><strong>Confidential</strong></td><td>$this->confidential</td></tr>\n";
$msg .= '<tr height="6"></tr>';
$msg .= '<tr height="2" bgcolor="#b84702"><td height="2" colspan="2"></td></tr>';
$msg .= '<tr height="6"></tr>';
$msg .= "<tr><td><h3>Document Options</h3></td><td></td></tr>\n";
//$msg .= "<tr><td><strong>Document Size</strong></td><td>$this->docSize</td></tr>\n";
$msg .= "<tr><td><strong>Paper Type</strong></td><td>$this->paperType</td></tr>\n";
$msg .= '<tr height="6"></tr>';
$msg .= '<tr height="2" bgcolor="#b84702"><td height="2" colspan="2"></td></tr>';
$msg .= '<tr height="6"></tr>';
$msg .= "<tr><td><h3>Cover Options</h3></td><td></td></tr>\n";
$msg .= "<tr><td><strong>Cover in color ink</strong></td><td>$this->coverColorInk</td></tr>\n";
$msg .= "<tr><td><strong>Cover file</strong></td><td><a href='/unet/copycenter/job_files/$this->coverRandName'>$this->coverName</a></td></tr>\n";
if (isset($this->comments) && $this->comments != "") {
$msg .= '<tr height="6"></tr>';
$msg .= '<tr height="2" bgcolor="#b84702"><td height="2" colspan="2"></td></tr>';
$msg .= '<tr height="6"></tr>';
$msg .= "<tr><td><strong>Comments</strong></td><td>$this->comments</td></tr>\n";
}
$msg .= "</table>";
if ($this->departmentCharge == 'Personal Copies')
    $msg .= "<br><h2>Total Due:___________________________</h2>";
$msg .= "</body></html>";
?>
