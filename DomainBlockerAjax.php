<?php
if( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    
    require_once dirname(__FILE__).'/DomainBlocker.php';
    
    $content = DomainBlocker::dispatchActions();
    print $content; 
}
?>
