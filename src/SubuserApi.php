<?php

/**
 * SendGrid Subuser API PHP Client
 * This package requires Guzzle.
 * @author Alex Dills, STechStudio
 * @version 1.0
 * @since File available since 12/11/12
 */

namespace RC\Sendgrid;

use Guzzle\Http\Client;

Class SubuserApi {

	/**
	 * @var $baseUrl Url of the Sendgrid API, in this case, API v2
	 * @var $user username for Sendgrid.com, set upon instantiation
	 * @var $key Password for Sendgrid.com, set upon instantiation
	 * @var $client set upon instantiation to be a Client object in Guzzle
	 */
	private $baseUrl = 'https://sendgrid.com/apiv2';
	private $user;
	private $key;
	private $client;

	function __construct() {
		$this->user = getenv('SENDGRID_USERNAME');
		$this->key = getenv('SENDGRID_KEY');
		$this->client = new Client($this->baseUrl);
	}
	/**
	 * Gets a list of the subusers of the subuser account in question
	 * @return array list of subusers for sendgrid subuser
	 */
	function getSubusers() {
		return $this->_postRequest('customer.profile.json',array(
			'task'		=> 'get'
			));
	}

	/**
	 * Checks whether a given subuser exists on the sendgrid account
	 * @return boolean of whether the subuser exists
	 */
	function subuserExists($subuserUsername) {
		$subusers = $this->getSubusers();

		foreach($subusers as $key => $info) {
			if($info['username'] == $subuserUsername) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Gets a list of the apps and their respective statuses for a certain subuser
	 * @param $subuser The subuser whose apps we're retreiving
	 * @return array list of apps for a subuser
	 */
	function getApps($subuser){
		return $this->_postRequest('customer.apps.json', array(
			'user'		=> $subuser,
			'task'		=> 'getavailable'
			));
	}

	/**
	 * Gets the status of a certain app belonging to a subuser
	 * @param $subuser The subuser whose app we're checking on
	 * @param $app The name of the SendGrid app in question, whose settings we're listing
	 * @return array of settings for a certain app
	 */
	function getAppSettings($subuser, $app){
		return $this->_postRequest('customer.apps.json',array(
			'user'		=> $subuser,
			'name'		=> $app,
			'task'		=> 'getsettings'
			));
	}

	/**
	 * Activates a specified app belonging to a subuser
	 * @param $subuser The subuser who owns the app we're enabling
	 * @param $app The app to be enabled
	 * @return boolean
	 */
	function appActivate($subuser, $app){
		return $this->_postRequest('customer.apps.json',array(
			'task'		=> 'activate',
			'name'		=> $app,
			'user'		=> $subuser
			));
	}


	/**
	 * Deactivates a specified app
	 * @param string $subuser the subuser whose app we're disabling
	 * @param string $app The app to disable
	 * @return boolean
	 */
	function appDeactivate($subuser, $app){
		return $this->_postRequest('customer.apps.json',array(
			'task'		=> 'deactivate',
			'name'		=> $app,
			'user'		=> $subuser
			));
	}



	/**
	 * Retrieves the existing Notifier Event URL for a specified subuser
	 * @param $subuser the subuser who's notify event URL we're checking
	 * @return string event URL of subuser
	 */
	function getEventUrl($subuser){
		$tmp = $this->_postRequest('customer.eventposturl.json',array(
			'task'		=> 'get',
			'user'		=> $subuser
			));
		return $tmp[0]['url'];
		}

	/**
	 * Sets the Event URL for a specified subuser
	 * @param $subuser The subuser whose notify event URL we're setting
	 * @param $url The URL to be set for the subuser
	 * @return boolean
	 */
	function setEventUrl($subuser, $url){
		return $this->_postRequest('customer.eventposturl.json',array(
			'task'		=> 'set',
			'user'		=> $subuser,
			'url'		=> $url
			));
	}

	/**
	 * Deletes the existing event URL for specified subuser
	 * @param string $subuser the subuser whose notify event URL we're deleting
	 * @return boolean
	 */
	function delEventUrl($subuser){
		return $this->_postRequest('customer.eventposturl.json',array(
			'task'		=> 'delete',
			'user'		=> $subuser
			));
	}

	/**
	 * Sets all the settings(except Batch, for some reason) for Event Notifier to 'true'
	 * @ param $subuser the subuser for whom we're changing the settings of Event Notifier
	 * @param $url The notify url of the subuser(Required by the API)
	 * @return boolean
	 */
	function setNotifyCheckboxes($subuser,$url){
		return $this->_postRequest('customer.apps.json',array(
			'task'		=> 'setup',
			'user'		=> $subuser,
			'name'		=> 'eventnotify',
			'processed'	=> '1',
			'dropped'	=> '1',
			'deferred'	=> '1',
			'delivered'	=> '1',
			'bounce'	=> '1',
			'click'		=> '1',
			'open'		=> '1',
			'unsubscribe'=>'1',
			'spamreport'=> '1',
			'url'		=> $url
			));
	}

	/**
	 * Upgrades the Event Notification app's webhook version to 3
	 */
	function upgradeEventNotifications($subuser,$url){
		return $this->_postRequest('customer.apps.json',array(
			'task'	=>	'setup',
			'user'	=>	$subuser,
			'name'	=>	'eventnotify',
			'processed'	=> '1',
			'dropped'	=> '1',
			'deferred'	=> '1',
			'delivered'	=> '1',
			'bounce'	=> '1',
			'click'		=> '1',
			'open'		=> '1',
			'unsubscribe'=>'1',
			'spamreport'=> '1',
			'version'	=>	'3',
			'url'	=>	$url
			));
	}

	/**
	 * Retrieves the whitelist e-mail from Sendgrid
	 * @param string $subuser The subuser to get the whitelist email for
	 * @return boolean or string False if an error is returned, string containing e-mail if successful
	 */
	function getWhitelistEmail($subuser){
		$tmp = $this->_postRequest('customer.apps.json',array(
			'task'		=> 'getsettings',
			'user'		=> $subuser,
			'name'		=> 'addresswhitelist'
			));
		if(empty($tmp['settings']['list'][0])){
			return false;
		}elseif(empty($tmp['settings']['list'][1])){
			return $tmp['settings']['list'][0];
		}else{
			return $tmp['settings']['list'];
		}
	}


	function setWhitelistEmail($subuser,$email){
		return $this->_postRequest('customer.apps.json',array(
			'task'		=> 'setup',
			'user'		=> $subuser,
			'name'		=> 'addresswhitelist',
			'list'		=> $email
			));
	}

	/**
	 * Adds a Whitelist e-mail for a specified subuser
	 * @param string $subuser the subuser to add a whitelist email to
	 * @param string $email The email to add to the Whitelist App
	 * @return boolean
	 */
	function addWhitelistEmail($subuser,$email){
		$existing = $this->getWhitelistEmail($subuser);
		if(!$existing){
			$existing = array(
				'0'		=> $email);
		}elseif(is_string($existing)){
			$existing = array(
				'0' 	=> $email,
				'1'		=> $existing
				);
		}else{
			$existing[] = $email;
		}

		$existing = array_flip(array_flip($existing));
		$postArray = array(
			'task'		=> 'setup',
			'user'		=> $subuser,
			'name'		=> 'addresswhitelist',
			'list'	=> $existing
			);
		//return $existing;
		return $this->_postRequest('customer.apps.json', $postArray);
	}

	function delWhitelistEmails($subuser){
		return $this->_postRequest('customer.apps.json', array(
			'task'		=>	'setup',
			'user'		=> $subuser,
			'name'		=> 'addresswhitelist',
			'list[]'		=> ''
			));
	}

	/**
	 * Adds a subuser to the Sendgrid account
	 * @param string $subuser The username for the new subuser
	 * @param string $password The password for the new subuser
	 * @param string $email E-mail address for the new subuser
	 * @param string $first First name for the new subuser
	 * @param string $last Last name for the new subuser
	 * @param string $address Address for the new subuser
	 * @param string $city City for the new subuser
	 * @param string $zip ZIP code for the new subuser
	 * @param string $country Country for the new subuser
	 * @param string $phone Phone number for the new subuser
	 * @param string $website Website for the new subuser
	 * @param string $company Company name for the new subuser
	 * @param string $mail_domain Mail domain for the new subuser
	 * @return boolean
	 */
	function addSubUser($subuser,$password,$email,$first,$last,$address,$city,$state,$zip,$country,$phone,$website,$company,$mail_domain){
		return $this->_postRequest('customer.add.json',array(
			'username'	=> $subuser,
			'password'	=> $password,
			'confirm_password' => $password,
			'email'		=> $email,
			'first_name'=> $first,
			'last_name'	=> $last,
			'address'	=> $address,
			'city'		=> $city,
			'zip'		=> $zip,
			'country'	=> $country,
			'phone'		=> $phone,
			'website'	=> $website,
			'company'	=> $company,
			'mail_domain'=> $mail_domain
			));
	}

	/**
	 * Enables a subuser on Sendgrid
	 * @param $subuser The subuser to be enabled
	 * @return boolean
	 */
	function enableSubuser($subuser){
		return $this->_postRequest('customer.enable.json',array(
			'user'		=> $subuser
			));
	}

	/**
	 * Disables a subuser on Sendgrid
	 * @param $subuser The subuser to be disabled
	 * @return boolean
	 */
	function disableSubuser($subuser){
		return $this->_postRequest('customer.disable.json',array(
			'user'		=> $subuser
			));
	}

	/**
	 * Enables access to the Sendgrid website for a subuser
	 * @param $subuser The subuser to enable access for
	 * @return boolean
	 */
	function enableSiteAccess($subuser){
		return $this->_postRequest('customer.website_enable.json',array(
			'user'		=> $subuser
			));
	}

	/**
	 * Disables access to the Sendgrid website for a subuser
	 * @param $subuser The subuser to enable access for
	 * @return boolean
	 */
	function disableSiteAccess($subuser){
		return $this->_postRequest('customers.website_disable.json',array(
			'user'		=> $subuser
			));
	}

	/**
	 * Make changes to a subuser's profile
	 * @param string $subuser Subuser whose profile to edit
	 * @param string $password new password for the subuser
	 * @param string $email new e-mail address for the subuser
	 * @param string $first New first name for the subuser
	 * @param string $last New last name for the subuser
	 * @param string $address New address for the subuser
	 * @param string $city New city for the subuser
	 * @param string $zip New ZIP code for the subuser
	 * @param string $country New country for the subuser
	 * @param string $phone New phone number for the subuser
	 * @param string $website New website for the subuser
	 * @param string $company New company for the subuser
	 * @param string $mail_domain New mail domain for the subuser
	 * @return boolean
	 */
	function updateSubuserProfile($subuser,$password,$email,$first,$last,$address,$city,$state,$zip,$country,$phone,$website,$company,$mail_domain){
		return $this->_postRequest('customer.profile.json',array(
			'username'	=> $subuser,
			'password'	=> $password,
			'confirm_password' => $password,
			'email'		=> $email,
			'first_name'=> $first,
			'last_name'	=> $last,
			'address'	=> $address,
			'city'		=> $city,
			'zip'		=> $zip,
			'country'	=> $country,
			'phone'		=> $phone,
			'website'	=> $website,
			'company'	=> $company,
			'mail_domain'=> $mail_domain
			));
	}

 	/**
 	 * Change subuser username
 	 * @param string $subuser The current username of the subuser
 	 * @param string $newUsername The new username of the subuser
 	 * @return boolean
 	 */
	function updateSubuserUsername($subuser,$newUsername){
		return $this->_postRequest('customer.profile.json',array(
			'task'		=> 'setUsername',
			'user'		=> $subuser,
			'username'	=> $newUsername
			));
	}

 	/**
 	 * Change subuser password
 	 * @param string $subuser The username of the subuser
 	 * @param string $password The new password for the subuser
 	 * @return boolean
 	 */
	function updateSubuserPassword($subuser,$password){
		return $this->_postRequest('customer.profile.json',array(
			'user'		=> $subuser,
			'password'	=> $password,
			'confirm_password' => $password
			));
	}

 	/**
 	 * Change subuser email
 	 * @param string $subuser The username of the subuser
 	 * @param string $newEmail The new email of the subuser
 	 * @return boolean
 	 */
	function updateSubuserEmail($subuser,$newEmail){
		return $this->_postRequest('customer.profile.json',array(
			'task'		=> 'setEmail',
			'user'		=> $subuser,
			'email'		=> $newEmail
			));
	}

	/**
	 * Retrieves the invalid emails for a subuser
	 * @param string $subuser The subuser whose invalid emails we want
	 * @param strign $date(Optional) the date to retreive invalid emails for
	 * @return array List of invalid e-mails
	 */
	function getInvalidEmails($subuser,$date = null){
		return $this->_postRequest('customer.invalidemails.json',array(
			'task'		=> 'get',
			'user'		=> $subuser,
			'date'		=> $date
			));
	}

	/**
	 * Removes invalid e-mails
	 * @param string $subuser The subuser we're deleting invalid emails for
	 * @param string $email The email to be removed
	 * @return boolean
	 */
	function removeInvalidEmail($subuser,$email = null){
		return $this->_postRequest('customer.invalidemails.json',array(
			'task'		=> 'delete',
			'user'		=> $subuser,
			'date'		=> $email
			));
	}

	/**
	 * Gets the parse settings for a subusers
	 * @param string $subuser The subuser we're getting parse settings for
	 * @return array Parse settings for subuser
	 */
	function getParseSettings($subuser){
		return $this->_postRequest('customer.parse.json',array(
			'task'		=> 'get',
			'user'		=> $subuser
			));
	}

	/**
	 * Retreives statistics for a certain user
	 * @param string $subuser the subuser we're getting statistics for
	 * @param string $days The number of days in the past to include statistics
	 * @param string $start_date The start date of the range to get statistics for
	 * @param string $end_date The end date of the range to get statistics for
	 * @return array List of statistics for the specified subuser in the range specified
	 */
	function retrieveStatistics($subuser,$days,$start_date,$end_date){
		return $this->_postRequest('customer.stats.json',array(
			'user'		=> $subuser,
			'days'		=> $days,
			'start_date'=> $start_date,
			'end_date'  => $end_date
			));
	}

	/**
	 * Retrieve all-time totals for a subuser
	 * @param string $subuser The subuser to get all-time totals for
	 * @return array list of all-time totals for the specified subuser
	 */
	function retrieveAggregates($subuser){
		return $this->_postRequest('customer.stats.json',array(
			'user'		=> $subuser,
			'aggregate'	=> '1'
			));
	}

	/**
	 * Lists the categories for a specified subuser
	 * @param string $subuser The subuser whom we're listing categories for
	 * @return array list of categories for the subuser
	 */
	function listCategories($subuser){
		return $this->_postRequest('customer.stats.json',array(
			'user'		=> $subuser,
			'list'		=> 'true'
			));
	}

	/**
	 * Gets statistics for a subuser sorted by a category
	 * @param string $subuser the subuser whose statistics we're getting
	 * @param string $category the category we're checking statistics for
	 * @param integer $days Number of days in the past to include statistics
	 * @param string $start_date The start date to look up statistics for
	 * @param string $end_date The end date to look up statistics for
	 * @return array List of statistics for the category specified
	 */
	function getStatsByCategory($subuser,$category,$days = NULL,$start_date = NULL,$end_date = NULL){
		return $this->_postRequest('customer.stats.json',array(
			'user'		=> $subuser,
			'category'	=> $category,
			'days'		=> $days,
			'start_date'=> $start_date,
			'end_date'	=> $end_date
			));
	}

	/**
	 * Gets a list of the limits on an account
	 * @param string $subuser The subuser we're getting limits for
	 * @return array limits on subuser
	 */
	function getLimits($subuser){
		return $this->_postRequest('customer.limit.json',array(
			'user'		=> $subuser,
			'task'		=> 'retrieve'
			));
	}

	/**
	 * Removes limits on a certain user
	 * @param string $subuser The subuser we're getting limits for
	 * @return boolean
	 */
	function removeLimits($subuser){
		return $this->_postRequest('customer.limit.json',array(
			'user'		=> $subuser,
			'task'		=> 'none'
			));
	}

	/**
	 * Sets a recurring credit reset period for a subuser
	 * @param string $subuser The subuser we're setting a reset period for
	 * @param integer $credits The number of credits the user will be given each reset
	 * @param string $period Must be set daily, weekly, or monthly.
	 * @param string $startdate Date to start, must be in YYYY-mm-dd format
	 * @param string $enddate Date to end, must be in YYYY-mm-dd format
	 * @param string $initial_credits Number of credits you want the account to initally be set to
	 * @return boolean
	 */
	function recurringReset($subuser,$credits = NULL,$period = NULL,$startdate = NULL,$enddate = NULL,$initial_credits = NULL){
		if(isset($credits) && $credits <= 0){
			$this->lastError = 'Credits must be integer greater than 0';
			return false;
		}else{return $this->_postRequest('customer.limit.json',array(
			'user'		=> $subuser,
			'task'		=> 'recurring',
			'credits'	=> $credits,
			'period'	=> $period,
			'startdate'	=> $startdate,
			'enddate'	=> $enddate,
			'initial_credits'=> $initial_credits
			));
		}
	}

	/**
	 * Sets a subuser's total credits
	 * @param string $subuser The subuser we're setting credits for
	 * @param integer $credits the number of credits the user will be given
	 * @return boolean
	 */
	function setCredits($subuser,$credits){
		if($credits <= 0){
			$this->lastError = 'Credits must be integer greater than 0';
			return false;
		}else{return $this->_postRequest('customer.limit.json',array(
			'task'		=> 'total',
			'user'		=> $subuser,
			'credits'	=> $credits
			));
		}
	}

	/**
	 * Increase credits by a certain amount for a subuser
	 * @param string $subuser Subuser we're adding credits to
	 * @param integer $credits Number of credits we're giving the subuser
	 * @return boolean
	 */
	function incrementCredits($subuser,$credits){
		if($credits <= 0){
			$this->lastError = 'Credits must be integer greater than 0';
			return false;
		}else{
			return $this->_postRequest('customer.limit.json',array(
			'task'		=> 'increment',
			'user'		=> $subuser,
			'credits'	=> $credits
			));
		}
	}

	/**
	 * Decrease credits by a certain amount for a subuser
	 * @param string $subuser Subuser we're taking credits from
	 * @param integer $credits Number of credits we're taking from the subuser
	 * @return boolean
	 */
	function decrementCredits($subuser,$credits){
		if($credits <= 0){
			$this->lastError = 'Credits must be integer greater than 0';
			return false;
		}else{
			return $this->_postRequest('customer.limit.json',array(
			'task'		=> 'decrement',
			'user'		=> $subuser,
			'credits'	=> $credits
			));
		}
	}

	/**
	 * Retrieve a list of the bounces for a subuser
	 * @param string $subuser Subuser whose bounces we're retrieving
	 * @return array of bounced e-mail addresses
	 */
	function getSubuserBounces($subuser){
		return $this->_postRequest('https://api.sendgrid.com/api/user.bounces.json',array(
			'task'	=>	'get',
			'user'	=>	$subuser,
			));
	}

	/**
	 * Remove a single bounced e-mail from a subuser
	 * @param string $subuser Subuser whose bounce we're removing
	 * @param email $email address to remove from the bounces list
	 * @return boolean
	 */
	function removeBounce($subuser,$email){
		return $this->_postRequest('https://api.sendgrid.com/api/user.bounces.json',array(
			'task'	=>	'delete',
			'user'	=>	$subuser,
			'email'	=>	$email
			));
	}

	/**
	 * Retrieve a list of the spam reports for a subuser
	 * @param string $subuser Subuser whose spam reports we're retrieving
	 * @return array of spam reports
	 */
	function getSubuserSpamReports($subuser) {
		return $this->_postRequest('https://api.sendgrid.com/api/user.spamreports.json', array(
			'task'	=>	'get',
			'user'	=>	$subuser
		));
	}

	/**
	 * Remove a spam report from a subuser account
	 * @param string $subuser Subuser whose account we're removing the spam report from
	 * @param string $email Email address to remove the spam report for
	 * @return boolean
	 */
	function removeSpamReport($subuser,$email){
		return $this->_postRequest('https://sendgrid.com/api/user.bounces.json',array(
			'task'	=>	'delete',
			'user'	=>	$subuser,
			'email'	=>	$email
			));
	}

	/**
	 * Find suppressed e-mails across all suppression types and return which ones they are
	 * @param string $subuser Subuser we're checking for these suppressions on
	 * @param array $emails array of emails to check for
	 * @return array that looks like this:
	 * 	[
	 *	 	'Bounces' => [
	 *	 		'bouncedemail1' => '[reason]',
	 *			'bouncedemail2' => '[reason]',
	 *			'bouncedemail3' => '[reason]'
	 *		],
	 *		'Spam Reports' => [
	 *			'spamreportedemail1' => '[reason]',
	 *			'spamreportedemail2' => '[reason]'
	 *		],
	 *		'Invalid Emails' => [
	 *			'invalidaddress1' => '[reason]',
	 *			'invalidaddress2' => '[reason]'
	 *		],
	 *		'Not Suppressed' => [
	 *			'email1',
	 *			'email2'
 	 *		]
	 * 	]
	 */

	function getSuppressions($subuser, $emails) {
		$results = ['Bounces' => [], 'Spam Reports' => [], 'Invalid Emails' => [], 'Not Suppressed' => []];

		if(is_string($emails)) {
			$emails = [$emails];
		}

		if(!is_array($emails)) {
			throw new \Exception("emails paremeter must be a list");
		}

		$notSuppressed = array_flip($emails);

		$subuserSuppressions = [
			'Spam Reports' => $this->getSubuserSpamReports($subuser),
			'Bounces' => $this->getSubuserBounces($subuser),
			'Invalid Emails' => $this->getInvalidEmails($subuser)
		];

		foreach($subuserSuppressions as $type => $suppressions) {
			if(!count($suppressions) > 0) continue;
			foreach($suppressions as $suppressedInfo) {
				if(in_array($suppressedInfo['email'], $emails)) {
					$results[$type][$suppressedInfo['email']] = $suppressedInfo['reason'];
					unset($notSuppressed[$suppressedInfo['email']]);
				}
			}
		}

		$results['Not Suppressed'] = array_flip($notSuppressed);

		return $results;
	}


	function clearSuppressions($subuser, $emails) {
		$results = ['Bounces' => [], 'Spam Reports' => [], 'Invalid Emails' => [], 'Not Suppressed' => []];

		if(is_string($emails)) {
			$emails = [$emails];
		}

		if(!is_array($emails)) {
			throw new \Exception("emails paremeter must be a list");
		}

		$notSuppressed = array_flip($emails);

		$subuserSuppressions = [
			'Spam Reports' => $this->getSubuserSpamReports($subuser),
			'Bounces' => $this->getSubuserBounces($subuser),
			'Invalid Emails' => $this->getInvalidEmails($subuser)
		];

		foreach($subuserSuppressions as $type => $suppressions) {
			if(!count($suppressions) > 0) continue;
			foreach($suppressions as $suppressedInfo) {
				if(in_array($suppressedInfo['email'], $emails)) {
					switch($type){
						case 'Spam Reports':
							$result = $this->removeSpamReport($subuser, $suppressedInfo['email']);
							break;
						case 'Bounces':
							$result = $this->removeBounce($subuser, $suppressedInfo['email']);
							break;
						case 'Invalid Emails':
							$result = $this->removeInvalidEmail($subuser, $suppressedInfo['email']);
							break;
					}

					unset($notSuppressed[$suppressedInfo['email']]);
					$results[$type][$suppressedInfo['email']] = ($result) ? "Suppression Cleared" : "Suppression failed: " . $this->getLastError();
				}
			}
		}

		$results['Not Suppressed'] = array_flip($notSuppressed);

		return $results;
	}

	/**
	 * Gets the last error that occurred
	 * @return the last error given by Sendgrid
	 */
	function getLastError(){
		return $this->lastError;
	}

	/**
	 * Used by the class to process all API requests to and from Sendgrid
	 * @param string $url The API page to use for the operation
	 * @param array $data POST data to send to the Sendgrid API
	 * @return Boolean or an array depending on whether the response was an error or not
	 */
	private function _postRequest($url, $data){
	$fullurl = (stripos($url,"http") !== FALSE) ? $url : $this->baseUrl . "/" . $url;

	$request = $this->client->post($fullurl);
	// Merge in our authentication
	$postFields = array_merge(
		$data,
		array(
			'api_user'	=> $this->user,
			'api_key'	=> $this->key)
	);

	$request->addPostFields($postFields);

	$response = $request->send();
	$response = $response->json();

		if(isset($response['message']) && $response['message'] == 'error'){
		    $this->lastError = $response['errors']['0'];
		    return false;
		}elseif(isset($response['error'])){
			$this->lastError = $response['error']['message'];
			return false;
		}elseif(isset($response['message']) && $response['message'] == 'success'){
		    return true;
		}else{
		    return $response;
		}
	}
}

?>