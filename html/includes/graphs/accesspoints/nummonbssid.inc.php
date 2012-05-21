<?php

$ap=accesspoint_by_id($id);
$device = device_by_id_cache($ap['device_id']);

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] ."/". 
safename("arubaap-$ap[name].$ap[radio_number].rrd");

$rrd_list[0]['filename'] = $rrd_filename;
$rrd_list[0]['descr'] = "MonBSSIDs";
$rrd_list[0]['ds'] = "nummonbssid";

$unit_text = "BSSIDs";

$units='';
$total_units='';
$colours='mixed';

$scale_min = "0";

$nototal = 1;

if ($rrd_list)
{
  include("includes/graphs/generic_multi_line.inc.php");
}


?>
