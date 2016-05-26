<?php

/*
 * 2016/5/23- (c) yoya@awm.jp
 */

class IO_Exif_Tag {
    static function getTagNameTable() {
        static $tagNameTable =
            [
             0x8825 => 'GPSInfo IFD Pointer',
             0x8769 => 'Exif IFD Pointer',
             0xA005 => 'Interoperability IFD Pointer',
             ];
        return $tagNameTable;
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
    static function getElementSizeTable() {
        static $elementSizeTable =
            [ /*BYTE*/     1 => 1,
              /*ASCII*/    2 => 1, /*UNDEFINED*/  7 => 1,
              /*SHORT*/    3 => 2,
              /*LONG*/     4 => 4, /*SLONG*/      9 => 4,
              /*RATIONAL*/ 5 => 8, /*SRATIONAL*/ 10 => 8 ];
        return $elementSizeTable;
    }
    static function getTagName($id) {
        $tagNameTable = $ths->getTagNameTable;
        if (isset($tagNameTable[$id])) {
            return $tagNameTable[$id];
        }
        return "(Unknown)";
    }
}
