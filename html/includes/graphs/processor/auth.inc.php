<?php

$sql = mysql_query("SELECT * FROM `processors` where `processor_id` = '".mres($_GET['id'])."'");
$proc = mysql_fetch_assoc($sql);

if(is_numeric($proc['device_id']) && device_permitted($proc['device_id'])) 
{
  $device = device_by_id_cache($proc['device_id']);
  $rrd_filename  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("processor-" . $proc['processor_type'] . "-" . $proc['processor_index'] . ".rrd");
  $title  = generatedevicelink($device);
  $title .= " :: Processor :: " . htmlentities($proc['processor_descr']);
  $auth = TRUE;
}

?>
