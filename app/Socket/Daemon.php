<?php

namespace App\Socket;

use Yaro\Socket\Websocket\WebsocketDaemon;
use App\Repositories\TicketsRepository;


class Daemon extends WebsocketDaemon
{
    /*
    [
        'id-seat' => [
            'section' => 'id-section',
            'users' => [
                'id-session' => ''
            ],
        ],
        //...
    ]
    */
    private $tickets = [];
    
    protected function onMessage($connectionId, $data, $type)
    {
        if (!strlen($data)) {
            return;
        }

        $data = json_decode($data, true);
        
        $info = $data['info'];
        
        switch ($data['action']) {
            case 'get_in_process_seats':
                $this->sendInProcessSeats($info['section']);
                break;
            case 'seat_set':
                $this->onSeatSet($info);
                break;
            case 'seat_leave':
                $this->onSeatLeave($info);
                break;
            case 'seat_reserve':
                $this->onReserveSeat($info);
            
            default:
                break;
        }
    } // end onMessage
    
    private function onReserveSeat($info)
    {
        $ticket = new TicketsRepository();
        $status = $ticket->setOccupiedStatus($info['ident']);
        
        unset($this->tickets[$info['ident']]);
        
        $this->send([
            'action' => 'seat_occupied',
            'info' => [
                'success' => $status,
                'section' => $info['section'],
                'seat'    => $info['ident'],
            ]
        ]);
    } // end onReserveSeat
    
    private function send($data)
    {
        $message = json_encode($data);
        
        foreach ($this->clients as $idClient => $client) {
            $this->sendToClient($idClient, $message);
        }
    } // end send
    
    private function sendInProcessSeats($section)
    {
        $tickets = array_filter($this->tickets, function($ticketInfo) use($section) {
            return $ticketInfo['section'] == $section && !empty($ticketInfo['users']);
        });
        $ids = array_keys($tickets);
        
        $this->send([
            'action' => 'get_in_process_seats',
            'info' => [
                'ids' => $ids,
                'section' => $section
            ]
        ]);
    } // end sendInProcessSeats
    
    private function onSeatSet($info)
    {
        $this->tickets[$info['ident']] = [
            'section' => $info['section'],
            'users' => [
                $info['user'] => true
            ],
        ];
        
        $this->sendInProcessSeats($info['section']);
    } // end onSeatSet
    
    private function onSeatLeave($info)
    {
        $idTicket = $info['ident'];
        $idUser = $info['user'];
        unset($this->tickets[$idTicket]['users'][$idUser]);
        
        if (empty($this->tickets[$idTicket]['users'])) {
            $this->send([
                'action' => 'seat_available',
                'info' => [
                    'id' => $idTicket,
                    'section' => $info['section']
                ]
            ]);
        }
    } // end onSeatLeave
    
}

