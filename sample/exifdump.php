<?php

require_once 'IO/Exif.php';

$options = getopt("f:hnv");

function usage() {
    fprintf(STDERR, "Usage: php exifdump.php -f <exif_file> [-n] [-h] [-v]\n");
    fprintf(STDERR, "ex) php exifdump.php -f test.exif -hnv \n");
}

if (isset($options['f']) === false) {
    usage();
    exit(1);
}

$exiffile = $options['f'];
if (($exiffile !== "php://stdin") && (is_readable($options['f']) === false)) {
    usage();
    exit(1);
}
$exifdata = file_get_contents($exiffile);

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
