<?php

/*
 * 2016/5/22- (c) yoya@awm.jp
 */

require_once 'IO/Exif/Bit.php';
require_once 'IO/Exif/Tag.php';
require_once 'IO/Exif/IFD.php';

class IO_Exif {
    var $exifData = null;
    var $byteOrder = null; // 1:Big Endian(MM), 2:LittleEndian(II)
    var $IFDs = null;
    const IFD_OFFSET_BASE = 6;
    function parse($exifData) {
        $reader = new IO_Bit();
        $reader->input($exifData);
        $this->exifData  = $exifData;
        $bit = new IO_Exif_Bit();
        $bit->input($exifData);
        // Head Binary Check
        $head6 = $bit->getData(6);
        if ($head6 != "Exif\0\0") {
            throw new Exception("Unknown head 6 byte: $head6");
            return false;
        }
        $byteOrderId = $bit->getData(2);
        switch ($byteOrderId) {
        case "MM": // Big Endian
            $this->byteOrder = 1;
            break;
        case "II": // Little Endian
            $this->byteOrder = 2;
            break;
        default:
            throw new Exception("Unknown byte order: $byteOrderId");
        }
        $bit->setByteOrder($this->byteOrder);
        $tiffVersion = $bit->getSHORT();
        if ($tiffVersion !== 0x002A) {
            throw new Exception("Unknown TIFF version:0x".dechex($tiffVersion));
        }
        $this->IFDs = array();
        $IFD0thOffset = $bit->getLONG();
        $ifdTable = IO_Exif_IFD::Factory($bit, self::IFD_OFFSET_BASE + $IFD0thOffset, "0th");
        $this->IFDs += $ifdTable;
        $IFD1thOffset = $bit->getLONG();
        if ($IFD1thOffset > 0) {
            $ifdTable = IO_Exif_IFD::Factory($bit, self::IFD_OFFSET_BASE + $IFD1thOffset, "1th");
            $this->IFDs += $ifdTable;
        }
        IO_Exif_IFD::sortIFDsByBaseOffset($this->IFDs);
    }
    function build() {
        ;
    }
    function dump($opts = array()) {
        foreach ($this->IFDs as $ifdName => $offsetTable) {
            echo "IFD:$ifdName".PHP_EOL;
            $opts += ['indent' => 1];
            $offsetTable->dump($opts);
        }
    }
}





