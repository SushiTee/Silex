<?php
/**
 * @author    Janek Ostendorf (ozzy) <ozzy2345de@gmail.com>
 * @copyright Copyright (c) 2013 SilexLab
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
 
/**
 * Checks if someone is logged in
 */
class LoginCheck {
	/**
	 * TODO: Add priorities
	 * @var ILoginChecker[]
	 */
	protected static $loginCheckers = [];

	/**
	 * @var ILoginChecker
	 */
	protected static $checkerInUse = null;

	public static function init() {
		// Load LoginCheckers
		foreach(scandir(DIR_LIB.'login/checkers/') as $file) {
			// Get the class
			if(!preg_match('/^([a-zA-Z0-9]+LoginChecker)\.class\.php$/', $file, $matches))
				continue;
			$checker = new $matches[1];

			if($checker instanceof ILoginChecker)
				self::$loginCheckers[] = $checker;
		}
	}

	/**
	 * @return bool
	 */
	public static function isLoggedIn() {
		if(self::$checkerInUse !== null && self::$checkerInUse->isLoggedIn())
			return true;
		$i = 0;
		while(isset(self::$loginCheckers[$i])) {
			if(self::$loginCheckers[$i]->isLoggedIn()) {
				self::$checkerInUse = self::$loginCheckers[$i];
				return true;
			}
			$i++;
		}
		return false;
	}

	/**
	 * @return null|User
	 */
	public static function getUser() {
		if(self::isLoggedIn() && self::$checkerInUse->getUser() instanceof User)
			return self::$checkerInUse->getUser();
		return UserFactory::getGuest();
	}
}
