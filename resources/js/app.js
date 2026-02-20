/**
 * First we will load all of this project's JavaScript dependencies which
 * includes React and other helpers. It's a great starting point while
 * building robust, powerful web applications using React + Laravel.
 */

import './bootstrap';

// TAMBAHKAN INI: Inisialisasi Alpine.js agar Modal & Dropdown berfungsi
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

/**
 * Next, we will create a fresh React component instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import './components/Example';
import './components/App';