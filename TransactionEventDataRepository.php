<?php

namespace App\Repositories;

use App\Models\TransactionEvent;

class TransactionEventDataRepository
{
    private $dbConnection;

    public function __construct(string $host, string $database, string $username, string $password, int $port){
        $this->dbConnection = new \mysqli($host, $username, $password, $database, $port);
    }

    /**
     * @param int $limit
     * @return array
     */
    private function getAllRaw(int $limit): array
    {
        $stmt = $this->dbConnection->prepare("SELECT * FROM tr_transaction_events LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $output = [];
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $output[] = $row;
            }
        }
        return $output;
    }

    /**
     * @param int $limit
     * @return array
     */
    public function getAll(int $limit): array
    {
        $result = $this->getAllRaw($limit);
        $output = [];
        if(count($result) > 0){
            foreach($result as $event){
                $output[] = new TransactionEvent($event);
            }
        }
        return $output;
    }

    public function deleteEvents(array $ids): bool
    {
        try {
            foreach($ids as $id)
            {
                $stmt = $this->dbConnection->prepare("DELETE FROM tr_transaction_events WHERE transaction_event_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
            }
            return true;
        }catch (\Exception $e)
        {
            return false;
        }
    }
}
