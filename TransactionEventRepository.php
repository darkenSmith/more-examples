<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\TransactionEvent;
use App\Repositories\Contracts\TransactionRepositoryContract;
use Cybertill\Framework\Repositories\BaseEloquentRepository;
use Exception;


class TransactionEventRepository extends BaseEloquentRepository implements TransactionRepositoryContract
{
    /**
     * @param TransactionEvent $model
     */
    public function __construct(TransactionEvent $model)
    {
        parent::__construct($model);
    }

    /**
     * @param array $eventIds
     * @return void
     * @throws Exception
     */
    public function bulkDeleteById(array $eventIds): void
    {
        $this->model->newModelQuery()->whereIn('transaction_event_id', $eventIds)->delete();
    }

    public function getTransactionByRef(string $ref): ?Transaction
    {
        return null;
    }
}
