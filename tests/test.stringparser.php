<?php
    require_once(__DIR__ . "/../src/StringParser.php");
    require_once(__DIR__ . "/../src/StringParserType.php");

    use stringparser\StringParser;
    use stringparser\StringParserType;

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