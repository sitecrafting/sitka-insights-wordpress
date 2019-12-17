<?php

/**
 * Public API for consuming the GearLab Tools API from WordPress code
 *
 * @copyright 2019 SiteCrafting, Inc.
 * @author    Coby Tamayo <ctamayo@sitecrafting.com>
 */

namespace GearLab;

function client() {
  return apply_filters('gearlab/api/client', null);
}

function search(array $params) : array {
  return client()->search($params);
}

function completions(array $params) : array {
  return client()->completions($params);
}
