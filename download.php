<?php

require_once "/var/authscripts/ncu_auth.inc";
require_once "./include/jobclass.php";

ncu_forcesecure();
ncu_forceauth();
$uname = ncu_getusername();

if (isset($_GET['jobid'])) {
    $aJob = new Job($uname);
    $aJob->get_job_by_id($_GET['jobid']);
    if ($aJob->username == $uname || $aJob->isAdmin)
    {
        $filesize = filesize($aJob->uploadDir . "/" . $aJob->randname);
        $mtype = mime_content_type($aJob->uploadDir . "/" . $aJob->randname);
        header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-Type: $mtype");
	header("Content-Disposition: attachment; filename=\"$aJob->filename\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: " . $filesize);
	$file = @fopen($aJob->uploadDir . "/" . $aJob->randname, "rb");
	if ($file) 
	{
		while (!feof($file))
		{
			print(fread($file, 1024*8));
			flush();
			if (connection_status()!=0)
			{
				@fclose($file);
				die();
			}
		}
		@fclose($file);	
	}
        
    }
}