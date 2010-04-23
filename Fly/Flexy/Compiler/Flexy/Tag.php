<?php
// This Package is based upon PEAR::HTML_Template_Flexy (ver 1.3.9 (stable) released on 2009-03-24)
//  Please visit http://pear.php.net/package/HTML_Template_Flexy
//
// +----------------------------------------------------------------------+
// | PHP Version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author:  Tomoaki Kosugi <kosugi@kips.gr.jp>                          |
// | Authors:  Alan Knowles <alan@akbkhome>                               |
// +----------------------------------------------------------------------+
//
/* FC/BC compatibility with php5 */
if ( (substr(phpversion(),0,1) < 5) && !function_exists('clone')) {
    eval('function clone($t) { return $t; }');
}

/**
* Compiler That deals with standard HTML Tag output.
* Since it's pretty complex it has it's own class.
* I guess this class should deal with the main namespace
* and the parent (standard compiler can redirect other namespaces to other classes.
*
* one instance of these exists for each namespace.
*
*
*/

class Fly_Flexy_Compiler_Flexy_Tag
{


    /**
    * Parent Compiler for
    *
    * @var  object  Fly_Flexy_Compiler
    *
    * @access public
    */
    var $compiler;

    /**
    *
    * Factory method to create Tag Handlers
    *
    * $type = namespace eg. <flexy:toJavascript loads Flexy.php
    * the default is this... (eg. Tag)
    *
    *
    * @param   string    Namespace handler for element.
    * @param   object   Fly_Flexy_Compiler
    *
    *
    * @return    object    tag compiler
    * @access   public
    */

    public static function factory($type,&$compiler) {
        if (!$type) {
            $type = 'Tag';
        }

        $class = 'Fly_Flexy_Compiler_Flexy_' . $type;
        if ($compiler->classExists($class)) {
            $ret = new $class;
            $ret->compiler = &$compiler;
            return $ret;
        }

        $filename = 'Fly/Flexy/Compiler/Flexy/' . ucfirst(strtolower($type)) . '.php';
        if (!self::fileExistsInPath($filename)) {
            $ret = self::factory('Tag', $compiler);
            return $ret;
        }
        // if we dont have a handler - just use the basic handler.
        if (!file_exists(dirname(__FILE__) . '/'. ucfirst(strtolower($type)) . '.php')) {
            $type = 'Tag';
        }

        include_once 'Fly/Flexy/Compiler/Flexy/' . ucfirst(strtolower($type)) . '.php';

        $class = 'Fly_Flexy_Compiler_Flexy_' . $type;
        if (!$compiler->classExists($class)) {
            $ret = false;
            return $ret;
        }
        $ret = Fly_Flexy_Compiler_Flexy_Tag::factory($type,$compiler);
        return $ret;
    }
    /**
    *
    * Check that a file exists in the "include_path"
    *
    * @param   string    Filename
    *
    * @return    boolean  true if it is in there.
    * @access   public
    */
    public static function fileExistsInPath($filename) {
        if (isset($GLOBALS['_'.__CLASS__]['cache'][$filename])) {
            return $GLOBALS['_'.__CLASS__]['cache'][$filename];
        }
        $bits = explode(PATH_SEPARATOR,ini_get('include_path'));
        foreach($bits as $b) {
            if (file_exists("$b/$filename")) {
                return $GLOBALS['_'.__CLASS__]['cache'][$filename] = true;
            }
        }
        return $GLOBALS['_'.__CLASS__]['cache'][$filename] = false;
    }



    /**
    * The current element to parse..
    *
    * @var object
    * @access public
    */
    var $element;

    /**
    * Flag to indicate has attribute flexy:foreach (so you cant mix it with flexy:if!)
    *
    * @var boolean
    * @access public
    */
    var $hasForeach = false;

    /**
     * flexy:loop
     *
     * @var bool
     * @author Satoshi Nishimura
     */
    public $hasLoop;

    /**
    * toString - display tag, attributes, postfix and any code in attributes.
    * Note first thing it does is call any parseTag Method that exists..
    *
    * Adding execute function of parseAttributeLoop by Satoshi Nishimura.
    *
    * @see parent::toString()
    */
    function toString($element)
    {

        global $_FLY_FLEXY_TOKEN;
        global $_FLY_FLEXY;

        // store the element in a variable
        $this->element = $element;
       // echo "toString: Line {$this->element->line} &lt;{$this->element->tag}&gt;\n";

        // if the FLEXYSTARTCHILDREN flag was set, only do children
        // normally set in BODY tag.
        // this will probably be superseeded by the Class compiler.

        if (isset($element->ucAttributes['FLEXY:STARTCHILDREN'])) {

            return $element->compileChildren($this->compiler);
        }
        // look for flexy:ignore..
        $flexyignore = $this->parseAttributeIgnore();

        // rewriting should be done with a tag.../flag.

        $this->reWriteURL("HREF");
        $this->reWriteURL("SRC");
        $this->reWriteURL("BACKGROUND");

        // handle elements
        if (($ret =$this->_parseTags()) !== false) {
            return $ret;
        }
        // these add to the close tag..

        $ret  = $this->parseAttributeForeach();
        $ret .= $this->parseAttributeIf();
        $ret .= $this->parseAttributeLoop();

        // support Custom Attributes...
        require_once 'Fly/Flexy/Compiler/Flexy/CustomFlexyAttributes.php';
		$customFlexyAttributes = new Fly_Flexy_Compiler_Flexy_CustomFlexyAttributes();
		$customFlexyAttributes->doCustomAttributes($element);


        $add = $this->toStringOpenTag($element,$ret);

        if ($add instanceof PEAR_Error) {
            return $add;
        }





        // post stuff this is probably in the wrong place...

        if ($element->postfix) {
            foreach ($element->postfix as $e) {
                $add = $e->compile($this->compiler);
                if ($add instanceof PEAR_Error) {
                    return $add;
                }
                $ret .= $add;
            }
        } else if ($this->element->postfix) { // if postfixed by self..
            foreach ($this->element->postfix as $e) {
                $add = $e->compile($this->compiler);
                if ($add instanceof PEAR_Error) {
                    return $add;
                }

                $ret .= $add;
            }
        }


        $tmp = $this->toStringChildren($element,$ret);
        if ($tmp instanceof PEAR_Error) {
            return  $tmp;
        }
        $tmp = $this->toStringCloseTag($element,$ret);
        if ($tmp instanceof PEAR_Error) {
            return  $tmp;
        }


        // reset flexyignore

        $_FLY_FLEXY_TOKEN['flexyIgnore'] = $flexyignore;

        if (isset($_FLY_FLEXY['currentOptions']['output.block']) &&
            ($_FLY_FLEXY['currentOptions']['output.block'] == $element->getAttribute('ID'))) {

           // echo $_FLY_FLEXY['compiledTemplate'];

            $fh = fopen($_FLY_FLEXY['compiledTemplate'],'w');
            fwrite($fh,$ret);
            fclose($fh);

        }



        return $ret;
    }

    /**
     * convert a tag into compiled version
     * @arg object Element
     * @arg inout output string to template
     * @return none? or pear error.
     *
     */

    function toStringOpenTag(&$element,&$ret)
	{
		// START ADDITION...
		if ((empty($element->tag)) || (empty($element->oTag))) {
			return;
		}
		// ...END ADDITION


		// spit ou the tag and attributes.

        if ($element->oTag{0} == '?') {
            $ret .= '<?php echo "<"; ?>';
        } else {
            $ret .= "<";
        }
        $ret .= $element->oTag;
        //echo '<PRE>'.print_r($element->attributes,true);
        foreach ($element->attributes as $k=>$v) {
            // if it's a flexy tag ignore it.


            if (strtoupper($k) == 'FLEXY:RAW') {
                if (!is_array($v) || !isset($v[1]) || !is_object($v[1])) {
                    return $this->_raiseErrorWithPositionAndTag(
                        'flexy:raw only accepts a variable or method call as an argument, eg.'.
                        ' flexy:raw="{somevalue}" you provided something else.' .

                         null,   FLY_FLEXY_ERROR_DIE);
                }
                $add = $v[1]->compile($this->compiler);
                if ($add instanceof PEAR_Error) {
                    return $add;
                }
                $ret .= ' ' . $add;
                continue;

            }

            if (strtoupper(substr($k,0,6)) == 'FLEXY:') {
                continue;
            }
            // true == an attribute without a ="xxx"
            if ($v === true) {
                $ret .= " $k";
                continue;
            }

            // if it's a string just dump it.
            if (is_string($v)) {
                $v = str_replace(array('{_(',')_}'),array('',''),$v);
                $ret .=  " {$k}={$v}";
                continue;
            }

            // normally the value is an array of string, however
            // if it is an object - then it's a conditional key.
            // eg.  if (something) echo ' SELECTED';
            // the object is responsible for adding it's space..

            if (is_object($v)) {
                $add = $v->compile($this->compiler);
                if ($add instanceof PEAR_Error) {
                    return $add;
                }

                $ret .= $add;
                continue;
            }

            // otherwise its a key="sometext{andsomevars}"

            $ret .=  " {$k}=";

            foreach($v as $item) {

                if (is_string($item)) {
                    // skip translation strings in tags.
                    $item = str_replace(array('{_(',')_}'),array('',''),$item);
                    $ret .= $item;
                    continue;
                }
                $add = $item->compile($this->compiler);
                if ($add instanceof PEAR_Error) {
                    return $add;
                }
                $ret .= $add;
            }
        }
        $ret .= ">";
	}
    /**
     * compile children to string.
     * @arg object Element
     * @arg inout output string to template
     * @return none? or pear error.
     */

	function toStringChildren(&$element,&$ret)
	{
		 // dump contents of script raw - to prevent gettext additions..
        //  print_r($element);
		//  make sure tag isn't empty because it wouldn't make sense to output script without script tags
        if (((! empty($element->tag)) && ($element->tag == 'SCRIPT'))
			|| ((! empty($element->oTag)) && ($element->oTag == 'SCRIPT'))) {
            foreach($element->children as $c) {
                //print_R($c);
                if (!$c) {
                    continue;
                }
                if ($c->token == 'Text') {
                    $ret .= $c->value;
                    continue;
                }
                // techically we shouldnt have anything else inside of script tags.
                // as the tokeinzer is supposted to ignore it..
            }
            return;
        }
        $add = $element->compileChildren($this->compiler);
        if ($add instanceof PEAR_Error) {
            return $add;
        }
        $ret .= $add;

	}
  /**
     * compile closing tag to string.
     * @arg object Element
     * @arg inout output string to template
     * @return none? or pear error.
     */

	function toStringCloseTag(&$element,&$ret)
	{
		// output the closing tag.
		//  If the tag is empty don't output closing tags, just output postfixes if any exist...
        if ( !$element->close) {
            return;
        }

        if ((! empty($element->tag)) && (! empty($element->oTag)))
        {
            $add = $element->close->compile($this->compiler);
            if ($add instanceof PEAR_Error) {
                return $add;
            }
            $ret .= $add;
            return;
        }
        // RICK - added by me
        // element has a seperate closing tag (eg. </something>) and opening and closing tags should be removed
        // because FLEXY:OMITTAG element attribute is set, but still need postfix stuff like for ending ifs and foreach
        // so this is NOT OPTIONAL if foreach and if are not optional.
        if ($element->close->postfix)  {
            foreach ($element->close->postfix as $e)  {
                $add = $e->compile($this->compiler);
                if ($add instanceof PEAR_Error)  {
                    return $add;
                }
                $ret .= $add;
            }
            return;
        }
        if ($this->element->close->postfix)  { // if postfixed by self..
            foreach ($this->element->close->postfix as $e)  {
                $add = $e->compile($this->compiler);
                if ($add instanceof PEAR_Error)  {
                    return $add;
                }

                $ret .= $add;
            }
            return;
        }

	}


    /**
    * Reads an flexy:foreach attribute -
    *
    *
    * @return   string to add to output.
    * @access   public
    */

    function parseAttributeIgnore()
    {

        global $_FLY_FLEXY_TOKEN;

        $flexyignore = $_FLY_FLEXY_TOKEN['flexyIgnore'];

        if ($this->element->getAttribute('FLEXY:IGNORE') !== false) {
            $_FLY_FLEXY_TOKEN['flexyIgnore'] = true;
            $this->element->clearAttribute('FLEXY:IGNORE');
        }
        return $flexyignore;

    }

    /**
    * Reads an flexy:foreach attribute -
    *
    *
    * @return   string to add to output.
    * @access   public
    */

    function parseAttributeForeach()
    {
        global  $_FLY_FLEXY;
        $foreach = $this->element->getAttribute('FLEXY:FOREACH');
        if ($foreach === false) {
            return '';
        }
        //var_dump($foreach);

        $this->element->hasForeach = true;
        // create a foreach element to wrap this with.

        $foreachObj =  $this->element->factory('Foreach',
                explode(',',$foreach),
                $this->element->line);
        // failed = probably not enough variables..


        if ($foreachObj === false) {

            return $this->_raiseErrorWithPositionAndTag(
                "Missing Arguments: An flexy:foreach attribute was found. flexy:foreach=&quot;$foreach&quot;<BR>
                 the syntax is  &lt;sometag flexy:foreach=&quot;onarray,withvariable[,withanothervar] &gt;<BR>",
                 null,  FLY_FLEXY_ERROR_DIE);
        }



        // does it have a closetag?
        if (!$this->element->close) {

            if ($this->element->getAttribute('/') === false) {


                return $this->_raiseErrorWithPositionAndTag(
                    "A flexy:foreach attribute was found without a corresponding &lt;/{$this->element->tag} tag",
                    null, FLY_FLEXY_ERROR_DIE);
            }
            // it's an xhtml tag!
            $this->element->postfix = array($this->element->factory("End", '', $this->element->line));
        } else {
            $this->element->close->postfix = array($this->element->factory("End", '', $this->element->line));
        }

        $this->element->clearAttribute('FLEXY:FOREACH');
        return $foreachObj->compile($this->compiler);
    }

    /**
    * flexy:loop attribute
    *
    * @author  Satoshi Nishimura
    * @return   string to add to output.
    */    
    public function parseAttributeLoop() 
    {
        $loop = $this->element->getAttribute('FLEXY:LOOP');
        if ($loop === false) {
            return '';
        }
        
        $this->element->hasLoop = true;
        
        $loopObj =  $this->element->factory('Loop', explode(',',$loop), $this->element->line);
        
        if ($loopObj === false) {
            return $this->_raiseErrorWithPositionAndTag(
                "Missing Arguments: An flexy:loop attribute was foundon Line {$this->element->line} 
                in tag &lt;{$this->element->tag} flexy:loop=&quot;$loop&quot; .....&gt;<BR>
                the syntax is  &lt;sometag flexy:loop=&quot;onarray,withvariable[,withanothervar] &gt;<BR>",
                 null,  Fly_FLEXY_ERROR_DIE);
        }
        
        
        
        if (!$this->element->close) {
        
            if ($this->element->getAttribute('/') === false) {
            
            
                return $this->_raiseErrorWithPositionAndTag(
                    "A flexy:loop attribute was found in &lt;{$this->element->name} tag without a corresponding &lt;/{$this->element->tag}
                        tag on Line {$this->element->line} &lt;{$this->element->tag}&gt;",
                    null, Fly_FLEXY_ERROR_DIE);
            }

            $this->element->postfix = array($this->element->factory("EndLoop", '', $this->element->line));
        } else {
            $this->element->close->postfix = array($this->element->factory("EndLoop", '', $this->element->line));
        }

        $this->element->clearAttribute('FLEXY:LOOP');
        return $loopObj->compile($this->compiler);
    }


    /**
    * Reads an flexy:if attribute -
    *
    *
    * @return   string to add to output.
    * @access   public
    */

    function parseAttributeIf()
    {
        // dont use the together, if is depreciated..
        $if = $this->element->getAttribute('FLEXY:IF');

        if ($if === false) {
            return '';
        }

        if (isset($this->element->hasForeach)) {
            return $this->_raiseErrorWithPositionAndTag("You may not use FOREACH and IF tags in the same tag",
                  null, FLY_FLEXY_ERROR_DIE);
        }

        // allow if="!somevar"
        $ifnegative = '';

        if ($if{0} == '!') {
            $ifnegative = '!';
            $if = substr($if,1);
        }
        // if="xxxxx"
        // if="xxxx.xxxx()" - should create a method prefixed with 'if:'
        // these checks should really be in the if/method class..!!!



        if (!preg_match('/^[_A-Z][A-Z0-9_]*(\[[0-9]+\])?((\[|%5B)[A-Z0-9_]+(\]|%5D))*'.
                '(\.[_A-Z][A-Z0-9_]*((\[|%5B)[A-Z0-9_]+(\]|%5D))*)*(\\([^)]*\))?$/i',$if)) {
            return $this->_raiseErrorWithPositionAndTag(
                "IF tags only accept simple object.variable or object.method() values. {$if}",
                null, FLY_FLEXY_ERROR_DIE);

        }

        if (substr($if,-1) == ')') {
            // grab args..
            $args = substr($if,strpos($if,'(')+1,-1);
            // simple explode ...

            $args = strlen(trim($args)) ? explode(',',$args) : array();
            //print_R($args);

            // this is nasty... - we need to check for quotes = eg. # at beg. & end..
            $args_clean = array();
            for ($i=0; $i<count($args); $i++) {
                if ($args[$i]{0} != '#') {
                    $args_clean[] = $args[$i];
                    continue;
                }
                // single # - so , must be inside..
                if ((strlen($args[$i]) > 1) && ($args[$i]{strlen($args[$i])-1}=='#')) {
                    $args_clean[] = $args[$i];
                    continue;
                }

                $args[$i] .=',' . $args[$i+1];
                // remove args+1..
                array_splice($args,$i+1,1);
                $i--;
                // reparse..
            }



            $ifObj =  $this->element->factory('Method',
                    array('if:'.$ifnegative.substr($if,0,strpos($if,'(')), $args_clean),
                    $this->element->line);
        } else {
            $ifObj =  $this->element->factory('If', $ifnegative.$if, $this->element->line);
        }

        // does it have a closetag? - you must have one - so you will have to hack in <span flexy:if=..><img></span> on tags
        // that do not have close tags - it's done this way to try and avoid mistakes.


        if (!$this->element->close) {
            //echo "<PRE>";print_R($this->element);

            if ($this->element->getAttribute('/') !== false) {
                $this->element->postfix = array($this->element->factory("End",'', $this->element->line));
            } else {

                 return $this->_raiseErrorWithPositionAndTag(
                    "An flexy:if attribute was found in &lt;{$this->element->name} tag without a corresponding &lt;/{$this->element->name} tag",
                    null, FLY_FLEXY_ERROR_DIE);

                }
        } else {

            $this->element->close->postfix = array($this->element->factory("End",'', $this->element->line));
        }
        $this->element->clearAttribute('FLEXY:IF');
        return $ifObj->compile($this->compiler);
    }

     /**
    * Reads Tags - and relays to parseTagXXXXXXX
    *
    *
    * @return   string | false = html output or ignore (just output the tag)
    * @access   private
    */


    function _parseTags()
    {
        global $_FLY_FLEXY_TOKEN;
        // doesnt really need strtolower etc. as php functions are not case sensitive!


        /* always render script correctly */
        if (0 == strcasecmp($this->element->tag,"script")) {
            return $this->parseTagScript();
        }

        if ($this->element->getAttribute('FLEXY:DYNAMIC')) {
            return $this->compiler->appendPhp(
                $this->getElementPhp( $this->element->getAttribute('ID') )
            );

        }

        if ($this->element->getAttribute('FLEXY:IGNOREONLY') !== false) {
            return false;
        }
        if ($_FLY_FLEXY_TOKEN['flexyIgnore']) {
            return false;
        }
        $tag = $this->element->tag;
        if (strpos($tag,':') !== false) {
            $bits = explode(':',$tag);
            $tag = $bits[1];
        }

        if (in_array(strtolower($tag), array('menulist','textbox','checkbox'))) {
            $method = 'parseXulTag';
        } else {
            $method = 'parseTag'.$tag;
            if (!method_exists($this,$method)) {
                return false;
            }
        }

        if (($this->element->getAttribute('NAME') === false) &&
            ($this->element->getAttribute('ID') === false) ) {
            return false;
        }
        // do any of the attributes use flexy data...
        //foreach ($this->element->attributes as $k=>$v) {
        //    if (is_array($v)) {
        //        return false;
        //   }
        //}

        //echo "call $method" . serialize($this->element->attributes). "\n";

        return $this->$method();
            // allow the parse methods to return output.

    }




    /**
    * produces the code for dynamic elements
    *
    * @return   string | false = html output or ignore (just output the tag)
    * @access   public
    */

    function getElementPhp($id,$mergeWithName=false,$varsOnly = false) {

        global $_FLY_FLEXY;
        static $tmpId=0;



        if (!$id) {

              return $this->_raiseErrorWithPositionAndTag("Dynamic tags require an ID value",
                 null, FLY_FLEXY_ERROR_DIE);

        }

        // dont mix and match..
        if (($this->element->getAttribute('FLEXY:IF') !== false) ||
            ($this->element->getAttribute('FLEXY:FOREACH') !== false) )
        {
            return $this->_raiseErrorWithPositionAndTag(
                " You can not mix flexy:if= or flexy:foreach= with dynamic form elements  " .
                " (turn off tag to element code with flexyIgnore=0, use flexy:ignore=&quot;yes&quot; in the tag" .
                " or put the conditional outside in a span tag",
                null, FLY_FLEXY_ERROR_DIE);
        }

        if ((strtolower($this->element->getAttribute('TYPE')) == 'checkbox' ) &&
                (substr($this->element->getAttribute('NAME'),-2) == '[]')) {
            if ($this->element->getAttribute('ID') === false) {
                $id = 'tmpId'. (++$tmpId);
                $this->element->attributes['id'] = $id;
                $this->element->ucAttributes['ID'] = $id;
            } else {
                $id = $this->element->getAttribute('ID');
            }
            $mergeWithName =  true;
        }





        if (isset($_FLY_FLEXY['elements'][$id])) {
           // echo "<PRE>";print_r($this);print_r($_FLY_FLEXY['elements']);echo "</PRE>";
            return $this->_raiseErrorWithPositionAndTag(
                "The Dynamic tag Name '$id' has already been used previously by  tag &lt;{$_FLY_FLEXY['elements'][$id]->tag}&gt;",
                null,FLY_FLEXY_ERROR_DIE);
        }

        $ret = '';
        $unset = '';

        //echo '<PRE>';print_r($this->element);echo '</PRE>';
        if (isset($this->element->ucAttributes['FLEXY:USE'])) {
            $ar = $this->element->ucAttributes['FLEXY:USE'];
            $str = '';

            for($i =1; $i < count($ar) -1; $i++) {
                switch(true) {
                    case $ar[$i] instanceof Fly_Flexy_Token_Var:
                        $str .= '. ' . $ar[$i]->toVar($ar[$i]->value). ' ';
                        break;
                    case is_string($ar[$i]):
                        $str .= '. ' . $ar[0] . $ar[$i] . $ar[0];
                        break;
                    default:
                        return $this->_raiseErrorWithPositionAndTag(
                            "unsupported type found in attribute, use flexy:ignore to prevent parsing or remove it. " .
                                print_r($this->element,true),
                            null,FLY_FLEXY_ERROR_DIE);
                }
            }
            $str = trim(ltrim($str,'.'));
            $_FLY_FLEXY['elements'][$id] = $this->toElement($this->element);

            return  $ret .
                '
                if (!isset($this->elements['.$str.'])) {
                    echo "ELEMENT MISSING $str";
                }
                echo $this->elements['.$str.']->toHtml();' .$unset;
        }

        //var_dump($this->element); exit();

        if ($this->elementUsesDynamic($this->element)) {
            $used = array();
            foreach ($this->element->attributes as $attribute => $attributeValue) {
                if (!is_array($attributeValue)) {
                    continue;
                }
                if (strtoupper(substr($attribute,0,6)) == 'FLEXY:') {
                    continue;
                }
                unset($this->element->attributes[$attribute]);
                // generate code to put data into value..
                $output_avar = '$this->elements[\''.$id.'\']->attributes[\''.$attribute.'\']';
                $used[] = "'{$attribute}'";
                $ret .= "\nif (!isset({$output_avar})) {\n";
                // get the " or ' that encapsulates the element.
                $wrapper = array_shift($attributeValue);
                array_pop($attributeValue);
                $ret .= "    {$output_avar} = '';\n";
                //echo '<PRE>';print_r($attributeValue);echo '</PRE>';
                foreach($attributeValue as $item) {

                    if (is_string($item)) {
                        $ret .= "    {$output_avar} .= {$wrapper}{$item}{$wrapper};\n";
                        continue;
                    }
                    if (!is_object($item) || !($item instanceof Fly_Flexy_Token_Var)) {
                        return $this->_raiseErrorWithPositionAndTag(
                            "unsupported type found in attribute, use flexy:ignore to prevent parsing or remove it. " .
                                print_r($this->element,true),
                            null,FLY_FLEXY_ERROR_DIE);
                    }

                    $var = $item->toVar($item->value);
                    if ($var instanceof PEAR_Error) {
                        return $var;
                    }
                    list($prefix,$suffix) = $this->compiler->getModifierWrapper($item);
                    $prefix =  substr($prefix,4);

                    $ret .= "    {$output_avar} .= {$prefix}{$var}{$suffix};\n";
                }

                $ret .= "}\n";
            }
            $ret .= "\$_attributes_used = array(".implode(',',$used).");\n";
            $unset = "\n".'if (isset($_attributes_used)) {  foreach($_attributes_used as $_a) {'."\n".
                     '    unset($this->elements[\''. $id .'\']->attributes[$_a]);'."\n" .
                     "}}\n";


        }



        // this is for a case where you can use a sprintf as the name, and overlay it with a variable element..
        $_FLY_FLEXY['elements'][$id] = $this->toElement($this->element);


        if ($varsOnly) { // used by form tag.
            return array($ret,$unset);
        }

        if ($var = $this->element->getAttribute('FLEXY:NAMEUSES')) {
            // force var to use name (as radio buttons pick up id.)

            $ename = $this->element->getAttribute('NAME');
            $printfnamevar = $printfvar = 'sprintf(\''.$ename .'\','.$this->element->toVar($var) .')';
            // support id replacement as well ...
            $idreplace = '';


            if (strtolower($this->element->getAttribute('TYPE')) == 'radio') {
                $ename = $this->element->getAttribute('ID');
                $printfvar = 'sprintf(\''.$ename .'\','.$this->element->toVar($var) .')';
            }


            if ($this->element->getAttribute('ID')) {
                $idvar     = 'sprintf(\''.$this->element->getAttribute('ID') .'\','.$this->element->toVar($var) .')';
                $idreplace = '$this->elements['.$printfvar.']->attributes[\'id\'] = '.$idvar.';';
            }
            return  $ret . '
                $_element = $this->mergeElement(
                    $this->elements[\''.$id.'\'],
                    isset('.$this->element->toVar($var).') && isset($this->elements['.$printfnamevar .']) ? $this->elements['.$printfnamevar .'] : false
                );
                $_element->attributes[\'name\'] = '.$printfnamevar. ';
                ' . $idreplace . '
                echo $_element->toHtml();' .$unset;

        }


        if ($mergeWithName) {
            $name = $this->element->getAttribute('NAME');
            //if ((strtolower($this->element->getAttribute('TYPE')) == 'checkbox') && (substr($name,-2) == '[]')) {
            //    $name = substr($name,0,-2);
            //}
            if (!$name) {
                return $ret .'
                    $_element = $this->elements[\''.$id.'\'];
                    echo  $_element->toHtml();' . $unset;
            } else {
                return  $ret .
                    '
                    $_element = $this->elements[\''.$id.'\'];
                    if (isset($this->elements[\''.$name.'\'])) {
                        $_element = $this->mergeElement($_element,$this->elements[\''.$name.'\']);
                    }
                    echo  $_element->toHtml();' . $unset;
            }

        }
        return $ret . 'echo $this->elements[\''.$id.'\']->toHtml();'. $unset;

    }

    /**
    * Reads an Script tag - check if PHP is allowed.
    *
    * @return   false|PEAR_Error
    * @access   public
    */
    function parseTagScript()
    {


        $lang = $this->element->getAttribute('LANGUAGE');
        if (!$lang) {
            return false;
        }
        $lang = strtoupper($lang);
        $allow = $GLOBALS['_FLY_FLEXY']['currentOptions']['allowPHP'];

        if ($allow === true) {

            return false;
        }

        if ($lang == "PHP") {
            if ($allow == 'delete') {
                return '';
            }
           return $this->_raiseErrorWithPositionAndTag('PHP code found in script (script)',
                FLY_FLEXY_ERROR_SYNTAX,FLY_FLEXY_ERROR_RETURN
            );
        }
        return false;

    }
    /**
    * Reads an Input tag - build a element object for it
    *
    *
    * @return   string | false = html output or ignore (just output the tag)
    * @access   public
    */


    function parseTagInput()
    {
        global $_FLY_FLEXY;

        if (in_array(strtoupper($this->element->getAttribute('TYPE')), array('SUBMIT','BUTTON','INPUT','')))  {
            $this->compiler->addStringToGettext($this->element->getAttribute('VALUE'));
        }
        // form elements : format:
        //value - fill out as PHP CODE

        // as a general rule, this uses name, rather than ID except on
        // radio
        $mergeWithName = false;
        $id = $this->element->getAttribute('NAME');


        if (isset($this->element->ucAttributes['FLEXY:RAW'])) {
            return $this->_raiseErrorWithPositionAndTag(
                    "Flexy:raw can only be used with flexy:ignore, to prevent conversion of html ".
                    "elements to flexy elements",
                    null, FLY_FLEXY_ERROR_DIE
            );
        }
        // checkboxes need more work.. - at the momemnt assume one with the same value...
        if (!in_array(strtoupper($this->element->getAttribute('TYPE')), array('RADIO'))) {
            if (!$id) {
                return false;
            }
            return $this->compiler->appendPhp($this->getElementPhp( $id,$mergeWithName));

        }
        // now we are only dealing with radio buttons.. which are a bit odd...

        // we need to create a generic holder for this based on the name..
        // this is only really available for use with setting stuff...

        if (!isset($_FLY_FLEXY['elements'][$id])) {
            $_FLY_FLEXY['elements'][$id] = new Fly_Flexy_Element("input",
                array('type'=>'radio'));

        }
        // we dont really care if it is getting reused loads of times.. (expected as each radio button will use it.
        $name = $id;
        $id = $this->element->getAttribute('ID');
        if (!$id) {
            $id = $name . '_' . $this->element->getAttribute('VALUE');
        }
        // this get's tricky as we could end up with elements with the same name... (if value was not set..,
        // or two elements have the same name..

        $mergeWithName = true;

        return $this->compiler->appendPhp($this->getElementPhp( $id,$mergeWithName));

    }

    /**
    * Deal with a TextArea tag - build a element object for it
    *
    * @return   string | false = html output or ignore (just output the tag)
    * @access   public
    */

    function parseTagTextArea()
    {

        return $this->compiler->appendPhp(
            $this->getElementPhp( $this->element->getAttribute('NAME')));



    }
    /**
    * Deal with Selects - build a element object for it (unless flexyignore is set)
    *
    *
    * @return   string | false = html output or ignore (just output the tag)
    * @access   public
    */

    function parseTagSelect()
    {
        return $this->compiler->appendPhp(
            $this->getElementPhp( $this->element->getAttribute('NAME')));
    }




     /**
    * Reads an Form tag - and set up the element object header etc.
    *
    * @return   string | false = html output or ignore (just output the tag)
    * @access   public
    */

    function parseTagForm()
    {
        global $_FLY_FLEXY;
        $copy = clone($this->element);
        $copy->children = array();
        $id = $this->element->getAttribute('NAME');
        // dont make forms dynamic if they dont have a name..
        if (!$id) {
            return false;
        }

        // this adds the element to the elements array.
        $old = clone($this->element);
        $this->element = $copy;
        list($prefix,$suffix) = $this->getElementPhp($id,false,true);
        $this->element= $old;


        return
            $this->compiler->appendPhp($prefix .'echo $this->elements[\''.$id.'\']->toHtmlnoClose();'.$suffix) .
            $this->element->compileChildren($this->compiler) .
            $this->compiler->appendHtml( "</{$copy->oTag}>");

    }





    /**
    * reWriteURL - can using the config option 'url_rewrite'
    *  format "from:to,from:to"
    * only handle left rewrite.
    * so
    *  "/images:/myroot/images"
    * would change
    *   /images/xyz.gif to /myroot/images/xyz.gif
    *   /images/stylesheet/imagestyles.css to  /myroot/images/stylesheet/imagestyles.css
    *   note /imagestyles did not get altered.
    * will only work on strings (forget about doing /images/{someimage}
    *
    *
    * @param    string attribute to rewrite
    * @return   none
    * @access   public
    */
    function reWriteURL($which)
    {
        global  $_FLY_FLEXY;


        if (!is_string($original = $this->element->getAttribute($which))) {
            return;
        }

        if ($original == '') {
            return;
        }

        if (empty($_FLY_FLEXY['currentOptions']['url_rewrite'])) {
            return;
        }

        $bits = explode(",",$_FLY_FLEXY['currentOptions']['url_rewrite']);
        $new = $original;

        foreach ($bits as $bit) {
            if (!strlen(trim($bit))) {
                continue;
            }
            $parts = explode (':', $bit);
            if (!isset($parts[1])) {
                return $this->_raiseErrorWithPositionAndTag('Fly_Flexy: url_rewrite syntax incorrect'.
                    print_r(array($bits,$bits),true),null,FLY_FLEXY_ERROR_DIE);
            }
            $new = preg_replace('#^'.$parts[0].'#',$parts[1], $new);
        }


        if ($original == $new) {
            return;
        }
        $this->element->ucAttributes[$which] = '"'. $new . '"';
    }

    /**
    * Convert flexy tokens to Fly_Flexy_Elements.
    *
    * @param    object token to convert into a element.
    * @return   object Fly_Flexy_Element
    * @access   public
    */
    function toElement($element,$stripspaces  = false)
    {
        require_once 'Fly/Flexy/Element.php';
        $ret = new Fly_Flexy_Element;

        if (get_class($element) != 'Fly_Flexy_Token_Tag') {
            $this->compiler->addStringToGettext($element->value);
            return $element->value;
        }


        $ret->tag = strtolower($element->tag);

        if ($ret->tag == 'menulist') {  // for XUL menulist, remove the white space between tags..
            $stripspaces = true;
        }

        $ats = $element->getAttributes();

        if (isset($element->attributes['flexy:xhtml'])) {
            $ats['flexy:xhtml'] = true;
        }

        foreach(array_keys($ats)  as $a) {
            $ret->attributes[$a] = $this->unHtmlEntities($ats[$a]);
        }
        //print_r($ats);
        if (!$element->children) {
            return $ret;
        }

        // children - normally to deal with <element>

        //print_r($this->children);
        foreach(array_keys($element->children) as $i) {
            // not quite sure why this happens - but it does.
            if (!is_object($element->children[$i])) {
                continue;
            }
            if ($stripspaces && (strtolower(get_class($element->children[$i])) != 'html_template_flexy_token_tag')) {
                continue;
            }
            $ret->children[] = $this->toElement($element->children[$i],$stripspaces);
        }
        return $ret;
    }

      /**
    * do the reverse of htmlspecialchars on an attribute..
    *
    * copied from get-html-translation-table man page
    *
    * @param   mixed       from attribute values
    *
    * @return   string          return
    * @access   public
    * @see      see also methods.....
    */

    function unHtmlEntities ($in)
    {
        if (!is_string($in)) {
            return $in;
        }
        $trans_tbl = get_html_translation_table (HTML_ENTITIES);
        $trans_tbl = array_flip ($trans_tbl);
        $ret = strtr ($in, $trans_tbl);
        return preg_replace('/&#(\d+);/me', "chr('\\1')",$ret);
    }


     /**
    * Deal with XUL tags
    *
    * @return   string | false = html output or ignore (just output the tag)
    * @access   public
    */

    function parseXulTag()
    {

        // does it contain any flexy tags??
        if ($this->elementUsesDynamic($this->element)) {
            return false;
        }

        return $this->compiler->appendPhp(
            $this->getElementPhp( $this->element->getAttribute('ID')));
    }

     /**
    * Recursively search for any flexy:if flexy:foreach or {xxxx} tags inside tags..
    *
    * @param    Fly_Flexy_Token   element to check.
    * @return   boolean true if it finds a  dynamic tag.
    * @access   public
    */


    function elementUsesDynamic($e)
    {
        if ($e instanceof Fly_Flexy_Token_Var) {
            return true;
        }
        if ($e instanceof Fly_Flexy_Token_Foreach) {
            return true;
        }
        if ($e instanceof Fly_Flexy_Token_If) {
            return true;
        }
        if ($e instanceof Fly_Flexy_Token_Method) {
            return true;
        }
        if (!($e instanceof Fly_Flexy_Token_Tag)) {
            return false;
        }
        if  ($e->getAttribute('FLEXY:IF')  !== false) {
            return true;
        }
        if  ($e->getAttribute('FLEXY:FOREACH')  !== false) {
            return true;
        }
        foreach($e->attributes as $k=>$v) {
            if (is_array($v) || is_object($v)) {
                return true;
            }
        }
        foreach($e->children as $c) {
            if ($this->elementUsesDynamic($c)) {
                return true;
            }
        }
        return false;



    }


    /**
    * calls Fly_Flexy::raiseError() with the current file, line and tag
    * @param    string  Message to display
    * @param    type   (see Fly_Flexy::raiseError())
    * @param    boolean  isFatal.
    *
    * @access   private
    */
    function _raiseErrorWithPositionAndTag($message, $type = null, $fatal = FLY_FLEXY_ERROR_RETURN ) {
        global $_FLY_FLEXY;
        $message = "Error:{$_FLY_FLEXY['filename']} on Line {$this->element->line}" .
                   " in Tag &lt;{$this->element->tag}&gt;:<BR>\n" . $message;
        return Fly_Flexy::raiseError($message, $type, $fatal);
    }


}