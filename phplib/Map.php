<?php

class Map {

	public static $map = array(
	    '/' => 'main.xml',
	    'features' => 'features/list.xml',
	    'features/new' => 'features/new.xml',
	    'features/%d' => 'features/show.xml',
	    'register' => 'register/index.xml',
	    'emailconfirm/%d/%s' => 'misc/email_confirm.xml',
	    404 => 'errors/p404.xml',
	    502 => 'errors/p502.xml',
	    'user/%s' => 'users/user.xml',
	    'user/%s/edit' => 'users/edit.xml',
	);
	public static $sinonim = array(
	    'user/%d' => 'user/%s',
	    'user/%d/edit' => 'user/%s/edit',
	);

}
