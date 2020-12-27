<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 10/29/19
 * Time: 4:44 PM
 */

namespace App\Utils;


use SimpleSAML\Auth\Simple;

class Authenticator
{
    public static function authenticate($adfsRequirements)
    {
        $auth = new Simple('default-sp');
        if ($auth->isAuthenticated()) {
            return Authenticator::isAllowed($auth->getAttributes(), $adfsRequirements);
        } else {
            $auth->requireAuth();
            if ($auth->isAuthenticated()) {
                return Authenticator::isAllowed($auth->getAttributes(), $adfsRequirements);
            } else {
                return false;
            }
        }
    }

    public static function isAllowed($attributes, $adfsRequirements)
    {
        $allowed = false;
        foreach ($attributes as $key => $values) {
            foreach($adfsRequirements as $requirement) {
                if ($requirement['key'] == $key) {
                    foreach ($values as $value) {
                      foreach($requirement['values'] as $reqValue) {
                          if ($value == $reqValue) {
                              $allowed = true;
                              break;
                          }
                       }
                    }
                }
            }
        }
        return $allowed;
    }
}
