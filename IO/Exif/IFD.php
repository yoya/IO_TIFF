<?php

/*
 * 2016/5/22- (c) yoya@awm.jp
 */

require_once 'IO/Exif/Bit.php';
require_once 'IO/Exif/Tag.php';

class IO_Exif_IFD {
    const IFD_OFFSET_BASE = 6;
    var $baseOffset = null;
    var $offsetTable = null;
    static function Factory($bit, $baseOffset, $ifdName) {
        $ifd = new IO_Exif_IFD();
        $additionalIFD = $ifd->makeOffsetTable($bit, $baseOffset);
        return [$ifdName => $ifd] +  $additionalIFD;
    }
    function makeOffsetTable($bit, $baseOffset) {
        $this->baseOffset = $baseOffset;
        $bit->setByteOffset($baseOffset);
        $nTags = $bit->getSHORT();
        $offsetTable = array();
        for ($i = 0 ; $i < $nTags ; $i++) {
            $tagNo    = $bit->getSHORT();
            $tagType  = $bit->getSHORT();
            $tagCount = $bit->getLONG();
            $tagOffset = $bit->getLONG();
            // echo "tag: $tagNo $tagType $tagCount\n";
            $offsetTable[$tagNo] = $tagOffset;
        }
        $nextOffset = $bit->getByteOffset(); // offset save
        $ifdList = array();
        $tag = new IO_Exif_Tag();
        foreach ($tag->getIFDNameTable() as $tagNo => $tagName) {
            if (! empty($offsetTable[$tagNo])) {
                // echo "XXX: $tagName\n";
                $tagOffset = $offsetTable[$tagNo];
                if ($tagOffset > 0) {
                    $ifdList += IO_Exif_IFD::Factory($bit, self::IFD_OFFSET_BASE + $tagOffset, $tagName);
                }
            }
        }
        $bit->setByteOffset($nextOffset); // offset restore
        $this->offsetTable = $offsetTable;
        return $ifdList;
    }
    function dump($opts) {
        $indent = $opts['indent'];
        $indentSpace = str_repeat(" ", $indent * 4);
        $indentSpace2 = str_repeat(" ", $indent * 4 * 2);
        echo $indentSpace."BaseOffset:".$this->baseOffset.PHP_EOL;
        echo $indentSpace."OffsetTable:(count=".count($this->offsetTable).")".PHP_EOL;
        foreach ($this->offsetTable as $tagId => $tagOffset) {

            echo $indentSpace2.$tagId.":".$tagOffset.PHP_EOL;
        }
    }
}
