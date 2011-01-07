<?php
// This Package is based upon PEAR::HTML_Template_Flexy (ver 1.3.9 (stable) released on 2009-03-24)
//  Please visit http://pear.php.net/package/Html_Template_Flexy
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
// | Authors:  Alan Knowles <alan@akbkhome.com>                           |
// +----------------------------------------------------------------------+
//
// $Id: $
//
//  The Source Lex file. (Tokenizer.lex) and the Generated one (Tokenizer.php)
// You should always work with the .lex file and generate by
//
// #mono phpLex/phpLex.exe Tokenizer.lex
// The lexer is available at http://sourceforge.net/projects/php-sharp/
// 
// or the equivialant .NET runtime on windows...
//
//  Note need to change a few of these defines, and work out
// how to modifiy the lexer to handle the changes..
//
define('FLY_FLEXY_TOKEN_NONE',1);
define('FLY_FLEXY_TOKEN_OK',2);
define('FLY_FLEXY_TOKEN_ERROR',3);
define("YYINITIAL"     ,0);
define("IN_SINGLEQUOTE"     ,   1) ;
define("IN_TAG"     ,           2)  ;
define("IN_ATTR"     ,          3);
define("IN_ATTRVAL"     ,       4) ;
define("IN_NETDATA"     ,       5);
define("IN_ENDTAG"     ,        6);
define("IN_DOUBLEQUOTE"     ,   7);
define("IN_MD"     ,            8);
define("IN_COM"     ,           9);
define("IN_DS",                 10);
define("IN_FLEXYMETHOD"     ,   11);
define("IN_FLEXYMETHODQUOTED"  ,12);
define("IN_FLEXYMETHODQUOTED_END" ,13);
define("IN_SCRIPT",             14);
define("IN_CDATA"     ,         15);
define("IN_DSCOM",              16);
define("IN_PHP",                17);
define("IN_COMSTYLE"     ,      18);
define("IN_METHODCHAIN", 19);
define('YY_E_INTERNAL', 0);
define('YY_E_MATCH',  1);
define('YY_BUFFER_SIZE', 4096);
define('YY_F' , -1);
define('YY_NO_STATE', -1);
define('YY_NOT_ACCEPT' ,  0);
define('YY_START' , 1);
define('YY_END' , 2);
define('YY_NO_ANCHOR' , 4);
define('YY_BOL' , 257);
define('YY_EOF' , 258);


class Fly_Flexy_Tokenizer
{

    /**
    * options array : meanings:
    *    ignore_html - return all tags as  text tokens
    *
    *
    * @var      boolean  public
    * @access   public
    */
    var $options = array(
        'ignore_html' => false,
        'token_factory'  => array('Fly_Flexy_Token','factory'),
    );
    /**
    * flag if inside a style tag. (so comments are ignored.. )
    *
    * @var boolean
    * @access private
    */
    var $inStyle = false;
    /**
    * the start position of a cdata block
    *
    * @var int
    * @access private
    */
    var $yyCdataBegin = 0;
     /**
    * the start position of a comment block
    *
    * @var int
    * @access private
    */
    var $yyCommentBegin = 0;
    /**
    * the name of the file being parsed (used by error messages)
    *
    * @var string
    * @access public
    */
    var $fileName;
    /**
    * the string containing an error if it occurs..
    *
    * @var string
    * @access public
    */
    var $error;
    /**
    * Flexible constructor
    *
    * @param   string       string to tokenize
    * @param   array        options array (see options above)       
    * 
    *
    * @return   Fly_Flexy_Tokenizer
    * @access   public
    */
    function &construct($data,$options= array()) 
    {
        $t = new Fly_Flexy_Tokenizer($data);
        foreach($options as $k=>$v) {
            if (is_object($v) || is_array($v)) {
                $t->options[$k] = &$v;
                continue;
            }
            $t->options[$k] = $v;
        }
        return $t;
    }
    /**
    * raise an error: = return an error token and set the error variable.
    *
    * 
    * @param   string           Error type
    * @param   string           Full Error message
    * @param   boolean          is it fatal..
    *
    * @return   int the error token.
    * @access   public
    */
    function raiseError($s,$n='',$isFatal=false) 
    {
        $this->error = "ERROR $n in File {$this->fileName} on Line {$this->yyline} Position:{$this->yy_buffer_end}: $s\n";
        return FLY_FLEXY_TOKEN_ERROR;
    }
    /**
    * return text
    *
    * Used mostly by the ignore HTML code. - really a macro :)
    *
    * @return   int   token ok.
    * @access   public
    */
    function returnSimple() 
    {
        $this->value = $this->createToken('TextSimple');
        return FLY_FLEXY_TOKEN_OK;
    }
    /**
    * Create a token based on the value of $this->options['token_call']
    *
    *
    * @return   Object   some kind of token..
    * @access   public
    */
    function createToken($token, $value = false, $line = false, $charPos = false) 
    {
        if ($value === false) {
            $value = $this->yytext();
        }
        if ($line === false) {
            $line = $this->yyline;
        }
        if ($charPos === false) {
            $charPos = $this->yy_buffer_start;
        }
        return call_user_func_array($this->options['token_factory'],array($token,$value,$line,$charPos));
    }


    var $yy_reader;
    var $yy_buffer_index;
    var $yy_buffer_read;
    var $yy_buffer_start;
    var $_fatal = false;
    var $yy_buffer_end;
    var $yy_buffer;
    var $yychar;
    var $yyline;
    var $yyEndOfLine;
    var $yy_at_bol;
    var $yy_lexical_state;

    function Fly_Flexy_Tokenizer($data) 
    {
        $this->yy_buffer = $data;
        $this->yy_buffer_read = strlen($data);
        $this->yy_buffer_index = 0;
        $this->yy_buffer_start = 0;
        $this->yy_buffer_end = 0;
        $this->yychar = 0;
        $this->yyline = 0;
        $this->yy_at_bol = true;
        $this->yy_lexical_state = YYINITIAL;
    }

    var $yy_state_dtrans = array  ( 
        0,
        240,
        37,
        139,
        265,
        266,
        267,
        268,
        57,
        273,
        276,
        278,
        300,
        314,
        315,
        323,
        87,
        89,
        91,
        363
    );


    function yybegin ($state)
    {
        $this->yy_lexical_state = $state;
    }



    function yy_advance ()
    {
        if ($this->yy_buffer_index < $this->yy_buffer_read) {
            return ord($this->yy_buffer{$this->yy_buffer_index++});
        }
        return YY_EOF;
    }


    function yy_move_end ()
    {
        if ($this->yy_buffer_end > $this->yy_buffer_start && 
            '\n' == $this->yy_buffer{$this->yy_buffer_end-1})
        {
            $this->yy_buffer_end--;
        }
        if ($this->yy_buffer_end > $this->yy_buffer_start &&
            '\r' == $this->yy_buffer{$this->yy_buffer_end-1})
        {
            $this->yy_buffer_end--;
        }
    }


    var $yy_last_was_cr=false;


    function yy_mark_start ()
    {
        for ($i = $this->yy_buffer_start; $i < $this->yy_buffer_index; $i++) {
            if ($this->yy_buffer{$i} == "\n" && !$this->yy_last_was_cr) {
                $this->yyline++; $this->yyEndOfLine = $this->yychar;
            }
            if ($this->yy_buffer{$i} == "\r") {
                $this->yyline++; $this->yyEndOfLine = $this->yychar;
                $this->yy_last_was_cr=true;
            } else {
                $this->yy_last_was_cr=false;
            }
        }
        $this->yychar = $this->yychar + $this->yy_buffer_index - $this->yy_buffer_start;
        $this->yy_buffer_start = $this->yy_buffer_index;
    }


    function yy_mark_end ()
    {
        $this->yy_buffer_end = $this->yy_buffer_index;
    }


    function  yy_to_mark ()
    {
        $this->yy_buffer_index = $this->yy_buffer_end;
        $this->yy_at_bol = ($this->yy_buffer_end > $this->yy_buffer_start) &&
            ($this->yy_buffer{$this->yy_buffer_end-1} == '\r' ||
            $this->yy_buffer{$this->yy_buffer_end-1} == '\n');
    }


    function yytext()
    {
        return substr($this->yy_buffer,$this->yy_buffer_start,$this->yy_buffer_end - $this->yy_buffer_start);
    }


    function yylength ()
    {
        return $this->yy_buffer_end - $this->yy_buffer_start;
    }


    var $yy_error_string = array(
        "Error: Internal error.\n",
        "Error: Unmatched input - \""
        );


    function yy_error ($code,$fatal)
    {
        if (method_exists($this,'raiseError')) { 
	        $this->_fatal = $fatal;
            $msg = $this->yy_error_string[$code];
            if ($code == 1) {
                $msg .= $this->yy_buffer[$this->yy_buffer_start] . "\"";
            }
 		    return $this->raiseError($msg, $code, $fatal); 
 		}
        echo $this->yy_error_string[$code];
        if ($fatal) {
            exit;
        }
    }


    var  $yy_acpt = array (
        /* 0 */   YY_NOT_ACCEPT,
        /* 1 */   YY_NO_ANCHOR,
        /* 2 */   YY_NO_ANCHOR,
        /* 3 */   YY_NO_ANCHOR,
        /* 4 */   YY_NO_ANCHOR,
        /* 5 */   YY_NO_ANCHOR,
        /* 6 */   YY_NO_ANCHOR,
        /* 7 */   YY_NO_ANCHOR,
        /* 8 */   YY_NO_ANCHOR,
        /* 9 */   YY_NO_ANCHOR,
        /* 10 */   YY_NO_ANCHOR,
        /* 11 */   YY_NO_ANCHOR,
        /* 12 */   YY_NO_ANCHOR,
        /* 13 */   YY_NO_ANCHOR,
        /* 14 */   YY_NO_ANCHOR,
        /* 15 */   YY_NO_ANCHOR,
        /* 16 */   YY_NO_ANCHOR,
        /* 17 */   YY_NO_ANCHOR,
        /* 18 */   YY_NO_ANCHOR,
        /* 19 */   YY_NO_ANCHOR,
        /* 20 */   YY_NO_ANCHOR,
        /* 21 */   YY_NO_ANCHOR,
        /* 22 */   YY_NO_ANCHOR,
        /* 23 */   YY_NO_ANCHOR,
        /* 24 */   YY_NO_ANCHOR,
        /* 25 */   YY_NO_ANCHOR,
        /* 26 */   YY_NO_ANCHOR,
        /* 27 */   YY_NO_ANCHOR,
        /* 28 */   YY_NO_ANCHOR,
        /* 29 */   YY_NO_ANCHOR,
        /* 30 */   YY_NO_ANCHOR,
        /* 31 */   YY_NO_ANCHOR,
        /* 32 */   YY_NO_ANCHOR,
        /* 33 */   YY_NO_ANCHOR,
        /* 34 */   YY_NO_ANCHOR,
        /* 35 */   YY_NO_ANCHOR,
        /* 36 */   YY_NO_ANCHOR,
        /* 37 */   YY_NO_ANCHOR,
        /* 38 */   YY_NO_ANCHOR,
        /* 39 */   YY_NO_ANCHOR,
        /* 40 */   YY_NO_ANCHOR,
        /* 41 */   YY_NO_ANCHOR,
        /* 42 */   YY_NO_ANCHOR,
        /* 43 */   YY_NO_ANCHOR,
        /* 44 */   YY_NO_ANCHOR,
        /* 45 */   YY_NO_ANCHOR,
        /* 46 */   YY_NO_ANCHOR,
        /* 47 */   YY_NO_ANCHOR,
        /* 48 */   YY_NO_ANCHOR,
        /* 49 */   YY_NO_ANCHOR,
        /* 50 */   YY_NO_ANCHOR,
        /* 51 */   YY_NO_ANCHOR,
        /* 52 */   YY_NO_ANCHOR,
        /* 53 */   YY_NO_ANCHOR,
        /* 54 */   YY_NO_ANCHOR,
        /* 55 */   YY_NO_ANCHOR,
        /* 56 */   YY_NO_ANCHOR,
        /* 57 */   YY_NO_ANCHOR,
        /* 58 */   YY_NO_ANCHOR,
        /* 59 */   YY_NO_ANCHOR,
        /* 60 */   YY_NO_ANCHOR,
        /* 61 */   YY_NO_ANCHOR,
        /* 62 */   YY_NO_ANCHOR,
        /* 63 */   YY_NO_ANCHOR,
        /* 64 */   YY_NO_ANCHOR,
        /* 65 */   YY_NO_ANCHOR,
        /* 66 */   YY_NO_ANCHOR,
        /* 67 */   YY_NO_ANCHOR,
        /* 68 */   YY_NO_ANCHOR,
        /* 69 */   YY_NO_ANCHOR,
        /* 70 */   YY_NO_ANCHOR,
        /* 71 */   YY_NO_ANCHOR,
        /* 72 */   YY_NO_ANCHOR,
        /* 73 */   YY_NO_ANCHOR,
        /* 74 */   YY_NO_ANCHOR,
        /* 75 */   YY_NO_ANCHOR,
        /* 76 */   YY_NO_ANCHOR,
        /* 77 */   YY_NO_ANCHOR,
        /* 78 */   YY_NO_ANCHOR,
        /* 79 */   YY_NO_ANCHOR,
        /* 80 */   YY_NO_ANCHOR,
        /* 81 */   YY_NO_ANCHOR,
        /* 82 */   YY_NO_ANCHOR,
        /* 83 */   YY_NO_ANCHOR,
        /* 84 */   YY_NO_ANCHOR,
        /* 85 */   YY_NO_ANCHOR,
        /* 86 */   YY_NO_ANCHOR,
        /* 87 */   YY_NO_ANCHOR,
        /* 88 */   YY_NO_ANCHOR,
        /* 89 */   YY_NO_ANCHOR,
        /* 90 */   YY_NO_ANCHOR,
        /* 91 */   YY_NO_ANCHOR,
        /* 92 */   YY_NO_ANCHOR,
        /* 93 */   YY_NO_ANCHOR,
        /* 94 */   YY_NO_ANCHOR,
        /* 95 */   YY_NO_ANCHOR,
        /* 96 */   YY_NO_ANCHOR,
        /* 97 */   YY_NO_ANCHOR,
        /* 98 */   YY_NOT_ACCEPT,
        /* 99 */   YY_NO_ANCHOR,
        /* 100 */   YY_NO_ANCHOR,
        /* 101 */   YY_NO_ANCHOR,
        /* 102 */   YY_NO_ANCHOR,
        /* 103 */   YY_NO_ANCHOR,
        /* 104 */   YY_NO_ANCHOR,
        /* 105 */   YY_NO_ANCHOR,
        /* 106 */   YY_NO_ANCHOR,
        /* 107 */   YY_NO_ANCHOR,
        /* 108 */   YY_NO_ANCHOR,
        /* 109 */   YY_NO_ANCHOR,
        /* 110 */   YY_NO_ANCHOR,
        /* 111 */   YY_NO_ANCHOR,
        /* 112 */   YY_NO_ANCHOR,
        /* 113 */   YY_NO_ANCHOR,
        /* 114 */   YY_NO_ANCHOR,
        /* 115 */   YY_NO_ANCHOR,
        /* 116 */   YY_NO_ANCHOR,
        /* 117 */   YY_NO_ANCHOR,
        /* 118 */   YY_NO_ANCHOR,
        /* 119 */   YY_NO_ANCHOR,
        /* 120 */   YY_NO_ANCHOR,
        /* 121 */   YY_NO_ANCHOR,
        /* 122 */   YY_NO_ANCHOR,
        /* 123 */   YY_NO_ANCHOR,
        /* 124 */   YY_NO_ANCHOR,
        /* 125 */   YY_NO_ANCHOR,
        /* 126 */   YY_NO_ANCHOR,
        /* 127 */   YY_NO_ANCHOR,
        /* 128 */   YY_NO_ANCHOR,
        /* 129 */   YY_NO_ANCHOR,
        /* 130 */   YY_NO_ANCHOR,
        /* 131 */   YY_NO_ANCHOR,
        /* 132 */   YY_NO_ANCHOR,
        /* 133 */   YY_NO_ANCHOR,
        /* 134 */   YY_NO_ANCHOR,
        /* 135 */   YY_NOT_ACCEPT,
        /* 136 */   YY_NO_ANCHOR,
        /* 137 */   YY_NO_ANCHOR,
        /* 138 */   YY_NO_ANCHOR,
        /* 139 */   YY_NO_ANCHOR,
        /* 140 */   YY_NO_ANCHOR,
        /* 141 */   YY_NO_ANCHOR,
        /* 142 */   YY_NO_ANCHOR,
        /* 143 */   YY_NO_ANCHOR,
        /* 144 */   YY_NO_ANCHOR,
        /* 145 */   YY_NO_ANCHOR,
        /* 146 */   YY_NO_ANCHOR,
        /* 147 */   YY_NOT_ACCEPT,
        /* 148 */   YY_NO_ANCHOR,
        /* 149 */   YY_NO_ANCHOR,
        /* 150 */   YY_NO_ANCHOR,
        /* 151 */   YY_NO_ANCHOR,
        /* 152 */   YY_NO_ANCHOR,
        /* 153 */   YY_NO_ANCHOR,
        /* 154 */   YY_NOT_ACCEPT,
        /* 155 */   YY_NO_ANCHOR,
        /* 156 */   YY_NO_ANCHOR,
        /* 157 */   YY_NOT_ACCEPT,
        /* 158 */   YY_NO_ANCHOR,
        /* 159 */   YY_NOT_ACCEPT,
        /* 160 */   YY_NO_ANCHOR,
        /* 161 */   YY_NOT_ACCEPT,
        /* 162 */   YY_NO_ANCHOR,
        /* 163 */   YY_NOT_ACCEPT,
        /* 164 */   YY_NO_ANCHOR,
        /* 165 */   YY_NOT_ACCEPT,
        /* 166 */   YY_NO_ANCHOR,
        /* 167 */   YY_NOT_ACCEPT,
        /* 168 */   YY_NO_ANCHOR,
        /* 169 */   YY_NOT_ACCEPT,
        /* 170 */   YY_NO_ANCHOR,
        /* 171 */   YY_NOT_ACCEPT,
        /* 172 */   YY_NO_ANCHOR,
        /* 173 */   YY_NOT_ACCEPT,
        /* 174 */   YY_NO_ANCHOR,
        /* 175 */   YY_NOT_ACCEPT,
        /* 176 */   YY_NOT_ACCEPT,
        /* 177 */   YY_NOT_ACCEPT,
        /* 178 */   YY_NOT_ACCEPT,
        /* 179 */   YY_NOT_ACCEPT,
        /* 180 */   YY_NOT_ACCEPT,
        /* 181 */   YY_NOT_ACCEPT,
        /* 182 */   YY_NOT_ACCEPT,
        /* 183 */   YY_NOT_ACCEPT,
        /* 184 */   YY_NOT_ACCEPT,
        /* 185 */   YY_NOT_ACCEPT,
        /* 186 */   YY_NOT_ACCEPT,
        /* 187 */   YY_NOT_ACCEPT,
        /* 188 */   YY_NOT_ACCEPT,
        /* 189 */   YY_NOT_ACCEPT,
        /* 190 */   YY_NOT_ACCEPT,
        /* 191 */   YY_NOT_ACCEPT,
        /* 192 */   YY_NOT_ACCEPT,
        /* 193 */   YY_NOT_ACCEPT,
        /* 194 */   YY_NOT_ACCEPT,
        /* 195 */   YY_NOT_ACCEPT,
        /* 196 */   YY_NOT_ACCEPT,
        /* 197 */   YY_NOT_ACCEPT,
        /* 198 */   YY_NOT_ACCEPT,
        /* 199 */   YY_NOT_ACCEPT,
        /* 200 */   YY_NOT_ACCEPT,
        /* 201 */   YY_NOT_ACCEPT,
        /* 202 */   YY_NOT_ACCEPT,
        /* 203 */   YY_NOT_ACCEPT,
        /* 204 */   YY_NOT_ACCEPT,
        /* 205 */   YY_NOT_ACCEPT,
        /* 206 */   YY_NOT_ACCEPT,
        /* 207 */   YY_NOT_ACCEPT,
        /* 208 */   YY_NOT_ACCEPT,
        /* 209 */   YY_NOT_ACCEPT,
        /* 210 */   YY_NOT_ACCEPT,
        /* 211 */   YY_NOT_ACCEPT,
        /* 212 */   YY_NOT_ACCEPT,
        /* 213 */   YY_NOT_ACCEPT,
        /* 214 */   YY_NOT_ACCEPT,
        /* 215 */   YY_NOT_ACCEPT,
        /* 216 */   YY_NOT_ACCEPT,
        /* 217 */   YY_NOT_ACCEPT,
        /* 218 */   YY_NOT_ACCEPT,
        /* 219 */   YY_NOT_ACCEPT,
        /* 220 */   YY_NOT_ACCEPT,
        /* 221 */   YY_NOT_ACCEPT,
        /* 222 */   YY_NOT_ACCEPT,
        /* 223 */   YY_NOT_ACCEPT,
        /* 224 */   YY_NOT_ACCEPT,
        /* 225 */   YY_NOT_ACCEPT,
        /* 226 */   YY_NOT_ACCEPT,
        /* 227 */   YY_NOT_ACCEPT,
        /* 228 */   YY_NOT_ACCEPT,
        /* 229 */   YY_NOT_ACCEPT,
        /* 230 */   YY_NOT_ACCEPT,
        /* 231 */   YY_NOT_ACCEPT,
        /* 232 */   YY_NOT_ACCEPT,
        /* 233 */   YY_NOT_ACCEPT,
        /* 234 */   YY_NOT_ACCEPT,
        /* 235 */   YY_NOT_ACCEPT,
        /* 236 */   YY_NOT_ACCEPT,
        /* 237 */   YY_NOT_ACCEPT,
        /* 238 */   YY_NOT_ACCEPT,
        /* 239 */   YY_NOT_ACCEPT,
        /* 240 */   YY_NOT_ACCEPT,
        /* 241 */   YY_NOT_ACCEPT,
        /* 242 */   YY_NOT_ACCEPT,
        /* 243 */   YY_NOT_ACCEPT,
        /* 244 */   YY_NOT_ACCEPT,
        /* 245 */   YY_NOT_ACCEPT,
        /* 246 */   YY_NOT_ACCEPT,
        /* 247 */   YY_NOT_ACCEPT,
        /* 248 */   YY_NOT_ACCEPT,
        /* 249 */   YY_NOT_ACCEPT,
        /* 250 */   YY_NOT_ACCEPT,
        /* 251 */   YY_NOT_ACCEPT,
        /* 252 */   YY_NOT_ACCEPT,
        /* 253 */   YY_NOT_ACCEPT,
        /* 254 */   YY_NOT_ACCEPT,
        /* 255 */   YY_NOT_ACCEPT,
        /* 256 */   YY_NOT_ACCEPT,
        /* 257 */   YY_NOT_ACCEPT,
        /* 258 */   YY_NOT_ACCEPT,
        /* 259 */   YY_NOT_ACCEPT,
        /* 260 */   YY_NOT_ACCEPT,
        /* 261 */   YY_NOT_ACCEPT,
        /* 262 */   YY_NOT_ACCEPT,
        /* 263 */   YY_NOT_ACCEPT,
        /* 264 */   YY_NOT_ACCEPT,
        /* 265 */   YY_NOT_ACCEPT,
        /* 266 */   YY_NOT_ACCEPT,
        /* 267 */   YY_NOT_ACCEPT,
        /* 268 */   YY_NOT_ACCEPT,
        /* 269 */   YY_NOT_ACCEPT,
        /* 270 */   YY_NOT_ACCEPT,
        /* 271 */   YY_NOT_ACCEPT,
        /* 272 */   YY_NOT_ACCEPT,
        /* 273 */   YY_NOT_ACCEPT,
        /* 274 */   YY_NOT_ACCEPT,
        /* 275 */   YY_NOT_ACCEPT,
        /* 276 */   YY_NOT_ACCEPT,
        /* 277 */   YY_NOT_ACCEPT,
        /* 278 */   YY_NOT_ACCEPT,
        /* 279 */   YY_NOT_ACCEPT,
        /* 280 */   YY_NOT_ACCEPT,
        /* 281 */   YY_NOT_ACCEPT,
        /* 282 */   YY_NOT_ACCEPT,
        /* 283 */   YY_NOT_ACCEPT,
        /* 284 */   YY_NOT_ACCEPT,
        /* 285 */   YY_NOT_ACCEPT,
        /* 286 */   YY_NOT_ACCEPT,
        /* 287 */   YY_NOT_ACCEPT,
        /* 288 */   YY_NOT_ACCEPT,
        /* 289 */   YY_NOT_ACCEPT,
        /* 290 */   YY_NOT_ACCEPT,
        /* 291 */   YY_NOT_ACCEPT,
        /* 292 */   YY_NOT_ACCEPT,
        /* 293 */   YY_NOT_ACCEPT,
        /* 294 */   YY_NOT_ACCEPT,
        /* 295 */   YY_NOT_ACCEPT,
        /* 296 */   YY_NOT_ACCEPT,
        /* 297 */   YY_NOT_ACCEPT,
        /* 298 */   YY_NOT_ACCEPT,
        /* 299 */   YY_NOT_ACCEPT,
        /* 300 */   YY_NOT_ACCEPT,
        /* 301 */   YY_NOT_ACCEPT,
        /* 302 */   YY_NOT_ACCEPT,
        /* 303 */   YY_NOT_ACCEPT,
        /* 304 */   YY_NOT_ACCEPT,
        /* 305 */   YY_NOT_ACCEPT,
        /* 306 */   YY_NOT_ACCEPT,
        /* 307 */   YY_NOT_ACCEPT,
        /* 308 */   YY_NOT_ACCEPT,
        /* 309 */   YY_NOT_ACCEPT,
        /* 310 */   YY_NOT_ACCEPT,
        /* 311 */   YY_NOT_ACCEPT,
        /* 312 */   YY_NOT_ACCEPT,
        /* 313 */   YY_NOT_ACCEPT,
        /* 314 */   YY_NOT_ACCEPT,
        /* 315 */   YY_NOT_ACCEPT,
        /* 316 */   YY_NOT_ACCEPT,
        /* 317 */   YY_NOT_ACCEPT,
        /* 318 */   YY_NOT_ACCEPT,
        /* 319 */   YY_NOT_ACCEPT,
        /* 320 */   YY_NOT_ACCEPT,
        /* 321 */   YY_NOT_ACCEPT,
        /* 322 */   YY_NOT_ACCEPT,
        /* 323 */   YY_NOT_ACCEPT,
        /* 324 */   YY_NOT_ACCEPT,
        /* 325 */   YY_NOT_ACCEPT,
        /* 326 */   YY_NOT_ACCEPT,
        /* 327 */   YY_NOT_ACCEPT,
        /* 328 */   YY_NOT_ACCEPT,
        /* 329 */   YY_NOT_ACCEPT,
        /* 330 */   YY_NOT_ACCEPT,
        /* 331 */   YY_NOT_ACCEPT,
        /* 332 */   YY_NOT_ACCEPT,
        /* 333 */   YY_NOT_ACCEPT,
        /* 334 */   YY_NOT_ACCEPT,
        /* 335 */   YY_NOT_ACCEPT,
        /* 336 */   YY_NOT_ACCEPT,
        /* 337 */   YY_NOT_ACCEPT,
        /* 338 */   YY_NOT_ACCEPT,
        /* 339 */   YY_NOT_ACCEPT,
        /* 340 */   YY_NOT_ACCEPT,
        /* 341 */   YY_NOT_ACCEPT,
        /* 342 */   YY_NOT_ACCEPT,
        /* 343 */   YY_NOT_ACCEPT,
        /* 344 */   YY_NOT_ACCEPT,
        /* 345 */   YY_NOT_ACCEPT,
        /* 346 */   YY_NOT_ACCEPT,
        /* 347 */   YY_NOT_ACCEPT,
        /* 348 */   YY_NOT_ACCEPT,
        /* 349 */   YY_NOT_ACCEPT,
        /* 350 */   YY_NOT_ACCEPT,
        /* 351 */   YY_NOT_ACCEPT,
        /* 352 */   YY_NOT_ACCEPT,
        /* 353 */   YY_NOT_ACCEPT,
        /* 354 */   YY_NOT_ACCEPT,
        /* 355 */   YY_NOT_ACCEPT,
        /* 356 */   YY_NOT_ACCEPT,
        /* 357 */   YY_NOT_ACCEPT,
        /* 358 */   YY_NOT_ACCEPT,
        /* 359 */   YY_NOT_ACCEPT,
        /* 360 */   YY_NOT_ACCEPT,
        /* 361 */   YY_NOT_ACCEPT,
        /* 362 */   YY_NOT_ACCEPT,
        /* 363 */   YY_NOT_ACCEPT,
        /* 364 */   YY_NOT_ACCEPT,
        /* 365 */   YY_NOT_ACCEPT,
        /* 366 */   YY_NOT_ACCEPT,
        /* 367 */   YY_NOT_ACCEPT,
        /* 368 */   YY_NOT_ACCEPT,
        /* 369 */   YY_NOT_ACCEPT,
        /* 370 */   YY_NOT_ACCEPT,
        /* 371 */   YY_NOT_ACCEPT,
        /* 372 */   YY_NOT_ACCEPT,
        /* 373 */   YY_NOT_ACCEPT,
        /* 374 */   YY_NOT_ACCEPT,
        /* 375 */   YY_NOT_ACCEPT,
        /* 376 */   YY_NOT_ACCEPT,
        /* 377 */   YY_NOT_ACCEPT,
        /* 378 */   YY_NOT_ACCEPT,
        /* 379 */   YY_NO_ANCHOR,
        /* 380 */   YY_NO_ANCHOR,
        /* 381 */   YY_NO_ANCHOR,
        /* 382 */   YY_NO_ANCHOR,
        /* 383 */   YY_NOT_ACCEPT,
        /* 384 */   YY_NOT_ACCEPT,
        /* 385 */   YY_NOT_ACCEPT,
        /* 386 */   YY_NOT_ACCEPT,
        /* 387 */   YY_NOT_ACCEPT,
        /* 388 */   YY_NOT_ACCEPT,
        /* 389 */   YY_NOT_ACCEPT,
        /* 390 */   YY_NOT_ACCEPT,
        /* 391 */   YY_NOT_ACCEPT,
        /* 392 */   YY_NOT_ACCEPT,
        /* 393 */   YY_NOT_ACCEPT,
        /* 394 */   YY_NOT_ACCEPT,
        /* 395 */   YY_NOT_ACCEPT,
        /* 396 */   YY_NOT_ACCEPT,
        /* 397 */   YY_NOT_ACCEPT,
        /* 398 */   YY_NOT_ACCEPT,
        /* 399 */   YY_NOT_ACCEPT,
        /* 400 */   YY_NOT_ACCEPT,
        /* 401 */   YY_NOT_ACCEPT,
        /* 402 */   YY_NOT_ACCEPT,
        /* 403 */   YY_NOT_ACCEPT,
        /* 404 */   YY_NOT_ACCEPT,
        /* 405 */   YY_NOT_ACCEPT,
        /* 406 */   YY_NOT_ACCEPT,
        /* 407 */   YY_NOT_ACCEPT,
        /* 408 */   YY_NOT_ACCEPT,
        /* 409 */   YY_NOT_ACCEPT,
        /* 410 */   YY_NOT_ACCEPT,
        /* 411 */   YY_NOT_ACCEPT,
        /* 412 */   YY_NOT_ACCEPT,
        /* 413 */   YY_NOT_ACCEPT,
        /* 414 */   YY_NOT_ACCEPT,
        /* 415 */   YY_NOT_ACCEPT,
        /* 416 */   YY_NOT_ACCEPT,
        /* 417 */   YY_NOT_ACCEPT,
        /* 418 */   YY_NOT_ACCEPT,
        /* 419 */   YY_NOT_ACCEPT,
        /* 420 */   YY_NOT_ACCEPT,
        /* 421 */   YY_NOT_ACCEPT,
        /* 422 */   YY_NOT_ACCEPT,
        /* 423 */   YY_NOT_ACCEPT,
        /* 424 */   YY_NOT_ACCEPT,
        /* 425 */   YY_NOT_ACCEPT,
        /* 426 */   YY_NOT_ACCEPT,
        /* 427 */   YY_NOT_ACCEPT,
        /* 428 */   YY_NOT_ACCEPT,
        /* 429 */   YY_NOT_ACCEPT,
        /* 430 */   YY_NOT_ACCEPT,
        /* 431 */   YY_NOT_ACCEPT,
        /* 432 */   YY_NOT_ACCEPT,
        /* 433 */   YY_NOT_ACCEPT,
        /* 434 */   YY_NOT_ACCEPT,
        /* 435 */   YY_NOT_ACCEPT,
        /* 436 */   YY_NOT_ACCEPT,
        /* 437 */   YY_NOT_ACCEPT,
        /* 438 */   YY_NOT_ACCEPT,
        /* 439 */   YY_NOT_ACCEPT,
        /* 440 */   YY_NOT_ACCEPT,
        /* 441 */   YY_NOT_ACCEPT,
        /* 442 */   YY_NOT_ACCEPT,
        /* 443 */   YY_NOT_ACCEPT,
        /* 444 */   YY_NOT_ACCEPT,
        /* 445 */   YY_NOT_ACCEPT,
        /* 446 */   YY_NOT_ACCEPT,
        /* 447 */   YY_NOT_ACCEPT,
        /* 448 */   YY_NOT_ACCEPT,
        /* 449 */   YY_NOT_ACCEPT,
        /* 450 */   YY_NOT_ACCEPT,
        /* 451 */   YY_NOT_ACCEPT,
        /* 452 */   YY_NOT_ACCEPT,
        /* 453 */   YY_NOT_ACCEPT,
        /* 454 */   YY_NOT_ACCEPT,
        /* 455 */   YY_NOT_ACCEPT,
        /* 456 */   YY_NOT_ACCEPT,
        /* 457 */   YY_NOT_ACCEPT,
        /* 458 */   YY_NOT_ACCEPT,
        /* 459 */   YY_NOT_ACCEPT,
        /* 460 */   YY_NOT_ACCEPT,
        /* 461 */   YY_NOT_ACCEPT,
        /* 462 */   YY_NOT_ACCEPT,
        /* 463 */   YY_NOT_ACCEPT,
        /* 464 */   YY_NOT_ACCEPT,
        /* 465 */   YY_NOT_ACCEPT,
        /* 466 */   YY_NOT_ACCEPT,
        /* 467 */   YY_NOT_ACCEPT,
        /* 468 */   YY_NOT_ACCEPT,
        /* 469 */   YY_NOT_ACCEPT,
        /* 470 */   YY_NOT_ACCEPT,
        /* 471 */   YY_NOT_ACCEPT,
        /* 472 */   YY_NOT_ACCEPT,
        /* 473 */   YY_NOT_ACCEPT,
        /* 474 */   YY_NOT_ACCEPT,
        /* 475 */   YY_NOT_ACCEPT,
        /* 476 */   YY_NOT_ACCEPT,
        /* 477 */   YY_NOT_ACCEPT,
        /* 478 */   YY_NOT_ACCEPT,
        /* 479 */   YY_NOT_ACCEPT,
        /* 480 */   YY_NOT_ACCEPT,
        /* 481 */   YY_NOT_ACCEPT,
        /* 482 */   YY_NOT_ACCEPT,
        /* 483 */   YY_NOT_ACCEPT,
        /* 484 */   YY_NOT_ACCEPT
        );


    var  $yy_cmap = array(
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 11, 5, 31, 31, 12, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        11, 14, 30, 2, 32, 25, 1, 29,
        33, 21, 32, 32, 53, 15, 7, 9,
        3, 3, 3, 3, 3, 45, 3, 56,
        3, 3, 10, 4, 8, 28, 13, 24,
        31, 19, 46, 17, 18, 6, 6, 6,
        6, 40, 6, 6, 6, 6, 6, 6,
        42, 6, 39, 35, 20, 6, 6, 6,
        6, 6, 6, 16, 26, 22, 31, 27,
        31, 51, 46, 37, 47, 50, 48, 6,
        52, 41, 6, 6, 55, 6, 54, 49,
        43, 6, 38, 36, 44, 6, 6, 6,
        6, 6, 6, 23, 31, 34, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 31, 31, 31, 31, 31, 31, 31,
        31, 0, 0 
         );


    var $yy_rmap = array(
        0, 1, 2, 3, 4, 5, 1, 6,
        7, 8, 9, 1, 10, 1, 11, 12,
        1, 3, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1,
        1, 13, 1, 1, 1, 14, 1, 1,
        1, 15, 16, 17, 1, 1, 18, 19,
        18, 1, 1, 1, 20, 1, 1, 21,
        1, 22, 1, 23, 24, 25, 1, 1,
        26, 27, 28, 29, 30, 1, 1, 31,
        32, 1, 33, 1, 1, 1, 1, 34,
        1, 1, 1, 35, 1, 36, 1, 37,
        1, 38, 1, 39, 40, 1, 1, 1,
        1, 1, 41, 42, 43, 1, 44, 45,
        1, 1, 46, 47, 48, 49, 50, 51,
        18, 52, 53, 54, 55, 56, 57, 58,
        59, 60, 61, 62, 1, 63, 1, 64,
        65, 66, 67, 68, 40, 69, 70, 71,
        72, 73, 74, 75, 76, 74, 77, 78,
        1, 79, 80, 81, 1, 82, 1, 1,
        83, 84, 85, 86, 87, 88, 89, 90,
        91, 92, 93, 94, 95, 96, 97, 98,
        99, 100, 101, 102, 103, 104, 105, 106,
        107, 108, 109, 110, 111, 112, 113, 114,
        115, 116, 117, 118, 119, 120, 121, 122,
        123, 124, 125, 126, 127, 128, 129, 130,
        131, 132, 133, 134, 135, 136, 137, 138,
        139, 140, 141, 142, 143, 144, 145, 146,
        147, 148, 149, 150, 151, 152, 153, 154,
        155, 156, 157, 158, 159, 160, 161, 162,
        163, 164, 165, 166, 167, 168, 169, 170,
        171, 72, 172, 173, 174, 175, 176, 177,
        178, 179, 180, 181, 182, 183, 184, 185,
        186, 187, 188, 189, 190, 191, 192, 193,
        16, 194, 195, 196, 197, 91, 198, 77,
        83, 199, 200, 63, 201, 202, 203, 93,
        95, 204, 97, 205, 206, 207, 208, 209,
        210, 211, 212, 213, 214, 215, 216, 217,
        218, 219, 220, 221, 222, 101, 223, 224,
        225, 226, 227, 228, 229, 230, 231, 232,
        233, 234, 235, 236, 237, 238, 239, 240,
        241, 242, 243, 244, 245, 246, 247, 248,
        249, 250, 251, 252, 253, 254, 255, 256,
        257, 258, 259, 260, 261, 262, 263, 264,
        265, 266, 267, 40, 268, 269, 270, 70,
        271, 272, 273, 274, 275, 276, 277, 278,
        279, 280, 281, 282, 283, 284, 285, 286,
        287, 288, 289, 290, 291, 292, 293, 294,
        295, 296, 297, 298, 76, 299, 300, 301,
        117, 302, 303, 304, 305, 306, 307, 308,
        309, 310, 311, 312, 313, 314, 315, 129,
        316, 317, 318, 319, 139, 320, 321, 322,
        323, 324, 325, 326, 151, 327, 328, 329,
        330, 331, 161, 332, 177, 333, 184, 334,
        206, 335, 213, 336, 224, 337, 230, 338,
        247, 339, 251, 340, 270, 341, 274, 342,
        286, 343, 290, 344, 345, 346, 323, 330,
        347, 348, 349, 350, 351, 352, 353, 354,
        355, 356, 357, 358, 359, 360, 361, 362,
        363, 364, 365, 366, 367, 368, 369, 370,
        371, 372, 373, 374, 375, 376, 377, 378,
        379, 380, 381, 382, 383 
        );


    var $yy_nxt = array(
        array( 1, 2, 3, 3, 3, 3, 3, 3,
            99, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 100, 379, 137,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, 98, 3, 3, 3, 4, 3,
            -1, 3, 3, 3, 3, 3, 3, 3,
            3, 4, 4, 4, 4, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 4, 4, 4, 4, 4,
            4, 4, 4, 4, 4, 3, 4, 4,
            4, 4, 4, 4, 4, 3, 4, 4,
            3 ),
        array( -1, 135, 3, 3, 3, 3, 3, 3,
            147, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, -1, 3, -1,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3 ),
        array( -1, -1, -1, 4, 101, 101, 4, 4,
            -1, -1, -1, -1, -1, -1, -1, 4,
            -1, 4, 4, 4, 4, -1, -1, -1,
            -1, -1, -1, 4, -1, -1, -1, -1,
            -1, -1, -1, 4, 4, 4, 4, 4,
            4, 4, 4, 4, 4, 4, 4, 4,
            4, 4, 4, 4, 4, -1, 4, 4,
            4 ),
        array( -1, -1, -1, 5, -1, 102, 5, 5,
            -1, -1, 5, 102, 102, -1, -1, 5,
            -1, 5, 5, 5, 5, -1, -1, -1,
            -1, -1, -1, 5, -1, -1, -1, -1,
            -1, -1, -1, 5, 5, 5, 5, 5,
            5, 5, 5, 5, 5, 5, 5, 5,
            5, 5, 5, 5, 5, -1, 5, 5,
            5 ),
        array( -1, -1, -1, -1, -1, 103, 15, -1,
            -1, -1, -1, 103, 103, -1, -1, -1,
            -1, 15, 15, 15, 15, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 15, 15, 15, 15, 15,
            15, 15, 15, 15, 15, -1, 15, 15,
            15, 15, 15, 15, 15, -1, 15, 15,
            -1 ),
        array( -1, -1, -1, 8, 104, 104, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 8, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            8 ),
        array( -1, -1, -1, 9, 105, 105, 9, 9,
            -1, -1, -1, -1, -1, -1, -1, 9,
            -1, 9, 9, 9, 9, -1, -1, -1,
            -1, -1, -1, 9, -1, -1, -1, -1,
            -1, -1, -1, 9, 9, 9, 9, 9,
            9, 9, 9, 9, 9, 9, 9, 9,
            9, 9, 9, 9, 9, -1, 9, 9,
            9 ),
        array( -1, -1, -1, 10, -1, 106, 10, 10,
            -1, 167, 10, 106, 106, -1, -1, 10,
            -1, 10, 10, 10, 10, -1, -1, -1,
            -1, -1, -1, 10, -1, -1, -1, -1,
            -1, -1, -1, 10, 10, 10, 10, 10,
            10, 10, 10, 10, 10, 10, 10, 10,
            10, 10, 10, 10, 10, -1, 10, 10,
            10 ),
        array( -1, -1, -1, 12, -1, 107, 12, 12,
            -1, -1, -1, 107, 107, -1, -1, 12,
            -1, 12, 12, 12, 12, -1, -1, -1,
            -1, -1, -1, 12, -1, -1, -1, -1,
            -1, -1, -1, 12, 12, 12, 12, 12,
            12, 12, 12, 12, 12, 12, 12, 12,
            12, 12, 12, 12, 12, -1, 12, 12,
            12 ),
        array( -1, -1, -1, -1, -1, 108, -1, -1,
            -1, -1, -1, 108, 108, -1, -1, -1,
            -1, 178, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 15, -1, 109, 15, 15,
            -1, -1, -1, 109, 109, -1, -1, 15,
            -1, 15, 15, 15, 15, -1, -1, -1,
            -1, -1, -1, 15, -1, -1, -1, -1,
            -1, -1, -1, 15, 15, 15, 15, 15,
            15, 15, 15, 15, 15, 15, 15, 15,
            15, 15, 15, 15, 15, -1, 15, 15,
            15 ),
        array( -1, 33, 33, 33, 33, 33, 33, 33,
            33, 33, 33, 33, 33, 33, 33, 33,
            33, 33, 33, 33, 33, 33, 33, -1,
            33, -1, 241, 33, 33, -1, 33, 33,
            33, 33, 33, 33, 33, 33, 33, 33,
            33, 33, 33, 33, 33, 33, 33, 33,
            33, 33, 33, 33, 33, 33, 33, 33,
            33 ),
        array( 1, 148, 148, 148, 148, 111, 148, 148,
            38, 148, 148, 111, 111, 39, 148, 155,
            148, 148, 148, 148, 148, 148, 148, 148,
            148, 148, 148, 148, 148, 148, 148, 148,
            148, 148, 148, 148, 148, 148, 148, 148,
            148, 148, 148, 148, 148, 148, 148, 148,
            148, 148, 148, 148, 148, 148, 148, 148,
            148 ),
        array( -1, -1, -1, 41, -1, 113, 41, 41,
            -1, -1, 41, 113, 113, -1, -1, 41,
            -1, 41, 41, 41, 41, -1, -1, -1,
            -1, -1, -1, 41, 43, -1, -1, -1,
            -1, -1, -1, 41, 41, 41, 41, 41,
            41, 41, 41, 41, 41, 41, 41, 41,
            41, 41, 41, 41, 41, -1, 41, 41,
            41 ),
        array( -1, -1, -1, -1, -1, 264, -1, -1,
            -1, -1, -1, 264, 264, 44, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, 43, -1, -1,
            -1, -1, -1, 43, 43, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, 46, 46, 46, 46, 114, 46, 46,
            46, 46, 46, 114, 114, -1, 46, 46,
            46, 46, 46, 46, 46, 46, 46, 46,
            46, 46, 46, 46, 46, -1, -1, 46,
            46, 46, 46, 46, 46, 46, 46, 46,
            46, 46, 46, 46, 46, 46, 46, 46,
            46, 46, 46, 46, 46, 46, 46, 46,
            46 ),
        array( -1, 46, 46, 47, 46, 115, 47, 47,
            46, 46, 46, 115, 115, -1, 46, 47,
            46, 47, 47, 47, 47, 46, 46, 46,
            46, 46, 46, 47, 46, -1, -1, 46,
            46, 46, 46, 47, 47, 47, 47, 47,
            47, 47, 47, 47, 47, 47, 47, 47,
            47, 47, 47, 47, 47, 46, 47, 47,
            47 ),
        array( -1, -1, -1, -1, -1, 52, -1, -1,
            -1, -1, -1, 52, 52, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, 55, 55, 55, 55, 55, 55, 55,
            55, 55, 55, 55, 55, 55, 55, 55,
            55, 55, 55, 55, 55, 55, 55, -1,
            55, -1, 269, 55, 55, 55, -1, 55,
            55, 55, 55, 55, 55, 55, 55, 55,
            55, 55, 55, 55, 55, 55, 55, 55,
            55, 55, 55, 55, 55, 55, 55, 55,
            55 ),
        array( 1, 58, 58, 59, 58, 117, 60, 61,
            58, 58, 58, 117, 117, 62, 58, 61,
            63, 60, 60, 60, 60, 58, 58, 58,
            58, 118, 58, 61, 58, 142, 152, 58,
            58, 58, 58, 60, 60, 60, 60, 60,
            60, 60, 60, 60, 60, 59, 60, 60,
            60, 60, 60, 60, 60, 58, 60, 60,
            59 ),
        array( -1, -1, -1, 59, -1, 119, 64, 64,
            -1, -1, -1, 119, 119, -1, -1, 64,
            -1, 64, 64, 64, 64, -1, -1, -1,
            -1, -1, -1, 64, -1, -1, -1, -1,
            -1, -1, -1, 64, 64, 64, 64, 64,
            64, 64, 64, 64, 64, 59, 64, 64,
            64, 64, 64, 64, 64, -1, 64, 64,
            59 ),
        array( -1, -1, -1, 60, -1, 120, 60, 60,
            -1, -1, -1, 120, 120, -1, -1, 60,
            -1, 60, 60, 60, 60, -1, -1, -1,
            -1, -1, -1, 60, -1, -1, -1, -1,
            -1, -1, -1, 60, 60, 60, 60, 60,
            60, 60, 60, 60, 60, 60, 60, 60,
            60, 60, 60, 60, 60, -1, 60, 60,
            60 ),
        array( -1, -1, -1, 61, -1, 121, 61, 61,
            -1, -1, -1, 121, 121, -1, -1, 61,
            -1, 61, 61, 61, 61, -1, -1, -1,
            -1, -1, -1, 61, -1, -1, -1, -1,
            -1, -1, -1, 61, 61, 61, 61, 61,
            61, 61, 61, 61, 61, 61, 61, 61,
            61, 61, 61, 61, 61, -1, 61, 61,
            61 ),
        array( -1, -1, -1, 64, -1, 122, 64, 64,
            -1, -1, -1, 122, 122, -1, -1, 64,
            -1, 64, 64, 64, 64, -1, -1, -1,
            -1, -1, -1, 64, -1, -1, -1, -1,
            -1, -1, -1, 64, 64, 64, 64, 64,
            64, 64, 64, 64, 64, 64, 64, 64,
            64, 64, 64, 64, 64, -1, 64, 64,
            64 ),
        array( -1, -1, -1, -1, -1, 65, -1, -1,
            -1, -1, -1, 65, 65, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 66, 123, 123, 66, 66,
            -1, -1, -1, 123, 123, -1, -1, 66,
            -1, 66, 66, 66, 66, -1, -1, -1,
            -1, -1, -1, 66, -1, -1, -1, -1,
            -1, -1, -1, 66, 66, 66, 66, 66,
            66, 66, 66, 66, 66, 66, 66, 66,
            66, 66, 66, 66, 66, -1, 66, 66,
            66 ),
        array( -1, -1, -1, -1, -1, 67, -1, -1,
            -1, -1, -1, 67, 67, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, 68, -1, -1,
            -1, -1, -1, 68, 68, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, 71, 71, 71, 71, 71, 71, 71,
            71, 71, 71, 71, 71, 71, 71, 71,
            71, 71, 71, 71, 71, 71, -1, 71,
            71, 71, 71, 71, 71, 71, 71, 71,
            71, 71, 71, 71, 71, 71, 71, 71,
            71, 71, 71, 71, 71, 71, 71, 71,
            71, 71, 71, 71, 71, 71, 71, 71,
            71 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 277, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 126, -1, -1,
            -1 ),
        array( -1, -1, -1, 79, -1, -1, 79, 302,
            -1, -1, -1, -1, -1, -1, -1, -1,
            303, 79, 79, 79, 79, -1, -1, -1,
            -1, 465, -1, 79, -1, -1, -1, -1,
            -1, -1, -1, 79, 79, 79, 79, 79,
            79, 79, 79, 79, 79, 79, 79, 79,
            79, 79, 79, 79, 79, -1, 79, 79,
            79 ),
        array( -1, 83, 83, 83, 83, 83, 83, 83,
            -1, 83, 83, 83, 83, 83, 83, 83,
            83, 83, 83, 83, 83, 83, 83, 83,
            83, 83, 83, 83, 83, 83, 83, 83,
            83, 83, 83, 83, 83, 83, 83, 83,
            83, 83, 83, 83, 83, 83, 83, 83,
            83, 83, 83, 83, 83, 83, 83, 83,
            83 ),
        array( -1, 85, 85, 85, 85, 85, 85, 85,
            85, 85, 85, 85, 85, 85, 85, 85,
            85, 85, 85, 85, 85, 85, -1, -1,
            85, 85, 85, 85, 85, 85, 85, 85,
            85, 85, 85, 85, 85, 85, 85, 85,
            85, 85, 85, 85, 85, 85, 85, 85,
            85, 85, 85, 85, 85, 85, 85, 85,
            85 ),
        array( 1, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 174,
            130, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130,
            130 ),
        array( 1, 131, 131, 131, 131, 131, 131, 131,
            131, 131, 131, 131, 131, 131, 131, 131,
            131, 131, 131, 131, 131, 131, 131, 131,
            344, 131, 131, 131, 131, 131, 131, 131,
            131, 131, 131, 131, 131, 131, 131, 131,
            131, 131, 131, 131, 131, 131, 131, 131,
            131, 131, 131, 131, 131, 131, 131, 131,
            131 ),
        array( 1, 92, 92, 92, 92, 132, 92, 92,
            92, 92, 92, 132, 132, 92, 92, 133,
            92, 92, 92, 92, 92, 92, 92, 146,
            92, 92, 92, 92, 92, 92, 92, 92,
            92, 92, 92, 92, 92, 92, 92, 92,
            92, 92, 92, 92, 92, 92, 92, 92,
            92, 92, 92, 92, 92, 92, 92, 92,
            92 ),
        array( -1, 145, 145, 145, 145, 145, 145, 145,
            145, 145, 145, 145, 145, 145, 145, -1,
            145, 145, 145, 145, 145, 145, 145, 145,
            145, 145, 145, 145, 145, 145, 145, 145,
            145, 145, 145, 145, 145, 145, 145, 145,
            145, 145, 145, 145, 145, 145, 145, 145,
            145, 145, 145, 145, 145, 145, 145, 145,
            145 ),
        array( -1, -1, -1, 8, -1, -1, 9, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 9, 9, 9, 9, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 9, 9, 9, 9, 9,
            9, 9, 9, 9, 9, 8, 9, 9,
            9, 9, 9, 9, 9, -1, 9, 9,
            8 ),
        array( -1, -1, -1, -1, -1, 3, 5, -1,
            -1, 154, -1, 3, 3, 6, 157, -1,
            3, 5, 5, 5, 5, -1, 3, 3,
            7, -1, 3, 3, -1, -1, -1, 3,
            -1, -1, 3, 5, 5, 5, 5, 5,
            5, 5, 5, 5, 5, -1, 5, 5,
            5, 5, 5, 5, 5, -1, 5, 5,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 159, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, 102, -1, -1,
            -1, -1, -1, 102, 102, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, 103, -1, -1,
            -1, -1, -1, 103, 103, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, 106, -1, -1,
            -1, 167, -1, 106, 106, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, 107, -1, -1,
            -1, -1, -1, 107, 107, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, 108, -1, -1,
            -1, -1, -1, 108, 108, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, 109, -1, -1,
            -1, -1, -1, 109, 109, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, 242, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 242, 242, 242, 242, -1, -1, -1,
            -1, -1, -1, 243, -1, -1, -1, -1,
            -1, -1, -1, 242, 242, 242, 242, 242,
            242, 242, 242, 242, 242, -1, 242, 242,
            242, 242, 242, 242, 242, -1, 242, 242,
            -1 ),
        array( -1, -1, -1, -1, -1, 111, -1, -1,
            -1, -1, -1, 111, 111, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, 113, -1, -1,
            -1, -1, -1, 113, 113, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, 43, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, 114, -1, -1,
            -1, -1, -1, 114, 114, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, 115, -1, -1,
            -1, -1, -1, 115, 115, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, 242, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 242, 242, 242, 242, -1, -1, -1,
            -1, -1, -1, 270, -1, -1, -1, -1,
            -1, -1, -1, 242, 242, 242, 242, 242,
            242, 242, 242, 242, 242, -1, 242, 242,
            242, 242, 242, 242, 242, -1, 242, 242,
            -1 ),
        array( -1, -1, -1, -1, -1, 117, -1, -1,
            -1, -1, -1, 117, 117, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, 65, 66, -1,
            -1, -1, -1, 65, 65, -1, -1, -1,
            -1, 66, 66, 66, 66, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 66, 66, 66, 66, 66,
            66, 66, 66, 66, 66, -1, 66, 66,
            66, 66, 66, 66, 66, -1, 66, 66,
            -1 ),
        array( -1, -1, -1, -1, -1, 119, -1, -1,
            -1, -1, -1, 119, 119, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, 120, -1, -1,
            -1, -1, -1, 120, 120, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, 121, -1, -1,
            -1, -1, -1, 121, 121, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, 122, -1, -1,
            -1, -1, -1, 122, 122, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, 123, -1, -1,
            -1, -1, -1, 123, 123, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 70, -1, 275,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, 302,
            -1, -1, -1, -1, -1, -1, -1, -1,
            303, -1, -1, -1, -1, -1, -1, -1,
            -1, 465, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 316, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 153, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 342,
            130, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130,
            130 ),
        array( -1, 131, 131, 131, 131, 131, 131, 131,
            131, 131, 131, 131, 131, 131, 131, 131,
            131, 131, 131, 131, 131, 131, 131, 131,
            -1, 131, 131, 131, 131, 131, 131, 131,
            131, 131, 131, 131, 131, 131, 131, 131,
            131, 131, 131, 131, 131, 131, 131, 131,
            131, 131, 131, 131, 131, 131, 131, 131,
            131 ),
        array( -1, 145, 145, 145, 145, 145, 145, 145,
            145, 145, 145, 145, 145, 145, 145, 345,
            145, 145, 145, 145, 145, 145, 145, 145,
            145, 145, 145, 145, 145, 145, 145, 145,
            145, 145, 145, 145, 145, 145, 145, 145,
            145, 145, 145, 145, 145, 145, 145, 145,
            145, 145, 145, 145, 145, 145, 145, 145,
            145 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 94, -1, 351,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 3, 3, 3, -1, 3,
            -1, 3, 3, 3, 3, 3, 3, 3,
            3, -1, -1, -1, -1, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 3, -1, -1,
            -1, -1, -1, -1, -1, 3, -1, -1,
            3 ),
        array( -1, 33, 33, 33, 33, 33, 33, 33,
            33, 33, 33, 33, 33, 33, 33, 33,
            33, 33, 33, 33, 33, 33, 33, 33,
            33, 33, 33, 33, 33, -1, 33, 33,
            33, 33, 33, 33, 33, 33, 33, 33,
            33, 33, 33, 33, 33, 33, 33, 33,
            33, 33, 33, 33, 33, 33, 33, 33,
            33 ),
        array( -1, -1, -1, -1, -1, -1, 161, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 161, 161, 161, 161, -1, -1, -1,
            -1, -1, -1, 163, -1, -1, -1, -1,
            -1, -1, -1, 161, 161, 161, 161, 161,
            161, 383, 161, 161, 161, -1, 161, 161,
            483, 161, 444, 161, 161, -1, 161, 473,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            244 ),
        array( 1, 148, 148, 148, 148, 111, 41, 148,
            38, 42, 148, 111, 111, 39, 148, 155,
            148, 41, 41, 41, 41, 148, 148, 148,
            158, 148, 148, 148, 148, 148, 148, 148,
            148, 148, 148, 41, 41, 41, 41, 41,
            41, 41, 41, 41, 41, 148, 41, 41,
            41, 41, 41, 41, 41, 148, 41, 41,
            148 ),
        array( -1, 46, 46, 140, 46, 115, 140, 140,
            46, 46, 46, 115, 115, -1, 46, 140,
            46, 140, 140, 140, 140, 46, 46, 46,
            46, 46, 46, 140, 46, -1, -1, 46,
            46, 46, 46, 140, 140, 140, 140, 140,
            140, 140, 140, 140, 140, 140, 140, 140,
            140, 140, 140, 140, 140, 46, 140, 140,
            140 ),
        array( -1, 271, 271, 271, 271, 271, 271, 271,
            271, 271, 271, 271, 271, 271, 271, 271,
            271, 271, 271, 271, 271, 271, 271, 271,
            271, 271, 271, 271, 271, 67, 271, 271,
            271, 271, 271, 271, 271, 271, 271, 271,
            271, 271, 271, 271, 271, 271, 271, 271,
            271, 271, 271, 271, 271, 271, 271, 271,
            271 ),
        array( -1, -1, -1, -1, -1, -1, 324, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 324, 324, 324, 324, -1, -1, -1,
            -1, -1, -1, 324, -1, -1, -1, -1,
            -1, -1, -1, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, -1, 324, 324,
            484, 324, 449, 324, 324, -1, 324, 474,
            -1 ),
        array( -1, 347, 347, 347, 347, 132, 347, 347,
            347, 347, 347, 132, 132, 347, 347, 347,
            347, 347, 347, 347, 347, 347, 347, -1,
            347, 347, 347, 347, 347, 347, 347, 347,
            347, 347, 347, 347, 347, 347, 347, 347,
            347, 347, 347, 347, 347, 347, 347, 347,
            347, 347, 347, 347, 347, 347, 347, 347,
            347 ),
        array( -1, -1, -1, -1, -1, -1, 346, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 346, 346, 346, 346, -1, -1, -1,
            -1, -1, -1, 346, -1, -1, -1, -1,
            -1, -1, -1, 346, 346, 346, 346, 346,
            346, 346, 346, 346, 346, -1, 346, 346,
            346, 346, 346, 346, 346, -1, 346, 346,
            -1 ),
        array( -1, -1, -1, -1, -1, 3, -1, -1,
            -1, -1, -1, 3, 3, -1, -1, -1,
            3, -1, -1, -1, -1, -1, 3, 3,
            -1, -1, 3, 3, -1, -1, -1, 3,
            -1, -1, 3, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, 135, 3, 3, 3, 3, 3, 3,
            147, 3, 3, 3, 3, 17, 3, 3,
            3, 3, 3, 3, 3, -1, 3, -1,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3 ),
        array( -1, 272, 272, 272, 272, 272, 272, 272,
            272, 272, 272, 272, 272, 272, 272, 272,
            272, 272, 272, 272, 272, 272, 272, 272,
            272, 272, 272, 272, 272, 272, 124, 272,
            272, 272, 272, 272, 272, 272, 272, 272,
            272, 272, 272, 272, 272, 272, 272, 272,
            272, 272, 272, 272, 272, 272, 272, 272,
            272 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 86, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, 165, 10, -1,
            -1, 167, -1, 165, 165, 11, -1, -1,
            -1, 10, 10, 10, 10, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 10, 10, 10, 10, 10,
            10, 10, 10, 10, 10, -1, 10, 10,
            10, 10, 10, 10, 10, -1, 10, 10,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 263,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, 68, 68, 68, 68, 68, 68, 68,
            68, 68, 68, 68, 68, 68, 68, 274,
            68, 68, 68, 68, 68, 68, 68, 68,
            68, 68, 68, 68, 68, 68, 68, 68,
            68, 68, 68, 68, 68, 68, 68, 68,
            68, 68, 68, 68, 68, 68, 68, 68,
            68, 68, 68, 68, 68, 68, 68, 68,
            68 ),
        array( -1, -1, -1, -1, -1, -1, 12, -1,
            -1, -1, -1, -1, -1, 13, -1, 169,
            14, 12, 12, 12, 12, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 12, 12, 12, 12, 12,
            12, 12, 12, 12, 12, -1, 12, 12,
            12, 12, 12, 12, 12, -1, 12, 12,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 45, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 16, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, 55, 55, 55, 55, 55, 55, 55,
            55, 55, 55, 55, 55, 55, 55, 55,
            55, 55, 55, 55, 55, 55, 55, 55,
            55, 55, -1, 55, 55, 55, -1, 55,
            55, 55, 55, 55, 55, 55, 55, 55,
            55, 55, 55, 55, 55, 55, 55, 55,
            55, 55, 55, 55, 55, 55, 55, 55,
            55 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, -1, 161, 161,
            161 ),
        array( -1, 279, 74, 279, 279, 279, 279, 279,
            279, 279, 279, 279, 279, 279, 279, 279,
            279, 279, 279, 279, 279, 279, 279, 279,
            279, 279, 279, 279, 279, 279, 279, 279,
            279, 279, 279, 279, 279, 279, 279, 279,
            279, 279, 279, 279, 279, 279, 279, 279,
            279, 279, 279, 279, 279, 279, 279, 279,
            279 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 20, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, 280, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 281, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 280, -1, -1,
            -1, -1, -1, -1, -1, 75, -1, -1,
            280 ),
        array( -1, -1, -1, -1, -1, 165, -1, -1,
            -1, 167, -1, 165, 165, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 282, -1, -1, 282, 283,
            -1, -1, -1, -1, -1, -1, -1, -1,
            284, 282, 282, 282, 282, 285, -1, -1,
            -1, 463, -1, 282, -1, -1, -1, -1,
            -1, -1, -1, 282, 282, 282, 282, 282,
            282, 282, 282, 282, 282, 282, 282, 282,
            282, 282, 282, 282, 282, 76, 282, 282,
            282 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            21, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, 77,
            -1, -1, 286, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 78, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 22,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, 301, 80, 301, 301, 301, 301, 301,
            301, 301, 301, 301, 301, 301, 301, 301,
            301, 301, 301, 301, 301, 301, 301, 301,
            301, 301, 301, 301, 301, 301, 301, 301,
            301, 301, 301, 301, 301, 301, 301, 301,
            301, 301, 301, 301, 301, 301, 301, 301,
            301, 301, 301, 301, 301, 301, 301, 301,
            301 ),
        array( -1, -1, -1, -1, -1, -1, 179, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 179, 179, 179, 179, -1, -1, -1,
            -1, -1, -1, 179, -1, -1, -1, -1,
            -1, -1, -1, 179, 179, 179, 179, 179,
            179, 179, 179, 179, 179, -1, 179, 179,
            179, 179, 179, 179, 179, -1, 179, 179,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 304, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 305, -1, -1, -1, -1, -1, -1,
            -1, -1, 81, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, 180, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 180, 180, 180, 180, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 180, 180, 180, 180, 180,
            180, 180, 180, 180, 180, -1, 180, 180,
            180, 180, 180, 180, 180, -1, 180, 180,
            -1 ),
        array( -1, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 343,
            130, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130,
            130 ),
        array( -1, -1, -1, 181, -1, -1, 181, -1,
            -1, -1, -1, -1, -1, -1, -1, 181,
            -1, 181, 181, 181, 181, -1, -1, -1,
            -1, -1, -1, 181, -1, -1, -1, -1,
            -1, -1, -1, 181, 181, 181, 181, 181,
            181, 181, 181, 181, 181, 181, 181, 181,
            181, 181, 181, 181, 181, -1, 181, 181,
            181 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 182, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 183, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 185, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 179, -1, -1, 179, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            186, 179, 179, 179, 179, -1, -1, -1,
            -1, 187, -1, 179, -1, -1, -1, -1,
            -1, 18, 19, 179, 179, 179, 179, 179,
            179, 179, 179, 179, 179, 179, 179, 179,
            179, 179, 179, 179, 179, -1, 179, 179,
            179 ),
        array( -1, -1, -1, -1, -1, -1, 180, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 180, 180, 180, 180, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 19, 180, 180, 180, 180, 180,
            180, 180, 180, 180, 180, -1, 180, 180,
            180, 180, 180, 180, 180, -1, 180, 180,
            -1 ),
        array( -1, -1, -1, 181, -1, -1, 181, -1,
            -1, -1, -1, -1, -1, -1, -1, 181,
            -1, 181, 181, 181, 181, -1, 188, -1,
            -1, 189, -1, 181, -1, -1, -1, -1,
            -1, -1, -1, 181, 181, 181, 181, 181,
            181, 181, 181, 181, 181, 181, 181, 181,
            181, 181, 181, 181, 181, -1, 181, 181,
            181 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 175, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, 190, -1,
            -1, -1, -1, -1, -1, -1, 191, -1,
            -1, 190, 190, 190, 190, -1, -1, -1,
            -1, -1, -1, 190, -1, -1, -1, -1,
            -1, -1, -1, 190, 190, 190, 190, 190,
            190, 190, 190, 190, 190, -1, 190, 190,
            190, 190, 190, 190, 190, -1, 190, 190,
            -1 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 192, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, -1, 161, 477,
            161 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 194, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 195, -1, -1, 195, -1,
            -1, -1, -1, -1, -1, -1, -1, 195,
            -1, 195, 195, 195, 195, -1, -1, -1,
            -1, -1, -1, 195, -1, -1, -1, -1,
            -1, -1, -1, 195, 195, 195, 195, 195,
            195, 195, 195, 195, 195, 195, 195, 195,
            195, 195, 195, 195, 195, -1, 195, 195,
            195 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 385, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            175, -1, -1, -1, -1, -1, -1, -1,
            -1, 176, -1, -1, -1, -1, -1, -1,
            -1, -1, 19, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 196, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 190, -1, -1, 190, 197,
            -1, -1, -1, -1, -1, -1, -1, -1,
            198, 190, 190, 190, 190, -1, -1, -1,
            -1, 445, -1, 190, -1, -1, -1, -1,
            -1, 23, 24, 190, 190, 190, 190, 190,
            190, 190, 190, 190, 190, 190, 190, 190,
            190, 190, 190, 190, 190, -1, 190, 190,
            190 ),
        array( -1, -1, -1, -1, -1, -1, 190, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 190, 190, 190, 190, -1, -1, -1,
            -1, -1, -1, 190, -1, -1, -1, -1,
            -1, -1, -1, 190, 190, 190, 190, 190,
            190, 190, 190, 190, 190, -1, 190, 190,
            190, 190, 190, 190, 190, -1, 190, 190,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, 180, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 180, 180, 180, 180, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 25, 180, 180, 180, 180, 180,
            180, 180, 180, 180, 180, -1, 180, 180,
            180, 180, 180, 180, 180, -1, 180, 180,
            -1 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 199, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, 200, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 195, -1, -1, 195, -1,
            -1, -1, -1, -1, -1, -1, -1, 195,
            -1, 195, 195, 195, 195, -1, 201, -1,
            -1, 202, -1, 195, -1, -1, -1, -1,
            -1, -1, -1, 195, 195, 195, 195, 195,
            195, 195, 195, 195, 195, 195, 195, 195,
            195, 195, 195, 195, 195, -1, 195, 195,
            195 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 188, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 188,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, 203, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 203, 203, 203, 203, -1, -1, -1,
            -1, -1, -1, 203, -1, -1, -1, -1,
            -1, -1, -1, 203, 203, 203, 203, 203,
            203, 203, 203, 203, 203, -1, 203, 203,
            203, 203, 203, 203, 203, -1, 203, 203,
            -1 ),
        array( -1, -1, -1, 204, -1, -1, 204, -1,
            -1, -1, -1, -1, -1, -1, -1, 204,
            -1, 204, 204, 204, 204, -1, -1, -1,
            -1, -1, -1, 204, -1, -1, -1, -1,
            -1, -1, -1, 204, 204, 204, 204, 204,
            204, 204, 204, 204, 204, 204, 204, 204,
            204, 204, 204, 204, 204, -1, 204, 204,
            204 ),
        array( -1, -1, -1, -1, -1, -1, 180, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 180, 180, 180, 180, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 26, 180, 180, 180, 180, 180,
            180, 180, 180, 180, 180, -1, 180, 180,
            180, 180, 180, 180, 180, -1, 180, 180,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 206, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            186, -1, -1, -1, -1, -1, -1, -1,
            -1, 187, -1, -1, -1, -1, -1, -1,
            -1, -1, 19, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 207, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 203, -1, -1, 203, 197,
            -1, -1, -1, -1, -1, -1, -1, -1,
            208, 203, 203, 203, 203, -1, -1, -1,
            -1, 451, -1, 203, -1, -1, -1, -1,
            -1, 23, 24, 203, 203, 203, 203, 203,
            203, 203, 203, 203, 203, 203, 203, 203,
            203, 203, 203, 203, 203, -1, 203, 203,
            203 ),
        array( -1, -1, -1, 204, -1, -1, 204, -1,
            -1, -1, -1, -1, -1, -1, -1, 204,
            -1, 204, 204, 204, 204, -1, 209, -1,
            -1, 210, -1, 204, -1, -1, -1, -1,
            -1, -1, -1, 204, 204, 204, 204, 204,
            204, 204, 204, 204, 204, 204, 204, 204,
            204, 204, 204, 204, 204, -1, 204, 204,
            204 ),
        array( -1, -1, -1, 205, -1, -1, 205, 212,
            -1, -1, -1, -1, -1, -1, -1, -1,
            446, 205, 205, 205, 205, -1, -1, -1,
            -1, 454, -1, 205, -1, -1, -1, -1,
            -1, -1, 27, 205, 205, 205, 205, 205,
            205, 205, 205, 205, 205, 205, 205, 205,
            205, 205, 205, 205, 205, -1, 205, 205,
            205 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            28, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 201, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 201,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 213, -1, -1, 213, -1,
            -1, -1, -1, -1, -1, -1, -1, 213,
            -1, 213, 213, 213, 213, -1, -1, -1,
            -1, -1, -1, 213, -1, -1, -1, -1,
            -1, -1, -1, 213, 213, 213, 213, 213,
            213, 213, 213, 213, 213, 213, 213, 213,
            213, 213, 213, 213, 213, -1, 213, 213,
            213 ),
        array( -1, -1, -1, -1, -1, -1, -1, 197,
            -1, -1, -1, -1, -1, -1, -1, -1,
            198, -1, -1, -1, -1, -1, -1, -1,
            -1, 445, -1, -1, -1, -1, -1, -1,
            -1, 23, 24, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 214, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 389, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, -1, -1, -1, 216, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 216, 216, 216, 216, -1, -1, -1,
            -1, -1, -1, 216, -1, -1, -1, -1,
            -1, -1, -1, 216, 216, 216, 216, 216,
            216, 216, 216, 216, 216, -1, 216, 216,
            216, 216, 216, 216, 216, -1, 216, 216,
            -1 ),
        array( -1, -1, -1, 213, -1, -1, 213, -1,
            -1, -1, -1, -1, -1, -1, -1, 213,
            -1, 213, 213, 213, 213, -1, 217, -1,
            -1, 218, -1, 213, -1, -1, -1, -1,
            -1, -1, -1, 213, 213, 213, 213, 213,
            213, 213, 213, 213, 213, 213, 213, 213,
            213, 213, 213, 213, 213, -1, 213, 213,
            213 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 209, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 209,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, 180, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 180, 180, 180, 180, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 29, 180, 180, 180, 180, 180,
            180, 180, 180, 180, 180, -1, 180, 180,
            180, 180, 180, 180, 180, -1, 180, 180,
            -1 ),
        array( -1, -1, -1, 216, -1, -1, 216, 212,
            -1, -1, -1, -1, -1, -1, -1, -1,
            220, 216, 216, 216, 216, -1, -1, -1,
            -1, 457, -1, 216, -1, -1, -1, -1,
            -1, -1, 27, 216, 216, 216, 216, 216,
            216, 216, 216, 216, 216, 216, 216, 216,
            216, 216, 216, 216, 216, -1, 216, 216,
            216 ),
        array( -1, -1, -1, -1, -1, -1, -1, 197,
            -1, -1, -1, -1, -1, -1, -1, -1,
            208, -1, -1, -1, -1, -1, -1, -1,
            -1, 451, -1, -1, -1, -1, -1, -1,
            -1, 23, 24, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 222, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 219, -1, -1, 219, 223,
            -1, -1, -1, -1, -1, -1, -1, -1,
            447, 219, 219, 219, 219, -1, -1, -1,
            -1, 460, -1, 219, -1, -1, -1, -1,
            -1, -1, 30, 219, 219, 219, 219, 219,
            219, 219, 219, 219, 219, 219, 219, 219,
            219, 219, 219, 219, 219, 392, 219, 219,
            219 ),
        array( -1, -1, -1, 224, -1, -1, 224, -1,
            -1, -1, -1, -1, -1, -1, -1, 224,
            -1, 224, 224, 224, 224, -1, -1, -1,
            -1, -1, -1, 224, -1, -1, -1, -1,
            -1, -1, -1, 224, 224, 224, 224, 224,
            224, 224, 224, 224, 224, 224, 224, 224,
            224, 224, 224, 224, 224, -1, 224, 224,
            224 ),
        array( -1, -1, -1, -1, -1, -1, -1, 212,
            -1, -1, -1, -1, -1, -1, -1, -1,
            446, -1, -1, -1, -1, -1, -1, -1,
            -1, 454, -1, -1, -1, -1, -1, -1,
            -1, -1, 27, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 217, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 217,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, 226, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 226, 226, 226, 226, -1, -1, -1,
            -1, -1, -1, 226, -1, -1, -1, -1,
            -1, -1, -1, 226, 226, 226, 226, 226,
            226, 226, 226, 226, 226, -1, 226, 226,
            226, 226, 226, 226, 226, -1, 226, 226,
            -1 ),
        array( -1, -1, -1, 224, -1, -1, 224, -1,
            -1, -1, -1, -1, -1, -1, -1, 224,
            -1, 224, 224, 224, 224, -1, 228, -1,
            -1, 229, -1, 224, -1, -1, -1, -1,
            -1, -1, -1, 224, 224, 224, 224, 224,
            224, 224, 224, 224, 224, 224, 224, 224,
            224, 224, 224, 224, 224, -1, 224, 224,
            224 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 221, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 221,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 226, -1, -1, 226, 223,
            -1, -1, -1, -1, -1, -1, -1, -1,
            230, 226, 226, 226, 226, -1, -1, -1,
            -1, 462, -1, 226, -1, -1, -1, -1,
            -1, -1, 30, 226, 226, 226, 226, 226,
            226, 226, 226, 226, 226, 226, 226, 226,
            226, 226, 226, 226, 226, 392, 226, 226,
            226 ),
        array( -1, -1, -1, 227, -1, -1, 227, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 227, 227, 227, 227, -1, -1, -1,
            -1, -1, -1, 227, -1, -1, -1, -1,
            -1, -1, 31, 227, 227, 227, 227, 227,
            227, 227, 227, 227, 227, 227, 227, 227,
            227, 227, 227, 227, 227, 232, 227, 227,
            227 ),
        array( -1, -1, -1, -1, -1, -1, -1, 212,
            -1, -1, -1, -1, -1, -1, -1, -1,
            220, -1, -1, -1, -1, -1, -1, -1,
            -1, 457, -1, -1, -1, -1, -1, -1,
            -1, -1, 27, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 233, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 234, -1, -1, 234, -1,
            -1, -1, -1, -1, -1, -1, -1, 234,
            -1, 234, 234, 234, 234, -1, -1, -1,
            -1, -1, -1, 234, -1, -1, -1, -1,
            -1, -1, -1, 234, 234, 234, 234, 234,
            234, 234, 234, 234, 234, 234, 234, 234,
            234, 234, 234, 234, 234, -1, 234, 234,
            234 ),
        array( -1, -1, -1, -1, -1, -1, -1, 223,
            -1, -1, -1, -1, -1, -1, -1, -1,
            447, -1, -1, -1, -1, -1, -1, -1,
            -1, 460, -1, -1, -1, -1, -1, -1,
            -1, -1, 30, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 392, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, 236, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 236, 236, 236, 236, -1, -1, -1,
            -1, -1, -1, 236, -1, -1, -1, -1,
            -1, -1, -1, 236, 236, 236, 236, 236,
            236, 236, 236, 236, 236, -1, 236, 236,
            236, 236, 236, 236, 236, -1, 236, 236,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 228, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 228,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 234, -1, -1, 234, -1,
            -1, -1, -1, -1, -1, -1, -1, 234,
            -1, 234, 234, 234, 234, -1, 237, -1,
            -1, 238, -1, 234, -1, -1, -1, -1,
            -1, -1, -1, 234, 234, 234, 234, 234,
            234, 234, 234, 234, 234, 234, 234, 234,
            234, 234, 234, 234, 234, -1, 234, 234,
            234 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 231, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 231,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 236, -1, -1, 236, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 236, 236, 236, 236, -1, -1, -1,
            -1, -1, -1, 236, -1, -1, -1, -1,
            -1, -1, 32, 236, 236, 236, 236, 236,
            236, 236, 236, 236, 236, 236, 236, 236,
            236, 236, 236, 236, 236, -1, 236, 236,
            236 ),
        array( -1, -1, -1, -1, -1, -1, -1, 223,
            -1, -1, -1, -1, -1, -1, -1, -1,
            230, -1, -1, -1, -1, -1, -1, -1,
            -1, 462, -1, -1, -1, -1, -1, -1,
            -1, -1, 30, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 392, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 239, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 237, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 237,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( 1, 33, 33, 33, 33, 33, 33, 33,
            33, 33, 33, 33, 33, 33, 33, 33,
            33, 33, 33, 33, 33, 33, 33, 110,
            33, 138, 136, 33, 33, 34, 33, 33,
            33, 33, 33, 33, 33, 33, 33, 33,
            33, 33, 33, 33, 33, 33, 33, 33,
            33, 33, 33, 33, 33, 33, 33, 33,
            33 ),
        array( -1, -1, -1, 242, -1, -1, 242, 245,
            -1, -1, 246, -1, -1, -1, -1, -1,
            247, 242, 242, 242, 242, -1, -1, -1,
            -1, 248, -1, 242, -1, -1, -1, -1,
            -1, 35, 36, 242, 242, 242, 242, 242,
            242, 242, 242, 242, 242, 242, 242, 242,
            242, 242, 242, 242, 242, -1, 242, 242,
            242 ),
        array( -1, -1, -1, 242, -1, -1, 242, 245,
            -1, -1, 246, -1, -1, -1, -1, -1,
            247, 242, 242, 242, 242, -1, -1, -1,
            -1, 248, -1, 242, -1, -1, -1, -1,
            -1, 150, 36, 242, 242, 242, 242, 242,
            242, 242, 242, 242, 242, 242, 242, 242,
            242, 242, 242, 242, 242, -1, 242, 242,
            242 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 249, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, 250, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 250, 250, 250, 250, -1, -1, -1,
            -1, -1, -1, 250, -1, -1, -1, -1,
            -1, -1, -1, 250, 250, 250, 250, 250,
            250, 250, 250, 250, 250, -1, 250, 250,
            250, 250, 250, 250, 250, -1, 250, 250,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, 251, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 251, 251, 251, 251, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 251, 251, 251, 251, 251,
            251, 251, 251, 251, 251, -1, 251, 251,
            251, 251, 251, 251, 251, -1, 251, 251,
            -1 ),
        array( -1, -1, -1, 252, -1, -1, 252, -1,
            -1, -1, -1, -1, -1, -1, -1, 252,
            -1, 252, 252, 252, 252, -1, -1, -1,
            -1, -1, -1, 252, -1, -1, -1, -1,
            -1, -1, -1, 252, 252, 252, 252, 252,
            252, 252, 252, 252, 252, 252, 252, 252,
            252, 252, 252, 252, 252, -1, 252, 252,
            252 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 421, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            253 ),
        array( -1, -1, -1, -1, -1, -1, 242, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 242, 242, 242, 242, -1, -1, -1,
            -1, -1, -1, 242, -1, -1, -1, -1,
            -1, -1, -1, 242, 242, 242, 242, 242,
            242, 242, 242, 242, 242, -1, 242, 242,
            242, 242, 242, 242, 242, -1, 242, 242,
            -1 ),
        array( -1, -1, -1, 250, -1, -1, 250, 245,
            -1, -1, 246, -1, -1, -1, -1, -1,
            254, 250, 250, 250, 250, -1, -1, -1,
            -1, 448, -1, 250, -1, -1, -1, -1,
            -1, 35, 36, 250, 250, 250, 250, 250,
            250, 250, 250, 250, 250, 250, 250, 250,
            250, 250, 250, 250, 250, -1, 250, 250,
            250 ),
        array( -1, -1, -1, -1, -1, -1, 251, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 251, 251, 251, 251, -1, -1, -1,
            -1, 255, -1, -1, -1, -1, -1, -1,
            -1, -1, 36, 251, 251, 251, 251, 251,
            251, 251, 251, 251, 251, -1, 251, 251,
            251, 251, 251, 251, 251, -1, 251, 251,
            -1 ),
        array( -1, -1, -1, 252, -1, -1, 252, -1,
            -1, -1, -1, -1, -1, -1, -1, 252,
            -1, 252, 252, 252, 252, -1, 256, -1,
            -1, 257, -1, 252, -1, -1, -1, -1,
            -1, -1, -1, 252, 252, 252, 252, 252,
            252, 252, 252, 252, 252, 252, 252, 252,
            252, 252, 252, 252, 252, -1, 252, 252,
            252 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 36, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 36,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 258, -1, -1, 258, -1,
            -1, -1, -1, -1, -1, -1, -1, 258,
            -1, 258, 258, 258, 258, -1, -1, -1,
            -1, -1, -1, 258, -1, -1, -1, -1,
            -1, -1, -1, 258, 258, 258, 258, 258,
            258, 258, 258, 258, 258, 258, 258, 258,
            258, 258, 258, 258, 258, -1, 258, 258,
            258 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            253 ),
        array( -1, -1, -1, -1, -1, -1, -1, 245,
            -1, -1, 246, -1, -1, -1, -1, -1,
            247, -1, -1, -1, -1, -1, -1, -1,
            -1, 248, -1, -1, -1, -1, -1, -1,
            -1, -1, 36, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 259, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 258, -1, -1, 258, -1,
            -1, -1, -1, -1, -1, -1, -1, 258,
            -1, 258, 258, 258, 258, -1, 260, -1,
            -1, 261, -1, 258, -1, -1, -1, -1,
            -1, -1, -1, 258, 258, 258, 258, 258,
            258, 258, 258, 258, 258, 258, 258, 258,
            258, 258, 258, 258, 258, -1, 258, 258,
            258 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 256, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 256,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, 245,
            -1, -1, 246, -1, -1, -1, -1, -1,
            254, -1, -1, -1, -1, -1, -1, -1,
            -1, 448, -1, -1, -1, -1, -1, -1,
            -1, -1, 36, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 262, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 260, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 260,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 40, -1, 263,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( 1, 46, 46, 47, 46, -1, 380, 380,
            112, 48, 46, 148, -1, 49, 46, 380,
            46, 380, 380, 380, 380, 46, 46, 46,
            46, 46, 46, 380, 46, 50, 51, 46,
            46, 46, 46, 380, 380, 380, 380, 380,
            380, 380, 380, 380, 380, 47, 380, 380,
            380, 380, 380, 380, 380, 46, 380, 380,
            47 ),
        array( 1, 148, 148, 148, 148, 52, 148, 148,
            148, 148, 148, 52, 52, 148, 148, 148,
            148, 148, 148, 148, 148, 148, 148, 148,
            148, 148, 148, 148, 148, 148, 148, 148,
            148, 148, 148, 148, 148, 148, 148, 148,
            148, 148, 148, 148, 148, 148, 148, 148,
            148, 148, 148, 148, 148, 148, 148, 148,
            148 ),
        array( 1, 53, 53, 53, 53, -1, 53, 53,
            53, 53, 53, 53, -1, 54, 53, 53,
            53, 53, 53, 53, 53, 53, 53, 53,
            53, 53, 53, 53, 53, 53, 53, 53,
            53, 53, 53, 53, 53, 53, 53, 53,
            53, 53, 53, 53, 53, 53, 53, 53,
            53, 53, 53, 53, 53, 53, 53, 53,
            53 ),
        array( 1, 55, 55, 55, 55, 55, 55, 55,
            55, 55, 55, 55, 55, 55, 55, 55,
            55, 55, 55, 55, 55, 55, 55, 116,
            55, 141, 160, 55, 55, 55, 56, 55,
            55, 55, 55, 55, 55, 55, 55, 55,
            55, 55, 55, 55, 55, 55, 55, 55,
            55, 55, 55, 55, 55, 55, 55, 55,
            55 ),
        array( -1, -1, -1, 242, -1, -1, 242, 245,
            -1, -1, 246, -1, -1, -1, -1, -1,
            247, 242, 242, 242, 242, -1, -1, -1,
            -1, 248, -1, 242, -1, -1, -1, -1,
            -1, 151, 36, 242, 242, 242, 242, 242,
            242, 242, 242, 242, 242, 242, 242, 242,
            242, 242, 242, 242, 242, -1, 242, 242,
            242 ),
        array( 1, 68, 68, 68, 68, 68, 68, 68,
            68, 68, 68, 68, 68, 68, 68, 156,
            68, 68, 68, 68, 68, 68, 68, 68,
            68, 68, 68, 68, 68, 68, 68, 68,
            68, 68, 68, 68, 68, 68, 68, 68,
            68, 68, 68, 68, 68, 68, 68, 68,
            68, 68, 68, 68, 68, 68, 68, 68,
            68 ),
        array( -1, 69, 69, 69, 69, 69, 69, 69,
            69, 69, 69, 69, 69, 70, 69, 125,
            69, 69, 69, 69, 69, 69, 69, 69,
            69, 69, 69, 69, 69, 69, 69, 69,
            69, 69, 69, 69, 69, 69, 69, 69,
            69, 69, 69, 69, 69, 69, 69, 69,
            69, 69, 69, 69, 69, 69, 69, 69,
            69 ),
        array( 1, 71, 71, 71, 71, 71, 71, 71,
            71, 71, 71, 71, 71, 71, 71, 71,
            71, 71, 71, 71, 71, 71, 72, 71,
            71, 71, 71, 71, 71, 71, 71, 71,
            71, 71, 71, 71, 71, 71, 71, 71,
            71, 71, 71, 71, 71, 71, 71, 71,
            71, 71, 71, 71, 71, 71, 71, 71,
            71 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 73, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( 1, 148, 162, 164, 148, -1, 166, 148,
            148, 148, 148, 148, -1, 148, 148, 148,
            148, 166, 166, 166, 166, 168, 148, 148,
            148, 148, 148, 166, 148, 148, 148, 148,
            148, 148, 148, 166, 166, 166, 166, 166,
            166, 166, 166, 166, 166, 164, 166, 166,
            166, 166, 166, 166, 166, 148, 166, 166,
            164 ),
        array( -1, -1, -1, -1, -1, -1, -1, 75,
            -1, -1, 395, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 75, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, 287, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 287, 287, 287, 287, -1, -1, -1,
            -1, -1, -1, 287, -1, -1, -1, -1,
            -1, -1, -1, 287, 287, 287, 287, 287,
            287, 287, 287, 287, 287, -1, 287, 287,
            287, 287, 287, 287, 287, -1, 287, 287,
            -1 ),
        array( -1, -1, -1, 288, -1, -1, 288, -1,
            -1, -1, -1, -1, -1, -1, -1, 288,
            -1, 288, 288, 288, 288, -1, -1, -1,
            -1, -1, -1, 288, -1, -1, -1, -1,
            -1, -1, -1, 288, 288, 288, 288, 288,
            288, 288, 288, 288, 288, 288, 288, 288,
            288, 288, 288, 288, 288, -1, 288, 288,
            288 ),
        array( -1, -1, -1, -1, -1, -1, -1, 76,
            -1, -1, 401, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 76, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, 289, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 289, 289, 289, 289, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 289, 289, 289, 289, 289,
            289, 289, 289, 289, 289, -1, 289, 289,
            289, 289, 289, 289, 289, -1, 289, 289,
            -1 ),
        array( -1, -1, -1, 287, -1, -1, 287, 283,
            -1, -1, -1, -1, -1, -1, -1, -1,
            291, 287, 287, 287, 287, 285, -1, -1,
            -1, 464, -1, 287, -1, -1, -1, -1,
            -1, -1, -1, 287, 287, 287, 287, 287,
            287, 287, 287, 287, 287, 287, 287, 287,
            287, 287, 287, 287, 287, 76, 287, 287,
            287 ),
        array( -1, -1, -1, 288, -1, -1, 288, -1,
            -1, -1, -1, -1, -1, -1, -1, 288,
            -1, 288, 288, 288, 288, -1, 292, -1,
            -1, 293, -1, 288, -1, -1, -1, -1,
            -1, -1, -1, 288, 288, 288, 288, 288,
            288, 288, 288, 288, 288, 288, 288, 288,
            288, 288, 288, 288, 288, -1, 288, 288,
            288 ),
        array( -1, -1, -1, -1, -1, -1, 289, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 289, 289, 289, 289, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 78, 289, 289, 289, 289, 289,
            289, 289, 289, 289, 289, -1, 289, 289,
            289, 289, 289, 289, 289, -1, 289, 289,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, 290, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 290, 290, 290, 290, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 75, 290, 290, 290, 290, 290,
            290, 290, 290, 290, 290, -1, 290, 290,
            290, 290, 290, 290, 290, -1, 290, 290,
            -1 ),
        array( -1, -1, -1, 295, -1, -1, 295, -1,
            -1, -1, -1, -1, -1, -1, -1, 295,
            -1, 295, 295, 295, 295, -1, -1, -1,
            -1, -1, -1, 295, -1, -1, -1, -1,
            -1, -1, -1, 295, 295, 295, 295, 295,
            295, 295, 295, 295, 295, 295, 295, 295,
            295, 295, 295, 295, 295, -1, 295, 295,
            295 ),
        array( -1, -1, -1, -1, -1, -1, -1, 283,
            -1, -1, -1, -1, -1, -1, -1, -1,
            284, -1, -1, -1, -1, 285, -1, -1,
            -1, 463, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 76, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 296, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, 294, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 294, 294, 294, 294, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 76, 294, 294, 294, 294, 294,
            294, 294, 294, 294, 294, -1, 294, 294,
            294, 294, 294, 294, 294, -1, 294, 294,
            -1 ),
        array( -1, -1, -1, 295, -1, -1, 295, -1,
            -1, -1, -1, -1, -1, -1, -1, 295,
            -1, 295, 295, 295, 295, -1, 297, -1,
            -1, 298, -1, 295, -1, -1, -1, -1,
            -1, -1, -1, 295, 295, 295, 295, 295,
            295, 295, 295, 295, 295, 295, 295, 295,
            295, 295, 295, 295, 295, -1, 295, 295,
            295 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 292, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 292,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, 283,
            -1, -1, -1, -1, -1, -1, -1, -1,
            291, -1, -1, -1, -1, 285, -1, -1,
            -1, 464, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 76, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 299, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 297, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 297,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( 1, 148, 170, 148, 148, -1, 79, 148,
            148, 148, 148, 148, -1, 148, 148, 148,
            148, 79, 79, 79, 79, 172, 148, 148,
            148, 148, 148, 79, 148, 148, 148, 148,
            148, 148, 148, 79, 79, 79, 79, 79,
            79, 79, 79, 79, 79, 148, 79, 79,
            79, 79, 79, 79, 79, 148, 79, 79,
            148 ),
        array( -1, -1, -1, -1, -1, -1, 381, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 381, 381, 381, 381, -1, -1, -1,
            -1, -1, -1, 381, -1, -1, -1, -1,
            -1, -1, -1, 381, 381, 381, 381, 381,
            381, 381, 381, 381, 381, -1, 381, 381,
            381, 381, 381, 381, 381, -1, 381, 381,
            -1 ),
        array( -1, -1, -1, 306, -1, -1, 306, -1,
            -1, -1, -1, -1, -1, -1, -1, 306,
            -1, 306, 306, 306, 306, -1, -1, -1,
            -1, -1, -1, 306, -1, -1, -1, -1,
            -1, -1, -1, 306, 306, 306, 306, 306,
            306, 306, 306, 306, 306, 306, 306, 306,
            306, 306, 306, 306, 306, -1, 306, 306,
            306 ),
        array( -1, -1, -1, -1, -1, -1, 307, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 307, 307, 307, 307, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 307, 307, 307, 307, 307,
            307, 307, 307, 307, 307, -1, 307, 307,
            307, 307, 307, 307, 307, -1, 307, 307,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            308 ),
        array( -1, -1, -1, 306, -1, -1, 306, -1,
            -1, -1, -1, -1, -1, -1, -1, 306,
            -1, 306, 306, 306, 306, -1, 127, -1,
            -1, 310, -1, 306, -1, -1, -1, -1,
            -1, -1, -1, 306, 306, 306, 306, 306,
            306, 306, 306, 306, 306, 306, 306, 306,
            306, 306, 306, 306, 306, -1, 306, 306,
            306 ),
        array( -1, -1, -1, -1, -1, -1, 307, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 307, 307, 307, 307, -1, -1, -1,
            -1, 305, -1, -1, -1, -1, -1, -1,
            -1, -1, 81, 307, 307, 307, 307, 307,
            307, 307, 307, 307, 307, -1, 307, 307,
            307, 307, 307, 307, 307, -1, 307, 307,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 81, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 81,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 311, -1, -1, 311, -1,
            -1, -1, -1, -1, -1, -1, -1, 311,
            -1, 311, 311, 311, 311, -1, -1, -1,
            -1, -1, -1, 311, -1, -1, -1, -1,
            -1, -1, -1, 311, 311, 311, 311, 311,
            311, 311, 311, 311, 311, 311, 311, 311,
            311, 311, 311, 311, 311, -1, 311, 311,
            311 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 312, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 311, -1, -1, 311, -1,
            -1, -1, -1, -1, -1, -1, -1, 311,
            -1, 311, 311, 311, 311, -1, 382, -1,
            -1, 313, -1, 311, -1, -1, -1, -1,
            -1, -1, -1, 311, 311, 311, 311, 311,
            311, 311, 311, 311, 311, 311, 311, 311,
            311, 311, 311, 311, 311, -1, 311, 311,
            311 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 127, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 127,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 396, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( 1, 148, 148, 148, 148, -1, 148, 148,
            148, 148, 148, 148, -1, 148, 148, 148,
            148, 148, 148, 148, 148, 172, 148, 148,
            148, 148, 148, 148, 148, 148, 148, 148,
            148, 148, 148, 148, 148, 148, 148, 148,
            148, 148, 148, 148, 148, 148, 148, 148,
            148, 148, 148, 148, 148, 82, 148, 148,
            148 ),
        array( 1, 83, 83, 83, 83, 83, 83, 83,
            128, 83, 83, 83, 83, 83, 83, 83,
            83, 83, 83, 83, 83, 83, 83, 83,
            83, 83, 83, 83, 83, 83, 83, 83,
            83, 83, 83, 83, 83, 83, 83, 83,
            83, 83, 83, 83, 83, 83, 83, 83,
            83, 83, 83, 83, 83, 83, 83, 83,
            83 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 317, 317, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 318, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 318, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 319, 319,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            320, 320, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 321, 321, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, 322, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, 322, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 84, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( 1, 85, 85, 85, 85, 85, 85, 85,
            85, 85, 85, 85, 85, 85, 85, 85,
            85, 85, 85, 85, 85, 85, 129, 143,
            85, 85, 85, 85, 85, 85, 85, 85,
            85, 85, 85, 85, 85, 85, 85, 85,
            85, 85, 85, 85, 85, 85, 85, 85,
            85, 85, 85, 85, 85, 85, 85, 85,
            85 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 173, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, -1, 324, 324,
            324 ),
        array( -1, -1, -1, -1, -1, -1, 327, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 327, 327, 327, 327, -1, -1, -1,
            -1, -1, -1, 327, -1, -1, -1, -1,
            -1, -1, -1, 327, 327, 327, 327, 327,
            327, 327, 327, 327, 327, -1, 327, 327,
            327, 327, 327, 327, 327, -1, 327, 327,
            -1 ),
        array( -1, -1, -1, 328, -1, -1, 328, -1,
            -1, -1, -1, -1, -1, -1, -1, 328,
            -1, 328, 328, 328, 328, -1, -1, -1,
            -1, -1, -1, 328, -1, -1, -1, -1,
            -1, -1, -1, 328, 328, 328, 328, 328,
            328, 328, 328, 328, 328, 328, 328, 328,
            328, 328, 328, 328, 328, -1, 328, 328,
            328 ),
        array( -1, -1, -1, 327, -1, -1, 327, 325,
            -1, -1, 173, -1, -1, -1, -1, -1,
            330, 327, 327, 327, 327, -1, -1, -1,
            -1, 468, -1, 327, -1, -1, -1, -1,
            -1, -1, 19, 327, 327, 327, 327, 327,
            327, 327, 327, 327, 327, 327, 327, 327,
            327, 327, 327, 327, 327, -1, 327, 327,
            327 ),
        array( -1, -1, -1, 328, -1, -1, 328, -1,
            -1, -1, -1, -1, -1, -1, -1, 328,
            -1, 328, 328, 328, 328, -1, 331, -1,
            -1, 332, -1, 328, -1, -1, -1, -1,
            -1, -1, -1, 328, 328, 328, 328, 328,
            328, 328, 328, 328, 328, 328, 328, 328,
            328, 328, 328, 328, 328, -1, 328, 328,
            328 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 192, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, -1, 324, 478,
            324 ),
        array( -1, -1, -1, 335, -1, -1, 335, -1,
            -1, -1, -1, -1, -1, -1, -1, 335,
            -1, 335, 335, 335, 335, -1, -1, -1,
            -1, -1, -1, 335, -1, -1, -1, -1,
            -1, -1, -1, 335, 335, 335, 335, 335,
            335, 335, 335, 335, 335, 335, 335, 335,
            335, 335, 335, 335, 335, -1, 335, 335,
            335 ),
        array( -1, -1, -1, -1, -1, -1, -1, 325,
            -1, -1, 173, -1, -1, -1, -1, -1,
            326, -1, -1, -1, -1, -1, -1, -1,
            -1, 467, -1, -1, -1, -1, -1, -1,
            -1, -1, 19, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 336, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 199, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, -1, 324, 324,
            324 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 387, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, -1, 324, 324,
            324 ),
        array( -1, -1, -1, 335, -1, -1, 335, -1,
            -1, -1, -1, -1, -1, -1, -1, 335,
            -1, 335, 335, 335, 335, -1, 337, -1,
            -1, 338, -1, 335, -1, -1, -1, -1,
            -1, -1, -1, 335, 335, 335, 335, 335,
            335, 335, 335, 335, 335, 335, 335, 335,
            335, 335, 335, 335, 335, -1, 335, 335,
            335 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 331, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 331,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, 325,
            -1, -1, 173, -1, -1, -1, -1, -1,
            330, -1, -1, -1, -1, -1, -1, -1,
            -1, 468, -1, -1, -1, -1, -1, -1,
            -1, -1, 19, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 339, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 337, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 337,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 389, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, -1, 324, 324,
            324 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 215, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, -1, 324, 324,
            324 ),
        array( -1, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, -1,
            130, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130,
            130, 130, 130, 130, 130, 130, 130, 130,
            130 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 88, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, 144, 144, 144, 144, 144, 144, 144,
            144, 144, 144, 144, 144, 90, 144, 144,
            144, 144, 144, 144, 144, 144, 144, 144,
            144, 144, 144, 144, 144, 144, 144, 144,
            144, 144, 144, 144, 144, 144, 144, 144,
            144, 144, 144, 144, 144, 144, 144, 144,
            144, 144, 144, 144, 144, 144, 144, 144,
            144 ),
        array( -1, 93, 93, 93, 93, 93, 93, 93,
            93, 93, 93, 93, 93, 94, 93, 134,
            93, 93, 93, 93, 93, 93, 93, 93,
            93, 93, 93, 93, 93, 93, 93, 93,
            93, 93, 93, 93, 93, 93, 93, 93,
            93, 93, 93, 93, 93, 93, 93, 93,
            93, 93, 93, 93, 93, 93, 93, 93,
            93 ),
        array( -1, -1, -1, 346, -1, -1, 346, 348,
            -1, -1, 349, -1, -1, -1, -1, -1,
            350, 346, 346, 346, 346, -1, -1, -1,
            -1, 469, -1, 346, -1, -1, -1, -1,
            -1, -1, 95, 346, 346, 346, 346, 346,
            346, 346, 346, 346, 346, 346, 346, 346,
            346, 346, 346, 346, 346, -1, 346, 346,
            346 ),
        array( -1, -1, -1, -1, -1, -1, 352, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 352, 352, 352, 352, -1, -1, -1,
            -1, -1, -1, 352, -1, -1, -1, -1,
            -1, -1, -1, 352, 352, 352, 352, 352,
            352, 352, 352, 352, 352, -1, 352, 352,
            352, 352, 352, 352, 352, -1, 352, 352,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, 353, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 353, 353, 353, 353, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 353, 353, 353, 353, 353,
            353, 353, 353, 353, 353, -1, 353, 353,
            353, 353, 353, 353, 353, -1, 353, 353,
            -1 ),
        array( -1, -1, -1, 354, -1, -1, 354, -1,
            -1, -1, -1, -1, -1, -1, -1, 354,
            -1, 354, 354, 354, 354, -1, -1, -1,
            -1, -1, -1, 354, -1, -1, -1, -1,
            -1, -1, -1, 354, 354, 354, 354, 354,
            354, 354, 354, 354, 354, 354, 354, 354,
            354, 354, 354, 354, 354, -1, 354, 354,
            354 ),
        array( -1, -1, -1, 352, -1, -1, 352, 348,
            -1, -1, 349, -1, -1, -1, -1, -1,
            355, 352, 352, 352, 352, -1, -1, -1,
            -1, 470, -1, 352, -1, -1, -1, -1,
            -1, -1, 95, 352, 352, 352, 352, 352,
            352, 352, 352, 352, 352, 352, 352, 352,
            352, 352, 352, 352, 352, -1, 352, 352,
            352 ),
        array( -1, -1, -1, -1, -1, -1, 353, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 353, 353, 353, 353, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 95, 353, 353, 353, 353, 353,
            353, 353, 353, 353, 353, -1, 353, 353,
            353, 353, 353, 353, 353, -1, 353, 353,
            -1 ),
        array( -1, -1, -1, 354, -1, -1, 354, -1,
            -1, -1, -1, -1, -1, -1, -1, 354,
            -1, 354, 354, 354, 354, -1, 356, -1,
            -1, 357, -1, 354, -1, -1, -1, -1,
            -1, -1, -1, 354, 354, 354, 354, 354,
            354, 354, 354, 354, 354, 354, 354, 354,
            354, 354, 354, 354, 354, -1, 354, 354,
            354 ),
        array( -1, -1, -1, 358, -1, -1, 358, -1,
            -1, -1, -1, -1, -1, -1, -1, 358,
            -1, 358, 358, 358, 358, -1, -1, -1,
            -1, -1, -1, 358, -1, -1, -1, -1,
            -1, -1, -1, 358, 358, 358, 358, 358,
            358, 358, 358, 358, 358, 358, 358, 358,
            358, 358, 358, 358, 358, -1, 358, 358,
            358 ),
        array( -1, -1, -1, -1, -1, -1, -1, 348,
            -1, -1, 349, -1, -1, -1, -1, -1,
            350, -1, -1, -1, -1, -1, -1, -1,
            -1, 469, -1, -1, -1, -1, -1, -1,
            -1, -1, 95, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 359, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 358, -1, -1, 358, -1,
            -1, -1, -1, -1, -1, -1, -1, 358,
            -1, 358, 358, 358, 358, -1, 360, -1,
            -1, 361, -1, 358, -1, -1, -1, -1,
            -1, -1, -1, 358, 358, 358, 358, 358,
            358, 358, 358, 358, 358, 358, 358, 358,
            358, 358, 358, 358, 358, -1, 358, 358,
            358 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 356, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 356,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, 348,
            -1, -1, 349, -1, -1, -1, -1, -1,
            355, -1, -1, -1, -1, -1, -1, -1,
            -1, 470, -1, -1, -1, -1, -1, -1,
            -1, -1, 95, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 362, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 360, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 360,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( 1, -1, -1, -1, -1, -1, 364, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 364, 364, 364, 364, -1, -1, -1,
            -1, -1, -1, 364, -1, -1, -1, -1,
            -1, -1, -1, 364, 364, 364, 364, 364,
            364, 364, 364, 364, 364, -1, 364, 364,
            364, 364, 364, 364, 364, -1, 364, 364,
            -1 ),
        array( -1, -1, -1, 364, -1, -1, 364, 365,
            -1, -1, 366, -1, -1, -1, -1, -1,
            367, 364, 364, 364, 364, -1, -1, -1,
            -1, 471, -1, 364, -1, -1, -1, -1,
            -1, 96, 97, 364, 364, 364, 364, 364,
            364, 364, 364, 364, 364, 364, 364, 364,
            364, 364, 364, 364, 364, -1, 364, 364,
            364 ),
        array( -1, -1, -1, -1, -1, -1, 368, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 368, 368, 368, 368, -1, -1, -1,
            -1, -1, -1, 368, -1, -1, -1, -1,
            -1, -1, -1, 368, 368, 368, 368, 368,
            368, 368, 368, 368, 368, -1, 368, 368,
            368, 368, 368, 368, 368, -1, 368, 368,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, 369, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 369, 369, 369, 369, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 369, 369, 369, 369, 369,
            369, 369, 369, 369, 369, -1, 369, 369,
            369, 369, 369, 369, 369, -1, 369, 369,
            -1 ),
        array( -1, -1, -1, 370, -1, -1, 370, -1,
            -1, -1, -1, -1, -1, -1, -1, 370,
            -1, 370, 370, 370, 370, -1, -1, -1,
            -1, -1, -1, 370, -1, -1, -1, -1,
            -1, -1, -1, 370, 370, 370, 370, 370,
            370, 370, 370, 370, 370, 370, 370, 370,
            370, 370, 370, 370, 370, -1, 370, 370,
            370 ),
        array( -1, -1, -1, 368, -1, -1, 368, 365,
            -1, -1, 366, -1, -1, -1, -1, -1,
            371, 368, 368, 368, 368, -1, -1, -1,
            -1, 472, -1, 368, -1, -1, -1, -1,
            -1, 96, 97, 368, 368, 368, 368, 368,
            368, 368, 368, 368, 368, 368, 368, 368,
            368, 368, 368, 368, 368, -1, 368, 368,
            368 ),
        array( -1, -1, -1, -1, -1, -1, 369, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 369, 369, 369, 369, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 97, 369, 369, 369, 369, 369,
            369, 369, 369, 369, 369, -1, 369, 369,
            369, 369, 369, 369, 369, -1, 369, 369,
            -1 ),
        array( -1, -1, -1, 370, -1, -1, 370, -1,
            -1, -1, -1, -1, -1, -1, -1, 370,
            -1, 370, 370, 370, 370, -1, 372, -1,
            -1, 373, -1, 370, -1, -1, -1, -1,
            -1, -1, -1, 370, 370, 370, 370, 370,
            370, 370, 370, 370, 370, 370, 370, 370,
            370, 370, 370, 370, 370, -1, 370, 370,
            370 ),
        array( -1, -1, -1, 374, -1, -1, 374, -1,
            -1, -1, -1, -1, -1, -1, -1, 374,
            -1, 374, 374, 374, 374, -1, -1, -1,
            -1, -1, -1, 374, -1, -1, -1, -1,
            -1, -1, -1, 374, 374, 374, 374, 374,
            374, 374, 374, 374, 374, 374, 374, 374,
            374, 374, 374, 374, 374, -1, 374, 374,
            374 ),
        array( -1, -1, -1, -1, -1, -1, -1, 365,
            -1, -1, 366, -1, -1, -1, -1, -1,
            367, -1, -1, -1, -1, -1, -1, -1,
            -1, 471, -1, -1, -1, -1, -1, -1,
            -1, -1, 97, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 375, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 374, -1, -1, 374, -1,
            -1, -1, -1, -1, -1, -1, -1, 374,
            -1, 374, 374, 374, 374, -1, 376, -1,
            -1, 377, -1, 374, -1, -1, -1, -1,
            -1, -1, -1, 374, 374, 374, 374, 374,
            374, 374, 374, 374, 374, 374, 374, 374,
            374, 374, 374, 374, 374, -1, 374, 374,
            374 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 372, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 372,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, 365,
            -1, -1, 366, -1, -1, -1, -1, -1,
            371, -1, -1, -1, -1, -1, -1, -1,
            -1, 472, -1, -1, -1, -1, -1, -1,
            -1, -1, 97, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 378, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 376, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 376,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, 135, 3, 3, 3, 3, 3, 3,
            147, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, -1, 149, -1,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3, 3, 3, 3, 3, 3, 3, 3,
            3 ),
        array( -1, -1, -1, 381, -1, -1, 381, 302,
            -1, -1, -1, -1, -1, -1, -1, -1,
            309, 381, 381, 381, 381, -1, -1, -1,
            -1, 466, -1, 381, -1, -1, -1, -1,
            -1, -1, -1, 381, 381, 381, 381, 381,
            381, 381, 381, 381, 381, 381, 381, 381,
            381, 381, 381, 381, 381, -1, 381, 381,
            381 ),
        array( -1, -1, -1, -1, -1, -1, -1, 302,
            -1, -1, -1, -1, -1, -1, -1, -1,
            309, -1, -1, -1, -1, -1, -1, -1,
            -1, 466, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            177, 161, 161, 161, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 384, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 387, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, -1, -1, -1, 205, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 205, 205, 205, 205, -1, -1, -1,
            -1, -1, -1, 205, -1, -1, -1, -1,
            -1, -1, -1, 205, 205, 205, 205, 205,
            205, 205, 205, 205, 205, -1, 205, 205,
            205, 205, 205, 205, 205, -1, 205, 205,
            -1 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 215, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, -1, -1, -1, 219, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 219, 219, 219, 219, -1, -1, -1,
            -1, -1, -1, 219, -1, -1, -1, -1,
            -1, -1, -1, 219, 219, 219, 219, 219,
            219, 219, 219, 219, 219, -1, 219, 219,
            219, 219, 219, 219, 219, -1, 219, 219,
            -1 ),
        array( -1, -1, -1, 390, -1, -1, 390, -1,
            -1, -1, -1, -1, -1, -1, -1, 390,
            -1, 390, 390, 390, 390, -1, 221, -1,
            -1, 391, -1, 390, -1, -1, -1, -1,
            -1, -1, -1, 390, 390, 390, 390, 390,
            390, 390, 390, 390, 390, 390, 390, 390,
            390, 390, 390, 390, 390, -1, 390, 390,
            390 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 225, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, 227, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 227, 227, 227, 227, -1, -1, -1,
            -1, -1, -1, 227, -1, -1, -1, -1,
            -1, -1, -1, 227, 227, 227, 227, 227,
            227, 227, 227, 227, 227, -1, 227, 227,
            227, 227, 227, 227, 227, -1, 227, 227,
            -1 ),
        array( -1, -1, -1, 393, -1, -1, 393, -1,
            -1, -1, -1, -1, -1, -1, -1, 393,
            -1, 393, 393, 393, 393, -1, 231, -1,
            -1, 394, -1, 393, -1, -1, -1, -1,
            -1, -1, -1, 393, 393, 393, 393, 393,
            393, 393, 393, 393, 393, 393, 393, 393,
            393, 393, 393, 393, 393, -1, 393, 393,
            393 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 235, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, 290, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 290, 290, 290, 290, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 290, 290, 290, 290, 290,
            290, 290, 290, 290, 290, -1, 290, 290,
            290, 290, 290, 290, 290, -1, 290, 290,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, 382, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 382,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 173, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, 324, 324, 329,
            324, 324, 324, 324, 324, -1, 324, 324,
            324 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 184,
            161, 161, 161, 161, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 399, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, 294, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 294, 294, 294, 294, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, 294, 294, 294, 294, 294,
            294, 294, 294, 294, 294, -1, 294, 294,
            294, 294, 294, 294, 294, -1, 294, 294,
            -1 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 173, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, 324, 324, 324,
            324, 324, 333, 324, 324, -1, 324, 324,
            324 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 161, 193, 161, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 404, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 173, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 324, 324, 324,
            324, 324, 324, 334, 324, 324, 324, 324,
            324, 324, 324, 324, 324, -1, 324, 324,
            324 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 386, 161, 161, 161, 161,
            161, 161, 161, 161, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, 390, -1, -1, 390, -1,
            -1, -1, -1, -1, -1, -1, -1, 390,
            -1, 390, 390, 390, 390, -1, -1, -1,
            -1, -1, -1, 390, -1, -1, -1, -1,
            -1, -1, -1, 390, 390, 390, 390, 390,
            390, 390, 390, 390, 390, 390, 390, 390,
            390, 390, 390, 390, 390, -1, 390, 390,
            390 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 408, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 173, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 340, -1, 324, 324,
            324 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 211, -1, 161, 161,
            161 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 412, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 173, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 324, 324, 324,
            324, 324, 324, 341, 324, 324, 324, 324,
            324, 324, 324, 324, 324, -1, 324, 324,
            324 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 388, 161, 161, 161, 161,
            161, 161, 161, 161, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, 393, -1, -1, 393, -1,
            -1, -1, -1, -1, -1, -1, -1, 393,
            -1, 393, 393, 393, 393, -1, -1, -1,
            -1, -1, -1, 393, -1, -1, -1, -1,
            -1, -1, -1, 393, 393, 393, 393, 393,
            393, 393, 393, 393, 393, 393, 393, 393,
            393, 393, 393, 393, 393, -1, 393, 393,
            393 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 416, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 418, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 420, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 422, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 424, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 426, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 428, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 430, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 432, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 434, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 436, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 438, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 440, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, 442, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, -1, 398, 450,
            161 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 400, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 423, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            253 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 173, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, -1, 397, 452,
            324 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 403, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 405, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 173, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 402, 324, 324, 324,
            324, 324, 324, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, -1, 324, 324,
            324 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 407, 161, 161, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 409, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 173, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, 324, 324, 324,
            324, 406, 324, 324, 324, -1, 324, 324,
            324 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 411, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 413, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 173, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 410, 324, 324,
            324, 324, 324, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, -1, 324, 324,
            324 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 415, 161, 161, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 417, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 173, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, 324, 324, 324,
            324, 414, 324, 324, 324, -1, 324, 324,
            324 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 419, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 425, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 427, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 429, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 431, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 433, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 435, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 437, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 439, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 441, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, 443, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1,
            -1 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 453, 161, 161, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 173, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, 324, 324, 324,
            324, 455, 324, 324, 324, -1, 324, 324,
            324 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 161, 161, 456, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 173, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, 324, 324, 324,
            324, 324, 324, 458, 324, -1, 324, 324,
            324 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 459, 161, 161, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 173, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, 324, 324, 324,
            324, 461, 324, 324, 324, -1, 324, 324,
            324 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 161, 475, 161, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 173, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, 324, 324, 324,
            324, 324, 476, 324, 324, -1, 324, 324,
            324 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 479, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 173, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 324, 480, 324,
            324, 324, 324, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, -1, 324, 324,
            324 ),
        array( -1, -1, -1, 161, -1, -1, 161, 171,
            -1, -1, 173, -1, -1, -1, -1, -1,
            175, 161, 161, 161, 161, -1, -1, -1,
            -1, 176, -1, 161, -1, -1, -1, -1,
            -1, 18, 19, 161, 161, 161, 161, 161,
            161, 161, 161, 161, 161, 161, 161, 161,
            161, 481, 161, 161, 161, -1, 161, 161,
            161 ),
        array( -1, -1, -1, 324, -1, -1, 324, 325,
            -1, -1, 173, -1, -1, -1, -1, -1,
            326, 324, 324, 324, 324, -1, -1, -1,
            -1, 467, -1, 324, -1, -1, -1, -1,
            -1, -1, 19, 324, 324, 324, 324, 324,
            324, 324, 324, 324, 324, 324, 324, 324,
            324, 482, 324, 324, 324, -1, 324, 324,
            324 )
        );


    function  yylex()
    {
        $yy_lookahead = '';
        $yy_anchor = YY_NO_ANCHOR;
        $yy_state = $this->yy_state_dtrans[$this->yy_lexical_state];
        $yy_next_state = YY_NO_STATE;
         $yy_last_accept_state = YY_NO_STATE;
        $yy_initial = true;
        $yy_this_accept = 0;
        
        $this->yy_mark_start();
        $yy_this_accept = $this->yy_acpt[$yy_state];
        if (YY_NOT_ACCEPT != $yy_this_accept) {
            $yy_last_accept_state = $yy_state;
            $this->yy_buffer_end = $this->yy_buffer_index;
        }
        while (true) {
            if ($yy_initial && $this->yy_at_bol) {
                $yy_lookahead =  YY_BOL;
            } else {
                $yy_lookahead = $this->yy_advance();
            }
            $yy_next_state = $this->yy_nxt[$this->yy_rmap[$yy_state]][$this->yy_cmap[$yy_lookahead]];
            if (YY_EOF == $yy_lookahead && $yy_initial) {
                return false;            }
            if (YY_F != $yy_next_state) {
                $yy_state = $yy_next_state;
                $yy_initial = false;
                $yy_this_accept = $this->yy_acpt[$yy_state];
                if (YY_NOT_ACCEPT != $yy_this_accept) {
                    $yy_last_accept_state = $yy_state;
                    $this->yy_buffer_end = $this->yy_buffer_index;
                }
            } else {
                if (YY_NO_STATE == $yy_last_accept_state) {
                    $this->yy_error(1,1);
                    if ($this->_fatal) {
                        return;
                    }
                } else {
                    $yy_anchor = $this->yy_acpt[$yy_last_accept_state];
                    if (0 != (YY_END & $yy_anchor)) {
                        $this->yy_move_end();
                    }
                    $this->yy_to_mark();
                    if ($yy_last_accept_state < 0) {
                        if ($yy_last_accept_state < 485) {
                            $this->yy_error(YY_E_INTERNAL, false);
                            if ($this->_fatal) {
                                return;
                            }
                        }
                    } else {

                        switch ($yy_last_accept_state) {
case 2:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 3:
{
    //abcd -- data characters  
    // { and ) added for flexy
    $this->value = $this->createToken('Text');
    return FLY_FLEXY_TOKEN_OK;
}
case 4:
{
    // &abc;
    $this->value = $this->createToken('Text');
    return FLY_FLEXY_TOKEN_OK;
}
case 5:
{
    //<name -- start tag */
    if ($this->options['ignore_html']) {
        return $this->returnSimple();
    }
    $this->tagName = trim(substr($this->yytext(),1));
    $this->tokenName = 'Tag';
    $this->value = '';
    $this->attributes = array();
    $this->yybegin(IN_ATTR);
    return FLY_FLEXY_TOKEN_NONE;
}
case 6:
{  
    // <> -- empty start tag */
    if ($this->options['ignore_html']) {
        return $this->returnSimple();
    }
    return $this->raiseError("empty tag"); 
}
case 7:
{ 
    /* <? php start.. */
    //echo "STARTING PHP?\n";
    $this->yyPhpBegin = $this->yy_buffer_start;
    $this->yybegin(IN_PHP);
    return FLY_FLEXY_TOKEN_NONE;
}
case 8:
{
    // &#123;
    $this->value = $this->createToken('Text');
    return FLY_FLEXY_TOKEN_OK;
}
case 9:
{
    // &#abc;
    $this->value = $this->createToken('Text');
    return FLY_FLEXY_TOKEN_OK;
}
case 10:
{
    /* </title> -- end tag */
    if ($this->options['ignore_html']) {
        return $this->returnSimple();
    }
    if ($this->inStyle) {
        $this->inStyle = false;
    }
    $this->tagName = trim(substr($this->yytext(),1));
    $this->tokenName = 'EndTag';
    $this->yybegin(IN_ENDTAG);
    $this->value = '';
    return FLY_FLEXY_TOKEN_NONE;
}
case 11:
{
    /* </> -- empty end tag */  
    if ($this->options['ignore_html']) {
        return $this->returnSimple();
    }
    return $this->raiseError("empty end tag not handled");
}
case 12:
{
    /* <!DOCTYPE -- markup declaration */
    if ($this->options['ignore_html']) {
        return $this->returnSimple();
    }
    $this->value = $this->createToken('Doctype');
    $this->yybegin(IN_MD);
    return FLY_FLEXY_TOKEN_OK;
}
case 13:
{
    /* <!> */
    if ($this->options['ignore_html']) {
        return $this->returnSimple();
    }
    return $this->raiseError("empty markup tag not handled"); 
}
case 14:
{
    /* <![ -- marked section */
    return $this->returnSimple();
}
case 15:
{ 
    /* eg. <?xml-stylesheet, <?php ... */
    $t = $this->yytext();
    $tagname = trim(strtoupper(substr($t,2)));
   // echo "STARTING XML? $t:$tagname\n";
    if ($tagname == 'PHP') {
        $this->yyPhpBegin = $this->yy_buffer_start;
        $this->yybegin(IN_PHP);
        return FLY_FLEXY_TOKEN_NONE;
    }
    // not php - it's xlm or something...
    // we treat this like a tag???
    // we are going to have to escape it eventually...!!!
    $this->tagName = trim(substr($t,1));
    $this->tokenName = 'Tag';
    $this->value = '';
    $this->attributes = array();
    $this->yybegin(IN_ATTR);
    return FLY_FLEXY_TOKEN_NONE;
}
case 16:
{
    $this->value = $this->createToken('GetTextEnd','');
    return FLY_FLEXY_TOKEN_OK;
}
case 17:
{ 
    /* ]]> -- marked section end */
    return $this->returnSimple();
}
case 18:
{
    $this->value =  '';
    $this->flexyMethod = substr($this->yytext(),1,-1);
    $this->flexyArgs = array();
    $this->yybegin(IN_FLEXYMETHOD);
    return FLY_FLEXY_TOKEN_NONE;
}
case 19:
{
    $t =  $this->yytext();
    $t = substr($t,1,-1);
    $this->value = $this->createToken('Var'  , $t);
    return FLY_FLEXY_TOKEN_OK;
}
case 20:
{
    $this->value = $this->createToken('GetTextStart','');
    return FLY_FLEXY_TOKEN_OK;
}
case 21:
{
    if ($this->options['ignore_html']) {
        return $this->returnSimple();
    }
    /* </name <  -- unclosed end tag */
    return $this->raiseError("Unclosed  end tag");
}
case 22:
{
    /* <!--  -- comment declaration */
    if ($this->options['ignore_html']) {
        return $this->returnSimple();
    }
    if ($this->inStyle) {
        $this->value = $this->createToken('Comment');
        $this->yybegin(IN_COMSTYLE);
        return FLY_FLEXY_TOKEN_OK;
    }
    $this->yyCommentBegin = $this->yy_buffer_end;
    //$this->value = $this->createToken('Comment',$this->yytext(),$this->yyline);
    $this->yybegin(IN_COM);
    return FLY_FLEXY_TOKEN_NONE;
}
case 23:
{
    $this->value =  '';
    $this->flexyMethod = substr($this->yytext(),1,-1);
    $this->flexyArgs = array();
    $this->yybegin(IN_FLEXYMETHOD);
    return FLY_FLEXY_TOKEN_NONE;
}
case 24:
{
    $this->value = $this->createToken('If',substr($this->yytext(),4,-1));
    return FLY_FLEXY_TOKEN_OK;
}
case 25:
{
    $this->value = $this->createToken('End', '');
    return FLY_FLEXY_TOKEN_OK;
}
case 26:
{
    $this->value = $this->createToken('Else', '');
    return FLY_FLEXY_TOKEN_OK;
}
case 27:
{
    $this->value = $this->createToken('Loop', explode(',',substr($this->yytext(),6,-1)));
    return FLY_FLEXY_TOKEN_OK;
}
case 28:
{
    /* <![ -- marked section */
    $this->value = $this->createToken('Cdata',$this->yytext(), $this->yyline);
    $this->yybegin(IN_CDATA);
    return FLY_FLEXY_TOKEN_OK;
}
case 29:
{
    $this->value = $this->createToken('EndLoop', '');
    return FLY_FLEXY_TOKEN_OK;
}
case 30:
{
    return $this->raiseError('invalid syntax for Foreach','',true);
}
case 31:
{
    $this->value = $this->createToken('Foreach', explode(',',substr($this->yytext(),9,-1)));
    return FLY_FLEXY_TOKEN_OK;
}
case 32:
{
    $this->value = $this->createToken('Foreach',  explode(',',substr($this->yytext(),9,-1)));
    return FLY_FLEXY_TOKEN_OK;
}
case 33:
{
    $this->attrVal[] = $this->yytext();
    return FLY_FLEXY_TOKEN_NONE;
}
case 34:
{
    $this->attrVal[] = "'";
     //var_dump($this->attrVal);
    $s = "";
    foreach($this->attrVal as $v) {
        if (!is_string($v)) {
            $this->attributes[$this->attrKey] = $this->attrVal;
            $this->yybegin(IN_ATTR);
            return FLY_FLEXY_TOKEN_NONE;
        }
        $s .= $v;
    }
    $this->attributes[$this->attrKey] = $s;
    $this->yybegin(IN_ATTR);
    return FLY_FLEXY_TOKEN_NONE;
}
case 35:
{
    $this->value =  '';
    $n = $this->yytext();
    if ($n{0} != "{") {
        $n = substr($n,2);
    }
    $this->flexyMethod = substr($n,1,-1);
    $this->flexyArgs = array();
    $this->flexyMethodState = $this->yy_lexical_state;
    $this->yybegin(IN_FLEXYMETHODQUOTED);
    return FLY_FLEXY_TOKEN_NONE;
}
case 36:
{
    $n = $this->yytext();
    if ($n{0} != '{') {
        $n = substr($n,3);
    } else {
        $n = substr($n,1);
    }
    if ($n{strlen($n)-1} != '}') {
        $n = substr($n,0,-3);
    } else {
        $n = substr($n,0,-1);
    }
    $this->attrVal[] = $this->createToken('Var'  , $n);
    return FLY_FLEXY_TOKEN_NONE;
}
case 37:
{
    $this->value = '';
    return FLY_FLEXY_TOKEN_NONE;
}
case 38:
{
    // <foo^<bar> -- unclosed start tag */
    return $this->raiseError("Unclosed tags not supported"); 
}
case 39:
{
    $this->value = $this->createToken($this->tokenName, array($this->tagName,$this->attributes));
    if (strtoupper($this->tagName) == 'SCRIPT') {
        $this->yybegin(IN_SCRIPT);
        return FLY_FLEXY_TOKEN_OK;
    }
    if (strtoupper($this->tagName) == 'STYLE') {
        $this->inStyle = true;
    } else {
        $this->inStyle = false;
    }
    $this->yybegin(YYINITIAL);
    return FLY_FLEXY_TOKEN_OK;
}
case 40:
{
    $this->value = $this->createToken($this->tokenName, array($this->tagName,$this->attributes));
    $this->yybegin(YYINITIAL);
    return FLY_FLEXY_TOKEN_OK;
}
case 41:
{
    // <img src="xxx" ...ismap...> the ismap */
    $this->attributes[trim($this->yytext())] = true;
    $this->value = '';
    return FLY_FLEXY_TOKEN_NONE;
}
case 42:
{
    // <em^/ -- NET tag */
    $this->yybegin(IN_NETDATA);
    $this->attributes["/"] = true;
    $this->value = '';
    return FLY_FLEXY_TOKEN_NONE;
}
case 43:
{
   // <a ^href = "xxx"> -- attribute name 
    $this->attrKey = substr(trim($this->yytext()),0,-1);
    $this->yybegin(IN_ATTRVAL);
    $this->value = '';
    return FLY_FLEXY_TOKEN_NONE;
}
case 44:
{
    // <em^/ -- NET tag */
    $this->attributes["/"] = true;
    $this->value = $this->createToken($this->tokenName, array($this->tagName,$this->attributes));
    $this->yybegin(YYINITIAL);
    return FLY_FLEXY_TOKEN_OK;
}
case 45:
{
    // <em^/ -- NET tag */
    $this->attributes["?"] = true;
    $this->value = $this->createToken($this->tokenName, array($this->tagName,$this->attributes));
    $this->yybegin(YYINITIAL);
    return FLY_FLEXY_TOKEN_OK;
}
case 46:
{
    // <a href = ^http://foo/> -- unquoted literal HACK */                          
    $this->attributes[$this->attrKey] = trim($this->yytext());
    $this->yybegin(IN_ATTR);
    //   $this->raiseError("attribute value needs quotes");
    $this->value = '';
    return FLY_FLEXY_TOKEN_NONE;
}
case 47:
{
    // <a name = ^12pt> -- number token */
    $this->attributes[$this->attrKey] = trim($this->yytext());
    $this->yybegin(IN_ATTR);
    $this->value = '';
    return FLY_FLEXY_TOKEN_NONE;
}
case 48:
{
    // <em^/ -- NET tag */
    return $this->raiseError("attribute value missing"); 
}
case 49:
{ 
    return $this->raiseError("Tag close found where attribute value expected"); 
}
case 50:
{
	//echo "STARTING SINGLEQUOTE";
    $this->attrVal = array( "'");
    $this->yybegin(IN_SINGLEQUOTE);
    return FLY_FLEXY_TOKEN_NONE;
}
case 51:
{
    //echo "START QUOTE";
    $this->attrVal =array("\"");
    $this->yybegin(IN_DOUBLEQUOTE);
    return FLY_FLEXY_TOKEN_NONE;
}
case 52:
{ 
    // whitespace switch back to IN_ATTR MODE.
    $this->value = '';
    $this->yybegin(IN_ATTR);
    return FLY_FLEXY_TOKEN_NONE;
}
case 53:
{ 
    return $this->raiseError("extraneous character in end tag"); 
}
case 54:
{ 
    $this->value = $this->createToken($this->tokenName, array($this->tagName));
        array($this->tagName);
    $this->yybegin(YYINITIAL);
    return FLY_FLEXY_TOKEN_OK;
}
case 55:
{
    //echo "GOT DATA:".$this->yytext();
    $this->attrVal[] = $this->yytext();
    return FLY_FLEXY_TOKEN_NONE;
}
case 56:
{
    //echo "GOT END DATA:".$this->yytext();
    $this->attrVal[] = "\"";
    $s = "";
    foreach($this->attrVal as $v) {
        if (!is_string($v)) {
            $this->attributes[$this->attrKey] = $this->attrVal;
            $this->yybegin(IN_ATTR);
            return FLY_FLEXY_TOKEN_NONE;
        }
        $s .= $v;
    }
    $this->attributes[$this->attrKey] = $s;
    $this->yybegin(IN_ATTR);
    return FLY_FLEXY_TOKEN_NONE;
}
case 57:
{ 
    $this->value = $this->createToken('WhiteSpace');
    return FLY_FLEXY_TOKEN_OK; 
}
case 58:
{
    return $this->raiseError("illegal character in markup declaration (0x".dechex(ord($this->yytext())).')');
}
case 59:
{   
    $this->value = $this->createToken('Number');
    return FLY_FLEXY_TOKEN_OK; 
}
case 60:
{ 
    $this->value = $this->createToken('Name');
    return FLY_FLEXY_TOKEN_OK; 
}
case 61:
{ 
    $this->value = $this->createToken('NameT');
    return FLY_FLEXY_TOKEN_OK; 
}
case 62:
{   
    $this->value = $this->createToken('CloseTag');
    $this->yybegin(YYINITIAL); 
    return FLY_FLEXY_TOKEN_OK; 
}
case 63:
{
    // <!doctype foo ^[  -- declaration subset */
    $this->value = $this->createToken('BeginDS');
    $this->yybegin(IN_DS);
    return FLY_FLEXY_TOKEN_OK;
}
case 64:
{ 
    $this->value = $this->createToken('NumberT');
    return FLY_FLEXY_TOKEN_OK; 
}
case 65:
{
    // <!entity ^% foo system "..." ...> -- parameter entity definition */
    $this->value = $this->createToken('EntityPar');
    return FLY_FLEXY_TOKEN_OK;
}
case 66:
{
    // <!doctype ^%foo;> -- parameter entity reference */
    $this->value = $this->createToken('EntityRef');
    return FLY_FLEXY_TOKEN_OK;
}
case 67:
{ 
    $this->value = $this->createToken('Literal');
    return FLY_FLEXY_TOKEN_OK; 
}
case 68:
{
    // inside a comment (not - or not --
    // <!^--...-->   -- comment */   
    return FLY_FLEXY_TOKEN_NONE;
}
case 69:
{
	// inside comment -- without a >
	return FLY_FLEXY_TOKEN_NONE;
}
case 70:
{   
    $this->value = $this->createToken('Comment',
        '<!--'. substr($this->yy_buffer,$this->yyCommentBegin ,$this->yy_buffer_end - $this->yyCommentBegin),
        $this->yyline,$this->yyCommentBegin
    );
    $this->yybegin(YYINITIAL); 
    return FLY_FLEXY_TOKEN_OK; 
}
case 71:
{ 
    $this->value = $this->createToken('Declaration');
    return FLY_FLEXY_TOKEN_OK;
}
case 72:
{ 
    // ] -- declaration subset close */
    $this->value = $this->createToken('DSEndSubset');
    $this->yybegin(IN_DSCOM); 
    return FLY_FLEXY_TOKEN_OK;
}
case 73:
{
    // ]]> -- marked section end */
     $this->value = $this->createToken('DSEnd');
    $this->yybegin(YYINITIAL);
    return FLY_FLEXY_TOKEN_OK;
}
case 74:
{
    $t = $this->yytext();
    if ($t{strlen($t)-1} == ",") {
        // add argument
        $this->flexyArgs[] = substr($t,0,-1);
        return FLY_FLEXY_TOKEN_NONE;
    }
    $this->flexyArgs[] = $t;
    return FLY_FLEXY_TOKEN_NONE;
}
case 75:
{
    $t = $this->yytext();
    if ($t{strlen($t)-1} == ",") {
        // add argument
        $this->flexyArgs[] = '#' . substr($t,0,-1) . '#';
        return FLY_FLEXY_TOKEN_NONE;
    }
	if ($t{strlen($t)-1} == ".") {
		$this->flexyArgs[] = substr($t,0,-2);
		$this->flexyArgs = array($this->createToken('MethodChain'  , array($this->flexyMethod, $this->flexyArgs)));
		$this->flexyMethod = '';
		$this->yybegin(IN_METHODCHAIN);
		return FLY_FLEXY_TOKEN_NONE;
	}
    if ($c = strpos($t,':')) {
        $this->flexyMethod .= substr($t,$c,-1);
        $t = '#' . substr($t,0,$c-1) . '#';
    } else {
        $t = '#' . substr($t,0,-2) . '#';
    }
    $this->flexyArgs[] = $t;
    $this->value = $this->createToken('Method', array($this->flexyMethod,$this->flexyArgs));
    $this->yybegin(YYINITIAL);
    return FLY_FLEXY_TOKEN_OK;
}
case 76:
{
    $t = $this->yytext();
    if ($t{strlen($t)-1} == ",") {
        // add argument
        $this->flexyArgs[] = substr($t,0,-1);
        return FLY_FLEXY_TOKEN_NONE;
    }
	if ($t{strlen($t)-1} == ".") {
		$this->flexyArgs[] = substr($t,0,-2);
		$this->flexyArgs = array($this->createToken('MethodChain'  , array($this->flexyMethod, $this->flexyArgs)));
		$this->flexyMethod = '';
		$this->yybegin(IN_METHODCHAIN);
		return FLY_FLEXY_TOKEN_NONE;
	}
    if ($c = strpos($t,':')) {
        $this->flexyMethod .= substr($t,$c,-1);
        $t = substr($t,0,$c-1);
    } else {
        $t = substr($t,0,-2);
    }
    $this->flexyArgs[] = $t;
    $this->value = $this->createToken('Method'  , array($this->flexyMethod,$this->flexyArgs));
    $this->yybegin(YYINITIAL);
    return FLY_FLEXY_TOKEN_OK;
}
case 77:
{
    $t = $this->yytext();
	$this->flexyArgs = array($this->createToken('MethodChain'  , array($this->flexyMethod,$this->flexyArgs)));
	$this->flexyMethod = '';
	$this->yybegin(IN_METHODCHAIN);
	return FLY_FLEXY_TOKEN_NONE;
}
case 78:
{
    $t = $this->yytext();
    if ($t{1} == ':') {
        $this->flexyMethod .= substr($t,1,-1);
    }
    $this->value = $this->createToken('Method'  , array($this->flexyMethod,$this->flexyArgs));
    $this->yybegin(YYINITIAL);
    return FLY_FLEXY_TOKEN_OK;
}
case 79:
{
    $t = $this->yytext();
    // add argument
    $this->flexyArgs[] = $t;
    $this->yybegin(IN_FLEXYMETHODQUOTED_END);
    return FLY_FLEXY_TOKEN_NONE;
}
case 80:
{
    $t = $this->yytext();
    $this->flexyArgs[] =$t;
    $this->yybegin(IN_FLEXYMETHODQUOTED_END);
    return FLY_FLEXY_TOKEN_NONE;
}
case 81:
{
    $t = $this->yytext();
    if ($p = strpos($t,':')) {
        $this->flexyMethod .= substr($t,$p,-1);
    }
    $this->attrVal[] = $this->createToken('Method'  , array($this->flexyMethod,$this->flexyArgs));
    $this->yybegin($this->flexyMethodState);
    return FLY_FLEXY_TOKEN_NONE;
}
case 82:
{
    $this->yybegin(IN_FLEXYMETHODQUOTED);
    return FLY_FLEXY_TOKEN_NONE;
}
case 83:
{
    // general text in script..
    $this->value = $this->createToken('Text');
    return FLY_FLEXY_TOKEN_OK;
}
case 84:
{
    // </script>
    $this->value = $this->createToken('EndTag', array('/script'));
    $this->yybegin(YYINITIAL);
    return FLY_FLEXY_TOKEN_OK;
}
case 85:
{ 
    $this->value = $this->createToken('Cdata',$this->yytext(), $this->yyline);
    return FLY_FLEXY_TOKEN_OK;
}
case 86:
{ 
    /* ]]> -- marked section end */
    $this->value = $this->createToken('Cdata',$this->yytext(), $this->yyline);
    $this->yybegin(YYINITIAL);
    return FLY_FLEXY_TOKEN_OK; 
}
case 87:
{
    // inside a comment (not - or not --
    // <!^--...-->   -- comment */   
    $this->value = $this->createToken('DSComment');
    return FLY_FLEXY_TOKEN_OK;
}
case 88:
{   
    $this->value = $this->createToken('DSEnd');
    $this->yybegin(YYINITIAL); 
    return FLY_FLEXY_TOKEN_OK; 
}
case 89:
{     
    /* anything inside of php tags */
    return FLY_FLEXY_TOKEN_NONE;
}
case 90:
{ 
    /* php end */
    $this->value = $this->createToken('Php',
        substr($this->yy_buffer,$this->yyPhpBegin ,$this->yy_buffer_end - $this->yyPhpBegin ),
        $this->yyline,$this->yyPhpBegin);
    $this->yybegin(YYINITIAL);
    return FLY_FLEXY_TOKEN_OK;
}
case 91:
{
    // inside a style comment (not - or not --
    // <!^--...-->   -- comment */   
    $this->value = $this->createToken('Comment');
	return FLY_FLEXY_TOKEN_OK;
}
case 92:
{
    // we allow anything inside of comstyle!!!
    $this->value = $this->createToken('Comment');
	return FLY_FLEXY_TOKEN_OK;
}
case 93:
{
	// inside style comment -- without a >
    $this->value = $this->createToken('Comment');
	return FLY_FLEXY_TOKEN_OK;
}
case 94:
{   
    // --> inside a style tag.
    $this->value = $this->createToken('Comment');
    $this->yybegin(YYINITIAL); 
    return FLY_FLEXY_TOKEN_OK; 
}
case 95:
{
    // var in commented out style bit..
    $t =  $this->yytext();
    $t = substr($t,1,-1);
    $this->value = $this->createToken('Var', $t);
    return FLY_FLEXY_TOKEN_OK;
}
case 96:
{
    $this->flexyMethod = substr($this->yytext(),0,-1);
    $this->yybegin(IN_FLEXYMETHOD);
	return FLY_FLEXY_TOKEN_NONE;
}
case 97:
{
    $t =  $this->yytext();
    $t = substr($t,1,-1);
    $this->value = $this->createToken('Var'  , array($t, $this->flexyArgs));
    return FLY_FLEXY_TOKEN_OK;
}
case 99:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 100:
{
    //abcd -- data characters  
    // { and ) added for flexy
    $this->value = $this->createToken('Text');
    return FLY_FLEXY_TOKEN_OK;
}
case 101:
{
    // &abc;
    $this->value = $this->createToken('Text');
    return FLY_FLEXY_TOKEN_OK;
}
case 102:
{
    //<name -- start tag */
    if ($this->options['ignore_html']) {
        return $this->returnSimple();
    }
    $this->tagName = trim(substr($this->yytext(),1));
    $this->tokenName = 'Tag';
    $this->value = '';
    $this->attributes = array();
    $this->yybegin(IN_ATTR);
    return FLY_FLEXY_TOKEN_NONE;
}
case 103:
{ 
    /* <? php start.. */
    //echo "STARTING PHP?\n";
    $this->yyPhpBegin = $this->yy_buffer_start;
    $this->yybegin(IN_PHP);
    return FLY_FLEXY_TOKEN_NONE;
}
case 104:
{
    // &#123;
    $this->value = $this->createToken('Text');
    return FLY_FLEXY_TOKEN_OK;
}
case 105:
{
    // &#abc;
    $this->value = $this->createToken('Text');
    return FLY_FLEXY_TOKEN_OK;
}
case 106:
{
    /* </title> -- end tag */
    if ($this->options['ignore_html']) {
        return $this->returnSimple();
    }
    if ($this->inStyle) {
        $this->inStyle = false;
    }
    $this->tagName = trim(substr($this->yytext(),1));
    $this->tokenName = 'EndTag';
    $this->yybegin(IN_ENDTAG);
    $this->value = '';
    return FLY_FLEXY_TOKEN_NONE;
}
case 107:
{
    /* <!DOCTYPE -- markup declaration */
    if ($this->options['ignore_html']) {
        return $this->returnSimple();
    }
    $this->value = $this->createToken('Doctype');
    $this->yybegin(IN_MD);
    return FLY_FLEXY_TOKEN_OK;
}
case 108:
{
    /* <![ -- marked section */
    return $this->returnSimple();
}
case 109:
{ 
    /* eg. <?xml-stylesheet, <?php ... */
    $t = $this->yytext();
    $tagname = trim(strtoupper(substr($t,2)));
   // echo "STARTING XML? $t:$tagname\n";
    if ($tagname == 'PHP') {
        $this->yyPhpBegin = $this->yy_buffer_start;
        $this->yybegin(IN_PHP);
        return FLY_FLEXY_TOKEN_NONE;
    }
    // not php - it's xlm or something...
    // we treat this like a tag???
    // we are going to have to escape it eventually...!!!
    $this->tagName = trim(substr($t,1));
    $this->tokenName = 'Tag';
    $this->value = '';
    $this->attributes = array();
    $this->yybegin(IN_ATTR);
    return FLY_FLEXY_TOKEN_NONE;
}
case 110:
{
    $this->attrVal[] = $this->yytext();
    return FLY_FLEXY_TOKEN_NONE;
}
case 111:
{
    $this->value = '';
    return FLY_FLEXY_TOKEN_NONE;
}
case 112:
{
    // <foo^<bar> -- unclosed start tag */
    return $this->raiseError("Unclosed tags not supported"); 
}
case 113:
{
    // <img src="xxx" ...ismap...> the ismap */
    $this->attributes[trim($this->yytext())] = true;
    $this->value = '';
    return FLY_FLEXY_TOKEN_NONE;
}
case 114:
{
    // <a href = ^http://foo/> -- unquoted literal HACK */                          
    $this->attributes[$this->attrKey] = trim($this->yytext());
    $this->yybegin(IN_ATTR);
    //   $this->raiseError("attribute value needs quotes");
    $this->value = '';
    return FLY_FLEXY_TOKEN_NONE;
}
case 115:
{
    // <a name = ^12pt> -- number token */
    $this->attributes[$this->attrKey] = trim($this->yytext());
    $this->yybegin(IN_ATTR);
    $this->value = '';
    return FLY_FLEXY_TOKEN_NONE;
}
case 116:
{
    //echo "GOT DATA:".$this->yytext();
    $this->attrVal[] = $this->yytext();
    return FLY_FLEXY_TOKEN_NONE;
}
case 117:
{ 
    $this->value = $this->createToken('WhiteSpace');
    return FLY_FLEXY_TOKEN_OK; 
}
case 118:
{
    return $this->raiseError("illegal character in markup declaration (0x".dechex(ord($this->yytext())).')');
}
case 119:
{   
    $this->value = $this->createToken('Number');
    return FLY_FLEXY_TOKEN_OK; 
}
case 120:
{ 
    $this->value = $this->createToken('Name');
    return FLY_FLEXY_TOKEN_OK; 
}
case 121:
{ 
    $this->value = $this->createToken('NameT');
    return FLY_FLEXY_TOKEN_OK; 
}
case 122:
{ 
    $this->value = $this->createToken('NumberT');
    return FLY_FLEXY_TOKEN_OK; 
}
case 123:
{
    // <!doctype ^%foo;> -- parameter entity reference */
    $this->value = $this->createToken('EntityRef');
    return FLY_FLEXY_TOKEN_OK;
}
case 124:
{ 
    $this->value = $this->createToken('Literal');
    return FLY_FLEXY_TOKEN_OK; 
}
case 125:
{
	// inside comment -- without a >
	return FLY_FLEXY_TOKEN_NONE;
}
case 126:
{
    $t = $this->yytext();
    if ($t{strlen($t)-1} == ",") {
        // add argument
        $this->flexyArgs[] = substr($t,0,-1);
        return FLY_FLEXY_TOKEN_NONE;
    }
    $this->flexyArgs[] = $t;
    return FLY_FLEXY_TOKEN_NONE;
}
case 127:
{
    $t = $this->yytext();
    // add argument
    $this->flexyArgs[] = $t;
    $this->yybegin(IN_FLEXYMETHODQUOTED_END);
    return FLY_FLEXY_TOKEN_NONE;
}
case 128:
{
    // general text in script..
    $this->value = $this->createToken('Text');
    return FLY_FLEXY_TOKEN_OK;
}
case 129:
{ 
    $this->value = $this->createToken('Cdata',$this->yytext(), $this->yyline);
    return FLY_FLEXY_TOKEN_OK;
}
case 130:
{
    // inside a comment (not - or not --
    // <!^--...-->   -- comment */   
    $this->value = $this->createToken('DSComment');
    return FLY_FLEXY_TOKEN_OK;
}
case 131:
{     
    /* anything inside of php tags */
    return FLY_FLEXY_TOKEN_NONE;
}
case 132:
{
    // inside a style comment (not - or not --
    // <!^--...-->   -- comment */   
    $this->value = $this->createToken('Comment');
	return FLY_FLEXY_TOKEN_OK;
}
case 133:
{
    // we allow anything inside of comstyle!!!
    $this->value = $this->createToken('Comment');
	return FLY_FLEXY_TOKEN_OK;
}
case 134:
{
	// inside style comment -- without a >
    $this->value = $this->createToken('Comment');
	return FLY_FLEXY_TOKEN_OK;
}
case 136:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 137:
{
    //abcd -- data characters  
    // { and ) added for flexy
    $this->value = $this->createToken('Text');
    return FLY_FLEXY_TOKEN_OK;
}
case 138:
{
    $this->attrVal[] = $this->yytext();
    return FLY_FLEXY_TOKEN_NONE;
}
case 139:
{
    $this->value = '';
    return FLY_FLEXY_TOKEN_NONE;
}
case 140:
{
    // <a name = ^12pt> -- number token */
    $this->attributes[$this->attrKey] = trim($this->yytext());
    $this->yybegin(IN_ATTR);
    $this->value = '';
    return FLY_FLEXY_TOKEN_NONE;
}
case 141:
{
    //echo "GOT DATA:".$this->yytext();
    $this->attrVal[] = $this->yytext();
    return FLY_FLEXY_TOKEN_NONE;
}
case 142:
{
    return $this->raiseError("illegal character in markup declaration (0x".dechex(ord($this->yytext())).')');
}
case 143:
{ 
    $this->value = $this->createToken('Cdata',$this->yytext(), $this->yyline);
    return FLY_FLEXY_TOKEN_OK;
}
case 144:
{     
    /* anything inside of php tags */
    return FLY_FLEXY_TOKEN_NONE;
}
case 145:
{
    // inside a style comment (not - or not --
    // <!^--...-->   -- comment */   
    $this->value = $this->createToken('Comment');
	return FLY_FLEXY_TOKEN_OK;
}
case 146:
{
    // we allow anything inside of comstyle!!!
    $this->value = $this->createToken('Comment');
	return FLY_FLEXY_TOKEN_OK;
}
case 148:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 149:
{
    //abcd -- data characters  
    // { and ) added for flexy
    $this->value = $this->createToken('Text');
    return FLY_FLEXY_TOKEN_OK;
}
case 150:
{
    $this->attrVal[] = $this->yytext();
    return FLY_FLEXY_TOKEN_NONE;
}
case 151:
{
    //echo "GOT DATA:".$this->yytext();
    $this->attrVal[] = $this->yytext();
    return FLY_FLEXY_TOKEN_NONE;
}
case 152:
{
    return $this->raiseError("illegal character in markup declaration (0x".dechex(ord($this->yytext())).')');
}
case 153:
{ 
    $this->value = $this->createToken('Cdata',$this->yytext(), $this->yyline);
    return FLY_FLEXY_TOKEN_OK;
}
case 155:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 156:
{
    return $this->raiseError("illegal character in markup declaration (0x".dechex(ord($this->yytext())).')');
}
case 158:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 160:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 162:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 164:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 166:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 168:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 170:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 172:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 174:
{
    return $this->raiseError("unexpected something: (".$this->yytext() .") character: 0x" . dechex(ord($this->yytext())));
}
case 379:
{
    //abcd -- data characters  
    // { and ) added for flexy
    $this->value = $this->createToken('Text');
    return FLY_FLEXY_TOKEN_OK;
}
case 380:
{
    // <a name = ^12pt> -- number token */
    $this->attributes[$this->attrKey] = trim($this->yytext());
    $this->yybegin(IN_ATTR);
    $this->value = '';
    return FLY_FLEXY_TOKEN_NONE;
}
case 381:
{
    $t = $this->yytext();
    // add argument
    $this->flexyArgs[] = $t;
    $this->yybegin(IN_FLEXYMETHODQUOTED_END);
    return FLY_FLEXY_TOKEN_NONE;
}
case 382:
{
    $t = $this->yytext();
    // add argument
    $this->flexyArgs[] = $t;
    $this->yybegin(IN_FLEXYMETHODQUOTED_END);
    return FLY_FLEXY_TOKEN_NONE;
}

                        }
                    }
                    if ($this->_fatal) {
                        return;
                    }
                    $yy_initial = true;
                    $yy_state = $this->yy_state_dtrans[$this->yy_lexical_state];
                    $yy_next_state = YY_NO_STATE;
                    $yy_last_accept_state = YY_NO_STATE;
                    $this->yy_mark_start();
                    $yy_this_accept = $this->yy_acpt[$yy_state];
                    if (YY_NOT_ACCEPT != $yy_this_accept) {
                        $yy_last_accept_state = $yy_state;
                        $this->yy_buffer_end = $this->yy_buffer_index;
                    }
                }
            }
        }
        return FLY_FLEXY_TOKEN_NONE;
    }
}
