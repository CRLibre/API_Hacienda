<?php
/*
 * Copyright (C) 2017-2020 CRLibre <https://crlibre.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/** @file wirez.php
 * Wirez communication system
 * Its like a chat or quick messaging, it will grow, give it time :)
 */

/** \addtogroup Modules 
 *  @{
 */

/**
 * \defgroup Wirez
 * @{
 */

# Nothing found, related to nothing found in the db (200) usually
define('ERROR_WIREZ_MSGS_NOTHING_FOUND', '-100');
define('ERROR_WIREZ_NO_VALID_CONTACT',   '-101');

/**
 * Boot up procedure
 */
function wirez_bootMeUp()
{
    // Just booting up
}
/**
 * Init function
 */
function wirez_init()
{
    $paths = array(

        # Send a message
        # @return message id
        #  what=messages_send
        #  &from=userWire
        #  &to=userWire
        #  &idConversation=0
        #  &subject=somethingNice
        #  &text=json(everyThingYouWantToSayAndItCanBeVeeeeeryLong and even with spaces :) and other chars)
        array(
            'r'             => 'messages_send',
            'action'        => 'wirez_messagesSend',
            'access'        => 'users_loggedIn',
            'params'        => array(
                array("key" => "from",              "def" => "", "req" => true),
                array("key" => "to",                "def" => "", "req" => true),
                array("key" => "idConversation",    "def" => "", "req" => true),
                array("key" => "subject",           "def" => "", "req" => false),
                array("key" => "text",              "def" => "", "req" => true),
            ),
            'file'          => 'messagesSend.php'
        ),

        # Get messages in conversations,
        # @return a list of messages
        # what=getMgsInConversation
        # &lastMsgId=0
        # &idConversation=0
        # &withWire=wire
        # &textLike=xxx
        array(
            'r'             => 'messages_get_in_conversation',
            'action'        => 'wirez_messagesGetInConversation',
            'access'        => 'users_loggedIn',
            'params'        => array(
                array("key" => "lastMsgId",         "def" => "0", "req" => false),
                array("key" => "idConversation",    "def" => "0", "req" => false),
                array("key" => "withWire",          "def" => "", "req" => true),
                array("key" => "textLike",          "def" => "", "req" => false)
            ),
            'file'          => 'messagesInConversation.php'
        ),

        # Get recent messages for someone
        # @return A list of messages
        # r=messages_get_recent
        # &lastMessageId=0
        array(
            'r'         => 'messages_get_recent',
            'action'    => 'wirez_messagesGetRecent',
            'access'    => 'users_loggedIn',
            'params'    => array(
                array("key" => "lastMessageId", "def" => "0", "req" => false),
            ),
            'file'      => 'messagesRecent.php'
        ),

        # Get the details about a conversation
        # @return The details about the conversation
        # r=conversations_get_details
        # &idConversation=0
        array(
            'r'         => 'conversations_get_details',
            'action'    => 'wirez_conversationsGetDetails',
            'access'    => 'users_loggedIn',
            'params'    => array(array("key" => "idConversation", "def" => "0", "req" => false)),
            'file'      => 'conversationsDetails.php'
        )
    );

    return $paths;
}

/**
 * Loads the details about a wire contact
 */
function wirez_loadContact($wire)
{
    grace_debug("Getting wirez: " . $wire);

    $q = sprintf("SELECT * FROM wirez_contacts WHERE wire = '%s'", $wire);

    $results = db_query($q, 1);
    return $results;
}
/**@}*/
/** @}*/
