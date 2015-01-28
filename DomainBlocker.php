<?php
/**
 *  DomainBlocker class
 *  Main class for domain blacklist addon
 * */
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/init.php';

class DomainBlocker {
    
    protected static $table_settings = 'mod_domain_blocker';
    protected static $table_domains  = 'mod_domains_blocked';

    protected function __construct() { /* prevents creating new instance of Singleton */ }
    private function __clone() { /* prevents cloning of Singleton */ }
    private function __wakeup() { /* prevents unserializing of Singleton */ }


    /**
     * And that's where all the fun starts!
     * @return Signleton $instance
     */
    public static function getInstance() {
        static $instance = null;

        if (null == $instance) {
            $instance = new static();
        }

        return $instance;
    }

    /**
     *  getBlockStrings method
     *  @param Array $options
     *  @return Array $data
     * */
    public static function getBlockStrings($options = array()) {
        $data   = array();
        $sql    = "SELECT * FROM ".self::$table_settings." ORDER BY `pattern` ASC";
        $records = full_query($sql);
        
        while( $row = mysql_fetch_assoc($records) ) {
            array_push($data, $row);
        }
        
        return $data;
    }


    /**
     *  checkDomain method
     *  @param Array $data - containing domain
     *  @return Mixed $result - containing info on domain validation
     * */
    public static function checkDomain($data = array()) {
        $result = array('status' => 0, 'msg' => array());

        $sql = "SELECT id, pattern FROM ".self::$table_settings." WHERE activated=1 ORDER BY id ASC";  
        $res = full_query($sql);

        $patterns = array();
        while( $row = mysql_fetch_assoc($res) ) {
            array_push($patterns, $row);
        }
        
        if( !empty($patterns) ) {
            foreach($patterns as $k => $pattern) {
                $position = strpos($data['sld'], $pattern['pattern']);

                if( $position !== false ) {
                    $result['status'] = 1;
                    break; 
                }
            } 
        }

        if( $result['status'] == 1 ) {
            $data = array(
                'domain'        => mysql_real_escape_string("{$data['sld']}{$data['tld']}"),
                'ipaddress'     => $_SERVER['REMOTE_ADDR'],
                'description'   => "Domain contains blocked words",
                'attempts'      => '1',
                'created'       => date('Y-m-d H:i:s', time()),    
            );

            $res = full_query("SELECT * FROM ".self::$table_domains." WHERE domain='{$data['domain']}' AND ipaddress='{$data['ipaddress']}' LIMIT 1");
            $record = mysql_fetch_assoc($res);

            if( !empty($record) ) {
                update_query(
                    self::$table_domains, 
                    array('attempts' => ++$record['attempts']), 
                    array('id' => $record['id'])
                );

            } else {
                insert_query(self::$table_domains, $data);
            }
            
            $result['msg'][] = "Domain contains \"offensive\" words.";
        }

        return $result; 
    }

    /**
     *  getBlockedDomains method
     *  @param Array $options
     *  @return Array $data
     * */
    public static function getBlockedDomains($options = array()) {
        $data = array();

        $records = full_query("SELECT * FROM ".self::$table_domains." ORDER BY domain ASC");

        while( $row = mysql_fetch_assoc($records) ) {
            array_push($data, $row);
        } 
        
        return $data; 
    }


    /**
     *  getStyles method
     *  @return String $files with all 
     * */
    public static function getStyles() {
        $files[] = '<script src="../modules/addons/domain_blocker/js/domain_blocker.js"></script>';  
        $files[] = '<script src="../modules/addons/domain_blocker/bootstrap/js/bootstrap.min.js"></script>';  
        $files[] = '<link href="../modules/addons/domain_blocker/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">';  
        $files[] = '<link href="../modules/addons/domain_blocker/css/style.css" rel="stylesheet" media="screen">';  
    
        return join("\n", $files);
    }


    /**
     *  dispatchActions method
     *  Main dispatcher method for handling all the AJAX/POST 
     *  method
     *  @return Mixed $result containing response JSON/Html 
     * */
    public static function dispatchActions() {
        if( isset($_POST['form_submit']) && !empty($_POST['data']) ) {
            $result = self::savePatternForm($_POST);
            $_SESSION['domain_blocker'] = json_decode($result, true);
            header('location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        } 

        if( !empty($_POST['action']) && in_array($_POST['action'], array('edit_pattern','add_pattern')) ) {
            return self::getEditPatternForm($_POST); 
        } 

        if( !empty($_POST['action']) && in_array($_POST['action'], array('delete_pattern')) ) {
            $result = self::removePattern($_POST);
        }

        if( !empty($_POST['action']) && in_array($_POST['action'], array('activate_pattern')) ) {
            $result = self::activatePattern($_POST); 
        }

        // got the session into the front-end 
        if( isset($result) && !empty($result) ) {
            $_SESSION['domain_blocker'] = json_decode($result, true);
            return $result;
        }
        
    }
    
    
    /**
     *  removePattern function
     *  @param Array $postdata - post data
     *  @return JSON $result - containing error/msg data
     * */
    public static function removePattern($postdata = array()) {
        $result = array('status' => 0, 'msg' => array());

        $deleted = full_query("DELETE FROM ".self::$table_settings." WHERE id='".mysql_real_escape_string($postdata['id'])."'");
        
        if( $deleted ) {
            $result['status'] = 1; 
            $result['msg'][] = "Pattern was successfully removed";
        } else {
            $result['msg'][] = "Couldn't delete Pattern, please check the code";
        } 

        return json_encode($result); 
    }


    /**
     *  activatePattern method
     *  @param Array $postdata - for ids being activated
     *  @return Array $result - on method success/failure
     * */
    public static function activatePattern($postdata = array()) {
        $result = array('status' => 0, 'msg' => array());
        
        if( !empty($postdata['ids']) ) {
            $sql = "UPDATE ".self::$table_settings." SET activated=1 WHERE id IN(".mysql_real_escape_string(implode(',', $postdata['ids'])).")";
            
            $updated = full_query($sql); 
            
            if( $updated ) {
                $result['status'] = 1;
                $result['msg'][] = "Successfully activated patterns";
            } else {
                $result['msg'][] = "Couldn't activate the patterns";
            }
        }

        return json_encode($result); 
    }


    /**
     *  savePatternForm function
     *  @param Array $postdata - containing all require posts
     *  return JSON $result - with success/failure on saving the record
     * */
    public static function savePatternForm($postdata = array()) {
        $data   = array();
        $result = array('status' => 0, 'msg' => array());

        if( !empty($postdata) ) {
            $data = array(
                'pattern'       => mysql_real_escape_string($postdata['data']['pattern']),
                'pattern_type'  => mysql_real_escape_string($postdata['data']['pattern_type']),
                'activated'     => ( isset($postdata['data']['activated']) ? 1 : 0 ),
                'created'       => date("Y-m-d H:i:s", time()),    
            );


            if( !isset($postdata['data']['id']) ) {
               $pattern_id = insert_query( self::$table_settings, $data ); 
            } else {
                $data['id'] = $pattern_id = $postdata['data']['id'];
                update_query(self::$table_settings, $data, array('id' => $pattern_id));
            }
        }

        if( !empty($pattern_id) ) {
            $result['status'] = $pattern_id;
            $result['msg'][] = "Pattern #$pattern_id was successfully saved";
        } else {
            $result['msg'][] = "Couldn't save Pattern record";
        }

        return json_encode($result);
    }


    /**
     *  getEditPatternForm function
     *  @param Array $data containing post fields optionally
     *  @return String $content with the modal form
     * */
    public static function getEditPatternForm($data = array()) {
        $content = array(); 
        $record  = array();

        if( !empty($data['id']) ) {
            $resource = full_query("SELECT * FROM ".self::$table_settings." WHERE id='".mysql_real_escape_string($data['id'])."'");
            $pattern = mysql_fetch_assoc($resource);
            if( !empty($pattern) ) {
                $record = $pattern;
            }
        } 
        
        $content[] = "<form method='post' class='form-horizontal'>";
            $content[] = "<div class='modal-body'>";
                $content[] = "<input type='hidden' name='module' value='domain_blocker'>";
                $content[] = "<input type='hidden' name='action' value='{$_POST['action']}'>";
                if( !empty($record['id']) ) {
                    $content[] = "<input type='hidden' name='data[id]' value='{$record['id']}'/>";
                }
                $content[] = "<div class='control-group'><div class='controls'><input type='text' name='data[pattern]' ".(!empty($record['pattern']) ? "value='{$record['pattern']}'" : '')."/></div></div>";
                $content[] = "<div class='control-group'><div class='controls'><select name='data[pattern_type]'>";
                    $content[] = "<option value=\"\">Choose Pattern Type</option>";
                    $content[] = "<option value='string' ".(($record['pattern_type'] == 'string') ? 'selected' : '').">String</option>";
                    $content[] = "<option value='regexp' ".(($record['pattern_type'] == 'regexp') ? 'selected' : '').">Regular Expression</option>";
                $content[] = "</select></div></div>";
            $content[] = "</div>";
            $content[] = "<div class='control-group'><Div class='controls'>";
                $content[] = "<label class='checkbox'><input type='checkbox' name='data[activated]' ".(($record['activated'] == 1) ? 'checked' : '')."/>Activated</label>";
            $content[] = "</div></div>";        
            $content[] = "<div class='modal-footer'>";
                $content[] = "<a href=\"javascript:void(0);\" class=\"btn\" data-dismiss=\"modal\">Close</a>";
                $content[] = "<input type='submit' name='form_submit' value='Save' class=\"btn btn-primary\">";
            $content[] = "</div>";
        $content[] = "</form>";

        return join("\n", $content);
    }

    /**
     *  activate method
     *  @return Array $result - checking if tables were properly created
     * */
    public static function activate() {
        $result = array('status' => 0, 'msg' => array());

        $sql[self::$table_settings] = "CREATE TABLE `mod_domain_blocker` (`id` bigint(20) NOT NULL AUTO_INCREMENT,`pattern` varchar(200) NOT NULL, `pattern_type` enum('regexp','string') NOT NULL,`activated` tinyint(4) DEFAULT '1',`created` datetime DEFAULT NULL,`modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `idx_pattern` (`pattern`),KEY `idx_activated` (`activated`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $sql[self::$table_domains] = "CREATE TABLE `mod_domains_blocked` (`id` bigint(20) NOT NULL AUTO_INCREMENT, `ipaddress` varchar(100) DEFAULT NULL, `domain` varchar(200) DEFAULT NULL,`attempts` int(11) DEFAULT '0',`description` mediumtext, `created` datetime DEFAULT NULL, `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `idx_domain` (`domain`), KEY `idx_ipaddress` (`ipaddress`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        foreach( $sql as $table_name => $qry ) {
            $result = mysql_query($qry);
            
            if( !$result ) {
                $result['status'] = 1;
                $result['msg'][] = "Table '$table_name' wasn't properly created. Try deactivating the plugin and activate it again"; 
            }
        }
        
        return $result; 
    }

    /**
     *  deactivate method
     *  @return Array $result - on droping all module tables
     * */ 
    public static function deactivate() {
        $result = array('status' => 0, 'msg' => array());
        
        $tables = array( self::$table_settings, self::$table_domains );

        foreach( $tables as $table_name ) {
            $sql = "DROP TABLE `$table_name`";
            $result = mysql_query($sql);

            if( !$result ) {
                $result['status'] = 1;
                $result['msg'][] = "Couldn't drop table '$table_name'";
            }
        }

        return $result;
    }
}
?>
