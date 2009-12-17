<?php

class Laiz_Util_Inifile
{
    private $compiledFile;
    private $data;
    public function __construct($file, $flag = false)
    {
        $this->data = parse_ini_file($file, $flag);

        $file = $this->compiledPath($file);
        if (file_exists($file)){
            $data = parse_ini_file($file, $flag);
            foreach ($data as $k => $v){
                if (isset($this->data[$k]))
                    $this->data[$k] = $v;
            }
        }

        $this->compiledFile = $file;
    }

    private function compiledPath($file)
    {
        $compiledFile = 'ini_' . md5($file);
        $configs = Laiz_Configure::get('Laiz_View');
        $compiledPath = $configs['FLEXY_COMPILE_DIR'].$compiledFile;
        return $compiledPath;
    }

    public function get($a, $b = null)
    {
        if ($b !== null){
            if (isset($this->data[$a][$b]))
                return $this->data[$a][$b];
            else
                return null;
        }
        if (isset($this->data[$a]))
            return $this->data[$a];
        return null;
    }

    public function getAll()
    {
        return $this->data;
    }

    public function set($a, $b, $c = null)
    {
        if ($c !== null)
            $this->data[$a][$b] = $c;
        else
            $this->data[$a] = $b;
        return $this;
    }

    public function write()
    {
        $data = '';
        foreach ($this->data as $k => $v){
            if (is_array($v)){
                $data .= "[$k]\n";
                foreach ($v as $_k => $_v){
                    $data .= "$_k = \"$_v\"\n";
                }
            }else{
                $data .= "$k = \"$v\"\n";
            }
        }

        return file_put_contents($this->compiledFile, $data);
    }
}
