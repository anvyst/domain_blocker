<?php
/**
 *  DomainBlocker addon with main 
 *  WHMCS addon hook configs
 * */
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

require_once dirname(__FILE__).'/DomainBlocker.php';

function domain_blocker_config() {
    $configarray = array(
        'name'          => 'Domain Blocker',
        'description'   => 'Helps preventing fraudulent domains from being registered',
        'version'       => '1.0',
        'language'      => 'english',
        'author'        => 'Andrey Vystavkin',
        'fields'        => array(),    
    );

    return $configarray;
}

function domain_blocker_activate() {
    $activated = DomainBlocker::activate();
    $result = array();

    $result['status'] = ($activated['status'] == 1) ? 'error' : 'success';
    $result['description'] = implode('.', $result['msg']);

    return $result;
}

function domain_blocker_deactivate() {
    $deactivated = DomainBlocker::deactivate();
    
    $result['status'] = ($deactivated['status'] == 1) ? 'error' : 'success';
    $result['description'] = implode('.', $result['msg']);

    return $result;
}

function domain_blocker_output($vars) {
    $smarty = new Smarty();
    $smarty->caching = false;
    $smarty->compile_dir = $GLOBALS['templates_compiledir'];

    echo DomainBlocker::getStyles();
    
    $page = isset($_GET['page']) ? $_GET['page'] : 'index';

    switch($page) {
        case 'index':
            $data = DomainBlocker::getBlockStrings();
            $smarty->assign('block_strings', $data);

            $blocked_domains = DomainBlocker::getBlockedDomains();
            $smarty->assign('blocked_domains', $blocked_domains); 
        break;
    }


    //where all $_POST's go to
    DomainBlocker::dispatchActions();
    
    $smarty->display(dirname(__FILE__)."/templates/$page.tpl");
}

?>
