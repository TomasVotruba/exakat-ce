<?php declare(strict_types = 1);
/*
 * Copyright 2012-2022 Damien Seguy – Exakat SAS <contact(at)exakat.io>
 * This file is part of Exakat.
 *
 * Exakat is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Exakat is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Exakat.  If not, see <http://www.gnu.org/licenses/>.
 *
 * The latest code can be found at <http://exakat.io/>.
 *
*/

namespace Exakat\Analyzer\Structures;

use Exakat\Analyzer\Analyzer;
use Exakat\Data\Methods;

class UselessCasting extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/PropagateCalls',
                     'Complete/VariableTypehint',
                    );
    }

    public function analyze(): void {
        // Function returning a type, then casted to that type
        $casts = array('T_STRING_CAST'  => 'string',
                       'T_BOOL_CAST'    => 'bool',
                       'T_INT_CAST'     => 'int',
                       'T_ARRAY_CAST'   => 'array',
                       'T_DOUBLE_CAST'  => 'float'
                  );

        $returnTypes = $this->methods->getFunctionsByReturn(Methods::STRICT);

        foreach($casts as $token => $type) {
            if (is_array($type)) {
                $returned = array();
                foreach($type as $t) {
                    $returned[] = $returnTypes[$t];
                }
                $returned = array_merge(...$returned);
            } else {
                $returned = $returnTypes[$type];
            }

            // native PHP functions
            $this->atomIs('Cast')
                 ->analyzerIsNot('self')
                 ->tokenIs($token)
                 ->outIs('CAST')
                 ->outIsIE('CODE') // In case there are some parenthesis
                 ->atomIs('Functioncall')
                 ->fullnspathIs($returned)
                 ->back('first');
            $this->prepareQuery();

            // custom user methods
            $this->atomIs('Cast')
                 ->analyzerIsNot('self')
                 ->tokenIs($token)
                 ->outIs('CAST')
                 ->outIsIE('CODE') // In case there are some parenthesis
                 ->atomIs(self::CALLS)
                 ->inIs('DEFINITION')
                 ->not(
                    $this->side()
                         ->outIs('RETURNTYPE')
                         ->count()
                         ->isMore(1)
                 )
                 ->outIs('RETURNTYPE')
                 ->is('fullnspath', makeFullNsPath($type))
                 ->back('first');
            $this->prepareQuery();
        }

        // function foo(array $a) { (array) $a; }
        $this->atomIs('Cast')
             ->analyzerIsNot('self')
             ->savePropertyAs('token', 'cast')
             ->outIs('CAST')
             ->outIsIE('CODE') // In case there are some parenthesis
             ->goToTypehint()
             ->has('fullnspath')
             ->raw(<<<'GREMLIN'
filter{ 
    (cast == "T_ARRAY_CAST"             && it.get().value("fullnspath") == "\\array")  || 
    (cast == "T_INT_CAST"               && it.get().value("fullnspath") == "\\int")    || 
    (cast == "T_DOUBLE_CAST"            && it.get().value("fullnspath") == "\\float")  || 
    (cast == "T_STRING"                 && it.get().value("fullnspath") == "\\object") || 
    (cast == "T_NAME_FULLY_QUALIFIED"   && it.get().value("fullnspath") == "\\object") || 
    (cast == "T_NAME_RELATIVE"          && it.get().value("fullnspath") == "\\object") || 
    (cast == "T_NAME_QUALIFIED"         && it.get().value("fullnspath") == "\\object") || 
    (cast == "T_BOOL_CAST"              && it.get().value("fullnspath") == "\\bool")   || 
    (cast == "T_STRING_CAST"            && it.get().value("fullnspath") == "\\string")
    ; 
}

GREMLIN
)
             ->inIs('TYPEHINT')
             ->is('typehint', 'one')
             ->back('first');
        $this->prepareQuery();

        // (bool) ($a > 2)
        $this->atomIs('Cast')
             ->tokenIs('T_BOOL_CAST')
             ->followParAs('CAST')
             ->atomIs('Comparison')
             ->back('first');
        $this->prepareQuery();

        // (int) 100
        $this->atomIs('Cast')
             ->tokenIs('T_INT_CAST')
             ->outIsIE('CODE')
             ->outIs('CAST')
             ->atomIsNot(array('Coalesce', 'Ternary'))
             ->atomIs('Integer', self::WITH_CONSTANTS)
             ->back('first');
        $this->prepareQuery();
    }
}

?>
