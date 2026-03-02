<?php
$h=file_get_contents('_tmp_soccerway.html');
$keys=['pq_graphql','flashscore.ninja','/res/_fs/build/','container.2035.css','canonical','application/ld+json','data-'];
foreach($keys as $k){
  $pos=stripos($h,$k);
  echo "KEY $k => ".($pos===false?'no':'yes')."\n";
  if($pos!==false){
    $start=max(0,$pos-200);
    echo substr($h,$start,500)."\n---\n";
  }
}
