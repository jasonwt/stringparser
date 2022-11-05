<?php
    namespace stringparser;

    require_once(__DIR__ . "/StringParserType.php");    

    class StringParser {
        protected $errors = [];

        public function __construct() {

        }

        public function __destruct() {

        }

        static public function GetDebugString() {
            $debugString = "";

            for ($cnt = 0; $cnt < count(func_get_args()); $cnt ++)
                $debugString .= print_r(func_get_args()[$cnt], true);

            $debugString .= "\n\nStack trace:\n";

            for ($cnt = 2; $cnt < count(debug_backtrace()); $cnt ++)
                $debugString .= "#" . ($cnt-2) . " " . debug_backtrace()[$cnt]["file"] . "(" . debug_backtrace()[$cnt]["line"] . "): " . debug_backtrace()[$cnt]["function"] . "()\n";

            return $debugString . "\n";
        }
//
        static public function Die(string $message) {
            die ($message . self::GetDebugString());
        }
//        
        public function Error(int $index = 0) {
            if (count($this->errors) == 0)
                return "";

            return $this->errors[max(0, min($index, count($this->errors)))]["error"];
        }
//
        public function Errno(int $index = 0) {
            if (count($this->errors) == 0)
                return 0;

            return $this->errors[max(0, min($index, count($this->errors)))]["errno"];
        }
//
        public function Errors() : array {
            return $this->errors;
        }
//
        protected function SetError(int $errno, string $error) : bool {
            if ($errno != 0) {
                $this->errors[] = ["errno" => $errno, "error" => $error . self::GetDebugString()];                

                return false;
            }

            return true;
        }
//
        protected function ClearErrors() {
            $this->errors = [];            
        }
//
        static public function UnparseArray(array $parsedArray, ?StringParserType $parsedStringType = null) : string {
            $arraySymbols = [];

            if (!is_null($parsedStringType)) {
                $parsedStringTypeValue = $parsedStringType->Value();

                for ($cnt = 0; $cnt < count($parsedStringTypeValue["beginRecursion"]); $cnt ++)
                    $arraySymbols[$parsedStringTypeValue["beginRecursion"][$cnt]] = $parsedStringTypeValue["endRecursion"][$cnt];                    
            }

            $returnValue = "";

            foreach ($parsedArray as $k => $v) {
                if (is_array($v)) {
                    $opening = "<ARRAY>";
                    $closing = "</ARRAY>";

                    if (isset($arraySymbols[$v["opening"]])) {
                        $opening = $v["opening"];
                        $closing = $v["closing"];
                    }

                    if (is_array($v["children"])) {
                        $returnValue .= (($returnValue == "") ? "" : " ") . $opening . self::UnparseArray($v["children"], $parsedStringType) . $closing;
                    } else {
                        $returnValue .= (($returnValue == "") ? "" : " ") . $v["children"];
                    }
                } else {
                    $returnValue .= (($returnValue == "") ? "" : " ") . $v;
                }
            }

            return $returnValue;
        }        
//
        protected function ArrayFilter(array $arr, string $str) : ?string {
            $values = array_filter($arr, function ($v, $k) use($str) {
                return ($v == substr($str, 0, strlen($v)));                    
            }, ARRAY_FILTER_USE_BOTH);

            if (count($values) == 0)
                return null;

            return array_keys($values)[0];            
        }

        public function Parse(
            string $str, 
            StringParserType $stringParserType, 
            ?callable $onBeginNoParse = null, 
            ?callable $onEndNoParse = null,
            ?callable $onBeginRecursion = null,
            ?callable $onEndRecursion = null,
            ?callable $onNewKeep = null,
            ?callable $onNewSkip = null,
            ?callable $onDiscard = null) : ?array {

            $this->ClearErrors();

            $results = $this->InternalParse(
                $str, 
                $stringParserType, 
                null, 
                null,
                $onBeginNoParse, 
                $onEndNoParse,
                $onBeginRecursion,
                $onEndRecursion,
                $onNewKeep,
                $onNewSkip,
                $onDiscard
            );

            if (is_null($results))
                die ('is_null');

            return $results;
        }

        protected function InternalParse(
            string &$str, 
            StringParserType $stringParserType, 
            ?string $beginKey, 
            ?string $endKey, 
            ?callable $onBeginNoParse, 
            ?callable $onEndNoParse,
            ?callable $onBeginRecursion,
            ?callable $onEndRecursion,
            ?callable $onNewKeep,
            ?callable $onNewSkip,
            ?callable $onDiscard) {

//            echo "InternalParse(str: $str, stringParserType: " . $stringParserType->Name() . ", beginKey: $beginKey, endKey: $endKey)\n";

            $parameters = $stringParserType->Value();

            $returnValue = array();
    
            $strPos = 0;

            $checkString = $str;
    
            while ($strPos < strlen($str)) {
                if (!is_null($beginKey) && in_array($beginKey, $parameters["beginNoParse"])) {
                    $nop = 0;
    
                    do {
                        if (($nop = strpos($str, $endKey, $nop+1)) === false)
                            return null;
    
                        for ($ncnt = $nop - 1; $ncnt >= 0 && $str[$ncnt] == "\\"; $ncnt --);
                            
                        if ((($nop - ($ncnt+1)) % 2) == 0)
                            break;
    
                    } while ($nop < strlen($str));
    
                    $returnValue = substr($str, 0, $nop);
                    $str = substr($str, $nop+strlen($endKey));
    
                    return $beginKey . $returnValue . $endKey;
                } else if (!is_null($endKey) && substr($str, $strPos, strlen($endKey)) == $endKey) {
                    if ($strPos > 0)
                        $returnValue[] = substr($str, 0, $strPos);
                    
                    $str = substr($str, $strPos+strlen($endKey));                        

                    return $returnValue;                      
                } else {
                    $results = null;
                    $strKey = "";

                    if (!is_null($firstNoParseKey = $this->ArrayFilter($parameters["beginNoParse"], substr($str, $strPos)))) {
                        $strKey      = $parameters["beginNoParse"][$firstNoParseKey];
                        $strKeyClose = $parameters["endNoParse"][$firstNoParseKey];
                        
                        if ($strPos > 0)
                            $returnValue[] = substr($str, 0, $strPos);
                        
                        $str = substr($str, $strPos+strlen($strKey));

                        if (($results = $this->InternalParse($str, $stringParserType, $strKey, $strKeyClose, $onBeginNoParse, $onEndNoParse, $onBeginRecursion, $onEndRecursion, $onNewKeep, $onNewSkip, $onDiscard)) === false) {
                            return false;
                        }

                    } else if (!is_null(($firstRecursionKey = $this->ArrayFilter($parameters["beginRecursion"], substr($str, $strPos))))) {
                        $strKey      = $parameters["beginRecursion"][$firstRecursionKey];
                        $strKeyClose = $parameters["endRecursion"][$firstRecursionKey];
                        
                        if ($strPos > 0)
                            $returnValue[] = substr($str, 0, $strPos);
                        
                        $str = substr($str, $strPos+strlen($strKey));

                        if (($results = $this->InternalParse($str, $stringParserType, $strKey, $strKeyClose, $onBeginNoParse, $onBeginNoParse, $onEndNoParse, $onBeginRecursion, $onEndRecursion, $onNewKeep, $onNewSkip, $onDiscard)) === false) {
                            return false;
                        }
                            
                        $results = array("opening" => $strKey, "closing" => $strKeyClose, "children" => $results);

                    } else if (!is_null(($firstNewKeepKey = $this->ArrayFilter($parameters["newKeep"], substr($str, $strPos))))) {
                        $strKey = $parameters["newKeep"][$firstNewKeepKey];

                        if ($strPos > 0)
                            $returnValue[] = substr($str, 0, $strPos);

                        $results = $strKey;

                        $str = substr($str, $strPos + strlen($strKey));                        

                    } else if (!is_null(($firstNewSkipKey = $this->ArrayFilter($parameters["newSkip"], substr($str, $strPos))))) {
                        $strKey = $parameters["newSkip"][$firstNewSkipKey];

                        if ($strPos > 0)
                            $results = substr($str, 0, $strPos);

                        $str = substr($str, $strPos + strlen($strKey));
                        
                    } else if (!is_null(($firstNoSkipKey = $this->ArrayFilter($parameters["discard"], substr($str, $strPos))))) {
                        $strKey = $parameters["discard"][$firstNoSkipKey];

                        $str = substr($str, 0, $strPos) . substr($str, $strPos+strlen($strKey));

                        continue;

                    } else {
                        $strPos ++;
                        continue;
                    }

                    if (!is_null($results) && !in_array($strKey, $parameters["discard"]))
                        $returnValue[] = $results;

                    $strPos = 0;

                }
                
            }
    
            if ($str != "")
                $returnValue[] = $str;

            if (is_null($endKey)) {
                
                return $returnValue;
            } else {
//                self::$error = $checkString;
  //              self::$partialParsedArray = $returnValue;

                return null;
            }
        }
    }
?>