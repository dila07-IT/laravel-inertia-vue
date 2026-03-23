# Laravel Inertia Vue Starter 🚀

Laravel Inertia Vue Starter is a developer-friendly package designed to simplify the integration of Inertia.js with Vue 3 in Laravel applications. It automates dependency installation, scaffolds essential files, and provides a smooth setup experience with intelligent features like safe file handling, overwrite prompts, and setup guidance when conflicts occur.

---

## ✨ Features

- Automatic installation of Inertia.js (Laravel adapter)
- Vue 3 + Inertia frontend setup
- Vite configuration ready to use
- Smart dependency detection (no duplicate installs)
- Safe file handling (no unwanted overwrites)
- Interactive overwrite confirmation
- `--force` option for full overwrite
- Auto-generated setup guide when files are skipped
- Clean and beginner-friendly setup

---

## ⚡ Installation

Install the package via Composer:

`bash`
composer require dilansabah/laravel-inertia-vue

`Run the installer command:`
php artisan inertia-vue:install

`Then start your frontend:`
npm run dev

---

## 🔧 What the Package Does

`When you run the install command, the package will:`
Install inertiajs/inertia-laravel
Install required npm packages:
vue
@inertiajs/vue3
@vitejs/plugin-vue
Create or update:
resources/js/app.js
resources/js/Pages/Home.vue
resources/views/app.blade.php
vite.config.js
Add a helpful comment in routes/web.php
Generate a setup guide if important files are skipped

---

## ⚠️ Skipped Files Handling

`If important files already exist (like app.js or vite.config.js), the package will:`
Ask before overwriting them
Skip them if you choose "no"
`Generate a guide file:`
inertia-vue-setup.md
Follow the instructions inside this file to complete the setup manually.

---

## Force Overwrite

`To overwrite all files without confirmation:`
php artisan inertia-vue:install --force

---

## 📂 Generated Structure

`After installation, your project will include:`
resources/js/app.js
resources/js/Pages/Home.vue
resources/views/app.blade.php
vite.config.js
