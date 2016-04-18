<?php

namespace Omnipay\GlobalIris\Message;

/**
 * Global Iris Redirect Authorize Request
 */
class RedirectAuthorizeRequest extends AbstractRequest
{
	public function getData()
	{
		return null;
	}

	public function getRedirectData()
	{
		return $this->getBaseData();
	}

	public function sendData($data)
	{
		return $this->response = new RedirectAuthorizeResponse($this, $data);
	}
}
