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
// | Original Authors: Alan Knowles <alan@akbkhome.com>                   |
// | Original Authors: Wolfram Kriesing <wolfram@kriesing.de>             |
// +----------------------------------------------------------------------+
//
/**
 * A Flexible Template engine - based on simpletemplate
 *
 * @abstract Long Description
 *  Have a look at the package description for details.
 *
 * usage:
 * $template = new Fly_Flexy($options);
 * $template->compiler('/name/of/template.html');
 * $data =new StdClass
 * $data->text = 'xxxx';
 * $template->outputObject($data,$elements)
 *
 * Notes:
 * $options can be blank if so, it is read from
 * PEAR::getStaticProperty('Fly_Flexy','options');
 *
 * the first argument to outputObject is an object (which could even be an
 * associateve array cast to an object) - I normally send it the controller class.
 * the seconde argument '$elements' is an array of Fly_Flexy_Elements
 * eg. array('name'=> new Fly_Flexy_Element('',array('value'=>'fred blogs'));
 *
 *
 *
 *
 */
/**
 *   @package    Fly_Flexy
 */
// prevent disaster when used with xdebug!
@ini_set('xdebug.max_nesting_level', 1000);
/*
* Global variable - used to store active options when compiling a template.
*/
$GLOBALS['_FLY_FLEXY'] = array();

define('FLY_FLEXY_ERROR_SYNTAX', - 1); // syntax error in template.
define('FLY_FLEXY_ERROR_INVALIDARGS', - 2); // bad arguments to methods.
define('FLY_FLEXY_ERROR_FILE', - 2); // file access problem
define('FLY_FLEXY_ERROR_RETURN', 1); // RETURN ERRORS
define('FLY_FLEXY_ERROR_DIE', 8); // FATAL DEATH

class Fly_Flexy
{
    /*
    *   @var    array   $options    the options for initializing the template class
    */
    public $options = array(
    	'compileDir' => '' , // where do you want to write to.. (defaults to session.save_path)
        'templateDir' => '' , // where are your templates
        // where the template comes from. ------------------------------------------
        'templateDirOrder' => '' , // set to 'reverse' to assume that first template
        'debug' => false , // prints a few messages
        // compiling conditions ------------------------------------------
        'compiler' => 'Flexy' , // which compiler to use. (Flexy,Regex, Raw,Xipe)
        'forceCompile' => false , // only suggested for debugging
        // regex Compiler       ------------------------------------------
        'filters' => array() , // used by regex compiler.
        // standard Compiler    ------------------------------------------
        'nonHTML' => false , // dont parse HTML tags (eg. email templates)
        'allowPHP' => false , // allow PHP in template (use true=allow, 'delete' = remove it.)
        'flexyIgnore' => 0 , // turn on/off the tag to element code
        'numberFormat' => ",2,'.',','" , // default number format  {xxx:n} format = eg. 1,200.00
        'url_rewrite' => '' , // url rewriting ability:
        // eg. "images/:test1/images/,js/:test1/js"
        // changes href="images/xxx" to href="test1/images/xxx"
        // and src="js/xxx.js" to src="test1/js/xxx.js"
        'compileToString' => false , // should the compiler return a string
        // rather than writing to a file.
        'privates' => false , // allow access to _variables (eg. suido privates
        'globals' => false , // allow access to _GET/_POST/_REQUEST/GLOBALS/_COOKIES/_SESSION
        'globalfunctions' => false , // allow GLOBALS.date(#d/m/Y#) to have access to all PHP's methods
        // warning dont use unless you trust the template authors
        // exec() becomes exposed.
        // get text/transalation suppport ------------------------------------------
        //  (flexy compiler only)
        'locale' => 'en' , // works with gettext or File_Gettext
        'textdomain' => '' , // for gettext emulation with File_Gettext
        // eg. 'messages' (or you can use the template name.
        'textdomainDir' => '' , // eg. /var/www/site.com/locale
        // so the french po file is:
        // /var/www/site.com/local/fr/LC_MESSAGE/{textdomain}.po
        'Translation2' => false , // to make Translation2 a provider.
        // rather than gettext.
        // set to:
        //  'Translation2' => array(
        //         'driver' => 'dataobjectsimple',
        //         'options' => array()
        //  );
        // or the slower way..
        //   = as it requires loading the code..
        //
        //  'Translation2' => new Translation2('dataobjectsimple','')
        'charset' => 'ISO-8859-1' , // charset used with htmlspecialchars to render data.
        // experimental
        // output options           ------------------------------------------
        'strict' => false , // All elements in the template must be defined -
        // makes php E_NOTICE warnings appear when outputing template.
        'fatalError' => FLY_FLEXY_ERROR_DIE , // default behavior is to die on errors in template.
        'plugins' => array(),
        // load classes to be made available via the plugin method
        // eg. = array('Savant') - loads the Savant methods.
        // = array('MyClass_Plugins' => 'MyClass/Plugins.php')
        //    Class, and where to include it from..
    );



    /**
     * The compiled template filename (Full path)
     *
     * @var string
     * @access public
     */
    public $compiledTemplate;

    /**
     * The source template filename (Full path)
     *
     * @var string
     * @access public
     */
    public $currentTemplate;

	/**
     * The getTextStrings Filename
     *
     * @var string
     * @access public
     */
    public $getTextStringsFile;

    /**
     * The serialized elements array file.
     *
     * @var string
     * @access public
     */
    public $elementsFile;

    /**
     * Array of HTML_elements which is displayed on the template
     *
     * Technically it's private (eg. only the template uses it..)
     *
     *
     * @var array of  Fly_Flexy_Elements
     * @access private
     */
    public $elements = array();

    /**
     *   Constructor
     *
     *   Initializes the Template engine, for each instance, accepts options or
     *   reads from PEAR::getStaticProperty('Fly_Flexy','options');
     *
     *   @access public
     *   @param    array    $options (Optional)
     */
    public function __construct ($options = array())
    {
        foreach ($options as $key => $aOption) {
            $this->options[$key] = $aOption;
        }
        $filters = $this->options['filters'];
        if (is_string($filters)) {
            $this->options['filters'] = explode(',', $filters);
        }
        if (is_string($this->options['templateDir'])) {
            $this->options['templateDir'] = explode(PATH_SEPARATOR, $this->options['templateDir']);
        }
    }
    /**
     *   compile the template
     *
     *   @access     public
     *   @param      string  $file   relative to the 'templateDir' which you set when calling the constructor
     *   @return     boolean true on success. (or string, if compileToString) PEAR_Error on failure..
     */
    function compile ($file)
    {
        if (! is_string($file) || ! (strlen($file) > 0)) {
            return $this->raiseError('Fly_Flexy::compile no file selected', FLY_FLEXY_ERROR_INVALIDARGS, FLY_FLEXY_ERROR_DIE);
        }

        if (! isset($this->options['compileDir'])) {
            return $this->raiseError("Please set option 'compileDir'", FLY_FLEXY_ERROR_FILE, FLY_FLEXY_ERROR_DIE);
        }
        if (! isset($this->options['locale']) || $this->options['locale']) {
            $this->options['locale'] = 'en';
        }
        //Remove the slash if there is one in front, just to be safe.
        $file = ltrim($file, '\\/' . DIRECTORY_SEPARATOR);
        if (strpos($file, '#')) {
            list ($file, $this->options['output.block']) = explode('#', $file);
        }

        // PART A mulitlanguage support: ( part B is gettext support in the engine..)
        //    - user created language version of template.
        //    - compile('abcdef.html') will check for compile('abcdef.en.html')
        //       (eg. when locale=en)
        $this->currentTemplate = false;
        clearstatcache();
        $parts = array();
        if (preg_match('/(.*)(\.[a-z]+)$/i', $file, $parts)) {
            $langFile = $parts[1] . '.' . $this->options['locale'] . $parts[2];
        }
        // look in all the posible locations for the template directory..
        $dirs = array_unique($this->options['templateDir']);
        if ($this->options['templateDirOrder'] == 'reverse') {
            $dirs = array_reverse($dirs);
        }
        foreach ($dirs as $tmplDir) {
            $tmplDir = realpath($tmplDir);
            $tmpl = $tmplDir . DIRECTORY_SEPARATOR . $file;
            $tmplLang = $tmplDir . DIRECTORY_SEPARATOR . $langFile;
            switch (true) {
                case is_file($tmplLang):
                    $this->currentTemplate = realpath($tmplLang);
                    $file = $langFile;
                    break;
                case is_file($tmpl):
                    $this->currentTemplate = realpath($tmpl);
                    break;
                default:
                    continue 2;
            }
            // check Directory traversal
            if (strncmp($this->currentTemplate, $tmplDir, strlen($tmplDir)) !== 0) {
                return $this->raiseError('Fly_Flexy::compile invalid filename:' . $file, FLY_FLEXY_ERROR_INVALIDARGS, FLY_FLEXY_ERROR_DIE);
            }
            // break if file is discovered
            break;
        }
        if ($this->currentTemplate === false) {
            // check if the compile dir has been created
            return $this->raiseError("Could not find Template {$file} in any of the directories<br>" . implode("<BR>", $this->options['templateDir']), FLY_FLEXY_ERROR_INVALIDARGS, FLY_FLEXY_ERROR_DIE);
        }
        // Savant compatible compiler
        if (is_string($this->options['compiler']) && ($this->options['compiler'] == 'Raw')) {
            $this->compiledTemplate = $this->currentTemplate;
            $this->debug("Using Raw Compiler");
            return true;
        }

        $compileDest = $this->options['compileDir'];

        // we generally just keep the directory structure as the application uses it,
        // so we dont get into conflict with names
        // if we have multi sources we do md5 the basedir..
        $base = $compileDest . DIRECTORY_SEPARATOR . $file;
        $fullFile = $this->compiledTemplate = $base . '.' . $this->options['locale'] . '.php';
        $this->getTextStringsFile = $base . '.gettext.serial';
        $this->elementsFile = $base . '.elements.serial';
        if (isset($this->options['output.block'])) {
            $this->compiledTemplate .= '#' . $this->options['output.block'];
        }
        $recompile = false;
        $isuptodate = file_exists($this->compiledTemplate) ? (filemtime($this->currentTemplate) == filemtime($this->compiledTemplate)) : 0;
        if (isset($this->options['forceCompile']) && $this->options['forceCompile'] || ! $isuptodate) {
            $recompile = true;
        } else {
            $this->debug("File looks like it is uptodate.");
            // use compiled template
            return true;
        }

        if (! is_dir($compileDest) || ! is_writeable($compileDest)) {
            require_once 'Fly/System.php';
            Fly_System::mkdir(array('-p' , $compileDest));
        }
        if (! is_dir($compileDest) || ! is_writeable($compileDest)) {
            return $this->raiseError("can not write to 'compileDir', which is <b>'$compileDest'</b><br>" . "Please give write and enter-rights to it", FLY_FLEXY_ERROR_FILE, FLY_FLEXY_ERROR_DIE);
        }
        if (! file_exists(dirname($this->compiledTemplate))) {
            require_once 'Fly/System.php';
            Fly_System::mkdir(array('-p' , '-m' , 0770 , dirname($this->compiledTemplate)));
        }
        // Compile the template in $file.
        require_once 'Fly/Flexy/Compiler.php';
        $compiler = Fly_Flexy_Compiler::factory($this->options);
        $ret = $compiler->compile($this);
        if ($ret instanceof PEAR_Error) {
            return $this->raiseError('Fly_Flexy fatal error:' . $ret->message, $ret->code, FLY_FLEXY_ERROR_DIE);
        }
        return $ret;
        //return $this->$method();
    }
    /**
     *  compiles all templates
     *  Used for offline batch compilation (eg. if your server doesn't have write access to the filesystem).
     *
     *   @access     public
     *
     */
    function compileAll ($dir = '', $regex = '/.html$/')
    {
        require_once 'Fly/Flexy/Compiler.php';
        $c = new Fly_Flexy_Compiler();
        $c->compileAll($this, $dir, $regex);
    }
    /**
     *   Outputs an object as $t
     *
     *   for example the using simpletags the object's variable $t->test
     *   would map to {test}
     *
     *   @version    01/12/14
     *   @access     public
     *   @param    object   to output
     *   @param    array  Fly_Flexy_Elements (or any object that implements toHtml())
     *   @return     none
     */
    public function outputObject (&$t, $elements = array())
    {
        if (! is_array($elements)) {
            return $this->raiseError('second Argument to Fly_Flexy::outputObject() was an ' . gettype($elements) . ', not an array', FLY_FLEXY_ERROR_INVALIDARGS, FLY_FLEXY_ERROR_DIE);
        }
        if (@$this->options['debug']) {
            echo "output $this->compiledTemplate<BR>";
        }
        $this->elements = $this->getElements();
        // Overlay values from $elements to $this->elements (which is created from the template)
        // Remove keys with no corresponding value.
        foreach ($elements as $k => $v) {
            // Remove key-value pair from $this->elements if hasn't a value in $elements.
            if (! $v) {
                unset($this->elements[$k]);
            }
            // Add key-value pair to $this->$elements if it's not there already.
            if (! isset($this->elements[$k])) {
                $this->elements[$k] = $v;
                continue;
            }
            // Call the clever element merger - that understands form values and
            // how to display them...
            $this->elements[$k] = $this->mergeElement($this->elements[$k], $v);
        }
        //echo '<PRE>'; print_r(array($elements,$this->elements));
        // we use PHP's error handler to hide errors in the template.
        // use $options['strict'] - if you want to force declaration of
        // all variables in the template
        $_error_reporting = error_reporting();
        if (! $this->options['strict']) {
            $_error_reporting = error_reporting($_error_reporting & ~E_NOTICE);
        }
        if (! is_readable($this->compiledTemplate)) {
            return $this->raiseError("Could not open the template: <b>'{$this->compiledTemplate}'</b><BR>" . "Please check the file permissions on the directory and file ", FLY_FLEXY_ERROR_FILE, FLY_FLEXY_ERROR_DIE);
        }
        // are we using the assign api!
        if (isset($this->assign)) {
            if (! $t) {
                $t = (object) $this->assign->variables;
            }
            extract($this->assign->variables);
            foreach (array_keys($this->assign->references) as $_k) {
                $$_k = &$this->assign->references[$_k];
            }
        }
        // used by Flexy Elements etc..
        $GLOBALS['_FLY_FLEXY']['options'] = $this->options;
        //TODO:ここでサンドボックスに入れる処理を書くか
        include ($this->compiledTemplate);
        // Return the error handler to its previous state.
        error_reporting($_error_reporting);
    }
    /**
     *   Outputs an object as $t, buffers the result and returns it.
     *
     *   See outputObject($t) for more details.
     *
     *   @version    01/12/14
     *   @access     public
     *   @param      object object to output as $t
     *   @return     string - result
     */
    public function bufferedOutputObject (&$t, $elements = array())
    {
        ob_start();
        $this->outputObject($t, $elements);
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }
    /**
     * static version which does new, compile and output all in one go.
     *
     *   See outputObject($t) for more details.
     *
     *   @version    01/12/14
     *   @access     public
     *   @param      object object to output as $t
     *   @param      filename of template
     *   @return     string - result
     */
    public static function &staticQuickTemplate ($file, &$t)
    {
        $template = new self();
        $template->compile($file);
        $template->outputObject($t);
    }

    /**
     *   if debugging is on, print the debug info to the screen
     *
     *   @access     public
     *   @param      string  $string       output to display
     *   @return     none
     */
    function debug ($string)
    {
        if ($this instanceof Fly_Flexy) {
            if (! $this->options['debug']) {
                return;
            }
        } else
            if (empty($GLOBALS['_FLY_FLEXY']['debug'])) {
                return;
            }
        echo "<PRE><B>FLEXY DEBUG:</B> $string</PRE>";
    }

    /**
     * A general Utility method that merges Fly_Flexy_Elements
     * Static method - no native debug avaiable..
     *
     * @param    Fly_Flexy_Element   $original  (eg. from getElements())
     * @param    Fly_Flexy_Element   $new (with data to replace/merge)
     * @return   Fly_Flexy_Element   the combined/merged data.
     * @static
     * @access   public
     */
    function mergeElement ($original, $new)
    {
        // If the properties of $original differ from those of $new and
        // they are set on $new, set them to $new's. Otherwise leave them
        // as they are.
        if ($new->tag && ($new->tag != $original->tag)) {
            $original->tag = $new->tag;
        }
        if ($new->override !== false) {
            $original->override = $new->override;
        }
        if (count($new->children)) {
            //echo "<PRE> COPY CHILDREN"; print_r($from->children);
            $original->children = $new->children;
        }
        if (is_array($new->attributes)) {
            foreach ($new->attributes as $key => $value) {
                $original->attributes[$key] = $value;
            }
        }
        // originals never have prefixes or suffixes..
        $original->prefix = $new->prefix;
        $original->suffix = $new->suffix;
        if ($new->value !== null) {
            $original->setValue($new->value);
        }
        return $original;
    }
    /**
     * Get an array of elements from the template
     *
     * All <form> elements (eg. <input><textarea) etc.) and anything marked as
     * dynamic  (eg. flexy:dynamic="yes") are converted in to elements
     * (simliar to XML_Tree_Node)
     * you can use this to build the default $elements array that is used by
     * outputObject() - or just create them and they will be overlayed when you
     * run outputObject()
     *
     *
     * @return   array   of Fly_Flexy_Element sDescription
     * @access   public
     */
    function getElements ()
    {
        if ($this->elementsFile && file_exists($this->elementsFile)) {
            require_once 'Fly/Flexy/Element.php';
            return unserialize(file_get_contents($this->elementsFile));
        }
        return array();
    }
    /**
     * Lazy loading of PEAR, and the error handler..
     * This should load Fly_Flexy_Error really..
     *
     * @param   string message
     * @param   int      error type.
     * @param   int      an equivalant to pear error return|die etc.
     *
     * @return   object      pear error.
     * @access   public
     */
    function raiseError ($message, $type = null, $fatal = FLY_FLEXY_ERROR_RETURN)
    {
        Fly_Flexy::debug("<B>Fly_Flexy::raiseError</B>$message");
        require_once 'PEAR.php';
        if (($this instanceof Fly_Flexy) && ($fatal == FLY_FLEXY_ERROR_DIE)) {
            // rewrite DIE!
            return PEAR::raiseError($message, $type, $this->options['fatalError']);
        }
        if (isset($GLOBALS['_FLY_FLEXY']['fatalError']) && ($fatal == FLY_FLEXY_ERROR_DIE)) {
            return PEAR::raiseError($message, $type, $GLOBALS['_FLY_FLEXY']['fatalError']);
        }
        return PEAR::raiseError($message, $type, $fatal);
    }
    /**
     *
     * Assign API -
     *
     * read the docs on Fly_Flexy_Assign::assign()
     *
     * @param   varargs ....
     *
     *
     * @return   mixed    PEAR_Error or true?
     * @access   public
     * @see  Fly_Flexy_Assign::assign()
     * @status alpha
     */
    function setData ()
    {
        require_once 'Fly/Flexy/Assign.php';
        // load assigner..
        if (! isset($this->assign)) {
            $this->assign = new Fly_Flexy_Assign();
        }
        return $this->assign->assign(func_get_args());
    }
    /**
     *
     * Assign API - by Reference
     *
     * read the docs on Fly_Flexy_Assign::assign()
     *
     * @param  key  string
     * @param  value mixed
     *
     * @return   mixed    PEAR_Error or true?
     * @access   public
     * @see  Fly_Flexy_Assign::assign()
     * @status alpha
     */
    function setDataByRef ($k, &$v)
    {
        require_once 'Fly/Flexy/Assign.php';
        // load assigner..
        if (! isset($this->assign)) {
            $this->assign = new Fly_Flexy_Assign();
        }
        $this->assign->assignRef($k, $v);
    }
    /**
     *
     * Plugin (used by templates as $this->plugin(...) or {this.plugin(#...#,#....#)}
     *
     * read the docs on Fly_Flexy_Plugin()
     *
     * @param  varargs ....
     *
     * @return   mixed    PEAR_Error or true?
     * @access   public
     * @see  Fly_Flexy_Plugin
     * @status alpha
     */
    function plugin ()
    {
        require_once 'Fly/Flexy/Plugin.php';
        // load pluginManager.
        if (! isset($this->plugin)) {
            $this->plugin = new Fly_Flexy_Plugin();
            $this->plugin->flexy = &$this;
        }
        return $this->plugin->call(func_get_args());
    }
    /**
     *
     * output / display ? - outputs an object, without copy by references..
     *
     * @param  optional mixed object to output
     *
     * @return   mixed    PEAR_Error or true?
     * @access   public
     * @see  Fly_Flexy::ouptutObject
     * @status alpha
     */
    function output ($object = false)
    {
        return $this->outputObject($object);
    }
    /**
     *
     * render the template with data..
     *
     * @param  optional mixed object to output
     *
     * @return   mixed    PEAR_Error or true?
     * @access   public
     * @see  Fly_Flexy::ouptutObject
     * @status alpha
     */
    function toString ($object = false)
    {
        return $this->bufferedOutputObject($object);
    }
    public function __toString()
    {
        return $this->toString();
    }
}
