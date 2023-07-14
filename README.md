# php-fexpr

**php-fexpr** is a PHP-ported version of [ganigeorgiev/fexpr](https://github.com/ganigeorgiev/fexpr)
that allows you to parse a filter query language and provides easy to work with AST structures.

Supports parenthesis and various conditional expression operators (see original project's [Grammar](https://github.com/ganigeorgiev/fexpr#grammar))

## Example usage

```bash
composer require nemorize/fexpr
```

```php
$ast = (new \Nemorize\Fexpr\Parser())->parse('id = 123 && status = "active"');
// json_encode($ast): [
//     {
//         "operation": {
//             "type": "join",
//             "literal": "&&"
//         },
//         "item": {
//             "left": {
//                 "type": "identifier",
//                 "literal": "id"
//             },
//             "operation": {
//                 "type": "sign",
//                 "literal": "=",
//             },
//             "right": {
//                 "type": "number",
//                 "literal": "123"
//             }
//         }
//     },
//     {
//         "operation": {
//             "type": "join",
//             "literal": "&&"
//         },
//         "item": {
//             "left": {
//                 "type": "identifier",
//                 "literal": "status"
//             },
//             "operation": {
//                 "type": "sign",
//                 "literal": "=",
//             },
//             "right": {
//                 "type": "string",
//                 "literal": "active"
//             }
//         }
//     }
// ]
```

## Using only the scanner
The tokenizer (aka. `\Nemorize\Fexpr\Scanner`) could be used without the parser\s state machine
so that you can write your own custom tokens processing:

```php
$scanner = new \Nemorize\Fexpr\Scanner();
foreach ($scanner->scan('id > 123') as $token) {
    var_dump($token);
}
```