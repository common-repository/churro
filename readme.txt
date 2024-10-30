=== Churro ===
Contributors: postpostmodern
Donate link: http://www.heifer.org/
Tags: mvc, framework
Requires at least: 2.8.0
Tested up to: 3.0.5
Stable tag: trunk

Sweet and simple MVC for Wordpress.

== Description ==
Churro is a plugin that allows developers to easily create pages and functionality, in a style similar to CodeIgniter.  By itself, Churro will not add any functionality to your Wordpress site.

Tested with Wordpress and Wordpress MU 2.8.0 ~ 3.0.5 
Requires PHP 5.  PHP 4 is like 10 years old, you know?

== Installation ==
1. Place entire /churro/ directory to the /wp-content/plugins/ directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Write some code

== Changelog ==
= 0.3 =
* Fairly major refactoring of code, moved virtually all functions into class methods.

= 0.2 =
* Cleaning things up, more testing. No new functionality.

= 0.14 =
* Experimental support for HTML Tidy.

= 0.13 =
* Minor bug fix in jquery queue in admin.

= 0.12 =
* Bug fixes, slightly more documentation.

= 0.1 =
* Initial public release. No documentation.

== Frequently Asked Questions ==
= This doesn't do anything! =
Churro does not add any functionality to Wordpress by itself.  Instead, it allows developers a structure to integrate well organized, easily maintainable, object oriented MVC code into their applications.

= Why did you create Churro? =
Churro was originally developed as a stand-alone framework, sort of a hobby project after I had learned Zend framework and wanted to use the same concepts in my own projects, with simpler syntax and lighter footprint.  After Churro reached a fairly stable state, I realized it's similarities to CodeIgniter.  After wrestling with Wordpress, I decided to refactor the Churro code as a plugin, to allow easy, rapid development.  

== Screenshots ==
1. Churros are delicious `/trunk/screenshot-1.png`

== Churro Basics ==
= URLs = 
Churro requires friendly urls rather than query strings.  Your url will usually be mapped as following: `yoursite.com/class/method/variables`

= Controllers =
The controller will be directly mapped to a file and class with the same name.  For example,
	`yoursite.com/contact`
will look for a file named contact.php in the /churro/ directory.  This file should have a class contact that extends Churro.  Without a defined method, it will default to index.  All methods accessible through URLs will have 'Action' appended to it.  The following code will provide scaffolding for `yoursite.com/contact` and `yoursite.com/contact/submit`

`<?php
class contact extends Churro {
	public function indexAction(){
	
	}
	
	public function submitAction(){
	
	}
}`

= Models =
Churro gives you access to several important Wordpress Models through the `$this` keyword.  The `$wp`, `$wpdb`, and `$wp_query` classes, as well as `$blog_id` in MU are all availble without using the global keyword, eg `$this->wpdb`.

= Views = 
Your views belong in your normal theme directory.  If you are using multiple themes, you can override the direcotry using `$this->Theme( 'directory-name' )` any place in the controller.  To specify a view to use, `$this->View( 'file-name' )`.  Seperating logic from  views is the most important thing to remember in Churro.  To set varaibles in your view, use `$this->vars->var_name` in the controller, and `$var_name` in your view.

= Ajax / Javascript =
If you use the excellent jQuery javascript library, Churro will detect ajax requests and automatically return JSON.  Churro defines a constant `ISAJAX` on each request.  With a little planning, it is very easy to create a dynamic application in a way that is totally accessible without javascript.

== Learning Churro ==
There is much more functionality in Churro than outlined here.  As of September 2, 2009, there is no complete, formal, documentation for writing code with Churro.  Stay tuned to [Substance Labs](http://labs.findsubstance.com/ "The passionate voice for interactive brand strategy and creative experiences") for tutorials.
