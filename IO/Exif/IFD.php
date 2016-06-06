<?php

/*
 * 2016/5/22- (c) yoya@awm.jp
 */

require_once 'IO/Exif/Bit.php';
require_once 'IO/Exif/Tag.php';

class IO_Exif_IFD {
    var $ifdName = null;
    const IFD_OFFSET_BASE = 6;
    var $baseOffset = null;
    var $baseSize = null;
    var $extendOffset = null;
    var $extendSize = null;
    var $tagTable = null;
    var $modified = false;
    var $offsetDelta = 0;
    static function Factory($bit, $baseOffset, $ifdName) {
        $ifd = new IO_Exif_IFD();
        $ifd->ifdName = $ifdName;
        $additionalIFD = $ifd->makeTagTable($bit, $baseOffset);
        return [$ifdName => $ifd] +  $additionalIFD;
    }
    function makeTagTable($bit, $baseOffset) {
        $this->baseOffset = $baseOffset;
        $bit->setByteOffset($baseOffset);
        $nTags = $bit->getSHORT();
        $tagTable = array();
        $IFDNameTable = IO_Exif_Tag::getIFDNameTable();
        $elementSizeTable = IO_Exif_Tag::getElementSizetable();
        for ($i = 0 ; $i < $nTags ; $i++) {
            $tagNo    = $bit->getSHORT();
            $tagType  = $bit->getSHORT();
            $tagCount = $bit->getLONG();
            $tagOffset = $bit->getLONG();
            // echo "tag: $tagNo $tagType $tagCount\n";
            $tagTable[$tagNo] = $tagOffset;
            //
            $valueSize = $elementSizeTable[$tagType] * $tagCount;
            if (($valueSize > 4) && (isset($IFDNameTable[$tagNo]) === false)) {
                // echo "{$this->ifdName}: $tagOffset : $valueSize : " . ($tagOffset + $valueSize) . "\n";
                $eoff  = self::IFD_OFFSET_BASE + $tagOffset;
                $esize = $eoff + $valueSize;
                if (($this->extendOffset === null) || ($eoff < $this->extendOffset)) {
                    $this->extendOffset = $eoff;
                }
                if (($this->extendSize === null) || ($this->extendSize < $esize)) {
                    $this->extendSize = $esize;
                }
            }
        }
        $nextOffset = $bit->getByteOffset(); // offset save
        $ifdList = array();

        foreach ($IFDNameTable as $tagNo => $tagName) {
            if (! empty($tagTable[$tagNo])) {
                // echo "XXX: $tagName\n";
                $tagOffset = $tagTable[$tagNo];
                if ($tagOffset > 0) {
                    $ifdList += IO_Exif_IFD::Factory($bit, self::IFD_OFFSET_BASE + $tagOffset, $tagName);
                }
            }
        }
        $bit->setByteOffset($nextOffset); // offset restore
        $this->baseSize = $nextOffset - $baseOffset;
        $this->tagTable = $tagTable;
        return $ifdList;
    }
    function build($bit) {
        $bit->setByteOffset($this->baseOffset, true);
        if ($this->modified === false) {
            ;
        }

        foreach ($this->tagTable as $offsetEntry) {
        }
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
        foreach ($this->tagTable as $tagId => $tagOffset) {
            echo $indentSpace2;
            $tagIdHex = sprintf("0x%04X", $tagId);
            if (empty($opts['name'])) {
                echo "$tagIdHex:";
            } else {
                $tagName = IO_Exif_Tag::getTagName($tagId);
                echo "$tagIdHex($tagName):";
            }
            echo $tagOffset.PHP_EOL;
        }
    }
    static function baseOffsetComp($ifd1, $ifd2) {
        return ($ifd1->baseOffset > $ifd2->baseOffset)?1:-1;
    }
    static function sortIFDsByBaseOffset(&$IFDs) {
        uasort($IFDs, "self::baseOffsetComp");
    }
    function moveOffset($offsetDelta) {
        if ($offsetDelta != 0) {
            $modified = true;
        } else {
            $this->offsetDelta = $offsetDelta;
        }
    }
}
