TIFF parser & builder.

- acceptable format: TIFF, Exif, JPEG

# Usage

```
$ php sample/tiffdump.php
Usage: php tiffdump.php -f <tiff_file> [-n] [-h] [-v]
ex) php tiffdump.php -f test.tiff -hnv
```

- DNG (TIFF container)
```
$ php sample/tiffdump.php -f test/APC_0025.dng
ByteOrder:II(LittleEndian)
TIFFVersion:0x002A
IFD:0th
    BaseOffset:8 BaseSize:710
    ExtendOffset:722 ExtendSize:133698
    TagTable:(count=59)
        0x00FE: Type:LONG Count:1 Data: [0]1
        0x0100: Type:LONG Count:1 Data: [0]256
        0x0101: Type:LONG Count:1 Data: [0]192
        0x0102: Type:SHORT Count:3 Offset:722 Data: [0]8 [1]0 [2]8
        0x0103: Type:SHORT Count:1 Data: [0]1
        0x0106: Type:SHORT Count:1 Data: [0]2
        0x010F: Type:ASCII Count:6 Offset:728 Data:Apple
        0x0110: Type:ASCII Count:14 Offset:734 Data:iPhone 7 Plus
        0x0111: Type:LONG Count:1 Data: [0]138956
        0x0112: Type:SHORT Count:1 Data: [0]1
        0x0115: Type:SHORT Count:1 Data: [0]3
        0x0116: Type:LONG Count:1 Data: [0]192
        0x0117: Type:LONG Count:1 Data: [0]147456
        0x011C: Type:SHORT Count:1 Data: [0]1
        0x0131: Type:ASCII Count:42 Offset:748 Data:Adobe Photoshop Lightroom 6.7 (Macintosh)
        0x0132: Type:ASCII Count:20 Offset:790 Data:2016:11:04 19:40:39
        0x014A: Type:LONG Count:4 Offset:810 Data: [0]134420 [1]136510 [2]136902 [3]137484
＜略＞
```

- Exif
```
$ php sample/tiffdump.php -f test/IMG_0905.exif
ByteOrder:MM:(BigEndian)
TIFFVersion: 0x002A
IFD:0th
    BaseOffset:8 BaseSize:110
    ExtendOffset:122 ExtendSize:70
    TagTable:(count=9)
        0x010F: Type:ASCII Count:6 Offset:122 Data:Apple
        0x0110: Type:ASCII Count:14 Offset:128 Data:iPhone 6 Plus
        0x0112: Type:SHORT Count:1 Data: [0]0
        0x011A: Type:RATIONAL Count:1 Offset:142 Data: [0]1207959552/16777216=72
        0x011B: Type:RATIONAL Count:1 Offset:150 Data: [0]1207959552/16777216=72
        0x0128: Type:SHORT Count:1 Data: [0]0
        0x0131: Type:ASCII Count:13 Offset:158 Data:Photos 1.0.1
        0x0132: Type:ASCII Count:20 Offset:172 Data:2015:10:03 10:13:51
        0x8769: Type:LONG Count:1 Offset:192
IFD:Exif
    BaseOffset:192 BaseSize:110
    ExtendOffset:306 ExtendSize:40
    TagTable:(count=9)
        0x9000: Type:UNDEFINED Count:4 Data:0221
        0x9003: Type:ASCII Count:20 Offset:306 Data:2015:10:03 10:13:51
        0x9004: Type:ASCII Count:20 Offset:326 Data:2015:10:03 10:13:51
        0x9101: Type:UNDEFINED Count:4 Data:
        0xA000: Type:UNDEFINED Count:4 Data:0100
        0xA001: Type:SHORT Count:1 Data: [0]0
        0xA002: Type:LONG Count:1 Data: [0]2147614720
        0xA003: Type:LONG Count:1 Data: [0]3758161920
        0xA406: Type:SHORT Count:1 Data: [0]0
```

# Reference

- http://dsas.blog.klab.org/archives/52123322.html
- http://www.vieas.com/exif23.html
- https://partners.adobe.com/public/developer/en/tiff/TIFF6.pdf
