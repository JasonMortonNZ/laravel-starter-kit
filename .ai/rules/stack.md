# The project framework stack

## Backend

* PHP 8.5
* Laravel 13
* Fortify (auth, 2FA)
* Wayfinder (typed TS route helpers).
* Reverb (WebSockets for broadcasting).

## Frontend

* Vue.js version 3
* Vue Router
* Pinia
* Axios
* Laravel Echo with pusher-js (talks to Reverb)
* Tailwind CSS version 4

## Tooling

* Vite+ (`vp`)
* TypeScript 6 (do not upgrade to 7 because `@vue/compiler-sfc` needs it's JS API)
* vue-tsc
* Pest 5
* PHPStan/Larastan (level max)
* Rector
* Pint
