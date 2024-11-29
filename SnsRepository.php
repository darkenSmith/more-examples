<?php

namespace App\Repositories;

use Aws\Exception\AwsException;
use Aws\Result;
use Aws\Sns\SnsClient;

class SnsRepository
{
    private $snsClient;

    /**
     * @param string $accessKey
     * @param string $secretAccessKey
     * @param string $region
     * @param string|null $profile
     */
    function __construct(
        string $accessKey,
        string $secretAccessKey,
        string $region = 'eu-west-2',
        ?string $profile = null
    ) {
        $config = [
            'region' => $region,
            'version' => '2010-03-31',
            'credentials' => [
                'key' => $accessKey,
                'secret' => $secretAccessKey
            ]
        ];

        if ($profile !== null) {
            $config['profile'] = $profile;
        }

        $this->snsClient = new SnsClient($config);
    }

    /**
     * @param string $message The stringified JSON object to be passed to SNS
     * @param string $topic The SNS topic to publish to
     * @return Result
     * @throws AwsException
     */
    public function sendMessage(string $message, string $topic): Result
    {
        return $this->snsClient->publish([
            'Message' => $message,
            'TopicArn' => $topic
        ]);
    }
}
