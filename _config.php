<?

// Version: 0.421
// @TODO make sure this file does not get overwritten on churro upgrade :-(

// any functionality that is shared between all controllers belongs here
class Custom_Churro{
	
	public function __construct(){
	
	}
	
	// any custom routing goes here
	// routes should be set in the following format
	// $routes['actual/url/(:num)/(:slug)'] = 'controller/$1/$2';
	// short codes (:num), (:slug), and (:any) will be replaced with the appropriate regex
	// no leading or trailing slashes!
	public static function Routes(){
		$routes = array(
		
		);
		
		return $routes;
	}
}

// end of file 
// /churro/_config.php