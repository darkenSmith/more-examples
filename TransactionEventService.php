<?php

namespace App\Services;

use App\Repositories\TransactionEventRepository;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class TransactionEventService
{
    /**
     * @var TransactionEventRepository
     */
    private $transactionEventRepository;

    /**
     * @param TransactionEventRepository $transactionEventRepository
     */
    public function __construct(TransactionEventRepository $transactionEventRepository)
    {
        $this->transactionEventRepository = $transactionEventRepository;
    }

    /**
     * get all existing product events from db up to a limit
     * @param string|null $databaseName
     * @param string|null $user
     * @param string|null $password
     * @param int|null $limit
     * @return Collection
     */
    public function getAllTransactionEvents(string $databaseName = null, string $user = null, string $password = null, int $limit = null): Collection
    {
        if($databaseName && $user && $password){
            config(['database.connections.retailer_non_prefix.database' => $databaseName]);
            config(['database.connections.retailer_non_prefix.username' => $user]);
            config(['database.connections.retailer_non_prefix.password' => $password]);
        }
        if ($limit !== null) {
            return $this->transactionEventRepository->all()->take($limit);
        } else {
            return $this->transactionEventRepository->all();
        }
    }

    /**
     * delete product events by product event id
     * @param array $eventIds
     * @return void
     * @throws Exception
     */
    public function deleteTransactionEventsByIds(array $eventIds): void
    {
        if (count($eventIds) > 0) {
            $this->transactionEventRepository->bulkDeleteById($eventIds);
        }
    }
}
