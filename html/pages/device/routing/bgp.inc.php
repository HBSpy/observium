<div style='padding: 10px; height: 20px; clear: both; display: block;'>
  <div style='float: left; font-size: 22px; font-weight: bold;'>Local AS : <?php echo($device['bgpLocalAs']); ?></div>
</div>

<?php
print_optionbar_start();

  echo("<span style='font-weight: bold;'>BGP</span> &#187; ");

  if (!isset($_GET['optb'])) { echo("<span class='pagemenu-selected'>"); }
  echo("<a href='/device/" . $device['device_id'] . "/routing/bgp/'>Basic</a>");
  if (!isset($_GET['optb'])) { echo("</span>"); }

  echo(" | ");

  if ($_GET['optb'] == "updates") { echo("<span class='pagemenu-selected'>"); }
  echo("<a href='/device/" . $device['device_id'] . "/routing/bgp/updates/'>Updates</a>");
  if ($_GET['optb'] == "updates") { echo("</span>"); }

  echo(" | Prefixes: ");

  if ($_GET['optb'] == "prefixes_ipv4unicast") { echo("<span class='pagemenu-selected'>"); }
  echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/routing/bgp/prefixes_ipv4unicast/'>IPv4</a>");
  if ($_GET['optb'] == "prefixes_ipv4unicast") { echo("</span>"); }

  echo(" | ");

  if ($_GET['optb'] == "prefixes_vpnv4unicast") { echo("<span class='pagemenu-selected'>"); }
  echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/routing/bgp/prefixes_vpnv4unicast/'>VPNv4</a>");
  if ($_GET['optb'] == "prefixes_vpnv4unicast") { echo("</span>"); }

  echo(" | ");

  if ($_GET['optb'] == "prefixes_ipv6unicast") { echo("<span class='pagemenu-selected'>"); }
  echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/routing/bgp/prefixes_ipv6unicast/'>IPv6</a>");
  if ($_GET['optb'] == "prefixes_ipv6unicast") { echo("</span>"); }

  echo(" | Traffic: ");

  if ($_GET['optb'] == "macaccounting_bits") { echo("<span class='pagemenu-selected'>"); }
  echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/routing/bgp/macaccounting_bits/'>Bits</a>");
  if ($_GET['optb'] == "macaccounting_bits") { echo("</span>"); }
  echo(" | ");
  if ($_GET['optb'] == "macaccounting_pkts") { echo("<span class='pagemenu-selected'>"); }
  echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/routing/bgp/macaccounting_pkts/'>Packets</a>");
  if ($_GET['optb'] == "macaccounting_pkts") { echo("</span>"); }


print_optionbar_end();

echo('<table border="0" cellspacing="0" cellpadding="5" width="100%">');
echo('<tr style="height: 30px"><td width=1></td><th></th><th>Peer address</th><th>Type</th><th>Remote AS</th><th>State</th><th>Uptime</th></tr>');

$i = "1";
$peer_query = mysql_query("select * from bgpPeers WHERE device_id = '".$device['device_id']."' ORDER BY bgpPeerRemoteAs, bgpPeerIdentifier");

while ($peer = mysql_fetch_assoc($peer_query))
{
  $has_macaccounting = mysql_result(mysql_query("SELECT COUNT(*) FROM `ipv4_mac` AS I, mac_accounting AS M WHERE I.ipv4_address = '".$peer['bgpPeerIdentifier']."' AND M.mac = I.mac_address"),0);
  unset($bg_image);
  if (!is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }


  unset ($alert, $bg_image);

  if (!is_integer($i/2)) { $bg_colour = $list_colour_b; } else { $bg_colour = $list_colour_a; }
  if ($peer['bgpPeerState'] == "established") { $col = "green"; } else { $col = "red"; $peer['alert']=1; }
  if ($peer['bgpPeerAdminStatus'] == "start" || $peer['bgpPeerAdminStatus'] == "running") { $admin_col = "green"; } else { $admin_col = "gray"; }
  if ($peer['bgpPeerAdminStatus'] == "stop") { $peer['alert']=0; $peer['disabled']=1; }

  if ($peer['bgpPeerRemoteAs'] == $device['bgpLocalAs']) { $peer_type = "<span style='color: #00f;'>iBGP</span>"; } else { $peer_type = "<span style='color: #0a0;'>eBGP</span>"; }

  $query = "SELECT * FROM ipv4_addresses AS A, ports AS I, devices AS D WHERE ";
  $query .= "(A.ipv4_address = '".$peer['bgpPeerIdentifier']."' AND I.interface_id = A.interface_id)";
  $query .= " AND D.device_id = I.device_id";
  $ipv4_host = mysql_fetch_assoc(mysql_query($query));

  $query = "SELECT * FROM ipv6_addresses AS A, ports AS I, devices AS D WHERE ";
  $query .= "(A.ipv6_address = '".$peer['bgpPeerIdentifier']."' AND I.interface_id = A.interface_id)";
  $query .= " AND D.device_id = I.device_id";
  $ipv6_host = mysql_fetch_assoc(mysql_query($query));

  if ($ipv4_host)
  {
    $peerhost = $ipv4_host;
  }
  elseif ($ipv6_host)
  {
    $peerhost = $ipv6_host;
  }
  else
  {
    unset($peerhost);
  }

  if ($peerhost)
  {
    $peername = generate_device_link($peerhost);
  }
  else
  {
    #$peername = gethostbyaddr($peer['bgpPeerIdentifier']); ## FFffuuu DNS
    if ($peername == $peer['bgpPeerIdentifier'])
    {
      unset($peername);
    } else {
      $peername = "<i>".$peername."<i>";
    }
  }

  $af_query = mysql_query("SELECT * FROM `bgpPeers_cbgp` WHERE `device_id` = '".$device['device_id']."' AND bgpPeerIdentifier = '".$peer['bgpPeerIdentifier']."'");
  unset($peer_af);
  unset($sep);
  while ($afisafi = mysql_fetch_assoc($af_query))
  {
    $afi = $afisafi['afi'];
    $safi = $afisafi['safi'];
    $this_afisafi = $afi.$safi;
    #$peer['afi'] .= $sep . $config['afi'][$afi][$safi];          ##### CLEAN ME UP, I AM MESSY AND I SMELL OF CHEESE! FIXME!
    $peer['afi'] .= $sep . $afi .".".$safi;
    $sep = "<br />";
    $peer['afisafi'][$this_afisafi] = 1; ## Build a list of valid AFI/SAFI for this peer


  }

  unset($sep);

  $graph_type       = "bgp_updates";
  $peer_daily_url   = "graph.php?id=" . $peer['bgpPeer_id'] . "&amp;type=" . $graph_type . "&amp;from=$day&amp;to=$now&amp;width=500&amp;height=150";
  $peeraddresslink  = "<span class=list-large><a href='device/" . $peer['device_id'] . "/routing/bgp/updates/' onmouseover=\"return overlib('<img src=\'$peer_daily_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">" . $peer['bgpPeerIdentifier'] . "</a></span>";

  echo('<tr bgcolor="'.$bg_colour.'"' . ($peer['alert'] ? ' bordercolor="#cc0000"' : '') .
                                        ($peer['disabled'] ? ' bordercolor="#cccccc"' : '') . ">
  ");


  echo("   <td width=20><span class=list-large>".$i."</span></td>
           <td>" . $peeraddresslink . "<br />".generate_device_link($peer, shorthost($peer['hostname']), 'bgp/')."</td>
	     <td>$peer_type</td>
           <td style='font-size: 10px; font-weight: bold; line-height: 10px;'>" . (isset($peer['afi']) ? $peer['afi'] : '') . "</td>
           <td><strong>AS" . $peer['bgpPeerRemoteAs'] . "</strong><br />" . $peer['astext'] . "</td>
           <td><strong><span style='color: $admin_col;'>" . $peer['bgpPeerAdminStatus'] . "<span><br /><span style='color: $col;'>" . $peer['bgpPeerState'] . "</span></strong></td>
           <td>" .formatUptime($peer['bgpPeerFsmEstablishedTime']). "<br />
               Updates <img src='images/16/arrow_down.png' align=absmiddle> " . $peer['bgpPeerInUpdates'] . "
                       <img src='images/16/arrow_up.png' align=absmiddle> " . $peer['bgpPeerOutUpdates'] . "</td>
          </tr>");


    unset($invalid);
    switch ($_GET['optb'])
    {
      case 'prefixes_ipv4unicast':
      case 'prefixes_ipv4multicast':
      case 'prefixes_ipv4vpn':
      case 'prefixes_ipv6unicast':
      case 'prefixes_ipv6multicast':
        list(,$afisafi) = explode("_", $_GET['optb']);
        if (isset($peer['afisafi'][$afisafi])) { $peer['graph'] = 1; }
      case 'updates':
        $graph_array['type']   = "bgp_" . $_GET['optb'];
        $graph_array['id']     = $peer['bgpPeer_id'];
    }

    switch ($_GET['optb'])
    {
      case 'macaccounting_bits':
      case 'macaccounting_pkts':
        $acc = mysql_fetch_assoc(mysql_query("SELECT * FROM `ipv4_mac` AS I, `mac_accounting` AS M, `ports` AS P, `devices` AS D WHERE I.ipv4_address = '".$peer['bgpPeerIdentifier']."' AND M.mac = I.mac_address AND P.interface_id = M.interface_id AND D.device_id = P.device_id"));
        $database = $config['rrd_dir'] . "/" . $device['hostname'] . "/cip-" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd";
        if (is_array($acc) && is_file($database))
        {
          $peer['graph']       = 1;
          $graph_array['id']   = $acc['ma_id'];
          $graph_array['type'] = $_GET['optb'];
        }
    }

    if ($_GET['optb'] == 'updates') { $peer['graph'] = 1; }

    if($peer['graph'])
    {
        $graph_array['height'] = "100";
        $graph_array['width']  = "216";
        $graph_array['to']     = $now;
        echo('<tr bgcolor="'.$bg_colour.'"' . ($bg_image ? ' background="'.$bg_image.'"' : '') . '"><td colspan="7">');
        include("includes/print-quadgraphs.inc.php");
        echo("</td></tr>");
    }

    $i++;

  unset($valid_afi_safi);
}
?>

</table>
