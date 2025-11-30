# remove-scripts-from-mysql-scraped-csv

The script used to remove all <script>'s from mySQL scraped database. Works both for live mySQL db and/or CSV.

Second script ("split_csv.php") is just quick tool for internal use to split large CSV's to some smaller parts.

No demo available. But this PHP will definitely remove all <script>'s from the CSV (filename should be specified as command-line argument) or mySQL db (simple configuration required in the PHP's header).
