<?php namespace Bkwld\Decoy;

// Dependencies
use \App;
use Illuminate\Support\Str;

/**
 * The router is what allows us to wildcard the admin routes so that the
 * developer doesn't need to hard code those.  One thing to know is that decoy
 * uses routes that are very literal with how the content is organized.  This
 * informs the breadcrumbs.  So, if you are looking at the edit view of a photo
 * that belongsTo() an article, the path would be admin/article/2/photo/4/edit
 */
class Router {
	
	// Properties
	private $dir;
	private $verb;
	private $path;
	private $actions = array('edit', 'create'); // These are action suffixes on paths
	
	/**
	 * Constructor
	 * @param $string dir The path "directory" of the admin.  I.e. "admin"
	 * @param $string verb GET,POST,etc
	 * @param $string path A URL path like 'admin/articles/new'
	 */
	public function __construct($dir, $verb, $path) {
		$this->dir = $dir;
		$this->verb = $verb;
		$this->path = $path;
	}
	
	/**
	 * Detect the controller for a given route and then execute the action
	 * that is specified in the route.
	 */
	public function detectAndExecute() {
		
		// Get the controller
		$controller = $this->detectController();
		if (!$controller || !class_exists($controller, true)) return false;
		
		
		// Get the action
		$action = $this->detectAction();
		if (!$action || !method_exists($controller, $action)) return false;

		// Get the id
		$id = $this->detectId();
		
		// Invoke the controller
		$controller = new $controller();
		$params = $id ? array($id) : array();
		return $controller->callAction(App::getFacadeApplication(), App::make('router'), $action, $params);
		
	}
	
	/**
	 * Detect the controller for a path.  Which is the last non-action
	 * string in the path
	 * @return string The controller name, i.e. Admin\ArticlesController
	 */
	public function detectController() {
		
		// The controller must begin with the config dir
		if (!preg_match('#^'.$this->dir.'#i', $this->path, $matches)) return false;
		
		// Find the controller from the end of the path
		$pattern = '#/([a-z-]+)(/\d)?(/('.implode('|', $this->actions).'))?/?$#i';
		if (!preg_match($pattern, $this->path, $matches)) return false;
		$name = $matches[1];
		
		// Form the namespaced controller
		return Str::studly($this->dir).'\\'.Str::studly($name).'Controller';
	}
	
	/**
	 * Detect the action for a path
	 */
	public function detectAction() {
		
		// If the path ends in one of the special actions, use that as the action
		if (preg_match('#[a-z-]+$#i', $this->path, $matches)) {
			if (in_array($matches[0], $this->actions)) return $matches[0];
		}
		
		// If the path ends in a number, the verb defines what it is
		if (preg_match('#\d+$#', $this->path)) {
			switch($this->verb) {
				case 'PUT': return 'update';
				case 'DELETE': return 'destroy';
			}
		}
		
		// Else, it must end with the controller name
		switch($this->verb) {
			case 'POST': return 'store';
			case 'GET': return 'index';
		}
		
		// Must have been an erorr if we got here
		return false;
	}
	
	/**
	 * Detect the id for the path
	 */
	public function detectId() {
		
		// If there is an id, it will be the last number
		if (preg_match('#\d+$#', $this->path, $matches)) return $matches[0];
		
		// .. or the route will be an action preceeded by an id
		$pattern = '#(\d+)/('.implode('|', $this->actions).')$#i';
		if (preg_match($pattern, $this->path, $matches)) return $matches[1];
		
		// There's no id
		return false;
		
	}
	
}