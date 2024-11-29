<?php

namespace App\Repositories;

use App\Models\LocationEvent;
use App\Repositories\Contracts\LocationRepositoryContract;
use Cybertill\Framework\Repositories\BaseEloquentRepository;
use Exception;


class LocationEventRepository extends BaseEloquentRepository implements LocationRepositoryContract
{
    /**
     * @param LocationEvent $model
     */
    public function __construct(LocationEvent $model)
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
        $this->model->newModelQuery()->whereIn('data_id', $eventIds)->delete();
    }
}
