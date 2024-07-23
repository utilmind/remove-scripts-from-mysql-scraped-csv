<?php
require_once(__DIR__.'/remove_scripts.php');

if ($data = file_get_contents('processed.csv')) {
  $splitter = '|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||';
  $data = explode($splitter, $data);

  @mkdir('prc', 0777, 1);

  $is_first = 1;
  $cnt = 0;
  $cur = 0;
  $part = false;
  foreach ($data as $key => $val) {

    if ($cnt == 169) { // 169 = 5 parts, 69 = 12 parts, 39 = 21 parts (files less than 1 Mb)
      ++$cur;
      write_file('prc/part_'.$cur.'.csv', $part);
      $cnt = 0;
      $part = '';
    }

    if (!$is_first) $val = $splitter.$val;
    else $is_first = false;
    $part.= $val;

    ++$cnt;
  }
  ++$cur;
  write_file('prc/part_'.$cur.'.csv', $part);
}
