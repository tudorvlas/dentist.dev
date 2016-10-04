<?php
$databases['default']['default'] = array(
  'database' => 'name',
  'username' => 'user',
  'password' => 'pass',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
$settings['install_profile'] = 'minimal';
$config_directories['sync'] = 'sites/files/config/sync';

// Make sure Drush keeps working.
// Modified from function drush_verify_cli()
$cli = (php_sapi_name() == 'cli');
// PASSWORD-PROTECT NON-PRODUCTION SITES (i.e. staging/dev).
if (!$cli && (isset($_ENV['AH_NON_PRODUCTION']) && $_ENV['AH_NON_PRODUCTION'])) {
  $username = 'admin';
  $password = 'j9G4mjzHaE">n6ew';
  if (!(isset($_SERVER['PHP_AUTH_USER']) && ($_SERVER['PHP_AUTH_USER'] == $username && $_SERVER['PHP_AUTH_PW'] == $password))) {
    header('WWW-Authenticate: Basic realm="This site is protected"');
    header('HTTP/1.0 401 Unauthorized');
    // Fallback message when the user presses cancel / escape.
    echo 'Access denied';
    exit;
  }
}
