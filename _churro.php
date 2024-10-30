<?
/*
Plugin Name: Wordpress Churro
Plugin URI: http://wordpress.org/extend/plugins/churro/
Description: Base functions and bootstrap for Wordpress Churro framework. Requires PHP 5.2
Version: 0.421
Author: Eric Eaglstun
Author URI: http://ericeaglstun.com

 /********************************************-/__
|___  WORDPRESS	           	        	        __/
/__     CHURRO 0.42                            ___|
 \-********************************************_/
 
Copyright 2009 - 2012 Eric Eaglstun( email : eric@findsubstance.com )

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
                    Version 2, December 2004

 Copyright (C) 2004 Sam Hocevar
  14 rue de Plaisance, 75014 Paris, France
 Everyone is permitted to copy and distribute verbatim or modified
 copies of this license document, and changing it is allowed as long
 as the name is changed.

            DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
   TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION

  0. You just DO WHAT THE FUCK YOU WANT TO. 
 
*/

// custom url routes and churro methods
if( file_exists(WP_PLUGIN_DIR.'/churro-controllers/_config.php') )
	require WP_PLUGIN_DIR.'/churro-controllers/_config.php';

// Churro needs to extend Custom
if( !class_exists('Custom_Churro') ){
	class Custom_Churro{
		static public function Routes(){
			return array();
		}
	}
}

// base class
require WP_PLUGIN_DIR.'/churro/_class_Churro.php';
	
// get this thing going as early as possible.
if( is_admin() ){
	add_action( 'admin_menu', 'Churro::Bootstrap' );
	
	register_activation_hook( __FILE__, 'Churro::Activation' );
} else {
	add_action( 'parse_request', 'Churro::Bootstrap' );
}

// thanks
// end of file /churro/_churro.php