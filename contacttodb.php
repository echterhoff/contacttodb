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
     * 
     * Plugin constructor
     *
     * @param object $subject
     * @param object $params
     */
    function plgContactContacttodb(&$subject, $params)
    {
        parent::__construct($subject, $params);
        $this->_plugin = JPluginHelper::getPlugin('system', 'contacttodb');

        //$this->params = $params;
    }
    // Execute after base contact validation has passed. Validate e.g. extra data given before proceeding to send/store the form
    // function onValidateContact(&$contact, &$data){ }

    // Executes after all validation plugin/processes have successfully validated the contact and data but before the mail has been sent
    function onSubmitContact(&$contact, &$data)
    {
        if ($this->params->get('autoanswercontactid', -1) != $contact->id)
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

        if ($this->params->get('autoanswerenabled', false)) {
            $this->_sendEmail($data, $contact);
        }
    }

    private function _sendEmail($data, $contact)
    {
        $app = JFactory::getApplication();

        $contact->email_to = $data['contact_email'];

        $mailfrom = $app->get('mailfrom');
        $fromname = $app->get('fromname');
        $sitename = $app->get('sitename');

        $name    = $data['contact_name'];
        $email   = JStringPunycode::emailToPunycode($data['contact_email']);
        // $subject = $data['contact_subject'];
        // $body    = $data['contact_message'];

        // Prepare email body
        // $prefix = JText::sprintf('COM_CONTACT_ENQUIRY_TEXT', JUri::base());
        $autoanswerbody = $this->params->get('autoanswerbody','');
        $autoanswerbody = str_replace('%name%', $name, $autoanswerbody);
        $autoanswerbody = str_replace('%email%', $email, $autoanswerbody);
        $autoanswerbody = str_replace('</p>', "\r\n", $autoanswerbody);
        $autoanswerbody = str_replace('</br>', "\r\n", $autoanswerbody);
        // $autoanswerbody = str_replace('<p>', "", $autoanswerbody);
        $autoanswerbody = strip_tags($autoanswerbody);
        $body   =  $autoanswerbody; //stripslashes($body);

        // Load the custom fields
        if (!empty($data['com_fields']) && $fields = FieldsHelper::getFields('com_contact.mail', $contact, true, $data['com_fields'])) {
            $output = FieldsHelper::render(
                'com_contact.mail',
                'fields.render',
                array(
                    'context' => 'com_contact.mail',
                    'item'    => $contact,
                    'fields'  => $fields,
                )
            );

            if ($output) {
                $body .= "\r\n\r\n" . $output;
            }
        }

        $mail = JFactory::getMailer();
        $mail->addRecipient($contact->email_to);
        $mail->addReplyTo($email, $name);
        $mail->setSender(array($mailfrom, $fromname));
        $mail->setSubject($sitename . ': ' . $this->params->get('autoanswersubject',''));
        $mail->setBody($body);
        $sent = $mail->Send();

        // If we are supposed to copy the sender, do so.

        // Check whether email copy function activated
        // if ($emailCopyToSender == true && !empty($data['contact_email_copy']))
        // {
        // 	$copytext    = JText::sprintf('COM_CONTACT_COPYTEXT_OF', $contact->name, $sitename);
        // 	$copytext    .= "\r\n\r\n" . $body;
        // 	$copysubject = JText::sprintf('COM_CONTACT_COPYSUBJECT_OF', $subject);

        // 	$mail = JFactory::getMailer();
        // 	$mail->addRecipient($email);
        // 	$mail->addReplyTo($email, $name);
        // 	$mail->setSender(array($mailfrom, $fromname));
        // 	$mail->setSubject($copysubject);
        // 	$mail->setBody($copytext);
        // 	$sent = $mail->Send();
        // }

        return $sent;
    }
}
