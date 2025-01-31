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


namespace Exakat\Analyzer\Type;

use Exakat\Analyzer\Analyzer;

class NoRealComparison extends Analyzer {
    public function analyze(): void {
        // 1.2 == 3.4
        // 1.2 == 3.4 + 0
        $this->atomIs('Comparison')
             ->codeIs(array('==', '!=', '===', '!=='), self::TRANSLATE, self::CASE_SENSITIVE)
             ->outIs(array('LEFT', 'RIGHT'))
             ->atomInsideNoDefinition(array('Float', 'Identifier', 'Nsname', 'Staticconstant'))
             ->hasNoIn(array('ARGUMENT', 'INDEX'))
             ->atomIs('Float', self::WITH_CONSTANTS)
             ->back('first');
        $this->prepareQuery();
    }
}

?>
