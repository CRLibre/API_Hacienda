"""
AUTO-PORTED FROM: api/modules/wirez/messagesSend.php
Mode: mechanical baseline (no manual refactor).
"""

from __future__ import annotations

def wirez_messagesSend():
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        global $user;
    
        grace_debug("Sending a message");
    
        # Get the sender and recipient details
        $sender = wirez_loadContact(params_get('from', ''));
    
        if ($sender == ERROR_DB_NO_RESULTS_FOUND)
            return ERROR_WIREZ_NO_VALID_CONTACT;
    
        $recipient = wirez_loadContact(params_get('to', ''));
    
        if ($recipient == ERROR_DB_NO_RESULTS_FOUND)
            return ERROR_WIREZ_NO_VALID_CONTACT;
    
        # Get the recipient
        # @todo Add wirez correctly
        $recipient = users_load(array('userName' => params_get('to', 0)));
    
        # If there is no conversation, I will create one, but only if there is a subject
        if (params_get('idConversation', 0) == 0 && params_get('subject', '') != "")
            $idConversation = wirez_conversationsCreate($user->idUser, params_get('subject', 0), $recipient->idUser);
        else
            $idConversation = params_get('idConversation', 0);
    
        # @ todo CHECK THAT THE CONVERSATION EXISTS AND VALIDATE USER!!!
        $q = sprintf("INSERT INTO wirez_msgs (timestamp, ip, idSender, idRecipient, text, attachments, idConversation)
            VALUES('%s','%s','%s','%s','%s','%s','%s')",
                time(),
                db_escape($_SERVER['REMOTE_ADDR']),
                db_escape($user->idUser),
                db_escape($recipient->idUser),
                db_escape(addslashes(json_decode(params_get('text',''), ENT_QUOTES))),
                '',
                db_escape($idConversation)
        );
    
        $r = db_query($q, 0);
    
        return $idConversation;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def wirez_conversationsCreate(idUser, subject, idRecipient):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        $timestamp = time();
    
        $insert = sprintf("INSERT INTO conversations (idUser, idRecipient, timestamp, subject) VALUES('%s', '%s', '%s', '%s')",
            db_escape($idUser),
            db_escape($idRecipient),
            $timestamp,
            db_escape($subject);
    
        $r = db_query($insert, 0);
    
        # Now I need to know the id of the conversation
        $q = sprintf("SELECT idConversation FROM conversations WHERE timestamp = '%s' AND idUser = '%s'", $timestamp, db_escape($idUser));
    
        $r = db_query($q, 1);
    
        $idConversation = $r->idConversation;
    
        return $idConversation;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')
