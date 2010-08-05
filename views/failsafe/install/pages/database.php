<?php
/**
 * Install database page
 */

echo elgg_echo('install:database:instructions');

echo elgg_view('install/forms/database', $vars);
