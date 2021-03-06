<?php

class CheckoutStep_PaymentMethod extends CheckoutStep{
	
	static $allowed_actions = array(
		'paymentmethod',
		'PaymentMethodForm',
	);
	
	function paymentmethod(){
		$methods = Checkout::get()->getPaymentMethods();
		if($methods->Count() == 1){ //skip step if there is only one payment type
			$this->setpaymentmethod(array(
				'PaymentMethod' => $methods->First()->ClassName
			), null);
			return;
		}
		return array(
			'Form' => $this->PaymentMethodForm()
		);
	}
	
	function PaymentMethodForm(){
		$checkout = Checkout::get();
		$fields = new FieldSet(new OptionsetField(
			'PaymentMethod','',$checkout->getPaymentMethods()->map('ClassName','Title'),$checkout->getPaymentMethods()->First()->ClassName
		));
		$actions = new FieldSet(
			new FormAction("setpaymentmethod","Continue")
		);
		$validator = new RequiredFields('PaymentMethod');
		$form = new Form($this->owner,"PaymentMethodForm",$fields,$actions);
		$this->owner->extend('updatePaymentMethodForm',$form);
		return $form;
	}
	
	function setpaymentmethod($data, $form){
		if($checkout = Checkout::get()){
			$checkout->setPaymentMethod($data["PaymentMethod"]);
		}
		Director::redirect($this->NextStepLink());
	}
	
	function getSelectedPaymentMethod(){
		if($checkout = Checkout::get()){
			return $checkout->getSelectedPaymentMethod();
		}
		return false;
	}
	
}