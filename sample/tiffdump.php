<?php

require_once 'IO/TIFF.php';

$options = getopt("f:hnv");

function usage() {
    fprintf(STDERR, "Usage: php tiffdump.php -f <tiff_file> [-n] [-h] [-v]\n");
    fprintf(STDERR, "ex) php tiffdump.php -f test.tiff -hnv \n");
}

if (isset($options['f']) === false) {
    usage();
    exit(1);
}

$tifffile = $options['f'];
if (($tifffile !== "php://stdin") && (is_readable($options['f']) === false)) {
    usage();
    exit(1);
}
$tiffdata = file_get_contents($tifffile);

$tiff = new IO_TIFF();
$tiff->parse($tiffdata);

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

$tiff->dump($opts);
