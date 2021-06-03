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

// use Joomla\CMS\Plugin\CMSPlugin;
// use Joomla\CMS\Event\Event;
// use Joomla\Event\SubscriberInterface;

jimport('joomla.plugin.plugin');
jimport('joomla.form.helper');


class plgContactContacttodb extends JPlugin //extends CMSPlugin implements SubscriberInterface
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

        // Columns
        $columns = array(
            'name',
            'email',
            'subject',
            'message',
            'fields'
        );

        // Values
        $values = array(
            isset($data['contact_name']) ? $db->quote($data['contact_name']) : null,
            isset($data['contact_email']) ? $db->quote($data['contact_email']) : null,
            isset($data['contact_subject']) ? $db->quote($data['contact_subject']) : null,
            isset($data['contact_message']) ? $db->quote($data['contact_message']) : null,
            isset($data['com_fields']) ? $db->quote(json_encode($data['com_fields'])) : $db->quote(json_encode(null))
        );

        $query
            ->insert($db->quoteName('#__contacttodb'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));

        $db->setQuery($query);
        $db->execute();
    }

    // function onValidateContact(&$contact, &$data){ }
}
