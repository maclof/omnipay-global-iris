<?php

namespace Omnipay\GlobalIris;

use Omnipay\Common\AbstractGateway;

/**
 * Global Iris Redirect Class
 */
class RedirectGateway extends AbstractGateway
{
	public function getName()
	{
		return 'Global Iris Redirect';
	}

	public function getDefaultParameters()
	{
		return array(
			'merchantId' => '',
			'secret'     => '',
			'account'    => 'internet',
			'testMode'   => false
		);
	}

	public function getMerchantId()
	{
		return $this->getParameter('merchantId');
	}

	public function setMerchantId($value)
	{
		return $this->setParameter('merchantId', $value);
	}

	public function getSecret()
	{
		return $this->getParameter('secret');
	}

	public function setSecret($value)
	{
		return $this->setParameter('secret', $value);
	}

	public function getAccount()
	{
		return $this->getParameter('account');
	}

	public function setAccount($value)
	{
		return $this->setParameter('account', $value);
	}

	public function authorize(array $parameters = array())
	{
		return $this->createRequest('\Omnipay\GlobalIris\Message\RedirectAuthorizeRequest', $parameters);
	}

	public function completeAuthorize(array $parameters = array())
	{
		return $this->createRequest('\Omnipay\GlobalIris\Message\RedirectCompleteAuthorizeRequest', $parameters);
	}

	public function purchase(array $parameters = array())
	{
		return $this->createRequest('\Omnipay\GlobalIris\Message\RedirectPurchaseRequest', $parameters);
	}

	public function completePurchase(array $parameters = array())
	{
		return $this->completeAuthorize($parameters);
	}
}
