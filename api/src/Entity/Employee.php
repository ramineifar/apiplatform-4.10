<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Enum\EmployeeRoles;
use App\Repository\EmployeeRepository;
use App\Validator\Constraints\RolesEmployee as AssertRolesEmployee;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'employee')]
#[ORM\Index(columns: ['email'], name: 'email_index')]
#[ApiResource(mercure: true)]
#[Get(security: "is_granted('ROLE_MANAGER') or object == user")]
#[Post(security: "is_granted('ROLE_MANAGER')")]
#[GetCollection(security: "is_granted('ROLE_MANAGER')")]
#[Patch(security: "is_granted('ROLE_MANAGER') or object == user")]
#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
#[UniqueEntity(fields: 'email', message: 'This email is already taken.')]
class Employee implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $name;
    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[ApiProperty(security: "is_granted('ROLE_ADMIN')")]
    private string $email;
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[ApiProperty(security: "object.owner == user")]
    private string $password;
    #[ORM\Column]
    #[Assert\NotBlank]
    private int $salary;

    #[ORM\Column(type: Types::JSON)]
    #[Assert\NotBlank]
    #[AssertRolesEmployee]
    private array $roles;

    /**
     * @var Collection<int, LeaveRequest>
     */
    #[ORM\OneToMany(mappedBy: 'employee', targetEntity: LeaveRequest::class)]
    #[ApiProperty(fetchEager: true)]
    private Collection $leaveRequests;

    public function __construct()
    {
        $this->leaveRequests = new ArrayCollection();
        $this->roles = array_unique(array_merge($this->roles ?? [], [EmployeeRoles::ROLE_EMPLOYEE->value]));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getSalary(): int
    {
        return $this->salary;
    }

    public function setSalary(int $salary): static
    {
        $this->salary = $salary;
        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    /**
     * @return Collection<int, LeaveRequest>
     */
    public function getLeaveRequests(): Collection
    {
        return $this->leaveRequests;
    }

    public function addLeaveRequest(LeaveRequest $leaveRequest): static
    {
        if (!$this->leaveRequests->contains($leaveRequest)) {
            $this->leaveRequests->add($leaveRequest);
            $leaveRequest->setEmployee($this);
        }

        return $this;
    }

    public function removeLeaveRequest(LeaveRequest $leaveRequest): static
    {
        if ($this->leaveRequests->removeElement($leaveRequest)) {
            // set the owning side to null (unless already changed)
            if ($leaveRequest->getEmployee() === $this) {
                $leaveRequest->setEmployee(null);
            }
        }

        return $this;
    }
}
