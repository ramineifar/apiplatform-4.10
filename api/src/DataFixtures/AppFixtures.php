<?php

namespace App\DataFixtures;

use App\Entity\Employee;
use App\Entity\LeaveRequest;
use App\Enum\EmployeeRoles;
use App\Enum\LeaveRequestStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $faker;
    private $manager;
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    )
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->generateEmployees();
        $this->generateLeaveRequests();

        $manager->flush();
    }

    private function generateEmployees(): void
    {
        for ($i = 0; $i < 6; $i++) {

            $employee = new Employee();
            $employee->setName($this->faker->name())
                ->setSalary($this->faker->numberBetween(1000, 5000))
                ->setEmail(preg_replace('/[^a-zA-Z0-9_.]/', '', strtolower($employee->getName())).'@gmail.com')
            ;
            $employee->setPassword($this->passwordHasher->hashPassword(
                $employee,
                $employee->getName()
            ));
            $this->addReference('employee' . $i, $employee);
            $this->manager->persist($employee);
        }

        $employeeManager = new Employee();
        $employeeManager->setName('Manager')
            ->setRoles([EmployeeRoles::ROLE_MANAGER->value])
            ->setSalary(7000)
            ->setEmail('manager@gmail.com');
        $employeeManager->setPassword($this->passwordHasher->hashPassword(
            $employeeManager,
            $employeeManager->getName()
        ));
        $this->addReference('employeeManager', $employeeManager);
        $this->manager->persist($employeeManager);

        $employeeAdmin = new Employee();
        $employeeAdmin->setName('Admin')
            ->setRoles([EmployeeRoles::ROLE_ADMIN->value])
            ->setSalary(11000)
            ->setEmail('admin@gmail.com');
        $employeeAdmin->setPassword($this->passwordHasher->hashPassword(
            $employeeAdmin,
            $employeeAdmin->getName()
        ));
        $this->addReference('employeeAdmin', $employeeAdmin);
        $this->manager->persist($employeeAdmin);
    }

    private function generateLeaveRequests(): void
    {
        for ($i = 0; $i < 6; $i++) {
            for ($j = 0; $j < 3; $j++) {
                $leaveRequest = $this->generateLeaveRequest($this->getReference('employee' . $i));
                $this->manager->persist($leaveRequest);
            }
        }
        $leaveRequestManager = $this->generateLeaveRequest($this->getReference('employeeManager'));
        $this->manager->persist($leaveRequestManager);

        $leaveRequestAdmin = $this->generateLeaveRequest($this->getReference('employeeAdmin'));
        $this->manager->persist($leaveRequestAdmin);

    }
    private function generateLeaveRequest(Employee $employee): LeaveRequest
    {
        $startDate = $this->faker->dateTimeBetween(random_int(-3,3) . ' months', '3 months');
        $endDate = new \DateTime($startDate->format('Y-m-d H:i:s'));
        $endDate->add(new \DateInterval('P' . random_int(1,10) . 'D'));
        $leaveRequest = new LeaveRequest();
        $leaveRequest->setEmployee($employee)
            ->setStartDate($startDate)
            ->setStatus(LeaveRequestStatus::randomLeaveRequest()->value);
        $leaveRequest->setEndDate($endDate);
        return $leaveRequest;
    }
}
