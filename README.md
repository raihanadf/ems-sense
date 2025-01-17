## Overview
This is the web app for the EMS-Sense project.

## How to install
Built simply using Laravel, Breeze, and just Livewire.

- Setup Laravel Project
  ```bash
  composer install && pnpm install && php artisan key:generate && php artisan storage:link
  ```
  - composer install
  - pnpm install
  - php artisan key:generate
  - php artisan storage:link

- Setup .env
  ```bash
  mv .env.example .env
  ```
  - rename the template to .env
  - sesuaiin aja sih sesuai keinginan

<hr/>

## How to run
- Serve with
  ```bash
  php artisan serve
  ```
  or
  ```bash
  composer dev
  ```
