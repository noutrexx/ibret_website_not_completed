<?php
$h=file_get_contents('https://us.soccerway.com/national/turkey/super-lig/');
file_put_contents('_tmp_soccerway.html',$h);
$patterns=['__NEXT_DATA__','window.__','graphql','lsid','eventId','tournament','stage','league'];
foreach($patterns as $p){echo $p.':'.(stripos($h,$p)!==false?'yes':'no')."\n";}
