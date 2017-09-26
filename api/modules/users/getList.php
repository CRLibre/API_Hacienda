<?php

/**
 * Gets a list of users
 */
function users_getList(){

	# Where should I look for
	# By wire only
	if(strpos(params_get('like'), "::") === 0){
		//TMP solution while I make up my mind how wirez countersign will be handled
		$where = sprintf("AND userName like '%s%%'
			ORDER BY wire ASC
			", str_replace("::", "", params_get('like')));
	}else{
		$where = sprintf("AND userName like '%%%s%%'
			OR userName like '%%%s%%'
			ORDER BY lastAccess DESC
			", params_get('like'), params_get('like'));
	}

	$q = sprintf("SELECT fullName, userName, about, country, avatar, lastAccess
		FROM `users` 
		WHERE status = 1
		%s
		LIMIT %s, %s", $where, params_get('ini', 0), params_get('end', 10));
	$allPeople = db_query($q, 2);
	return $allPeople;
}


