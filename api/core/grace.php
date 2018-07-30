<?php

/** @ingroup Constants
 *  @{
 */
# Details about how I am going to be running
//! Print it all in grace
define('GRACE_PRINT_ALL', true);
//! Print absurd messages
define('GRACE_PRINT_ABSURD', true);
//! Print debug messages
define('GRACE_PRINT_DEBUG', true);
//! Print error messages
define('GRACE_PRINT_ERROR', true);

/** @} */
/** @ingroup GlobalVars
 *  @{
 */
//! Debug messages stored in Grace
global $grace_logMsgs;



/** @} */

/**
 * I am the one who actually talks
 * DO NOT call me directly, use the other functions
 */
function _grace_talk($msg, $who = 'info') {
    global $grace_logMsgs;

    if (GRACE_PRINT_ALL) {
        $fileName = conf_get('logPath', 'grace', '');
        $txtName = date('y_m_d_h', time()) . "_errors.log";
        # Format the message
        $msg = sprintf("[%s] %s @ %s", $who, date('y-m-d h:m:s', time()), $msg) . "\n";
        
        if(conf_get('display', 'grace', false)){            
            echo $msg . ($who == 'a' ? "" : "\n" . "<br />");
        }
        
        if(conf_get('errors', 'grace', false)){
            if(!file_exists($fileName)){
                mkdir($fileName, 0777, true);
                touch($fileName . $txtName);
            }
            
            error_log($msg, 3, $fileName . $txtName);
            # Add the message to the debug pool if you want me to store them in a file
            if (conf_get('logPath', 'grace', '') != '' && $who != 'a') {
                $grace_logMsgs[] = $msg;
            }
        }
    }
}

/**
 * Logs all messages in this session
 */
function grace_storeLog() {

    global $grace_logMsgs;

    # Add the last message
    grace_debug("Finished! Memory used: Mb" . (memory_get_peak_usage() / 1000000) . "<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");

    # Do I have a place to store them?
    $fileName = conf_get('logPath', 'grace', '');

    if(conf_get('debug', 'grace', false)){
        if ($fileName != '') {

            # Create a new file every hour
            $fileName = $fileName . "CalAPI_log_" . date('y_m_d_h', time()) . ".txt";

            # Merge arrays to make them readable
            $grace_logMsgs = implode("\n", $grace_logMsgs) . "\n";

            //Open a connection
            $fp = fopen($fileName, 'a');
            if ($fp) {
                fwrite($fp, $grace_logMsgs);
                fclose($fp);
            }
        }
    }
}

/**
 * Debuging messages
 */
function grace_debug($msg) {
    if (GRACE_PRINT_DEBUG == true)
        _grace_talk($msg, 'd');
}

/**
 * Informational messages
 */
function grace_info($msg) {
    if (GRACE_PRINT_DEBUG == true)
        _grace_talk($msg, 'i');
}

/**
 * Error messages
 */
function grace_error($msg) {
    if (GRACE_PRINT_ERROR == true)
        _grace_talk($msg, 'e');
}

/**
 * Use this if you want to send messages that are absurd, that you want to see
 * only if you REALLY need it.
 */
function grace_absurd($msg) {
    if (GRACE_PRINT_ABSURD == true)
        _grace_talk($msg, 'a');
}

/**
 * Very lightweight browser detection
 * http://us2.php.net/get_browser
 */
function lestatz_browserInfo($agent = null) {

    // Declare known browsers to look for
    $known = array('msie', 'firefox', 'safari', 'webkit', 'opera', 'netscape', 'konqueror', 'gecko');

    // Clean up agent and build regex that matches phrases for known browsers (e.g. "Firefox/2.0" or "MSIE 6.0" (This only matches the 
    //	major and minor version numbers.  E.g. "2.0.0.6" is parsed as simply "2.0"
    $agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
    $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9]+(?:\.[0-9]+)?)#';

    // Find all phrases (or return empty array if none found)
    if (!preg_match_all($pattern, $agent, $matches))
        return array();

    // Since some UAs have more than one phrase	(e.g Firefox has a Gecko phrase, Opera 7,8
    //  have a MSIE phrase), use the last one found (the right-most one in the UA).
    //  That's usually the most correct.
    $i = count($matches['browser']) - 1;

    return array($matches['browser'][$i] => $matches['version'][$i]);
}
