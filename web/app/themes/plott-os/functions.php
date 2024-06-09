<?php

/**
 * ------------------------------------
 * Register the autoloader
 * ------------------------------------
 * Composer provides a convenient, automatically generated class loader for
 * our theme. We will simply require it into the script here so that we
 * don't have to worry about manually loading any of our classes later on.
 */

 if(!file_exists($composer = __DIR__ . '/vendor/autoload.php')){
    wp_die(__('Error locating autoloader. Please run <code>composer install</code>', 'plott'));
 }

 require $composer;

 /**
  * 
  * ----------------------------------
  * Register theme files
  * ----------------------------------
  *
  */
  
//   collect(['setup', 'filters'])
//   ->each(function($file){
//     if(! locate_template($file = "app/{$file}.php", true, true)){
//         wp_die(
//             sprintf(__('Error locating <code>%s</code> for inclusion', 'plott'), $file)
//         );
//     }
//   });