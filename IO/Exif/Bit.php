<?php

/*
 * 2016/5/22- (c) yoya@awm.jp
 */

require_once 'IO/Bit.php';

class IO_Exif_Bit extends IO_Bit {
    var $byteOrder = null;
    function setByteOrder($byteOrder) {
        $this->byteOrder = $byteOrder;
    }
    function getByteOffset() {
        list($byteOffset, $bitOffset) = $this->getOffset();
        return $byteOffset;
    }
    function setByteOffset($offset) {
        $this->setOffset($offset, 0);
    }
    // type:1
    function getBYTE() {
        return $this->getUI8();
    }
    // type:2
    function getASCII($len) {
        return $this->getData($len);
    }
    // type:3
    function getSHORT() {
        if ($this->byteOrder === 1) {
            return $this->getUI16BE();
        }
        return $this->getUI16LE();
    }
    // type:4
    function getLONG() {
        if ($this->byteOrder === 1) {
            return $this->getUI32BE();
        }
        return $this->getUI32LE();
    }
    // type:5
    function getRATIONAL($len) {
        if ($this->byteOrder === 1) {
            $numer = $this->getUI32BE();
            $denom = $this->getUI32BE();
        } else {
            $numer = $this->getUI32LE();
            $denom = $this->getUI32LE();
        }
        return [$numer, $denom];
    }
    // type:7
    function getUNDEFINED($len) {
        return $this->getData($len);
    }
    // type:9
    function getSLONG() {
        if ($this->byteOrder === 1) {
            return $this->getSI32BE();
        }
        return $this->getSI32LE();
    }
    // type:10
    function getSRATIONAL($len) {
        if ($this->byteOrder === 1) {
            $numer = $this->getSI32BE();
            $denom = $this->getSI32BE();
        } else {
            $numer = $this->getSI32LE();
            $denom = $this->getSI32LE();
        }
        return [$numer, $denom];
    }
}
