# Gate Imageboard

Gate is a demonstration imageboard for PHP static analysis. The domain is a working Laravel app; the point of the repo is to give analyzers a large, idiomatic Laravel codebase to run against.

It is a corpus for:

- [php-typing-conformance](https://github.com/zonuexe/php-typing-conformance) ([report](https://zonuexe.github.io/php-typing-conformance/)) — isolated typing tests and a cross-tool scoreboard
- [Steins](https://github.com/rigortype/steins) — a PHP static analyzer

php-typing-conformance measures how tools treat a single type spelling. Gate is the other half: a full application, so the same tools also see Laravel's framework types, helpers, and the ways people actually write them.

## Plan

1. Finish a conventional Laravel imageboard (auth, teams, posts, images). The app has to run and stay coherent.
2. Then grow Laravel surface area: current APIs, older compatible spellings, and common extension packages.

Stage 1 is a normal app. Stage 2 keeps it runnable; the extra idioms are the corpus, not broken or synthetic code.

The tree is still the official Laravel Livewire starter kit (Fortify auth, 2FA, passkeys, teams, settings). Imageboard features are not in yet.

## Stack

- PHP 8.3+, Laravel 13
- Livewire 4, Flux UI, Laravel Fortify
- Pest, Larastan, Pint
- Tailwind CSS 4, Vite 8

## Setup

```bash
composer setup
composer dev
```

`composer test` runs Pint, PHPStan (Larastan), and Pest.

## Copyright

```
Gate is a demonstration imageboard for Steins static analysis — It's the choice.
(C) 2026  TypedDeck, USAMI Kenta <tadsan@zonu.me>

Copying and distribution of this file, with or without modification, are permitted in any medium without royalty provided the copyright notice and this notice are preserved. This file is offered as-is, without any warranty.
```
