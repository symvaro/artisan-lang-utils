Utils to import/export language resources.

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

Then add the following service provider to config/app.php

```
    'providers' => [
        ...
        Symvaro\ArtisanLangUtils\ArtisanLangUtilsServiceProvider::class,
    ]
```

# Use

List commands that are provided by this package

```
php artisan list lang
```
