Laravel Global Scope for Limiting
=================================

This is a trait used for eloquent global scope. It can be used to add where condition to the query.

## Installation

Add `xjchen/limited-trait` as a requirement to `composer.json`:

```javascript
{
    "require": {
        "xjchen/limited-trait": "dev-master"
    }
}
```

Update your packages with `composer update` or install with `composer install`.

You can also add the package using `composer require xjchen/limited-trait` and later specifying the version you want (for now, `dev-master` is your best bet).

## Usage

1. add `use LimitedTrait` in your model
2. fill `protected $limitedColumns = ['limited_column1', 'limited_column2']`
3. add method `getLimitedLimitedColumn1` and `getLimitedLimitedColumns2`
