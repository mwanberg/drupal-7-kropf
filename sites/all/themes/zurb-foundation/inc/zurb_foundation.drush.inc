<?php
/**
 * @file
 * Contains functions only needed for drush integration.
 */

/**
 * Implementation of hook_drush_command().
 */
function zurb_foundation_drush_command() {
  $items = array();

  $items['foundation-sub-theme'] = array(
    'description' => 'Create a custom sub-theme of ZURB foundation',
    'aliases' => array('fst'),
    'arguments' => array(
      'name'         => 'Your sub-theme name.',
      'machine_name' => 'A machine-readable name for your theme, optional only  [a-z, 0-9] ',
    ),
    'options' => array(
      'machine-name' => '[a-z, 0-9] A machine-readable name for your theme.',
      //'name'         => 'A name for your theme.',
      //'without-rtl'  => 'Remove all RTL stylesheets.',
    ),
    'examples' => array(
      'drush fst "custom theme name"' => 'Create a sub-theme with the default options.',
    ),
  );

  return $items;
}

/**
 * Create a Zurb foundation sub-theme.
 */
function drush_zurb_foundation_sub_theme($name = NULL, $machine_name = NULL) {
  if (empty($name)) {
    drush_print(dt("\nYou cannot create a sub-theme with no name. \nPlease provide a name for the sub-theme.\ni.e. drush fst [name]\n"));
    return;
  }

  $machine_name = !empty($machine_name) ? preg_replace('/[^a-z0-9_]+/', '', strtolower($machine_name)) : preg_replace('/[^a-z0-9_]+/', '', strtolower($name));

  // @TODO file path
  $subtheme_path = $theme_dir . '/' . $machine_name;
  $zurb_path = drush_locate_root() . '/' . drupal_get_path('theme', 'zurb_foundation');
  $subtheme_path = explode('/', $zurb_path);
  array_pop($subtheme_path);
  $subtheme_path = implode('/', $subtheme_path) . '/' . str_replace('_', '-', $machine_name);

  // Make a fresh copy of the subtheme.
  $s = drush_copy_dir("$zurb_path/STARTER/", $subtheme_path);
  if (empty($s)) {
    return;
  }

  // Rename the info file and fill in the theme name.
  drush_op('rename', "$subtheme_path/STARTER.info.txt", "$subtheme_path/$machine_name.info");
  drush_op('rename', "$subtheme_path/styles/custom.css", "$subtheme_path/styles/$machine_name.scss");
  drush_op('rename', "$subtheme_path/scripts/scripts.js", "$subtheme_path/scripts/$machine_name.js");
  drush_op('zurb_foundation_file_str_replace', "$subtheme_path/$machine_name.info", ', <strong>you shouldn\'t enable this theme</strong>. instead, use <em>drush zfst</em> or manually copy it to create a new sub-theme.', "");
  // Add documentation link
  drush_op('zurb_foundation_file_str_replace', "$subtheme_path/$machine_name.info", 'STARTER', "$machine_name");
  drush_op('zurb_foundation_file_str_replace', "$subtheme_path/$machine_name.info", 'STARTER', "$name");

  // Notify user of the newly created theme.
  drush_print(dt("\n!name sub-theme was created in !path. \n",
    array(
      '!name' => $name,
      '!path' => $subtheme_path,
    )
  ));

  drush_pm_enable($machine_name);

}

/**
 * Internal helper: Replace strings in a file.
 */
function zurb_foundation_file_str_replace($file_path, $find, $replace) {
  $file_contents = file_get_contents($file_path);
  $file_contents = str_replace($find, $replace, $file_contents);
  file_put_contents($file_path, $file_contents);
}

/**
 * Implements hook_drush_help().
 */
function zurb_foundation_drush_help($section) {
  switch ($section) {
    case 'drush:foundation-sub-theme':
      return dt("Create a ZURB foundation custom sub-theme.");
  }
}
