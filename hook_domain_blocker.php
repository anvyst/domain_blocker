<?php
/**
 *  DomainBlocker hook that
 *  prevents registering offensive/fraudulent domains
 *  @return Array $errors if domain is fraudelent
 * */
function hook_domain_blocker($vars) {
    require_once dirname(dirname(dirname(__FILE__))).'/modules/addons/domain_blocker/DomainBlocker.php';

    $blocker = DomainBlocker::getInstance();

    $errors = $blocker::checkDomain($vars);

    return $errors;
}

add_hook('ShoppingCartValidateDomain', '2', 'hook_domain_blocker');
?>
