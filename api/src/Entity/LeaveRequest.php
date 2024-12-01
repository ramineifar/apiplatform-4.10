<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Enum\LeaveRequestStatus;
use App\Repository\LeaveRequestRepository;
use App\State\EmployeesLeaveRequestsProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(mercure: true)]
#[Get(security: "is_granted('ROLE_MANAGER') or object == user")]
#[Post(security: "is_granted('ROLE_MANAGER') or object == user")]
#[GetCollection(security: "is_granted('ROLE_MANAGER')")]
#[Patch(security: "is_granted('ROLE_MANAGER') or object == user")]
#[ORM\Entity(repositoryClass: LeaveRequestRepository::class)]
#[ApiResource(
    uriTemplate: '/employees/{employeeId}/leave_requests',
    shortName: 'Leave Requests By Employee',
    operations: [ new GetCollection() ],
    uriVariables: [
        'employeeId' => new Link(toProperty: 'employee', fromClass: Employee::class),
    ]
)]
class LeaveRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    private \DateTimeInterface $startDate;
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    private \DateTimeInterface $endDate;
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $status;

    #[ORM\ManyToOne(inversedBy: 'leaveRequests')]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty(fetchEager: true)]
    private ?Employee $employee = null;

    public function __construct()
    {
        $this->setStatus(LeaveRequestStatus::PENDING->value);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): \DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getEmployee(): ?Employee
    {
        return $this->employee;
    }

    public function setEmployee(?Employee $employee): static
    {
        $this->employee = $employee;

        return $this;
    }
}
