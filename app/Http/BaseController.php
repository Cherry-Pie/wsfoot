<?php

namespace App\Http;

use App\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class BaseController
{

	protected $request;

	public function __construct($request)
    {
        $this->request = $request;
    } // end __construct

    public function init()
    {
    } // end init

    protected function render($template, $data = [])
    {
        $view = new Template($template, $data);

        return new Response($view->fetch());
    } // end render

    protected function json($data = [])
    {
        return new JsonResponse($data);
    } // end json

}
