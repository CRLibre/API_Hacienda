"""
AUTO-PORTED FROM: api/modules/wirez/conversationsDetails.php
Mode: mechanical baseline (no manual refactor).
"""

from __future__ import annotations

def wirez_conversationsGetDetails():
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        # @todo confirm that the user can see this conversation
        # I will hold the details
        $details = array();
    
        $q = sprintf("
            SELECT c.*, 
            u.fullName as senderName, u.userName AS senderWire,
            uu.fullName AS recipientName, uu.userName as recipientWire
            FROM conversations c
            INNER JOIN users u on u.idUser = c.idUser
            INNER JOIN users uu ON uu.idUser = c.idRecipient
            WHERE c.idConversation = '%s'
            ", db_escape(params_get('idConversation', 0)));
    
        $r = db_query($q);
    
        if ($r != ERROR_DB_NO_RESULTS_FOUND)
            $details = $r;
    
        return $details;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')
