<?php
    namespace stringparser;

    require_once(__DIR__ . "/../vendor/autoload.php");

    use enums\Enum;

    class StringParserType extends Enum {
    //class NullDbStringParserType extends Enum {
        public static function SQL() {return static::__callStatic("SQL");}
        public static function EQUATION() {return static::__callStatic("EQUATION");}
        public static function WHITESPACE() {return static::__callStatic("WHITESPACE");}
        public static function HTML() {return static::__callStatic("HTML");}

        protected static function EnumElements(): array {
            return [
                "HTML" => [
                    "beginNoParse"   => ["<!--", "'", '"'],
                    "endNoParse"     => ["-->", "'", '"'],
                    "beginRecursion" => ["{", "(", "<html", "<head", "<body", "<table", "<th", "<tr", "<td", "<div", "<"],
                    "endRecursion"   => ["}", ")", "</html>", "</head>", "</body>", "</table>", "</th>", "</tr>", "</td>", "</div>", ">"],
                    "newKeep"        => [">", "="],
                    "newSkip"        => [" "],
                    "discard"        => ["<!--", "\n", "\r"]
                ],

                "SQL" => [
                    "beginNoParse"   => ["'", '"', '`'],
                    "endNoParse"     => ["'", '"', '`'],
                    "beginRecursion" => ["("],
                    "endRecursion"   => [")"],
                    "newKeep"        => ["*", "+", "-", "/", ","],
                    "newSkip"        => [" ", "\t", "\n", "\r"],
                    "discard"        => []
                ],

                "EQUATION" => [
                    "beginNoParse"   => ["'", '"', '`'],
                    "endNoParse"     => ["'", '"', '`'],
                    "beginRecursion" => ["("],
                    "endRecursion"   => [")"],
                    "newKeep"        => ["**", "*", "+", "-", "/", ","],
                    "newSkip"        => [" ", "\t", "\n", "\r"],
                    "discard"        => []
                ],

                "WHITESPACE" => [
                    "beginNoParse"   => [],
                    "endNoParse"     => [],
                    "beginRecursion" => [],
                    "endRecursion"   => [],
                    "newKeep"        => [],
                    "newSkip"        => [" ", "\t", "\n", "\r"],
                    "discard"        => []
                ]
            ];
        }
    }

?>