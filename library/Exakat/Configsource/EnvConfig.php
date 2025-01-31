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

namespace Exakat\Configsource;

use Exakat\Project as Project;

class EnvConfig extends Config {
    protected $config  = array();

    public function loadConfig(Project $project): ?string {
        if (!empty($e = getenv('EXAKAT_IGNORE_RULES'))) {
            $this->config['ignore_rules'] = str2array($e);
        }

        if (!empty($e = getenv('EXAKAT_IGNORE_DIRS'))) {
            $this->config['ignore_dirs'] = str2array($e);
        }

        if (!empty($e = getenv('EXAKAT_INCLUDE_DIRS'))) {
            $this->config['include_dirs'] = str2array($e);
        }

        if (!empty($e = getenv('EXAKAT_FILE_EXTENSIONS'))) {
            $this->config['file_extensions'] = str2array($e);
            $this->config['file_extensions'] = $this->cleanFileExtensions($this->config['file_extensions']);
        }

        if (!empty($e = getenv('EXAKAT_PROJECT_REPORTS'))) {
            $this->config['project_reports'] = str2array($e);
            $this->config['project_reports'] = $this->cleanProjectReports($this->config['project_reports']);
        }

        if (!empty($e = getenv('EXAKAT_PROJECT_RULESETS'))) {
            $this->config['project_rulesets'] = str2array($e);
            $this->config['project_rulesets'] = $this->cleanProjectReports($this->config['project_rulesets']);
        }

        return empty($this->config) ? self::NOT_LOADED : 'environnment';
    }
}

?>