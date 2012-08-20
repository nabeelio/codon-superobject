codon-superobject
================

A class for parsing and using JSON for config files

```php
<?php
\Codon\Config::loadJSONData($some_json_string);
\Codon\Config::get('mapped.array.path.here');
# or as

\Codon\Config::loadJSONData($some_json_string);
$instance = \Codon\Config::getInstance();
# or
$instance = \Codon\Config::i();

$value = $instance->map('mapped.array.path.here');
```