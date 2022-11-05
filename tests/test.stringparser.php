<?php
    require_once(__DIR__ . "/../src/StringParser.php");
    require_once(__DIR__ . "/../src/StringParserType.php");

    use stringparser\StringParser;
    use stringparser\StringParserType;

    function executeTests(array $tests) {
        $sp = new StringParser();

        $returnValue = ["success" => [],"failed" => []];

        for ($cnt = 0; $cnt < count($tests); $cnt ++) {
            $test = $tests[$cnt];

            $data = $test["data"];
            $parsedStringType = (isset($test["stringParserType"]) ? $test["stringParserType"] : StringParserType::WHITESPACE());
            
            

            if (is_null($parsedArray = $sp->Parse($data, $parsedStringType))) {
                $returnValue["failed"][$test["data"]] = [
                    "parsedStringType" => $parsedStringType,                    
                    "parsedArrayMD5"   => "",
                    "unparsedValue"    => "",                    
                    "reparsedArrayMD5" => "",
                    "parsedArray"      => "(false)",
                    "reparsedArray"    => ""
                ];
            } else {
                print_r($parsedArray);

                $parsedArrayMD5 = md5(serialize($parsedArray));
                $unparsedValue = StringParser::UnparseArray($parsedArray, $parsedStringType);

                $newReturnValue = [
                    "parsedStringType" => $parsedStringType,
                    "parsedArrayMD5"   => $parsedArrayMD5,
                    "unparsedValue"    => $unparsedValue,
                    "reparsedArrayMD5" => ""
                ];

                if (is_null($reparsedArray = $sp->Parse($unparsedValue, $parsedStringType))) {
                    $newReturnValue["parsedArray"]   = $reparsedArray;
                    $newReturnValue["reparsedArray"] = "(false)";
                    
                    $returnValue["failed"][$test["data"]] = $newReturnValue;
                } else {
                    $newReturnValue["reparsedArrayMD5"] = md5(serialize($reparsedArray));
                
                    if ($newReturnValue["reparsedArrayMD5"] == $newReturnValue["parsedArrayMD5"]) {
                        $returnValue["success"][$test["data"]] = $newReturnValue;
                    } else {
                        $newReturnValue["parsedArray"] = $parsedArrayMD5;
                        $newReturnValue["reparsedArray"] = $reparsedArray;

                        $returnValue["failed"][$test["data"]] = $newReturnValue;
                    }
                }
            } 
        }   
        
        return $returnValue;            
    }



    $tests = [
        [
            "stringParserType" => StringParserType::SQL(),
            "data" => "SELECT * FROM people WHERE (id=groupid OR name='myname') LIMIT/* 1,*/ 2"
        ],[
            "stringParserType" => StringParserType::HTML(),
            "data" => "
                <html>
                    <head>
                        <title>
                            Page Title
                        </title>            
                    </head>
                    <body class=\"bodyClass\">
                        <title>
                            Page Title
                        </title>

                        <div id=\"divid\" class=\"divClass divClass2\">
                            <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">
                                <th>
                                    <td width=\"50%\">
                                        First Name
                                    </td>
                                    <td width=\"50%\">
                                        Last Name
                                    </td>
                                </th>

                                <tr>
                                    <td width=\"50%\">
                                        Jason
                                    </td>
                                    <td width=\"50%\">
                                        Thompson
                                    </td>
                                </tr>

                                <tr>
                                    <td width=\"50%\">
                                        Stacy
                                    </td>
                                    <td width=\"50%\">
                                        Thompson
                                    </td>
                                </tr>

                                <tr>
                                    <td width=\"50%\">
                                        Peyton
                                    </td>
                                    <td width=\"50%\">
                                        Thompson
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </body>        
                </html>
            "
        ]
    ];


    die(print_r(executeTests($tests)));



    $sp = new StringParser();

    $htmlData = "
        <html>
            <head>
                <title>
                    Page Title
                </title>            
            </head>
            <body class=\"bodyClass\">
                <title>
                    Page Title
                </title>

                <div id=\"divid\" class=\"divClass divClass2\">
                    <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">
                        <th>
                            <td width=\"50%\">
                                First Name
                            </td>
                            <td width=\"50%\">
                                Last Name
                            </td>
                        </th>

                        <tr>
                            <td width=\"50%\">
                                Jason
                            </td>
                            <td width=\"50%\">
                                Thompson
                            </td>
                        </tr>

                        <tr>
                            <td width=\"50%\">
                                Stacy
                            </td>
                            <td width=\"50%\">
                                Thompson
                            </td>
                        </tr>

                        <tr>
                            <td width=\"50%\">
                                Peyton
                            </td>
                            <td width=\"50%\">
                                Thompson
                            </td>
                        </tr>
                    </table>
                </div>
            </body>        
        </html>
    ";

    print_r($sp->Parse($htmlData, StringParserType::HTML()));


?>