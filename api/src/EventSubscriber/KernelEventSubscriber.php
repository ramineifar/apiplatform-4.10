<?php

namespace App\EventSubscriber;

use App\Entity\LeaveRequest;
use App\Enum\LeaveRequestStatus;
use App\Repository\LeaveRequestRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class KernelEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly LeaveRequestRepository $leaveRequestRepository,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if ($request->get('_api_resource_class') === LeaveRequest::class) {
            if (in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PATCH], true)) {
                $allLeaveRequest = $this->leaveRequestRepository->findBy([
                    'employe' => $this->tokenStorage->getToken()->getUser(),
                    'status' => LeaveRequestStatus::APPROVED->value
                ]);

                if ($request->getMethod() === Request::METHOD_PATCH) {
                    $idLeaveRequest = (int) $request->get('id');
                    $allLeaveRequest = array_filter(
                        $allLeaveRequest,
                        static function ($leaveRequest) use ($idLeaveRequest) {
                            return $leaveRequest->getId() !== $idLeaveRequest;
                        });
                }
                $countDaysApproved = 0;
                if (empty($allLeaveRequest)) {
                    return;
                }
                foreach ($allLeaveRequest as $leaveRequest) {
                    $date1 = $leaveRequest->getStartDate();
                    $date2 = $leaveRequest->getEndDate();
                    $countDaysApproved += $date1->diff($date2)->days;
                }
                $requestContent = json_decode($request->getContent(), false);
                if ($requestContent->status !== LeaveRequestStatus::REJECTED) {
                    $date1 = new \DateTime($requestContent->startDate);
                    $date2 = new \DateTime($requestContent->endDate);
                    $countDaysApproved += $date1->diff($date2)->days;
                }
                if ($countDaysApproved > 30) {
                    throw new \Exception('Vous avez dépassé la limite de 30 jours par an !');
                }
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
