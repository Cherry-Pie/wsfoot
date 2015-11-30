<?php

namespace App\Http;

use App\Template;
use App\Repositories\TicketsRepository;

class MainController extends BaseController
{
    private $tickets;    

    public function init()
    {
        $this->tickets = new TicketsRepository();
    } // end init

    public function showMainPage()
    {
        $idUser = session_id();
        $sections = $this->tickets->getSections();
        $availableCount = $this->tickets->getAvailableSeatsCount();
        
        return $this->render('main', compact('idUser', 'sections', 'availableCount'));
    } // end showMainPage

    public function getSectionInfo()
    {
        $tickets = $this->tickets->getTicketsFromSection($this->request->get('section'));
        $availableCount = count(array_filter($tickets, function($ticketInfo) {
            return $ticketInfo['status'] == 'available';
        }));

        $view = new Template('modal_section', compact('tickets'));

        return $this->json([
            'html' => $view->fetch(),
            'count' => $availableCount
        ]);
    } // end getSectionInfo

}


