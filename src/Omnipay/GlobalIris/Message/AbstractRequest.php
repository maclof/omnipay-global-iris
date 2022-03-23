<?php

namespace Omnipay\GlobalIris\Message;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;

/**
 * Global Iris Abstract Request
 */
abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
	public function getMerchantId()
	{
		return $this->getParameter('merchantId');
	}

	public function setMerchantId($value)
	{
		return $this->setParameter('merchantId', $value);
	}

	public function getAccount()
	{
		return $this->getParameter('account');
	}

	public function setAccount($value)
	{
		return $this->setParameter('account', $value);
	}

	public function getSecret()
	{
		return $this->getParameter('secret');
	}

	public function setSecret($value)
	{
		return $this->setParameter('secret', $value);
	}

	public function setStore($value)
	{
		return $this->setParameter('store', $value);
	}

	public function getStore()
	{
		return $this->getParameters('store');
	}

	public function getPares()
	{
		return $this->getParameter('pares');
	}

	public function setPares($value)
	{
		return $this->setParameter('pares', $value);
	}

	public function setCavv($value)
	{
		return $this->setParameter('cavv', $value);
	}

	public function getCavv()
	{
		return $this->getParameter('cavv');
	}

	public function setXid($value)
	{
		return $this->setParameter('xid', $value);
	}

	public function getXid()
	{
		return $this->getParameter('xid');
	}

	public function setEci($value)
	{
		return $this->setParameter('eci', $value);
	}

	public function getEci()
	{
		return $this->getParameter('eci');
	}

	public function setNotifyUrl($value)
	{
		return $this->setParameter('notifyUrl', $value);
	}

	public function getNotifyUrl()
	{
		return $this->getParameter('notifyUrl');
	}

	public function getBaseData($autoSettle = true, $card = null)
	{
		$card = $this->getCard();

		$phoneNumber = $card->getPhone();

		$phoneUtil = PhoneNumberUtil::getInstance();
		$numberProto = null;
		try {
			$numberProto = $phoneUtil->parse($card->getPhone(), $card->getShippingCountry());
			$nationalPhoneNumber = $phoneUtil->format($numberProto, PhoneNumberFormat::NATIONAL);
			$nationalPhoneNumber = preg_replace('/[^0-9.]+/', '', $nationalPhoneNumber);
			$phoneNumber = $numberProto->getCountryCode() . '|' . $nationalPhoneNumber;
		} catch (NumberParseException $e) {
			//
		}

		$data = array(
			'TIMESTAMP'                       => gmdate('YmdHis'),
			'MERCHANT_ID'                     => $this->getMerchantId(),
			'ACCOUNT'                         => $this->getAccount(),
			'ORDER_ID'                        => $this->getTransactionId(),
			'AMOUNT'                          => (int) round($this->getAmount() * 100),
			'CURRENCY'                        => $this->getCurrency(),
			'AUTO_SETTLE_FLAG'                => $autoSettle ? 1 : 0,
			'MERCHANT_RESPONSE_URL'           => $this->getReturnUrl(),
			
			'HPP_VERSION'                     => 2,
			'HPP_CHANNEL'                     => 'ECOM',
			
			'HPP_CUSTOMER_EMAIL'              => $card->getEmail(),
			'HPP_CUSTOMER_PHONENUMBER_MOBILE' => $phoneNumber,
			
			'HPP_BILLING_STREET1'             => substr($card->getBillingAddress1(), 0, 50),
			'HPP_BILLING_STREET2'             => substr($card->getBillingAddress2(), 0, 50),
			'HPP_BILLING_STREET3'             => '',
			'HPP_BILLING_CITY'                => substr($card->getBillingCity(), 0, 50),
			'HPP_BILLING_STATE'               => '', // $card->getBillingState(), Should be the country subdivision code defined in ISO 3166-2 minus the country code itself
			'HPP_BILLING_POSTALCODE'          => substr($card->getBillingPostcode(), 0, 16),
			'HPP_BILLING_COUNTRY'             => $card->getBillingCountryUn(),
			
			'HPP_SHIPPING_STREET1'            => substr($card->getShippingAddress1(), 0, 50),
			'HPP_SHIPPING_STREET2'            => substr($card->getShippingAddress2(), 0, 50),
			'HPP_SHIPPING_STREET3'            => '',
			'HPP_SHIPPING_CITY'               => substr($card->getShippingCity(), 0, 50),
			'HPP_SHIPPING_STATE'              => '', // $card->getShippingState(), Should be the country subdivision code defined in ISO 3166-2 minus the country code itself
			'HPP_SHIPPING_POSTALCODE'         => substr($card->getShippingPostcode(), 0, 16),
			'HPP_SHIPPING_COUNTRY'            => $card->getShippingCountryUn(),
			
			'HPP_ADDRESS_MATCH_INDICATOR'     => 'FALSE',
			'HPP_CHALLENGE_REQUEST_INDICATOR' => 'NO_PREFERENCE',
		);

		$data['SHA1HASH'] = $this->createSignature($data, 'sha1', $card);

		return $data;
	}

	public function createSignature($data, $method = 'sha1', $card = null)
	{
		$hash = $method(rtrim(implode('.', array(
			$data['TIMESTAMP'],
			$data['MERCHANT_ID'],
			$data['ORDER_ID'],
			$data['AMOUNT'],
			$data['CURRENCY'],
			$card !== null ? $card->getNumber() : null
		)), '.'));
		
		return $method($hash.'.'.$this->getSecret());
	}

	public function getRequestXML($card, $autoSettle = true, $extraData = array(), $addressData = true, $cardData = true)
	{
		$data    = $this->getBaseData($autoSettle, $card);
		$brand   = (strcasecmp($card->getBrand(), "mastercard") == 0) ? "mc" : $card->getBrand();
		$request = new \SimpleXMLElement('<request />');

		$request['timestamp']        = $data['TIMESTAMP'];
		$request['type']             = $this->getType();

		$request->merchantid         = $this->getMerchantId();
		$request->account            = $this->getAccount();
		$request->orderid            = $data['ORDER_ID'];
		//$request->md5hash            = $this->createSignature($data, 'md5', $card);
		$request->custipaddress      = $this->getClientIp();

		$request->amount             = $data['AMOUNT'];
		$request->amount['currency'] = $data['CURRENCY'];

		$request->autosettle['flag'] = (int)$data['AUTO_SETTLE_FLAG'];

		// Flesh out the XML structure
		$request->addChild('card');
		$request->card->addChild('cvn');
		$request->card->number       = $card->getNumber();
		$request->card->expdate      = $card->getExpiryDate('my');
		$request->card->type         = strtoupper($brand);
		$request->card->chname       = $card->getName();

		// Not all request want this data
		if ($cardData) {
			$request->card->issueno = $card->getIssueNumber();
			$request->card->addChild('cvn');
			$request->card->cvn->addChild('number', $card->getCvv());
			$request->card->cvn->addChild('presind', 1);
		}

		// not all requests want this data
		if ($addressData) {
			$request->address['type']    = 'billing';
			$request->address->code      = $card->getBillingPostcode();
			$request->address->country   = strtoupper($card->getBillingCountry());
		}

		// Add in extra array data for any obscure fields
		if (!empty($extraData)) {
			foreach ($extraData as $key => $value) {
				$request->$key = $value;
			}
		}

		$request->sha1hash           = $data['SHA1HASH'];

		return $request->asXML();
	}

	protected function getType()
	{
		return 'auth';
	}
}
