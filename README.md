# davidlienhard/database-query-validator
🐘 php tool to validate database queries written using [`davidlienhard/database`](https://github.com/davidlienhard/database)

## Configuration
This tool requires a file named `query-validator.json` in your main project directory. CLI arguments are not supported at the moment.
The following configuration options are possible:
 - **`paths`** (`array`): list of paths (folders or files) to scan.
 - **`exclusions`** (`array`): list of paths to exclude from the scans
 - **`dumpfile`** (`string`): path to a mysql dump file to use for type-checks

All paths are relative to the path of the configuration file. If no configuration file can be found all the files in your currect folder will be scanned.

### Example Configuration-File
```json
{
    "paths": [
        "src"
    ],
    "exclusions": [
        "**/exclude.php"
    ],
    "dumpfile": "dump.sql"
}
```

## Todo
This project is still work in progress and there is a lot of work todo.
 - improve validation of queries
 - remove check of `use` keyword. Tools like `phpstan` can do this better
 - improve documentation (of course)
 - add unit tests
 - improve recognition of database-queries
 - improve config
   - add support of CLI arguments
   - add support for different filetypes (yml...)
