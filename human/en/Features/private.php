name = "private Keyword";
id = "private"
alias[] = ""
description = "The private keyword is part of the three keywords to define visibility of a method, property or constant. It is the strictest level of visibility : it restrict usage to the current class only. Child class can't redefine it, nor access it.

The private keyword cannot be used with the final keyword : a private method is not visible in the child classes, and can't also be redefined.

"
code = "<?php

class x {
    private const X = 1;
    
    final function method() { 
        echo self::X;
    }
}

?>"
documentation = "https://www.php.net/manual/en/language.oop5.visibility.php"
analyzers[] = ""
cobblers[] = ""
phpVersionSince = ""
phpVersionUntil = ""
related[] = "final"
related[] = "visibility"
