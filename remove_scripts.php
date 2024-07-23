<?php
//========================= CONFIGURATION =============================

$db_host  = 'localhost';
$db_user  = 'username';
$db_pass  = 'pass';
$db_name  = 'dbname';
$db_table = false; // if not specified, it will be retrieved from $_GET['table'] parameter.
$db_field = false; // if not specifeid, it will check out and process ALL db-fields (including primary index). Also we suppose that primary index is "id", if this field specified (when we can't determinate the first field on the fly.).

$stop_delimiter = '|'; // delimiter between CVS cells. To detect the end of the line, if <script> ends unclosed.

//=====================================================================

function strip_tag_by_name($t, $tag, $stop_delimiter = false) { // delimiter is stopping-character, alternative to closing </tag>. (Used if the content finished before closing the tag.)
  if ($stop_delimiter)
    $stop_delimiter = '|(?='.preg_quote($stop_delimiter, '/').')';

  return preg_replace("/<$tag(.*?)(<\/$tag>$stop_delimiter|$)/is", '', $t);
}

function process_db_table($db_table, $db_field = false, $stop_delimiter = false) {
  $indexes = false;
  $updates = false;

  if ($r = mysql_query('SELECT '.($db_field ? 'id,'.$db_field : '*').' FROM '.$db_table)) {
    while ($row = mysql_fetch_assoc($r)) {
      $index_field = array_keys($row)[0];
      $index_value = array_values($row)[0];
      foreach ($row as $field => $data) {
        if (stripos($data, '<script') !== false) {
          $data = mysql_real_escape_string(strip_tag_by_name($data, 'script', $stop_delimiter));
          $indexes[] = $index_value;
          $updates[] = "UPDATE $db_table SET $field=\"$data\" WHERE $index_field=$index_value;";
        }
      }
    }
    mysql_free_result($r);
  }

  if (is_array($updates))
    foreach ($updates as $key => $val) {
      print "Updating index #$indexes[$key]...<br />\n";
      mysql_query($val);

      // debug
      print mysql_error();
      // print "$val<br />\n";
    }
}

function write_file($fn, $s, $mode = 'w') { // set mode to 'a' for append
  if ($f = fopen($fn, $mode)) { // remember about directory permissions in PHP's safe mode!
    flock($f, LOCK_EX);
    $r = fwrite($f, $s);
    flock($f, LOCK_UN);
    fclose($f);
    @chmod($fn, 0777);
    return $r == strlen($s) ? $r : false;
  }
  return false;
}

function process_csv_file($src_file, $tgt_file) {
  if ($data = file_get_contents($src_file)) {
    $data = strip_tag_by_name($data, 'script', '|');
    write_file($tgt_file, $data);
  }
}



// If command line arguments is set -- processing CVS file.
if (isset($argv[1]) && isset($argv[2]) &&
    file_exists($argv[1])) {
  process_csv_file($argv[1], $argv[2]);
  exit;
}

if ($db_table || (isset($_GET['table']) && ($db_table = $_GET['table']))) {

  if (!$db_link = mysql_connect($db_host, $db_user, $db_pass)) {
    echo 'Can\'t connect to db. Please set up configuration.';
    exit;
  }
  $mtime = microtime(1);
  mysql_select_db($db_name); // open
  process_db_table($db_table, $db_field, $stop_delimiter); // process
  mysql_close($db_link);     // close

  echo 'Done in '.number_format((microtime(1) - $mtime), 2).' seconds.';
}else {
  echo 'Please provide "table" parameter.';
}
