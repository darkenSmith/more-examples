<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\InitialiseLocationImportRequest;
use App\Services\HealthCheckService;
use App\Services\LocationService;
use Aws\Sqs\Exception\SqsException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncServiceController
{
    /**
     * @var LocationService
     */
    protected $locationService;

    public function __construct(
        LocationService  $locationService,
        HealthCheckService $healthCheckService
    ) {
        $this->locationService = $locationService;
        $this->healthCheckService = $healthCheckService;
    }

    public function initialise(InitialiseLocationImportRequest $request): JsonResponse
    {
        try {
            $locations = $this->locationService->populateLocationSyncQueue(
                $request->get('queue_name'),
                $request->get('per_page')
            );

        } catch (SqsException $e) {
            return response()->json(
                ['error' => $e->getAwsErrorMessage()],
                $e->getStatusCode()
            );
        } catch (\Exception $e) {
            return response()->json(
                ['error' => $e->getMessage()],
                400
            );
        } catch (GuzzleException $e) {
            return response()->json(
                ['error' => $e->getMessage()],
                $e->getCode()
            );
        }
        return response()->json($locations->appends($request->query()));
    }

    /**
     * @param Request $request
     * @return string
     */
    public function healthCheck(Request $request)
    {
        try {
            $this->healthCheckService->checkDBConnection( 'retailer_non_prefix');
            $this->healthCheckService->checkDBConnection( 'retailer');
            return 'true';
        } catch (\Exception $e) {
            return 'false';
        }
    }
}
