<?php
//src/Entity/Employees.php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Employees Entity
 * 
 * Represents an employee in the BikeStore system
 * Manages employee information and store relationships
 * 
 * @ORM\Entity
 * @ORM\Table(name="employees")
 * 
 * @package BikeStore\Entity
 * @author Your Name
 * @version 1.0
 */
class Employees {
    /**
     * Unique identifier for the employee
     * 
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @var int Employee ID
     */
    private int $employee_id;

    /**
     * Store where the employee works
     * Nullable for employees not assigned to a specific store
     * 
     * @ORM\ManyToOne(targetEntity="Stores", inversedBy="employees")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="store_id", nullable=true)
     * 
     * @var ?Stores Associated store or null
     */
    private ?Stores $store;

    /**
     * Full name of the employee
     * 
     * @ORM\Column(type="string")
     * @var string Employee's full name
     */
    private string $employee_name;

    /**
     * Email address of the employee
     * Used for authentication and communication
     * 
     * @ORM\Column(type="string")
     * @var string Employee's email address
     */
    private string $employee_email;

    /**
     * Hashed password for employee authentication
     * 
     * @ORM\Column(type="string")
     * @var string Employee's hashed password
     */
    private string $employee_password;

    /**
     * Role of the employee in the system
     * Possible values: employee, chief, it
     * 
     * @ORM\Column(type="string")
     * @var string Employee's role
     */
    private string $employee_role;

    /**
     * Get the employee's unique identifier
     * 
     * @return int The employee's ID
     */
    public function getEmployeeId(): int {
        return $this->employee_id;
    }

    /**
     * Get the store where the employee works
     * 
     * @return ?Stores The associated store or null if not assigned
     */
    public function getStore(): ?Stores {
        return $this->store;
    }

    /**
     * Set the store for this employee
     * Updates the store assignment
     * 
     * @param ?Stores $store The store to assign or null to remove assignment
     * @return self Returns this instance for method chaining
     */
    public function setStore(?Stores $store): self {
        $this->store = $store;
        return $this;
    }

    /**
     * Get the value of employee_name
     *
     * @return string
     */
    public function getEmployeeName(): string {
        return $this->employee_name;
    }

    /**
     * Set the value of employee_name
     *
     * @param string $employee_name
     * @return self
     */
    public function setEmployeeName(string $employee_name): self {
        $this->employee_name = $employee_name;
        return $this;
    }

    /**
     * Get the value of employee_email
     *
     * @return string
     */
    public function getEmployeeEmail(): string {
        return $this->employee_email;
    }

    /**
     * Set the value of employee_email
     *
     * @param string $employee_email
     * @return self
     */
    public function setEmployeeEmail(string $employee_email): self {
        $this->employee_email = $employee_email;
        return $this;
    }

    /**
     * Get the value of employee_password
     *
     * @return string
     */
    public function getEmployeePassword(): string {
        return $this->employee_password;
    }

    /**
     * Set the value of employee_password
     *
     * @param string $employee_password
     * @return self
     */
    public function setEmployeePassword(string $employee_password): self {
        $this->employee_password = $employee_password;
        return $this;
    }

    /**
     * Get the value of employee_role
     *
     * @return string
     */
    public function getEmployeeRole(): string {
        return $this->employee_role;
    }

    /**
     * Set the value of employee_role
     *
     * @param string $employee_role
     * @return self
     */
    public function setEmployeeRole(string $employee_role): self {
        $this->employee_role = $employee_role;
        return $this;
    }
}
?>