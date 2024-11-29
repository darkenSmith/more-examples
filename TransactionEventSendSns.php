<?php

namespace App\Console\Commands;

use App\Models\Retailer;
use App\Models\TransactionEvent;
use App\Repositories\SnsRepository;
use App\Repositories\TransactionEventDataRepository;
use App\Services\TransactionEventService;
use Cybertill\Framework\Authentication\CTUserModel;
use Cybertill\Framework\Queue\CybertillDispatchable;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class TransactionEventSendSns extends Command
{
    use CybertillDispatchable;

    protected $signature = 'send:send-sns-messages';

    /**
     * @var TransactionEventService
     */
    private $eventService;

    /**
     * @var SnsRepository
     */
    private $snsRepository;

    /**
     * @return void
     * @throws Exception
     */
    public function handle
    (
    )
    {

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

            $eventRepo = new TransactionEventDataRepository($host, $dbName, $dbUserName, $dbPass, 3306);

            /**
             * @var TransactionEvent[] $allEvents
             */
            $allEvents = $eventRepo->getAll(10);

            if(count($allEvents) > 0){
                $changes = [];
                $eventIds = [];
                foreach($allEvents as $event){
                    if(!isset($changes[$event->sl_transaction_id])){
                        $changes[$event->sl_transaction_id] = [];
                    }
                    if(!in_array($event->change, $changes[$event->sl_transaction_id])){
                        $changes[$event->sl_transaction_id][] = $event->change;
                    }
                    $eventIds[] = $event->transaction_event_id;
                }
                $message = [
                    'job' => 'transactionImportJob',
                    'data' => [
                        'retailerDbName' => $dbName,
                        'ctNumber' => $subDomain,
                        'user' => $name,
                        'transactions' => $changes
                    ]
                ];

                $snsRepo->sendMessage(json_encode($message), $topic);
                $eventRepo->deleteEvents($eventIds);
            }
        }
    }

}
