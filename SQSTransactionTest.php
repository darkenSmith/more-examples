<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\AuthenticateSystemUser;
use Tests\TestCase;

class SQSTransactionTest extends TestCase
{

    use AuthenticateSystemUser;

    protected function setUp()
    {
        parent::setUp();
        $this->setupAuthenticateSystemUser();
    }

    /** @test */
    public function data()
    {
        $token = base64_encode(':' . Auth::user()->getRememberToken());

        dump($token);

        $response = $this->getJson('/api/transactions/initialise?queue_name=devTransactionQueue&per_page=1',
            [
                'Authorization' => 'BASIC ' . $token,
                'Accept' => 'application/json',
            ]);

         dump($response->getContent());
    }

}

