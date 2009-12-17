<?php
/**
 * Validate Filter Class File
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright Copyright (c) 2006-2009 Satoshi Nishimura
 */

/**
 * Validate Filter Class
 *
 * TODO: refactoring
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright Copyright (c) 2006-2009 Satoshi Nishimura
 */
class Laiz_Validator_Filter
{
    /** @var Array */
    private $config = array();

    /** @var Laiz_Validator */
    private $validator;

    /** @var Laiz_Request */
    private $request;

    /** @var Laiz_Container */
    private $container;

    /** @var Laiz_Validator_Response */
    private $response;

    /** @var string Key name of default error message */
    private $COMMON_ERROR_KEY = 'message';

    /** @var string */
    private $VALIDATION_ERROR_MESSAGE_PREFIX = 'error';

    public function __construct(Laiz_Validator $validator, Laiz_Request $req
                                , Laiz_Container $container){
        $this->validator = $validator;
        $this->request = $req;
        $this->container = $container;
    }

    /**
     *
     */
    public function setValidationErrorMessagePrefix($prefix)
    {
        $this->VALIDATION_ERROR_MESSAGE_PREFIX = $prefix;
    }

    public function setCommonErrorKey($keyName)
    {
        $this->COMMON_ERROR_KEY = $keyName;
    }

    public function setConfig(Array $config)
    {
        $this->config = $config;
    }

    /**
     * 入力チェックを実行する
     *
     * @return mixed エラーがある場合にはアクション転送に対するキーを返却
     * @access public
     */
    public function run(){
        $this->response = new Laiz_Validator_Response($this->request);
        $this->container->registerInterface($this->response, 1);
        $validates = $this->config;

        if (!is_array($validates)){ return; }
        if ($validates === array()){ return; }

        // 必須の共通設定チェック
        if (!isset($validates['error'])){
            trigger_error('error setting not found in validator section.', E_USER_ERROR);
            return false;

        }
        $result = $validates['error'];

        $defaultStopper = 0;
        $checkedVars = array();
        $defaultErrorMessage = '';
        $commonErrorMessage = null;
        $commonErrorKey     = $this->COMMON_ERROR_KEY;
        $existsError = false;
        // 共通設定
        if (isset($validates['stopper'])){
            $defaultStopper = $validates['stopper'];
        }
        if (isset($validates['defaultErrorMessage'])){
            $defaultErrorMessage = $validates['defaultErrorMessage'];
        }
        if (isset($validates['commonErrorMessage'])){
            if (isset($validates['commonErrorKey']))
                $commonErrorKey = $validates['commonErrorKey'];

            $commonErrorMessage = $validates['commonErrorMessage'];
        }

        foreach ($validates as $key => $value){
            if (preg_match('/^commonErrorMessage:([^ :]*)$/', trim($key), $matches)){
                $commonErrorKey     = $matches[1];
                $commonErrorMessage = $value;
                continue;
            }

            /*
             * バリデート処理
             */
            // 設定の解析
            $keys = explode(':', $key);
            $keys = array_map('trim', $keys);
            @list($varName, $func, $stopper, $errMsgName) = $keys;
            if (!$func)
                continue;

            // ストッパーが指定されていない場合の設定
            if (!isset($stopper) || $stopper === ''){
                $stopper = $defaultStopper;
            }

            // エラーメッセージが指定されていない場合の設定
            $msg = strlen($value) != 0 ? $value : $defaultErrorMessage;

            // 変数名の解析
            $varNames = explode(',', $varName);
            $varNames = array_map('trim', $varNames);
            foreach ($varNames as $varName){
                $ret = $this->validate($varName, $checkedVars, $stopper, $func, $errMsgName, $msg);

                if ($ret && $commonErrorMessage){
                    $this->request->add($commonErrorKey, $commonErrorMessage);
                }
                if ($ret === 'stop'){
                    return $result;
                
                }elseif ($ret === true){
                    $existsError = true;
                }
            }
        }

        if ($existsError){
            // エラーによるアクション転送
            return $result;
        }
    }

    /**
     * 変数が配列かどうかで振り分けてバリデートを実行する
     *
     * @param string $varName 変数名
     * @param string[] &$checkedVars ストッパー有りのチェック済変数名
     * @param string $stopper 'all'なら全体に対するストッパー、それ以外なら変数に対するストッパー
     * @param string $func バリデートの実装関数
     * @param string $msgName エラーメッセージ格納変数名
     * @param string $msg エラーメッセージ
     * @return mixed エラーがある場合に true、ストッパーがある場合に 'stop'
     * @access private
     */
    private function validate($varName, &$checkedVars, $stopper, $func, $msgName, $msg){
        // バリデートメソッドの決定
        $args = array();
        if (strpos($func, '.') !== false){
            // 引数がある場合
            $args = explode('.', $func);
            $args = array_map('trim', $args);
            $func = array_shift($args);
        }
        $func = 'is' . ucfirst($func);

        // バリデータ引数の設定
        $emptyFlag = true;
        foreach ($args as $key => $arg){
            if ($arg[0] === '$'){
                if (preg_match('/^([^[]+)\[\]$/', ltrim($arg, '$'), $matches)){
                    $args[$key] = $this->request->get($matches[1]);
                }else{
                    $args[$key] = $this->request->get(ltrim($arg, '$'));
                }

                // バリデータ変数が存在する場合はフラグをセット
                if ($args[$key] !== '' || $args[$key] !== null)
                    $emptyFlag = false;
            }
        }

        // バリデート関数が存在するかどうかをチェック
        if (!is_callable(array($this->validator, $func))){
            // method_existsは引数がオブジェクトなのでis_callableを使う(PHP < PHP5.1)

            trigger_error("Not exists $func function in validator class.", E_USER_WARNING);
            return true;
        }


        $argsWithoutKeyVar = $args;
        if(preg_match('/([^[]+)\[\]$/', $varName, $matches)){
            $varName = $matches[1];
            $keyVar = $this->request->get($varName);

            // チェックする変数名が[]で終わっている場合は、配列の全ての変数をチェックする
            // この場合のエラーメッセージ格納変数は、変数名の最後に配列の添字が付く
            if (is_array($keyVar)){
                $existsError = false;
                foreach ($keyVar as $k => $v){
                    $newMsgName = $this->VALIDATION_ERROR_MESSAGE_PREFIX.ucfirst($varName) . $k;
                    $args = $argsWithoutKeyVar;
                    foreach ($args as $_k => $_v){
                        if (is_array($_v)){
                            $args[$_k] = $_v[$k]; // ==check== わかりやすく
                        }
                    }

                    // バリデータ引数に設定ファイルのキーを追加
                    array_unshift($args, $v);

                    $ret = $this->_validate($varName, $checkedVars, $stopper, $func, $args, $newMsgName, $msg);
                    if ($ret === 'stop')
                        return $ret;
                    $existsError = ($ret || $existsError);
                }

                return $existsError;
            }
        }else{
            $keyVar = $this->request->get($varName);

            // バリデータ引数に設定ファイルのキーを追加
            array_unshift($args, $keyVar);

            return $this->_validate($varName, $checkedVars, $stopper, $func, $args, $msgName, $msg);
        }
    }

    /**
     * 変数ひとつに対するバリデートを実行する
     *
     * @param string $varName 変数名
     * @param string[] &$checkedVars ストッパー有りのチェック済変数名
     * @param string $stopper 'all'なら全体に対するストッパー、それ以外なら変数に対するストッパー
     * @param string $func バリデートの実装関数
     * @param string $args バリデート関数を呼ぶときの引数の配列
     * @param string $msgName エラーメッセージ格納変数名
     * @param string $msg エラーメッセージ
     * @return mixed エラーがある場合に true、ストッパーがある場合に 'stop'
     * @access private
     */
    private function _validate($varName, &$checkedVars, $stopper, $func, $args, $msgName, $msg){
        // 変数が空の場合はバリデータをスキップ
        if ($func != 'isRequired'){
            $argsAreEmpty = true;
            foreach ($args as $arg){
                if (is_string($arg) && strlen($arg) > 0){
                    $argsAreEmpty = false;
                }elseif (is_array($arg)){
                    foreach ($arg as $a)
                        if (strlen($a) > 0)
                            $argsAreEmpty = false;
                }
            }
            // 変数が空で尚かつバリデータが required でなければ return
            if ($argsAreEmpty)
                return false;
        }

        // ストッパーを1以外で指定した場合は違う変数でもグループ化できる
        if ($stopper === '1'){
            $checkStopper = $varName;
        }else{
            $checkStopper = $stopper;
        }
        // ストッパーが設定されている変数ならばバリデートを行わない
        if (in_array($checkStopper, $checkedVars, true)){
            return false;
        }


        // バリデートメソッドを実行
        if (call_user_func_array(array($this->validator, $func), $args)){
            return false;
        }
                    
        // エラーメッセージの格納
        if ($msg){
            $errMsgName = $msgName ? $msgName
                : $this->VALIDATION_ERROR_MESSAGE_PREFIX.ucfirst($varName);

            $this->response->set($errMsgName, $msg);
        }

            
        /*
         * バリデートの事後処理
         */
        if ($stopper === 'all'){
            // ストッパー設定によってアクション転送を即実行
            return 'stop';
        }

        // チェック済変数を設定する
        if ($stopper){
            if ($stopper !== '1'){
                // ストッパーに1以外を設定した場合はそれらの変数をグループ化する
                $varName = $stopper;
            }
            
            $checkedVars[] = $varName;
        }

        return true;
    }
}
