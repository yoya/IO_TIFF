<?php

/*
 * 2016/5/22- (c) yoya@awm.jp
 */

require_once 'IO/TIFF/Bit.php';
require_once 'IO/TIFF/Tag.php';
require_once 'IO/TIFF/IFD.php';

class IO_TIFF {
    var $tiffData = null;
    var $byteOrder = null; // 1:Big Endian(MM), 2:LittleEndian(II)
    var $tiffVersion = null;
    var $IFDs = null;
    var $IFDRemoveList = array();
    function parse($tiffData) {
        $this->tiffData  = $tiffData;
        $bit = new IO_TIFF_Bit();
        // Head Binary Check
        $head2 = substr($tiffData, 0, 2);
        $head6 = substr($tiffData, 0, 6);
        if ($head2 === "II" || $head2 === "MM") { // TIFF format
            $bit->input($tiffData);
        } else if ($head6 === "Exif\0\0") { // Exif format
            $this->$tiffData = $tiffData = substr($tiffData, 6);
            $bit->input($tiffData);
        } else if ($head2 === "\xff\xd8") { // JPEG format
            $jpegBit = new IO_Bit();
            $jpegBit->input($tiffData);
            $jpegBit->setOffset(2, 0); // skip SOI
            $found = false;
            while ($jpegBit->getUI8() == 0xff) { // chunk marker
                $marker2 = $jpegBit->getUI8();
                $len = $jpegBit->getUI16BE();
                if ($marker2 === 0xe1) { // APP1
                    if ($jpegBit->getData(6) === "Exif\0\0") {
                        $found = true;
                        break;
                    }
                }
                $jpegBit->incrementOffset($len - 2, 0);
            }
            list($offset, $dummy) = $jpegBit->getOffset();
            if ($found === false) {
                throw new Exception("Wrong JPEG format. offset: $offset");
            }
            $bit->input(substr($tiffData, $offset, $len - 2));
        } else {
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
        $this->tiffVersion = $tiffVersion;
        $this->IFDs = array();
        $IFD0thOffset = $bit->getLONG();
        $ifdTable = IO_TIFF_IFD::Factory($bit, $IFD0thOffset, "0th");
        $this->IFDs += $ifdTable;
        $IFD1thOffset = $bit->getLONG();
        if ($IFD1thOffset > 0) {
            $ifdTable = IO_TIFF_IFD::Factory($bit, $IFD1thOffset, "1th");
            $this->IFDs += $ifdTable;
        }
        IO_TIFF_IFD::sortIFDsByBaseOffset($this->IFDs);
    }
    function build() {
        $bit = new IO_TIFF_Bit();
        $bit->putData("Exif\0\0");
        $bit->setByteOrder($this->byteOrder);
        switch ($this->byteOrder) {
        case 1: 
            $byteOrderId = "MM";
            break;
        case 2: // II
            $byteOrderId = "II";
            break;
        default:
            throw new Exception("Unknown byte order: $byteOrderId");
        }
        $bit->putData($byteOrderId);
        $bit->putSHORT(0x002A); // TIFF version
        $bit->putLONG(8);
        $rebuild = false;
        foreach ($this->IFDs as $ifd)  {
            if (($ifd->modified === true) ||
                ($bit->getByteOffset() < $ifd->baseOffset)) {
                // 変更のあるタグがあった場合、オフセットが後ろに続かない場合
                // その後ろは全部バイナリを再構築する
                $rebuild = true;
            }
            if ($rebuild === false) {
                $baseAndExtendLength = ($ifd->extendOffset + $ifd->extendSize) - $ifd->baseOffset;
                $baseAndExtendData = substr($this->tiffData, $ifd->baseOffset, $baseAndExtendLength);
                $bit->setByteOffset($ifd->baseOffset, true);
                $bit->putData($baseAndExtendData);
            } else {
                $ifd->renumberTagTableOffset($baseOffset);
                $ifd->build($bit);
            }
            $bit->alignNBytes(2);
        }
        return $bit->output();
    }
    function dump($opts = array()) {
        if (! empty($opts['hexdump'])) {
            $bitin = new IO_Bit();
            $bitin->input($this->tiffData);
        }
        echo "ByteOrder:";
        if ($this->byteOrder == 1) {
            echo "MM:(BigEndian)\n";
        } else {
            echo "II(LittleEndian)\n";
        }
        printf("TIFFVersion:0x%04X\n", $this->tiffVersion);
        if (! empty($opts['hexdump'])) {
            $bitin->hexdump(0, 8);
            $nextOffset = 8;
        }
        foreach ($this->IFDs as $ifdName => $offsetTable) {
            echo "IFD:$ifdName".PHP_EOL;
            $opts += ['indent' => 1];
            $offsetTable->dump($opts);
            if (! empty($opts['hexdump'])) {
                $baseOffset = $offsetTable->baseOffset;
                if ($nextOffset < $baseOffset) {
                    echo "(useless scape)".PHP_EOL;
                    $bitin->hexdump($nextOffset);
                }
                $nextOffset = $offsetTable->extendOffset + $offsetTable->extendSize;
                $byteLength = $nextOffset - $offsetTable->baseOffset;
                $bitin->hexdump($offsetTable->baseOffset, $byteLength);
            }
        }
        if (! empty($opts['hexdump'])) {
            $tiffDataSize = strlen($this->tiffData);
            if ($nextOffset < $tiffDataSize) {
                echo "(useless scape)".PHP_EOL;
                $bitin->hexdump($nextOffset, $tiffDataSize - $nextOffset);
            }
        }
    }
    function addIFDRemoveList(string $ifdName) {
        assert(is_string($ifdName));
        $IFDRemoveList [$ifdName] = true;
    }
}
