<?php

namespace App\Console\Commands;

use App\Models\LocationEvent;
use App\Models\Retailer;
use App\Repositories\LocationEventDataRepository;
use App\Repositories\SnsRepository;
use App\Services\LocationEventService;
use Cybertill\Framework\Authentication\CTUserModel;
use Cybertill\Framework\Queue\CybertillDispatchable;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class LocationEventSendSns extends Command
{
    use CybertillDispatchable;

    protected $signature = 'send:send-sns-messages';

    /**
     * @var LocationEventService
     */
    private $eventService;

    /**
     * @var SnsRepository
     */
    private $snsRepository;

    /**
     * @param LocationEventService $eventService
     * @return void
     * @throws Exception
     */
    public function handle
    (
        LocationEventService $eventService
    )
    {
        $this->eventService = $eventService;

        $retailers = Retailer::all()->where('status', '=', 1)->reverse();

        foreach ($retailers as $retailer) {
            $key = config('services.aws.key');
            $secret = config('services.aws.secret');
            $region = config('services.aws.region');
            $topic = config('services.aws.topic');

            $user = CTUserModel::getSystemUser($retailer->db_name);

            if($user == NULL)
                continue;

            Auth::login($user);

            $snsRepo = new SnsRepository($key, $secret, $region);

            $dbName = $retailer->db_name;
            $dbPass = $retailer->db_pass;
            $dbUserName = $retailer->db_user;
            $subDomain = $retailer->sub_domain;
            $host = $retailer->db_host;
            $name = $retailer->name;

            $eventRepo = new LocationEventDataRepository($host, $retailer->db_name, $retailer->db_user, $retailer->db_pass, 3306);

            /**
             * @var LocationEvent[] $all
             */
            $all = $eventRepo->getAll(10);

            if(count($all) > 0){
                $changes = [];
                foreach($all as $event){
                    $event['ctDataType'] = 'data_location';
                    if(!isset($changes[$event['id']])){
                        $changes[$event['id']] = $event;
                    }
                }
                $message = [
                    'job' => 'locationImportJob',
                    'data' => [
                        'retailerDbName' => $dbName,
                        'ctNumber' => $subDomain,
                        'user' => $name,
                        'locations' => $changes
                    ]
                ];

                $snsRepo->sendMessage(json_encode($message), $topic);
                foreach ($all as $event) {
                    $this->eventService->deleteLocationEventsByIds([$event['id']]);
                }
            }
        }
    }

}
