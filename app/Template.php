<?php

namespace App;

class Template
{
    
	private $data = [];
	private $path = '';

    public function __construct($template, $data = [])
    {
    	$this->path = app_path('templates/'. $template .'.html');
	    $this->data = $data;
    } // end __construct

    public function with($key, $value)
    {
    	$this->data[$key] = $value;

    	return $this;
    } // end with

    public function fetch()
    {
    	if (!file_exists($this->path)) {
    		throw new \Exception('Template view not found: '. $this->path);
    	}

    	extract($this->data);
	    ob_start();
	    require $this->path;

	    $html = ob_get_clean();

    	return $html;
    } // end fetch

}
