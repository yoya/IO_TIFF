<?php

require_once 'IO/TIFF.php';

$options = getopt("f:hnvO");

function usage() {
    fprintf(STDERR, "Usage: php tiffdump.php -f <tiff_file> [-n] [-h] [-v] [-O] [-U] \n");
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

if (substr($tiffdata, 0, 2) === "\xff\xd8") { // JPEG - SOI(Start of Image)
    echo "JPEG Format:".PHP_EOL;
    $cur = 2; // skip SOI marker
    $found = false; // search APP1 - Exif
    while (! $found) {
        $marker1 = ord($tiffdata[$cur]);
        $marker2 = ord($tiffdata[$cur+1]);
        if ($marker1 !== 0xFF) {
            echo "Broken chunk marker(".$marker1.") offset:$cur\n";
            exit (1);
        }
        switch($marker2) {
        case 0xE1: // APP1
            if (substr($tiffdata, $cur+4, 6) === "Exif\0\0") {
                $found = true;
            }
            break;
        case 0xDA: // SOS
        case 0xD9: // EOI (End of Image)
            echo "Not found APP1 chunk.\n";
            exit (1);
            break;
        }
        $len = 0x100 * ord($tiffdata[$cur+2]) + ord($tiffdata[$cur+3]);
        if ($found) {
            $tiffdata = substr($tiffdata, $cur + 4, $len - 2/*marker field*/);
        }
        $cur += 2/*length field*/ + $len;
    }
}

if (substr($tiffdata, 0, 6) === "Exif\0\0") {
    echo "Exif Format:".PHP_EOL;
    $tiffdata = substr($tiffdata, 6);
}

$tiff = new IO_TIFF();
$tiff->parse($tiffdata);

$opts = array();

$opts['hexdump'] = isset($options['h']);
$opts['name'] = isset($options['n']);
$opts['verbose'] = isset($options['v']);
$opts['omit'] = (! isset($options['O']));
$opts['useless'] = (! isset($options['U'])); // hexdump useless space

$tiff->dump($opts);
