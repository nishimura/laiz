laiz.action.Component\_Converter
================================

Convert request arguments by ini file.

Ini File
--------

    [converter]
    arg1 = trim                    ; framework converter
    arg2 = trim|removeHyphen       ; multiple


Custom Converter
----------------

    <?php
    use \laiz\action\Converter;
    class MyConverter implements Converter
    {
        public function myRtrim($arg)
        {
            return rtrim($arg);
        }
    }


    ;; ini file
    [converter]
    arg1 = myRtrim
