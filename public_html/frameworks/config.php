<?php
// I couldn't get this to work any other way - depending from where framework_ini.php was
// called, the current directory was sometimes the frameworks directory and sometimes the
// project root, making it impossible to have a consistent relative path to config.php
require_once('../config.php');
