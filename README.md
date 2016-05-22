# Usage

```
$ php sample/exifdump.php
Usage: php exifdump.php -f <exif_file> [-n] [-h] [-v]
ex) php exifdump.php -f test.exif -hnv
$ php sample/exifdump.php -f IMG_0905.exif
IFD:0th
    BaseOffset:14
    OffsetTable:(count=9)
        271:122
        272:128
        274:65536
        282:142
        283:150
        296:131072
        305:158
        306:172
        34665:192
IFD:Exif
    BaseOffset:198
    OffsetTable:(count=9)
        36864:808596017
        36867:306
        36868:326
        37121:16909056
        40960:808529968
        40961:65536
        40962:640
        40963:480
        41990:0
```

# Reference

- http://dsas.blog.klab.org/archives/52123322.html
