<?php
defined('BASEPATH') OR exit('Access denied.');

/**
 * Make sure the server can run a function
 *
 * @author Juno_okyo <junookyo@gmail.com>
 * @link   https://junookyo.blogspot.com/
 * @param  string  $name  Function to check for
 * @return bool
 */
function function_usable($name) {
  $disable_funcs = get_cfg_var('disable_functions');
  $disable_funcs = ($disable_funcs === FALSE OR empty($disable_funcs)) ? ini_get('disable_functions') : $disable_funcs;

  if ($disable_funcs === FALSE OR empty($disable_funcs)) {
    return TRUE;
  } else {
    if (strpos($disable_funcs, ',') === FALSE) {
      return strtolower($disable_funcs) !== $name;
    }

    $disable_funcs = str_replace(' ', '', $disable_funcs);
    $disable_funcs = explode(',', strtolower($disable_funcs));
    return ! in_array($name, $disable_funcs);
  }
}

// Test exec()
function_usable('exec') OR exit('Error: The firewall cannot run on this server. Please remove "exec" from "disable_functions" in php.ini');
