<?php

namespace App\Tests\Service;

use App\Entity\Reservation;
use App\Service\Api\ReservationApiService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class ReservationApiServiceTest extends KernelTestCase
{
    private ReservationApiService $service;

    protected function setUp(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse("34637;Nombre 1;2018-10-04;2018-10-05;Hotel 4;112.49;Cobrar Devolver"),
        ]);

        $logger = $this->createMock(LoggerInterface::class);

        $this->service = new ReservationApiService(
            $httpClient,
            'https://fake-api.com/reservas.csv',
            'test',
            'test',
            $logger
        );
    }

    public function testGetReservations(): void
    {
        self::bootKernel();

        /** @var ReservationApiService $service */

        $reservations = $this->service->getReservations();

        $this->assertIsArray($reservations);
        $this->assertNotEmpty($reservations);
        $this->assertInstanceOf(Reservation::class, $reservations[0]);
    }

    public function testFilterReservations(): void
    {
        $reservations = [
            new Reservation([
                "locator" => "34549",
                "guest" => "Nombre 3",
                "checkin" => "2018-06-22",
                "checkout" => "2018-06-27",
                "hotel" => "Hotel 4",
                "price" => 1029.95,
                "actions" => "Cobrar Devolver"
            ]),
            new Reservation([
                "locator" => "34550",
                "guest" => "Nombre 4",
                "checkin" => "2018-06-23",
                "checkout" => "2018-06-28",
                "hotel" => "Hotel 8",
                "price" => 2029.55,
                "actions" => "Cobrar Devolver"
            ])
        ];
        $filtered = $this->service->filterReservations($reservations, '34549');

        $this->assertCount(1, $filtered);
    }
}
