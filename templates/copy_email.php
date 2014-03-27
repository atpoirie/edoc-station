<?php

$msg = '
<HTML>
<HEAD>

</HEAD>
<BODY>
<h2>Online Copy Center Confirmation</h2>
<table width="525px" style="border:solid 1px #b84702">
<tr width="525px">
<td colspan="2"></td>
</tr>
';
$msg .= "<tr><td><strong>Phone Number</strong></td><td>$this->phone</td></tr>\n";
$msg .= "<tr><td><strong>Job Name</strong></td><td>$this->jobName</td></tr>\n";
$msg .= "<tr><td><strong>Charge to</strong></td><td>$this->departmentCharge</td></tr>\n";
if (isset($this->account) && $this->account != "") {
    $msg .= "<tr><td><strong>Account Number</strong></td><td>$this->account</td></tr>\n";
}
$msg .= "<tr><td><strong>File</strong></td><td>$this->filename</td></tr>\n";
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
$msg .= "<tr><td><h3>Paper Selection</h3></td><td></td></tr>\n";
$msg .= "<tr><td><strong>Paper Size</strong></td><td>$this->paperSize</td></tr>\n";
$msg .= "<tr><td><strong>Paper Type</strong></td><td>$this->paperType</td></tr>\n";
$msg .= "<tr><td><strong>Paper Color</strong></td><td>$this->paperColor</td></tr>\n";
$msg .= '<tr height="6"></tr>';
$msg .= '<tr height="2" bgcolor="#b84702"><td height="2" colspan="2"></td></tr>';
$msg .= '<tr height="6"></tr>';
$msg .= "<tr><td><h3>Document Options</h3></td><td></td></tr>\n";
$msg .= "<tr><td><strong>Double Sided</strong></td><td>$this->duplex</td></tr>\n";
$msg .= "<tr><td><strong>Print in color</strong></td><td>$this->colorInk</td></tr>\n";
$msg .= "<tr><td><strong>Folding</strong></td><td>$this->folding</td></tr>\n";
$msg .= "<tr><td><strong>Stapling</strong></td><td>$this->stapling</td></tr>\n";
$msg .= "<tr><td><strong>Cutting</strong></td><td>$this->cutting</td></tr>\n";
$msg .= "<tr><td><strong>Laminate</strong></td><td>$this->laminate</td></tr>\n";
$msg .= "<tr><td><strong>UnCollate</strong></td><td>$this->collate</td></tr>\n";
$msg .= "<tr><td><strong>Hole Punch</strong></td><td>$this->holePunch</td></tr>\n";
if (isset($this->comments) && $this->comments != "") {
$msg .= '<tr height="6"></tr>';
$msg .= '<tr height="2" bgcolor="#b84702"><td height="2" colspan="2"></td></tr>';
$msg .= '<tr height="6"></tr>';
$msg .= "<tr><td><strong>Comments</strong></td><td>$this->comments</td></tr>\n";
}
$msg .= "</table></body></html>";

?>
