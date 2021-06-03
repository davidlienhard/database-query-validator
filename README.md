# davidlienhard/database-query-validator
🐘 php tool to validate database queries written using [`davidlienhard/database`](https://github.com/davidlienhard/database)

[![Latest Stable Version](https://img.shields.io/packagist/v/davidlienhard/database-query-validator.svg?style=flat-square)](https://packagist.org/packages/davidlienhard/database-query-validator)
[![Source Code](https://img.shields.io/badge/source-davidlienhard/database--query--validator-blue.svg?style=flat-square)](https://github.com/davidlienhard/database-query-validator)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/davidlienhard/database-query-validator/blob/master/LICENSE)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.0-8892BF.svg?style=flat-square)](https://php.net/)
[![CI Status](https://github.com/davidlienhard/database-query-validator/actions/workflows/check.yml/badge.svg)](https://github.com/davidlienhard/database-query-validator/actions/workflows/check.yml)

## Configuration
This tool requires a file named `query-validator.json` in your main project directory. CLI arguments are not supported at the moment.
The following configuration options are possible:
 - **`paths`** (`array`): list of paths (folders or files) to scan.
 - **`exclusions`** (`array`): list of paths to exclude from the scans
 - **`dumpfile`** (`string`): path to a mysql dump file to use for type-checks
 - **`parameters`** (`object`):
   - `ignoresyntax` (`bool`): whether or not to ignore syntax-errors in the queries
   - `strictinserts` (`bool`): checks if inserts contains all text-colums of table that are set to not null
   - `strictinsertsignoremissingtablenames` (`bool`): whether to ignore queries where the tablename could not be extracted on strict imports

All paths are relative to the path of the configuration file. If no configuration file can be found all the files in your currect folder will be scanned.

### Example Configuration-File

#### JSON
```json
{
    "paths": [
        "src"
    ],
    "exclusions": [
        "**/exclude.php"
    ],
    "dumpfile": "dump.sql",
    "parameters": {
        "ignoresyntax": false,
        "strictinserts": false
    }
}
```

#### YAML
```yml
paths:
  - src
exclusions:
  - "**/exclude.php"
dumpfile: dump.sql
parameters:
  ignoresyntax: false
  strictinserts: false
```

## Todo
This project is still work in progress and there is a lot of work todo.
 - improve validation of queries
 - improve documentation (of course)
 - add unit tests
 - improve recognition of database-queries
 - improve config
   - add support of CLI arguments

## License

The MIT License (MIT). Please see [LICENSE](https://github.com/davidlienhard/httpclient/blob/master/LICENSE) for more information.
