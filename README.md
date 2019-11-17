This package contains artisan commands to import/export and work with 
language resources.

# Install

In composer.json add the following to repositories:

```
    "repositories": [{ 
        "type": "vcs", 
        "url":  "git@git.symvaro.com:dev/artisan-lang-utils.git" 
    }]
```

and require:

```
    "symvaro/artisan-lang-utils": "dev-master"
```

# Use

__Warning:__ The artisan lang commands will alter the language files
when used for editing or import. That means that:
 
 * every content except keys and values will be removed (e.g. comments), 
 * variables will be replaced with their values and 
 * nested array structures will be flattened.
 
Therefore it's recommended to use these tools only in combination with a VCS!

## Export/Import

To export the languages strings in the supported formats use the command like:

```
php artisan lang:export --language=en --format=po filename.po
```

If the filename is omitted, stdout will be used and if no language parameter is specified, the
default language will be used. The following formats are currently supported: tsv (default), 
json, po and resource, where resource is a laravel lang folder. The tsv format contains 
a tab separated key message pair for every row. The control characters `\n, \t, \\, \r\n` are
escaped with `\ `.

The import command line api is similarly structured like the export.

```
php artisan lang:import --language=en --format=po filename
```

It will read from stdin, if no filename is specified. There is also 
the `--replace-all` option, which will remove language strings,
if they are not present in the import file.

## Edit

Available commands to ease editing of language strings:

 * Add or replace (`lang:add {--l|language=} {?key}`)
 * Removing (`lang:remove {?key}`)

## Examples

The commands can be combined with common shell utils. The tsv format is especially supporting this. For example to
__list all non unique messages__ you can use this:

```sh
./artisan lang:export \
    | awk -F"\t" '{ print $2 }' \
    | sort | uniq -c | sort -rn \
    | grep -vE "^[ ]*1"
```

