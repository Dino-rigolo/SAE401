<?php
//src/Entity/Stocks.php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Stocks Entity
 * 
 * Represents product inventory in stores
 * Manages stock levels and relationships between products and stores
 * 
 * @ORM\Entity
 * @ORM\Table(name="stocks")
 * 
 * @package BikeStore\Entity
 * @author Your Name
 * @version 1.0
 */
class Stocks {
    /**
     * Unique identifier for the stock entry
     * 
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @var int Stock entry ID
     */
    private int $stock_id;

    /**
     * Store associated with this stock entry
     * Nullable to handle cases where store is temporarily undefined
     * 
     * @ORM\ManyToOne(targetEntity="Stores", inversedBy="stocks")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="store_id", nullable=true)
     * 
     * @var ?Stores Associated store or null
     */
    private ?Stores $store;

    /**
     * Product being tracked in inventory
     * Nullable to handle cases where product is temporarily undefined
     * 
     * @ORM\ManyToOne(targetEntity="Products")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id", nullable=true)
     * 
     * @var ?Products Associated product or null
     */
    private ?Products $product;

    /**
     * Quantity of product in stock
     * Nullable to handle out-of-stock situations
     * 
     * @ORM\Column(type="integer", nullable=true)
     * @var ?int Current quantity in stock
     */
    private ?int $quantity;

    /**
     * Get the stock entry ID
     * 
     * @return int The unique identifier for this stock entry
     */
    public function getStockId(): int {
        return $this->stock_id;
    }

    /**
     * Get the associated store
     * 
     * @return ?Stores The store where this stock is located or null
     */
    public function getStore(): ?Stores {
        return $this->store;
    }

    /**
     * Set the store for this stock entry
     * 
     * @param ?Stores $store The store to associate or null
     * @return self Returns this instance for method chaining
     */
    public function setStore(?Stores $store): self {
        $this->store = $store;
        return $this;
    }

    /**
     * Get the product in stock
     * 
     * @return ?Products The product being tracked or null
     */
    public function getProduct(): ?Products {
        return $this->product;
    }

    /**
     * Set the product for this stock entry
     * 
     * @param ?Products $product The product to track or null
     * @return self Returns this instance for method chaining
     */
    public function setProduct(?Products $product): self {
        $this->product = $product;
        return $this;
    }

    /**
     * Get the current quantity in stock
     * 
     * @return ?int The current quantity or null if out of stock
     */
    public function getQuantity(): ?int {
        return $this->quantity;
    }

    /**
     * Set the quantity in stock
     * 
     * @param ?int $quantity The new quantity or null for out of stock
     * @return self Returns this instance for method chaining
     */
    public function setQuantity(?int $quantity): self {
        $this->quantity = $quantity;
        return $this;
    }
}
?>