<?php namespace Sugi;

require_once __DIR__."/OAuth2/OAuth2.php";

class OAuth2 extends Oauth2\OAuth2
{
	public function __construct(array $config = array())
	{
		$storage = $config['storage'];
		unset($config['storage']);
		parent::__construct($storage, $config);
	}
}
