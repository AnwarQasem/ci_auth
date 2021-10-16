<?php
$routes->post('auth/login',              '\Anwarqasem\CiAuth\CiAuth::login');
$routes->post('auth/register',           '\Anwarqasem\CiAuth\CiAuth::register');
$routes->post('auth/forgot_password',    '\Anwarqasem\CiAuth\CiAuth::forgot_password');
$routes->post('auth/change_password',    '\Anwarqasem\CiAuth\CiAuth::change_password');
$routes->post('auth/update_profile',    '\Anwarqasem\CiAuth\CiAuth::update_profile');
