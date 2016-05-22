<?php

require_once 'IO/Exif.php';

$options = getopt("f:hnv");

if ((isset($options['f']) === false) || (is_readable($options['f']) === false)) {
    fprintf(STDERR, "Usage: php exifdump.php -f <exif_file> [-n] [-h] [-v]\n");
    fprintf(STDERR, "ex) php exifdump.php -f test.exif -hnv \n");
    exit(1);
}

$exifdata = file_get_contents($options['f']);

$exif = new IO_Exif();
$exif->parse($exifdata);

$opts = array();
if (isset($options['h'])) {
    $opts['hexdump'] = true;
}
if (isset($options['n'])) {
    $opts['name'] = true;
}
if (isset($options['v'])) {
    $opts['verbose'] = true;
}

$exif->dump($opts);
