<?php
/**
 * Class file for inflection.
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 */

/**
 * Class for inflection.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Util_Inflector
{
    private $singularized = array();

    private $irregularSingular = array(
        'children' => 'child',
        'men' => 'man',
        'people' => 'person'
                                       );

    private $uninflectedSingular = array(
        '.*ss', 'information');

    private $singularRules = array(
        '/(o)es$/i' => '\1',
        '/uses$/' => 'us',
        '/(x|ch|ss|sh)es$/i' => '\1',
        '/([^aeiouy]|qu)ies$/i' => '\1y',
        '/([lr])ves$/i' => '\1f',
        '/s$/i' => '');

    /**
     * Returns singular word from plural.
     * 
     * @param string $word plural word
     * @return string singular word
     */
    public function singularize($word)
    {
        if (isset($this->singularized[$word]))
            return $this->singularized[$word];

        $irregulars = join('|', array_keys($this->irregularSingular));
        $pattern = '/(' . $irregulars . ')$/i';
        if (preg_match($pattern, $word, $matches)){
            $this->singularized[$word]
                = $matches[1]
                . substr($word, 0, 1)
                . substr($this->irregularSingular[strtolower($matches[1])], 1);
            return $this->singularized[$word];
        }

        $pattern = '/^(' . join('|', $this->uninflectedSingular) . ')$/i';
        if (preg_match($pattern, $word)){
            $this->singularized[$word] = $word;
            return $word;
        }

        foreach ($this->singularRules as $rule => $rep){
            if (preg_match($rule, $word)){
                $this->singularized[$word] = preg_replace($rule, $rep, $word);
                return $this->singularized[$word];
            }
        }
        $this->singularized[$word] = $word;
        return $word;
    }

    /**
     * Returns underscored singular word from CamelCasedPluralWord.
     *
     * @param string $camelCasedPluralWord
     * @return string underscored word
     */
    public function classify($camelCasedPluralWord)
    {
        $str = preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $camelCasedPluralWord);
        return ucfirst($this->singularize($str));
    }
}
