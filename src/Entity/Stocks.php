<?php
//src/Entity/Stocks.php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="stocks")
 */
class Stocks {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $stock_id;

    /**
     * @ORM\ManyToOne(targetEntity="Stores", inversedBy="stocks")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="store_id", nullable=true)
     */
    private ?Stores $store;

    /**
     * @ORM\ManyToOne(targetEntity="Products")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id", nullable=true)
     */
    private ?Products $product;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $quantity;

    // Getters et setters pour chaque propriété

    /**
     * Get the value of stock_id
     *
     * @return int
     */
    public function getStockId(): int {
        return $this->stock_id;
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
     * Get the value of product
     *
     * @return ?Products
     */
    public function getProduct(): ?Products {
        return $this->product;
    }

    /**
     * Set the value of product
     *
     * @param ?Products $product
     * @return self
     */
    public function setProduct(?Products $product): self {
        $this->product = $product;
        return $this;
    }

    /**
     * Get the value of quantity
     *
     * @return ?int
     */
    public function getQuantity(): ?int {
        return $this->quantity;
    }

    /**
     * Set the value of quantity
     *
     * @param ?int $quantity
     * @return self
     */
    public function setQuantity(?int $quantity): self {
        $this->quantity = $quantity;
        return $this;
    }
}
?>