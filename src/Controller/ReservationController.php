<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Api\ReservationApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ReservationController extends AbstractController
{
    private ReservationApiService $reservationApiService;

    public function __construct(ReservationApiService $reservationApiService)
    {
        $this->reservationApiService = $reservationApiService;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws \HttpException
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/reservations', name: 'reservation_list')]
    public function list(Request $request): Response
    {
        try {
            $reservations = $this->reservationApiService->getReservations();

            $searchTerm = $request->query->get('search');

            $reservationsFiltered = $this->reservationApiService->filterReservations($reservations, $searchTerm);

            return $this->render('reservation/list.html.twig', [
                'reservations' => $reservationsFiltered,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('reservation_list');
        }
    }

    #[Route('/download-reservation', name: 'reservation_download_json')]
    public function downloadJson(Request $request): Response
    {
        $reservations = $this->reservationApiService->getReservations();
        $searchTerm = $request->query->get('search');
        $reservationsFiltered = $this->reservationApiService->filterReservations($reservations, $searchTerm);
        $reservationsFiltered = array_values($reservationsFiltered);
        $result = $this->mapReservation($reservationsFiltered);

        $jsonContent = json_encode($result, JSON_PRETTY_PRINT);

        $response = new Response($jsonContent);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Content-Disposition', 'attachment; filename="reservations.json"');

        return $response;
    }

    private function mapReservation(array $reservations): array
    {
        return array_map(function ($reservation) {
            return [
                'locator' => $reservation->getLocator(),
                'guest' => $reservation->getGuest(),
                'checkin' => $reservation->getCheckin()->format('d/m/Y'),
                'checkout' => $reservation->getCheckout()->format('d/m/y'),
                'hotel' => $reservation->getHotel(),
                'price' => $reservation->getPrice(),
                'actions' => $reservation->getActions(),
            ];
        }, $reservations);
    }
}
