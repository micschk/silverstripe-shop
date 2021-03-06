<?php

class CheckoutStep_Summary extends CheckoutStep{
	
	static $allowed_actions = array(
		'summary',
		'ConfirmationForm',
	);
	
	function summary(){
		$form = $this->ConfirmationForm();
		$this->owner->extend('updateConfirmationForm',$form);
		return array(
			'Form' => $form 
		);
	}
	
	function ConfirmationForm(){
		$cff = CheckoutFieldFactory::singleton();
		$fields = new FieldSet(
			$cff->getNotesField()
		);
		if($tf = $cff->getTermsConditionsField()){
			$fields->push($tf);
		}
		$actions = new FieldSet(
			new FormAction("place","Confirm and Pay")
		);
		$validator = new CheckoutValidator();
		$form = new Form($this->owner,"ConfirmationForm",$fields,$actions, $validator);
		$this->owner->extend('updateConfirmationForm',$form);
		return $form;
	}
	
	function place($data, $form){
		$order = ShoppingCart::curr();
		$form->saveInto($order);
		$order->write();
		$processor = OrderProcessor::create($order);
		//try to place order
		if(!$processor->placeOrder(Member::currentMember())){
			$form->sessionMessage($processor->getError(), 'bad');
			Director::redirectBack();
			return false;
		}
		$paymentredirect = $processor->makePayment(Checkout::get($order)->getSelectedPaymentMethod(false));
		if(!Director::redirected_to()){ //only redirect if one hasn't been done already
			Director::redirect($paymentredirect);
		}
		return;
	}
	
}