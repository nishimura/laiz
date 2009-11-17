<?php
interface Laiz_Parser extends Laiz_Component
{
    /**
     * @param string $fineName
     * @return string
     */
    public function parse($fileName);
}
