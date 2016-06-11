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
    function setByteOffset($offset, $writeMode = false) {
        if ($writeMode === false) { // read mode
            $this->setOffset($offset, 0);
        } else {
            $byteOffset = $this->getByteOffset();
            if ($offset < $byteOffset) {
                throw new Exception("setByteOffset: offset:$offset < byteOffset:$byteOffset");
            }
            $dataSize = strlen($this->_data);
            if ($dataSize < $offset) {
                $this->setOffset($dataSize, 0);
                $this->putData(str_repeat("\0", $offset - $dataSize));
            }
        }
    }
    /*
     * get function
     */
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
    function getRATIONAL() {
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
    function getSRATIONAL() {
        if ($this->byteOrder === 1) {
            $numer = $this->getSI32BE();
            $denom = $this->getSI32BE();
        } else {
            $numer = $this->getSI32LE();
            $denom = $this->getSI32LE();
        }
        return [$numer, $denom];
    }
    /*
     * put function
     */
    // type:1
    function putBYTE($v) {
        $this->putUI8($v);
    }
    // type:2
    function putASCII($v, $len) {
        $this->putData($v, $len);
    }
    // type:3
    function putSHORT($v) {
        if ($this->byteOrder === 1) {
            $this->putUI16BE($v);
        } else {
            $this->putUI16LE($v);
        }
    }
    // type:4
    function putLONG($v) {
        if ($this->byteOrder === 1) {
            $this->putUI32BE($v);
        } else {
            $this->putUI32LE($v);
        }
    }
    // type:5
    function putRATIONAL($v) {
        if ($this->byteOrder === 1) {
            $this->putUI32BE($v[0]);
            $this->putUI32BE($v[1]);
        } else {
            $this->putUI32LE($v[0]);
            $this->putUI32LE($v[1]);
        }
    }
    // type:7
    function putUNDEFINED($v, $len) {
        $this->putData($v, $len);
    }
    // type:9
    function putSLONG($v) {
        if ($this->byteOrder === 1) {
            $this->putSI32BE($v);
        } else {
            $this->putSI32LE($v);
        }
    }
    // type:10
    function putSRATIONAL($v) {
        if ($this->byteOrder === 1) {
            $this->putSI32BE($v[0]);
            $this->putSI32BE($v[1]);
        } else {
            $this->putSI32LE($v[0]);
            $this->putSI32LE($v[1]);
        }
    }
    /*
     * etc
     */
    function alignNBytes($nBytes) {
        $byteOffset = $this->getByteOffset();
        $n = $byteOffset % $nBytes;
        if ($n > 0) {
            $this->putData(str_repeat("\0", $nBytes - $n));
        }
    }
}
