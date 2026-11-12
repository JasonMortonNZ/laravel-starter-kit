# PHP Rules

You must follow the following PHP rules:

* Use strict types where possible
* Use modern PHP feature and functionality as per the minimum version of PHP specified in the `composer.json` file

## PHP strictness

- Every PHP file starts with `declare(strict_types=1);`. Pint enforces this.
- Classes are `final` by default (`final readonly` for value objects). Prefer `private` over `protected`.
- Use strict comparisons (`===`), `mb_*` string functions, immutable dates, and imported class/function names (no leading `\`).
- Every parameter, property and return has a native type. Add PHPDoc generics or array shapes wherever PHPStan needs them.
- Before finishing PHP changes, run `vendor/bin/rector`, `vendor/bin/pint --dirty --format agent` and `vendor/bin/phpstan`. All three must come out clean.
