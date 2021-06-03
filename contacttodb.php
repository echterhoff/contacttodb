<?php

/**
 * @version  3.4.1.4
 * @Project  Contact to database
 * @author   Lars Echterhoff
 * @package
 * @copyright Copyright (C) 2021 Lars Echterhoff
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 * @description This plugin stores your submitted formulars from the joomla build-in contact extension into the database.
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

define("GLOBAL_VARS_DEV", true);

if (GLOBAL_VARS_DEV) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

if (!function_exists("dd")) {

    function dd()
    {
        if (!GLOBAL_VARS_DEV)
            return;
        $args = func_get_args();
        echo "<pre>";
        foreach ($args as $arg) {
            if (is_array($arg) || is_object($arg)) {
                print_r($arg);
            } else {
                var_dump($arg);
            }
        }
        echo "</pre>";
    }
}
if (!function_exists("dd_entities")) {

    function dd_entities()
    {
        if (!GLOBAL_VARS_DEV)
            return;
        $args = func_get_args();
        echo "<pre>";
        foreach ($args as $arg) {
            if (is_array($arg) || is_object($arg)) {
                echo htmlentities(utf8_decode(print_r($arg, true)));
            } else {
                var_dump($arg);
            }
        }
        echo "</pre>";
    }
}

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Event\Event;

jimport('joomla.plugin.plugin');
jimport('joomla.form.helper');


class plgContactContacttodb extends JPlugin
{

    /**
     * Load the language file on instantiation
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    protected $db;

    protected $app;

    /**
     * Plugin constructor
     *
     * @param object $subject
     * @param object $params
     */
    function plgContactContacttodb(&$subject, $params)
    {
        define("CTDB_CACHEPATH", JPATH_CACHE . "/plg_globalvariables");
        parent::__construct($subject, $params);
        $this->_plugin = JPluginHelper::getPlugin('system', 'contacttodb');

        $params = $this->params;
    }

    function onSubmitContact(&$contact, &$data)
    {
        if ($contact->id != 16)
            return;

        $db = $this->db;

        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('id', 'name', 'email', 'subject', 'message', 'fields')));
        $query->from($db->quoteName('#__contacttodb'));
        $query->where($db->quoteName('name') . ' LIKE ' . $db->quote('lars%'));
        $query->order('ordering ASC');

        // Reset the query using our newly populated query object.
        $db->setQuery($query);

        // Load the results as a list of stdClass objects (see later for more options on retrieving data).
        $results = $db->loadObjectList();
        print_r($results);


        dd($data);
        die('Works!');
    }

    function onValidateContact(&$contact, &$data)
    {
        if ($contact->id != 16)
            return;
        dd($data);
        die('Works!');
    }
}
