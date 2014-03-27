<?php

	require_once("/var/authscripts/ncu_auth.inc");
   $sage = ncu_sage_unet_menu();
	$jobid = $_GET['jobid'];
	$query = "SELECT jobtype from unet_mc_jobid WHERE jobid = '$jobid'";
	
	$res =& $sage->query($query);
	if ( $res->numRows() == 1)
	{
		$row = $res->fetchRow();
		$jobtype = $row[0];
	}
	else
	{
		die("Unable to find job for jobid $jobid");
	}
	
   $query = "SELECT filename, randname from unet_mc_".$jobtype." WHERE jobid = '$jobid'";
	$res =& $sage->query($query);
   $row = $res->fetchRow();

   $fparts = explode(".", $row[0]);
   $fext = $fparts[count($fparts) - 1];
   $randname = $row[1];
   $filename = $row[0];
   $fpath = "/var/www/unet/copycenter/job_files/";
   $filesize = filesize($fpath . $randname);
   $mtype = mime_content_type($fpath . $randname);

   header("Pragma: public");
   header("Expires: 0");
   header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
   header("Cache-Control: public");
   header("Content-Description: File Transfer");
   header("Content-Type: $mtype");
   header("Content-Disposition: attachment; filename=\"$filename\"");
   header("Content-Transfer-Encoding: binary");
   header("Content-Length: " . $filesize);
   $file = @fopen($fpath . $randname, "rb");
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


