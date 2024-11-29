<?php

namespace App\Services;

use App\Http\Resources\DefaultSettingResource;
use App\Http\Resources\LocationResourceCollection;
use App\Models\CTModels\DefaultSetting;
use App\Repositories\DefaultSettingApiRepository;
use App\Repositories\SnsRepository;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use App\Repositories\LocationRepository;
use App\Repositories\Contracts\LocationRepositoryContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class LocationService
{
    /**
     * @var LocationRepository
     */
    private $locationRepository;

    /**
     * LocationServiceTest constructor.
     * @param LocationRepositoryContract $locationRepository
     * @param DefaultSettingApiRepository $defaultSettingsRepository
     */
    public function __construct(LocationRepositoryContract $locationRepository, DefaultSettingApiRepository $defaultSettingsRepository)
    {
        $this->locationRepository = $locationRepository;
        $this->defaultSettingsRepository = $defaultSettingsRepository;
    }

    /**
     * @throws GuzzleException
     */
    public function populateLocationSyncQueue(string $queueName, int $perPage = null)
    {
        $user = Auth::user();

        if (!$perPage || $perPage > 10) {
            $perPage = 10;
        }

        $locations = DB::connection('retailer_non_prefix')
            ->table('data_location')
            ->paginate($perPage);

        /**
         * Generates an array of location ids in the format:
         * [id => [], ...]
         */
        $formattedLocationIds = collect($locations->toArray()["data"])->mapWithKeys(function ($item) {
            $defaultVat = new DefaultSettingResource($this->defaultSettingsRepository->find('vat_number'));
            $defaultVat = (json_encode(json_decode($defaultVat->value), true));
            $item->ctDataType = 'data_location';
            $item->vat_number = ($item->vat_number) ?  $item->vat_number : $defaultVat;
            return [$item->id => $item];
        })->toArray();

        $key = config('services.aws.key');
        $secret = config('services.aws.secret');
        $region = config('services.aws.region');
        $topic = config('services.aws.topic');

        $snsRepo = new SnsRepository($key, $secret, $region);

        $data = [
            'job' => 'locationImportJob',
            'data' => [
                'retailerDbName' => $user->DbName,
                'ctNumber' => $user->retailer->sub_domain,
                'user' => $user->name,
                'locations' => $formattedLocationIds
            ]
        ];
        $snsRepo->sendMessage(json_encode($data), $topic);

        return $locations;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getCurrentUserLocation()
    {
        $user = Auth::user();
        $userLocation = $this->locationRepository->find($user->location_id);
        if ($userLocation) {
            return $userLocation;
        } else {
            abort(403, 'Location does NOT exist for the user : ' . $user->location_id);
        }
    }

    public function getAllLocations(): Collection
    {
        return $this->locationRepository->get();
    }

}
