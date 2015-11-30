<?php

namespace App\Repositories;

use App\DB;
use App\Config;

class TicketsRepository 
{
    private $db;    

    public function __construct()
    {
        $this->db = new DB(Config::get('database'));
    } // end __construct
    
    public function __destruct()
    {
        $this->db = null;
    } // end __destruct
    
    public function setOccupiedStatus($id)
    {
        // using default Read Committed isolation level is enough
        $this->db->beginTransaction();
        
        $count = $this->db->update(
            'UPDATE tickets SET status = "occupied" WHERE status = "available" AND id = ?',
            [$id]
        );
        $this->db->commit();
        
        return (bool)$count;
    } // end setOccupiedStatus

    public function getSections()
    {
        $result = $this->db->select('SELECT DISTINCT section FROM tickets ORDER BY section ASC');
        return array_column($result, 'section');
    } // end getSections

    public function getAvailableSeatsCount()
    {
        $result = $this->db->first('SELECT COUNT(*) as cnt FROM tickets WHERE status = "available"');
        
        return $result['cnt'];
    } // end getAvailableSeatsCount
    
    public function getTicketsFromSection($section)
    {
        return $this->db->select(
            'SELECT * FROM tickets WHERE section = ? ORDER BY tier, seat ASC', 
            [$section]
        );
    } // end getTicketsFromSection
}
