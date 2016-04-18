<?php

namespace Omnipay\GlobalIris\Message;

use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * Global Iris Redirect Purchase Response
 */
class RedirectPurchaseResponse extends RedirectAuthorizeResponse
{
	public function getRedirectData()
	{
		return $this->getRequest()->getBaseData();
	}
}
