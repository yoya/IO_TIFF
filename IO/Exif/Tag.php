<?php

/*
 * 2016/5/23- (c) yoya@awm.jp
 */

class IO_Exif_Tag {
    static $tagNameTable =
        [
         0x8825 => 'GPSInfo IFD Pointer',
         0x8769 => 'Exif IFD Pointer',
         0xA005 => 'Interoperability IFD Pointer',
         ];
    var $IFDNameTable =
        [
         0x8825 => 'GPS',
         0x8769 => 'Exif',
         0xA005 => 'Interop',
         ];
    function getIFDNameTable() {
        return $this->IFDNameTable;
    }
    static function getTagName($id) {
        if (isset($this->tagNameTable[$id])) {
            return $this->tagNameTable[$id];
        }
        return "(Unknown)";
    }
}
