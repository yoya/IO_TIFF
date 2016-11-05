<?php

/*
 * 2016/5/22- (c) yoya@awm.jp
 */

require_once 'IO/TIFF/Bit.php';
require_once 'IO/TIFF/Tag.php';

class IO_TIFF_IFD {
    var $ifdName = null;
    const IFD_OFFSET_BASE = 6;
    var $baseOffset = null;
    var $baseSize = null;
    var $extendOffset = null;
    var $extendSize = null;
    var $tagTable = null;
    var $modified = false;
    static function Factory($bit, $baseOffset, $ifdName) {
        $ifd = new IO_TIFF_IFD();
        $ifd->ifdName = $ifdName;
        $additionalIFD = $ifd->makeTagTable($bit, $baseOffset);
        return [$ifdName => $ifd] +  $additionalIFD;
    }
    static function getIFDNameTable() {
        static $IFDNameTable =
            [
             0x8825 => 'GPSInfo',
             0x8769 => 'Exif',
             0xA005 => 'Interoperability',
             ];
        return $IFDNameTable;
    }
    static function baseOffsetComp($ifd1, $ifd2) {
        return ($ifd1->baseOffset > $ifd2->baseOffset)?1:-1;
    }
    static function sortIFDsByBaseOffset(&$IFDs) {
        uasort($IFDs, "self::baseOffsetComp");
    }
    function makeTagTable($bit, $baseOffset) {
        $this->baseOffset = $baseOffset;
        $bit->setByteOffset($baseOffset);
        $nTags = $bit->getSHORT();
        $tagTable = array();
        $IFDNameTable = self::getIFDNameTable();
        $elementSizeTable = IO_TIFF_Tag::getElementSizetable();
        for ($i = 0 ; $i < $nTags ; $i++) {
            $tagNo    = $bit->getSHORT();
            $tagType  = $bit->getSHORT();
            $tagCount = $bit->getLONG();

            $tagOffset = null;
            $tagData = null;
            $dataSize = IO_TIFF_Tag::getDataSize($tagType, $tagCount);
            // echo "tag: $tagNo $tagType $tagCount\n";
            if ($dataSize <= 4) {
                if (isset($IFDNameTable[$tagNo]) === true) {
                    $tagOffset = $bit->getLONG();
                } else {
                    $tagData = substr($bit->getData(4), 0, $dataSize);
                }
            } else {
                $tagOffset = $bit->getLONG();
                // echo "{$this->ifdName}: $tagOffset : $dataSize : " . ($tagOffset + $dataSize) . "\n";
                $eoff  = self::IFD_OFFSET_BASE + $tagOffset;
                $oldOffset = $bit->getByteOffset();
                $bit->setByteOffset($eoff);
                $tagData = $bit->getData($dataSize);
                $bit->setByteOffset($oldOffset);
                //
                if (($this->extendOffset === null) || ($eoff < $this->extendOffset)) {
                    $this->extendOffset = $eoff;
                }
                $esize = $eoff + $dataSize - $this->extendOffset;
                if (($this->extendSize === null) || ($this->extendSize < $esize)) {
                    $this->extendSize = $esize;
                }
            }
            $tagTable[$tagNo] = IO_TIFF_Tag::Factory($tagType, $tagCount, $tagOffset, $tagData, $bit->getByteOrder());
        }
        $nextOffset = $bit->getByteOffset(); // offset save
        $ifdList = array();
        foreach ($IFDNameTable as $tagNo => $tagName) {
            if (! empty($tagTable[$tagNo])) {
                // echo "XXX: $tagName\n";
                $tag = $tagTable[$tagNo];
                if ($tag->offset > 0) {
                    $ifdList += IO_TIFF_IFD::Factory($bit, self::IFD_OFFSET_BASE + $tag->offset, $tagName);
                }
            }
        }
        $bit->setByteOffset($nextOffset); // offset restore
        $this->baseSize = $nextOffset - $baseOffset;
        $this->tagTable = $tagTable;
        return $ifdList;
    }
    function build($bit) {
        $bit->alignNBytes(2);
        $baseOffset = $bit->getByteOffset();
        $nTags = count($this->tagTable);
        $bit->putSHORT($nTags);
        foreach ($this->tagTable as $offsetEntry) {
            ;
        }
        throw new Exception("Not implemented yet!");
    }
    function dump($opts) {
        $indent = $opts['indent'];
        $indentSpace = str_repeat(" ", $indent * 4);
        $indentSpace2 = str_repeat(" ", $indent * 4 * 2);
        echo $indentSpace."BaseOffset:".$this->baseOffset." ";
        echo "BaseSize:".$this->baseSize.PHP_EOL;
        echo $indentSpace."ExtendOffset:".$this->extendOffset." ";
        echo "ExtendSize:".$this->extendSize.PHP_EOL;
        echo $indentSpace."TagTable:(count=".count($this->tagTable).")".PHP_EOL;
        foreach ($this->tagTable as $tagId => $tag) {
            echo $indentSpace2;
            $tagIdHex = sprintf("0x%04X", $tagId);
            if (empty($opts['name'])) {
                echo "$tagIdHex:";
            } else {
                $tagName = IO_TIFF_Tag::getTagName($tagId);
                echo "$tagIdHex($tagName):";
            }
            $tag->dump($opts);
        }
    }
    function renumberTagTableOffset($baseOffset) {
        // [nTags] + count * ([tagNo] + [tagType] + [tagCount] + [tagOffset])
        $baseSize = 2 + count($this->tagTable) * (2+2+4+4);
        $extendOffset = $baseOffset + $baseSize;
        $extendSize = 0;
        foreach ($this->tagTable as $tag) {
            ;
        }        
        //
        $this->baseOffset = $baseOffset;
        $this->baseSize = $baseSize;
        $this->extendOffset = $extendOffset;
        $this->baseSize = $baseSize;
    }
}
