# Paysera Commission task
------------

This is the implementation of the following [task](https://gist.github.com/PayseraGithub/ef2a59d0a6d6e680af2e46ccff1bca37) which is part of my interview.

## Requirements
The current repo has been implemented on the following environment:
 
- PHP 7.4.19 (cli) (built: May  4 2021 14:28:04) ( ZTS Visual C++ 2017 x86 ).
- Windows 11.

### Note:
- No special php extension are required to be enabled.

## Installation		
The repo depends on the paysera provided skeleton, so every thing is made via `composer`.

```php
 composer install
```
## Configuring the script
The parser will take an array of `option => value` considered as the configuration for classes, bellow is the explanation of every option:

### **Required**: Full path of file to be parsed.
```php
[
    'csv_file' => '/data/2022-04-18/test-input.csv',
]
```


### **Optional**: Whether to show debug messages during the execute of script.
```php
[
    'debug_mode' => false | true,
]
```

### **Optional**: The API end point to get rates.
```php
[
    'rates_api_url' => 'https://developers.paysera.com/tasks/api/currency-exchange-rates',
]
```

### **Optional**: The number of decimals to take with out rounding for rates exchange.
```php
[
    'currency_precision' => [
                'EUR' => 2,
                'USD' => 4,
                'JPY' => 2,
            ],
]
```

### **Optional**: The number of decimals to take with rounding up for fee.
```php
[
    'currency_fee_precision' => [
        'EUR' => 2,
        'USD' => 2,
        'JPY' => 0,
    ],
]
```



## Run tests
Before you run tests against your data please consider to change the followings:
- The `tests\test-input.csv` has the list of transactions, and `tests\test-output.csv` has the right fees for each transaction.

- Since the rates can be changed every day, and in order to make the calculation matches the rates provided in the task then you have to host `src\RestApi\currency-exchange-rates.txt` in a server, and change the link in `tests\Service\ParserTest.php` line `25`.

After setting up every thing then you can run tests.
```php
 composer run test
```
To run `tests` and `php-fixer`
```php
 composer run tesix
```

# Full example
```php
<?php

    declare(strict_types=1);

    require_once __DIR__ . '/vendor/autoload.php';

    use CommissionFees\Service\Parser;


    $options = [
        'csv_file' => $argv[1],
        'rates_api_url' => 'http://127.0.0.1:8888/currency-exchange-rates.txt',
        'currency_precision' => [
            'EUR' => 2,
            'USD' => 4,
            'JPY' => 2,
        ],
        'currency_fee_precision' => [
            'EUR' => 2,
            'USD' => 2,
            'JPY' => 0,
        ],
    ];

    $parser = new Parser($options);
    $data = $parser->parseFile();

    // Generate a JSON file contains all transactions details along with calculated fees.
    file_put_contents('transaction.json', json_encode($data));

    // Generate only a file that have all fees.
    $parser->generateFeesFile();

    // Output the to console fees.
    print_r($parser->getFees());
?>
```



## TODO 
- [ ] Implement the rest API end point.
- [ ] Add a test server so no need to depend on external one.
- [ ] Write more tests to methods.
- [ ] Add documentation to wiki section.


## Versioning

For transparency into our release cycle and in striving to maintain backward compatibility, `commission-fee task test` is maintained under the Semantic Versioning guidelines.

See the Releases section of our project for changelogs for each release version.


## FAQ

**Can this script used via a CLI?**
- The short answer is yes.

**Where about Rest API?**
- Currently is not implemented, but the script is designed as library, so the implementation of the end point will not be too much.

**Does the modification of the script will break other parts?**
- No, the script is implemented with consideration of SOLID patterns.

**Can we add more currencies?**
- Yes, you just need to pass the precision of it(rates and fees) but that is optional.

**Why did you not use any framework?**
- Simply because there were no necessary code to reuse, another reason, core implementation make you free from any changes that may break your code if the framework made breakable changes.


## Issues
Please report all issues [here](https://github.com/hAbd0u/commisionfee-task-test/issues).

## Code of Conduct

This project has adopted the [Open Source Code of Conduct](https://opensource.guide/code-of-conduct/). For more information see the [Code of Conduct FAQ](https://opensource.guide/code-of-conduct/faq/) or contact [opencode@opensource.guide](mailto:opencode@opensource.guide) with any additional questions or comments.



## Author
- [BELADEL ILYES ABDELRAZAK](https://github.com/hAbd0u)



## Acknowledgments

The initial repo is implemented based on this [skeleton](https://github.com/paysera/skeleton-commission-task/archive/master.zip).
It was provided as part of [task](https://gist.github.com/PayseraGithub/ef2a59d0a6d6e680af2e46ccff1bca37) requirement.


## License

[![License](https://img.shields.io/badge/License-BSD%202--Clause-orange.svg)](https://opensource.org/licenses/BSD-2-Clause)