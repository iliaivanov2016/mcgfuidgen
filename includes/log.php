<?php
function mcgfuidgen_log($msg){
	$log = MCGFUIDGEN_LOG_FILE;
 if (@filesize($log) > 100*1024*1024)
  @unlink($log);
 $cur_retry = 1;
 while ($cur_retry <= 100) {
  @$fh = fopen($log,"a");
  if (!$fh) {
   $cur_retry++;
   continue;
  }
  $prefix = @date("d.m.Y H:i:s")."\t".((count($_POST) > 0)?$_SERVER["REQUEST_URI"].http_build_query($_POST) : $_SERVER["REQUEST_URI"])."\n";
  @fwrite($fh,$prefix.$msg."\n");
  @fclose($fh);
  break;
 } // retry;
}