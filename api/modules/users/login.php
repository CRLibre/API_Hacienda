<?php

/**
 * Logout a user
 */
function users_logMeOut(){

	grace_debug("Log out");

	users_destroySession();
	params_set('sessionKey', 'longGone');
	return 'good bye';

}

