<?php

namespace Omnipay\GlobalIris\Message;

use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * Global Iris Redirect Authorize Response
 */
class RedirectAuthorizeResponse extends Response implements RedirectResponseInterface
{
	protected $endpoint = 'https://redirect.globaliris.com/epage.cgi';

	public function isSuccessful()
	{
		return false;
	}

	public function isRedirect()
	{
		return true;
	}

	public function getRedirectUrl()
	{
		return $this->getCheckoutEndpoint();
	}

	public function getTransactionReference()
	{
		return $this->getRequest()->getTransactionId();
	}

	public function getRedirectMethod()
	{
		return 'POST';
	}

	public function getRedirectData()
	{
		return $this->getRequest()->getBaseData(false);
	}

	protected function getCheckoutEndpoint()
	{
		return $this->endpoint;
	}
}
