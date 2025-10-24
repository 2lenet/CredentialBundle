<?php

namespace Lle\CredentialBundle\Service;

use Lle\CredentialBundle\Exception\ProjectNotFoundException;
use Lle\CredentialBundle\Exception\ProjectAlreadyInitializedException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClientService
{
    private string $clientUrl;
    private string $projectCode;

    public function __construct(
        protected ParameterBagInterface $parameterBag,
        protected HttpClientInterface   $client,
    )
    {
        $this->clientUrl = $this->parameterBag->get('lle_credential.client_url');
        $this->projectCode = $this->parameterBag->get('lle_credential.project_code');
    }

    public function pull(): array
    {
        $response = $this->client->request(
            'GET',
            $this->clientUrl . '/api/credential/pull/' .$this->projectCode
        );

        if ($response->getStatusCode() === Response::HTTP_NOT_FOUND){
            throw new ProjectNotFoundException($response->getContent());
        }

        return json_decode($response->getContent());
    }

    public function warmup(array $credentials): void
    {
        $response = $this->client->request(
            'POST',
            $this->clientUrl . '/api/credential/warmup/' . $this->projectCode,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($credentials)
            ]
        );

        if ($response->getStatusCode() === Response::HTTP_NOT_FOUND){
            throw new ProjectNotFoundException($response->getContent());
        }
    }

    public function init(array $data): void
    {
        $response = $this->client->request(
            'POST',
            $this->clientUrl . '/api/credential/init' . $this->projectCode,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode(
                    $data,
                    JSON_PRETTY_PRINT
                )
            ]
        );

        if ($response->getStatusCode() === Response::HTTP_NOT_FOUND) {
            throw new ProjectNotFoundException($response->getContent());
        }

        if ($response->getStatusCode() === Response::HTTP_BAD_REQUEST) {
            throw new ProjectAlreadyInitializedException($response->getContent());
        }
    }
}
