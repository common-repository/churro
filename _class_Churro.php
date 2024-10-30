<?

// Version: 0.421

class Churro extends Custom_Churro{
	
	private $continue = FALSE;				// Usually need to continue back to wordpress when admin
	private $jsonAuto = TRUE;				// Whether to add html and success to json response automatically
	private $themedir = FALSE;				// theme directory
	private $method = FALSE;				// the method we will be using
	private $template_file = '';			// the actual file to use, no extension
											// should be $this->themedir.'/'.$this->view.'.php'
	protected $blog_id = FALSE;				// useful for MU
	protected $queried = FALSE;				// has wp_query run
	protected $view = null; 				// the template / .php file to render
	
	protected $json = array();				// object will be returned back auto on ajax
	protected $vars = array();				// object to hold varaibles for the template
	
	protected $wp = FALSE; 					// reference to wp object
	protected $wpdb = FALSE; 				// reference to wp database object
	
	public $wp_query = FALSE; 				// reference to wp query object
	
	// static variables
	protected static $cPath = '/';			// url 
	
	public function __construct(){
		global $blog_id, $wp, $wpdb, $wp_query;
		
		$this->blog_id = $blog_id;
		$this->wp = &$wp;
		$this->wpdb = &$wpdb;
		$this->wp_query = &$wp_query;
		
		$this->json = (object) array();
		$this->vars = (object) array();
		
		if( is_admin() ){
			$this->ContinueWP( TRUE );
		}
		
		$this->Theme();
		
		// let everybody know
		add_filter( 'the_generator', 'Churro::ShamelessPromotion' );
	}
	
	public function ContinueWP( $bool = null ){
		if( is_bool($bool) ){
			$this->continue = $bool;
		}
		return $this->continue;
	}
	
	public function Do404(){
		$this->wp_query->is_404 = TRUE;
		$this->wp_query->is_page = FALSE;
		$this->wp_query->is_singular = FALSE;
		
		// TODO: make the theme path tell the default theme value
		$this->Theme( '' );
		echo $this->Render( '404' );
		die();
	}
	
	public function Globals(){
		foreach( $this->vars as $k=>$v ){
			if( !isset($this->wp_query->query_vars[$k]) ){
				$this->wp_query->query_vars[$k] = $v;
			}
		}
		
		// i havent decided if this return value is good, it probably doenst matter
		return (object) $this->wp_query->query_vars;
	}
	
	public function Json(){
		return $this->json;
	}
	
	/*
	*	sets and gets whether to automatically add success and html fields to json response
	*	@param boolean $bool optional
	*	@return boolean
	*/
	public function JsonAuto( $bool = NULL ){
		if( is_bool($bool) ){
			$this->jsonAuto = $bool;
		}
		
		return $this->jsonAuto;
	}
	
	// get or set the controller
	public function Method( $method = null ){
		if( $method && method_exists($this, $method) ){
			$this->method = $method;
		} else if( $method && !method_exists($this, $method) ){
			return FALSE;
		}
		
		return $this->method;
	}
	
	public function Queried( $bool = null ){
		if( is_bool($bool) ){
			$this->queried = $bool;
		}
		
		return (bool) $this->queried;
	}
	
	public function Query( $query = null ){
		if( !is_null($query) ){
			$this->wp_query->parse_query( $query );
		}
		
		// this is what $this->Globals is doing.  consolidate?
		foreach( $this->wp_query->query_vars as $k=>$v ){
			$this->wp->set_query_var( $k ,$v );
		}
		
		if( !is_array($this->wp->query_vars) ){
			$this->wp->query_vars = (array) $this->wp->query_vars;
		}
		
		$this->wp->query_posts();
		$this->wp->register_globals();
		$this->queried = TRUE;
		
		$this->wp_query->is_home = self::$cPath == '/';
	}
	
	public function Redirect( $url = null ){
		if( ISAJAX ){
			return TRUE;
		}
		wp_redirect( $url );
		die();
	}
	
	/*
	*
	*	@param string $html
	*	@param array | object $vars
	*	@return string
	*/
	public function Render( $html = null, $vars = array() ){
		if( is_null($this->view) && is_null($html) && !is_admin() ){
			if( $this->wp_query->is_home ){
				$this->template_file = get_home_template();
			} else if( $this->wp_query->is_page ){
				$this->template_file = get_page_template();
			} else if( $this->wp_query->is_single ){
				$this->template_file = get_single_template();
			} else {
				return FALSE;
			}
		} else if( $html ){
			$this->template_file = $this->themedir.'/'.$html.'.php';
		} else if( $this->View() ){
			$this->template_file = $this->themedir.'/'. $this->view .'.php';
		}
		
		if( !$this->template_file || !file_exists($this->template_file) ){
			return FALSE;
		}
		
		// make object variables available to the template
		if( !count($vars) ){
			$vars = $this->vars;
		}
		$vars = (array) $vars;
		
		// do the magic
		extract( $vars, EXTR_SKIP );
		
		ob_start();
			include $this->template_file;
			$view = ob_get_contents();
		ob_end_clean();
		
		return $view;
	}
	
	/*
	*	set the theme directory manually or auto
	*	@param string optional, name of theme directory.  no leading or trailing slashes
	*	@return BOOL
	*/
	public function Theme( $template = null ){
		if( !$template ){
			$template = get_template();
		}
		
		$this->themedir = get_theme_root().'/'.$template;
		return TRUE;
	}
	
	/*
	*	set or get the template file
	*	
	*/
	public function View( $view = null ){
		if( $view ){
			return $this->view = $view;
		} else {
			return $this->view;
		}
	}
	
	/* static functions */
	
	/*
	*	sets up base directories and config files on plugin activation
	*	@return NULL
	*/
	public static function Activation(){
		// set up controller directory
		if( !is_dir(WP_PLUGIN_DIR.'/churro-controllers/') )
			mkdir( WP_PLUGIN_DIR.'/churro-controllers/' );
		
		// set up config file
		if( !file_exists(WP_PLUGIN_DIR.'/churro-controllers/_config.php') && file_exists(WP_PLUGIN_DIR.'/churro/_config.php') )
			rename( WP_PLUGIN_DIR.'/churro/_config.php', WP_PLUGIN_DIR.'/churro-controllers/_config.php' );
	}
	
	/*
	*	parses the path and pass to self::Path()
	*	attached to `parse_request` action for main site
	*	attached to `admin_menu` action for wp-admin/
	*	requires pretty permalinks
	*	calls `Churro_Bootstrap` action
	*	@return bool
	*/
	public static function Bootstrap(){
		// sessions are nice 
		if( !session_id() && !headers_sent() )
			session_start();
					
		do_action( 'Churro_Bootstrap' );
		
		// this is specific to jQuery.  why would you use anything else?
		if( !defined('ISAJAX') ) define( 'ISAJAX' , isset($_SERVER['HTTP_X_REQUESTED_WITH']) );
		
		if( is_admin() ){
			// dont pop the .php off the admin script anymore! 
			$cPath = explode( ' ', $_SERVER['SCRIPT_NAME'] ); 
			// dashboard.php will be require_once()'d in admin/index.php too, but we may need it sooner. boo.
			//require_once ABSPATH.'wp-admin/includes/dashboard.php';
		} else {
			// remove query string
			$cPath = explode( '?', $_SERVER['REQUEST_URI'] );
		}
		
		self::$cPath = $cPath[0];
		
		$success = self::Path( self::$cPath );
		
		// if we are here churro did not process the page, or we have forced wp to continue.
		// does it matter what is returned? i don't think so.
		return $success;
	}
	
	/*
	*	parse the reuested path and find correct file / controller / action
	*	param string $cPath optional
	*	param string $prefix optional
	*/
	public static function Path( $cPath = '', $prefix = '' ){
		self::_stripPathSlashes( $cPath );
		
		// handle custom routes
		$friendly = array( ':any', ':num', ':slug' );
		$regex = array( '.+', '[0-9]+', '[A-Za-z0-9\-.]+' );

		foreach( self::Routes() as $k=>$v ){
			$k = str_replace( $friendly, $regex, $k );
			if( preg_match('#^'.$k.'$#', $cPath) ){
				$cPath = preg_replace( '#^'.$k.'$#', $v, $cPath );
				break;
			}
		}
		// matched rule / custom rule
		
		// break url down into elements
		$explode = explode( '/', $cPath );
		$explode = array_values( array_filter($explode, 'trim') );
		if( !isset($explode[0]) ){
			$explode[0] = 'index';
		}
		
		// load the correct file / method
		$path = WP_PLUGIN_DIR.'/churro-controllers/';
		$controller = '';
		$method = '';
		
		$args = array();
		
		// loop through the path segments to find the correct class/controller and method.
		// the rest of the path segments will be passed to the method as arguments.
		foreach( $explode as $key => $seg ){	
			$test = $path.self::_camelCase($seg);
			
			if( is_dir($test) ){
				$controller = self::_camelCase( $controller.'-'.$seg );
				$method = isset( $explode[$key+1] ) ? $explode[$key+1] : 'index';
				$path = $test.'/';
				unset( $explode[$key]);
			} else if( is_file($test.'.php') ){
				$controller = self::_camelCase( $controller.'-'.$seg );
				$method = isset( $explode[$key+1] ) ? $explode[$key+1] : 'index';
				$path = $test.'.php';
				unset( $explode[$key]);
			} else {
				
			}
		}
		
		if( reset($explode) == $method ){
			array_shift( $explode );
		}
		
		$_method = self::_camelCase( $method ).'Action';
		
		if( is_dir($path) ){
			$path .= 'index.php';
		}
		
		// make sure the path to the controller exists
		if( file_exists($path) )
			include $path;
		else
			return FALSE;
		
		// set up the class. Controller is an extended Churro
		$controller = $controller.'_Churro';
		
		if( !class_exists($controller) ){
			return FALSE;
		}
		
		$Churro = new $controller;
		if( !($Churro instanceof Churro) ){
			return FALSE;
		} 
		
		// Check that the corresponding method exists
		$_method = $Churro->Method( $_method );
		
		// check that the method exists, or a default index action exists, or return
		// TODO: make sure this works correctly with controllers in subdirectories
		if( !$_method && method_exists($controller, 'indexAction') ){
			array_unshift( $explode, $method );
			$_method = 'indexAction';
		} else if( !$_method ){
			return FALSE;
		} else if( $_method == end($explode).'Action' ){
			array_pop( $explode );
		}
		
		// check if the controller is public / wants to accept variables
		$c = new ReflectionMethod( $controller, $_method );
		$args = count( $explode );
		
		if( $args < $c->getNumberOfRequiredParameters() || $args > $c->getNumberOfParameters() || !$c->isPublic() ){
			return FALSE;
		}
		
		do_action( 'template_redirect' );
		
		// everything is good, lets do it
		ob_start();
		
			// do the global actions, only if a specific controller is not found.
			if( method_exists($Churro, 'init') ){
				call_user_func( array($Churro, 'init') );
			}
			
			// run the controller
			$success = call_user_func_array( array($Churro, $_method), $explode );
			$success = is_bool( $success ) && !$success ? FALSE : TRUE;
			
			// run wp_query if it has not
			if( !$Churro->Queried() ){
				$Churro->Query( '' );
			}
			
			$Churro->Globals();
			
			// set the global query back to our version. i thought assigning by reference took care of this?
			global $wp_query;
			$wp_query = $Churro->wp_query;
			
			// TODO: see if theres a better way to do this
			$html = $Churro->Render();		// the content I want
			$html2 = ob_get_contents();		// extra output from debugs, etc
		
		ob_end_clean();
		
		$html = trim( $html2 ).$html;
		
		if( ISAJAX ){
			// ajax http request
			status_header( 200 );
			
			$json = $Churro->Json();
			
			if( is_object($json) && $Churro->JsonAuto() ){
				if( !isset($json->success) ){
					$json->success = $success;
				}
				
				if( !isset($json->html) ){
					$json->html = self::_trimJSON($html); 
				}
			}
			
			//die( 'strlen: '.strlen(serialize($json)) );
			ini_set( 'memory_limit', '256M' );
			echo json_encode( $json );
			
			die();
		} else {
			
			// normal http request
			if( !$Churro->ContinueWP() && !stripos($html, '</html>') ){
				
				$file = TEMPLATEPATH.'/churro-default.php';
				if( file_exists($file) ){
					include $file;
					// it's already dead, i believe. lets be safe.
					die();
				} else {
					ob_start();
						get_header();
						echo $html;
						get_footer();
						$html = ob_get_contents();
					ob_end_clean();
				}
			}
			
			if( defined('USE_TIDY_WITH_CHURRO') && USE_TIDY_WITH_CHURRO && extension_loaded('tidy') ){
				$html = self::_tidy( $html );
			}
			
			echo $html;
			
			if( !$Churro->ContinueWP() ){
				die();
			}
		}
	}
	
	/*
	*	gets a list of rewriting routes for Custom Churro class if Routes method exists
	*	@return array
	*/
	public static function Routes(){
		/*
		if( method_exists(get_parent_class(), 'Routes') )
			return Custom_Churro::Routes();
		else
			return array();
		*/
		return Custom_Churro::Routes();
	}
	
	/*
	*	add a meta tag below wordpress generator
	*	attached to `the_generator` filter
	*	@return string
	*/
	public static function ShamelessPromotion( $x ){
		$x .= "\n<meta name=\"generator\" content=\"Churro\" />\n";
		return $x;
	}
	
	/*
	*	camelCase strings by using dash - as delimeter
	*	replace periods with dash after camel case
	*	@param string
	*	@return string
	*/
	public static function _camelCase( $str = '' ){
		$cc = preg_replace_callback( '/-(\w)/', 'Churro::_camelCaseCallback', $str );
		
		return str_replace( '.', '_', $cc);
	}
	
	/*
	*	callback for _camelCase()
	*/
	public static function _camelCaseCallback( $matches ){
	     return ucfirst( $matches[1] );
	}
	
	/*
	*	clean up the html output if tidy is installed.
	*	experiemental for now, don't use on prod
	*	@param string
	*	@return string
	*/
	private static function _tidy( $html = '' ){
		$config = array( 'indent' => true,
						 'indent-spaces' => 4,
						 'wrap' => 0 );
                
		$tidy = new tidy;
		$tidy->parseString($html, $config);
		$tidy->cleanRepair();
		
		$html = (string) $tidy->value;

		return $html;
	}
	
	/*
	*	remove leading, trailing, and empty slashes in url
	*	@param string $cPath reference
	*/
	private static function _stripPathSlashes( &$cPath ){
		$aPath = explode('/', $cPath);
		$aPath = array_filter($aPath);
		$cPath = implode('/',$aPath);
	}
	
	/*
	*	remove line breaks and tabs from json string
	*	@param string
	*	@return string
	*/
	private static function _trimJSON( $string = '' ){
		$chars = array( "\n", "\t" );
		$string = str_replace( $chars, ' ', $string );	
		return $string;
	}
}

// end of file 
// /churro/_class_Churro.php