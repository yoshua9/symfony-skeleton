<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Reservation;
use App\Service\Api\ReservationApiService;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReservationControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();

        $mockService = $this->createMock(ReservationApiService::class);
        $mockService->method('getReservations')->willReturn([
            new Reservation([
                "locator" => "34549",
                "guest" => "Nombre 3",
                "checkin" => "2018-06-22",
                "checkout" => "2018-06-27",
                "hotel" => "Hotel 4",
                "price" => 1029.95,
                "actions" => "Cobrar Devolver"
            ])
        ]);
        $mockService->method('filterReservations')->willReturn([
            new Reservation([
                "locator" => "34549",
                "guest" => "Nombre 3",
                "checkin" => "2018-06-22",
                "checkout" => "2018-06-27",
                "hotel" => "Hotel 4",
                "price" => 1029.95,
                "actions" => "Cobrar Devolver"
            ])
        ]);
        $container = $this->client->getContainer();
        $container->set(ReservationApiService::class, $mockService);

    }

    public function testListAction(): void
    {
        $this->client->request('GET', '/reservations?search=34549');

        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('h1:contains("Reservations List")');

        $content = $this->client->getResponse()->getContent();
        $this->assertStringContainsString('34549', $content);
        $this->assertStringContainsString('Nombre 3', $content);
        $this->assertStringContainsString('Hotel 4', $content);
        $this->assertStringContainsString('Cobrar Devolver', $content);
        $this->assertStringContainsString('22/06/2018', $content);
        $this->assertStringContainsString('27/06/2018', $content);
        $this->assertStringContainsString('1029.95', $content);
    }

    public function testDownloadJsonAction(): void
    {
        $this->client->request('GET', '/download-reservation?search=34549');

        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $this->assertStringContainsString(
            'attachment; filename="reservations.json"',
            $this->client->getResponse()->headers->get('Content-Disposition')
        );

        $this->assertJson($this->client->getResponse()->getContent());
    }
}
