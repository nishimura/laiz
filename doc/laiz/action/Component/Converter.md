laiz.action.Component\_Converter
================================

Convert request arguments by ini file.

Ini File
--------

    [converter]
    arg1 = trim                    ; framework converter
    arg2 = trim|removeHyphen       ; multiple
    arr  = arrayToObject,MyClass


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
        public function arrayToMyClass($arr, $className)
        {
            $obj = new $className();
            foreach (...)
                ...
            return $obj;
        }
    }


    ;; ini file
    [converter]
    arg1 = myRtrim
    arr  = arrayToMyClass,MyClass

