<?php

declare(strict_types=1);

namespace Packyderm\Frontend;

use Exception;
use Grpc\ChannelCredentials;
use Packyderm\Grpc\BuildImageReq;
use Packyderm\Grpc\ConfigFileReq;
use Packyderm\Grpc\ImageBuilderClient;
use const Grpc\STATUS_OK;

final class ImageBuilder
{
    private ImageBuilderClient $imageBuilderClient;

    public function __construct()
    {
        $this->imageBuilderClient = new ImageBuilderClient('localhost:3229', [
            'credentials' => ChannelCredentials::createInsecure(),
        ]);
    }

    public function fetchConfigFile() : string
    {
        [$response, $status] = $this->imageBuilderClient->FetchConfigFile((new ConfigFileReq))->wait();
        if ($status->code !== STATUS_OK) {
            throw new Exception('An error occurred' . json_encode($status));
        }

        return $response->getContents();
    }

    public function buildImage(string $dockerfile) : void
    {
        [$_, $status] = $this->imageBuilderClient->BuildImage((new BuildImageReq)->setDockerfile($dockerfile))->wait();
        if ($status->code !== STATUS_OK) {
            throw new Exception('An error occurred' . json_encode($status));
        }
    }
}
