<?php
//src/Entity/Employees.php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="employees")
 */
class Employees {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $employee_id;

    /**
     * @ORM\ManyToOne(targetEntity="Stores", inversedBy="employees")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="store_id", nullable=true)
     */
    private ?Stores $store;

    /**
     * @ORM\Column(type="string")
     */
    private string $employee_name;

    /**
     * @ORM\Column(type="string")
     */
    private string $employee_email;

    /**
     * @ORM\Column(type="string")
     */
    private string $employee_password;

    /**
     * @ORM\Column(type="string")
     */
    private string $employee_role;

    // Getters et setters pour chaque propriété

    /**
     * Get the value of employee_id
     *
     * @return int
     */
    public function getEmployeeId(): int {
        return $this->employee_id;
    }

    /**
     * Get the value of store
     *
     * @return ?Stores
     */
    public function getStore(): ?Stores {
        return $this->store;
    }

    /**
     * Set the value of store
     *
     * @param ?Stores $store
     * @return self
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