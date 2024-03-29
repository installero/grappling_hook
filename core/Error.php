<?php

class Error {
	const E_404 = 404;

	// modules
	const E_MODULE_NOT_FOUND = 502;
	const E_MUST_BE_IMPLEMENTED = 502;
	const E_MODULE_SETTINGS_NOT_FOUND = 502;
	const E_WRITEMODULE_MISSED = 502;
	// xslt
	const E_XSLT_TEMPLATE_FILE_MISSED = 502;
	const E_XSLT_MAIN_TEMPLATE_FILE_MISSED = 502;
	//include
	const E_INCLUDE_FAILED = 502;
	//database
	const E_QUERY = 502;
	// user
	const E_USER_NOT_FOUND = 404;
	const E_WRONG_ROLE = 502;

	public static function CheckThrowAuth($needRole = 1) {
		global $current_user;
		$needRoleName = Users::$rolenames[$needRole];
		$haveRoleName = Users::$rolenames[$current_user->getRole()];
		if ($current_user->getRole() < $needRole) {
			throw new Exception('Вы — ' . $haveRoleName . ', а должны быть не ниже, чем ' . $needRoleName . ', чтобы посетить эту страницу', 403);
		}
	}

}