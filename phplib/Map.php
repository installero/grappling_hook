<?php

class Map {

	public static $map =
		array(
	    '/' => 'main.xml',
	    'register' => 'register/index.xml',
	    'emailconfirm/%d/%s' => 'misc/email_confirm.xml',
	    404 => 'errors/p404.xml',
	    502 => 'errors/p502.xml',
	);
	public static $sinonim =
		array(
	    'user/%d' => 'user/%s',
	);

}
