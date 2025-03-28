<?php

namespace App\Service\Api;

use App\Entity\Reservation;
use DateMalformedStringException;
use DateTimeImmutable;
use Exception;
use HttpException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ReservationApiService
{
    private HttpClientInterface $httpClient;
    private string $apiUrl;
    private string $apiUser;
    private string $apiPwd;
    private LoggerInterface $logger;
    private string $csvDelimiter;
    private string $header = 'Localizador;Huésped;fecha de entrada;fecha de salida;Hotel;Precio;Posibles Acciones';


    public function __construct(HttpClientInterface $httpClient, string $apiUrl, string $apiUser, string $apiPwd, LoggerInterface $logger, string $csvDelimiter = ';')
    {
        $this->httpClient = $httpClient;
        $this->apiUrl = $apiUrl;
        $this->apiUser = $apiUser;
        $this->apiPwd = $apiPwd;
        $this->logger = $logger;
        $this->csvDelimiter = $csvDelimiter;
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws HttpException
     * @throws DateMalformedStringException
     */public function getReservations(): array
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                $this->apiUrl,
                ['auth_basic' => [$this->apiUser, $this->apiPwd]]
            );

            if ($response->getStatusCode() !== 200) {
                throw new HttpException($response->getStatusCode(), 'Failed to fetch reservations');
            }

            $csvContent = $response->getContent();
            $lines = explode("\n", trim($csvContent));

            if (empty($lines)) {
                throw new RuntimeException('No data found in the CSV response.');
            }
            $header = str_getcsv($this->header, $this->csvDelimiter);
            $reservations = [];

            foreach ($lines as $line) {
                $data = str_getcsv($line, $this->csvDelimiter);
                if (count($data) === count($header)) {
                    $reservationData = array_combine($header, $data);

                    $reservations[] = $this->mapReservation($reservationData);
                }
            }

            return $reservations;

        } catch (Exception $e) {
            $this->logger->error('Error fetching reservations: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @param array $reservationData
     * @return Reservation
     * @throws DateMalformedStringException
     */
    private function mapReservation(array $reservationData): Reservation
    {
        return new Reservation([
            'locator' => $reservationData['Localizador'],
            'guest' => $reservationData['Huésped'],
            'checkin' => $reservationData['fecha de entrada'],
            'checkout' => $reservationData['fecha de salida'],
            'hotel' => $reservationData['Hotel'],
            'price' => (float)$reservationData['Precio'],
            'actions' => $reservationData['Posibles Acciones'] ?? null,
        ]);
    }

    public function filterReservations(array $reservations, ?string $searchTerm): array
    {
        if (empty($searchTerm)) {
            return $reservations;
        }

        $searchTerm = trim($searchTerm);

        return array_filter($reservations, function ($reservation) use ($searchTerm) {
            return stripos($reservation->getGuest(), $searchTerm) !== false
                || stripos($reservation->getHotel(), $searchTerm) !== false
                || stripos($reservation->getLocator(), $searchTerm) !== false
                || stripos($reservation->getCheckin()->format('d/m/Y'), $searchTerm) !== false
                || stripos($reservation->getCheckout()->format('d/m/Y'), $searchTerm) !== false
                || stripos($reservation->getPrice(), $searchTerm) !== false
                || stripos($reservation->getActions(), $searchTerm) !== false;
        });
    }
}