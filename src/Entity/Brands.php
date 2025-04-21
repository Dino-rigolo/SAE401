<?php
//src/Entity/Brands.php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Brands Entity
 * 
 * Represents a bicycle brand in the system
 * Contains brand information and manages product relationships
 * 
 * @ORM\Entity
 * @ORM\Table(name="brands")
 * 
 * @package BikeStore\Entity
 * @version 1.0
 */
class Brands {
    /**
     * Unique identifier for the brand
     * 
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @var int
     */
    private int $brand_id;

    /**
     * Name of the brand
     * 
     * @ORM\Column(type="string")
     * @var string
     */
    private string $brand_name;

    /**
     * Collection of products associated with this brand
     * 
     * @ORM\OneToMany(targetEntity="Products", mappedBy="brand")
     * @var Collection<Products>
     */
    private Collection $products;

    /**
     * Constructor
     * Initializes products collection
     */
    public function __construct() {
        $this->products = new ArrayCollection();
    }

    /**
     * Get the brand ID
     * 
     * @return int The brand's unique identifier
     */
    public function getBrandId(): int {
        return $this->brand_id;
    }

    /**
     * Set the brand ID
     * 
     * @param int $brand_id The brand's unique identifier
     * @return self Returns this instance for method chaining
     */
    public function setBrandId(int $brand_id): self {
        $this->brand_id = $brand_id;
        return $this;
    }

    /**
     * Get the brand name
     * 
     * @return string The name of the brand
     */
    public function getBrandName(): string {
        return $this->brand_name;
    }

    /**
     * Set the brand name
     * 
     * @param string $brand_name The name of the brand
     * @return self Returns this instance for method chaining
     */
    public function setBrandName(string $brand_name): self {
        $this->brand_name = $brand_name;
        return $this;
    }

    /**
     * Get all products for this brand
     * 
     * @return Collection<Products> Collection of associated products
     */
    public function getProducts(): Collection {
        return $this->products;
    }

    /**
     * Add a product to this brand
     * 
     * @param Products $product The product to add
     * @return self Returns this instance for method chaining
     */
    public function addProduct(Products $product): self {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setBrand($this);
        }
        return $this;
    }

    /**
     * Remove a product from this brand
     * 
     * @param Products $product The product to remove
     * @return self Returns this instance for method chaining
     */
    public function removeProduct(Products $product): self {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            if ($product->getBrand() === $this) {
                $product->setBrand(null);
            }
        }
        return $this;
    }

    /**
     * Convert brand to string representation
     * 
     * @return string HTML formatted string with brand ID and name
     */
    public function __toString(): string {
        return "<p>{$this->brand_id} - {$this->brand_name}</p>";
    }
}
?>
