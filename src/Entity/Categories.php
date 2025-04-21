<?php
//src/Entity/Categories.php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Categories Entity
 * 
 * Represents a product category in the BikeStore system
 * Manages category information and product relationships
 * 
 * @ORM\Entity
 * @ORM\Table(name="categories")
 * 
 * @package BikeStore\Entity
 * @author Your Name
 * @version 1.0
 */
class Categories {
    /**
     * Unique identifier for the category
     * 
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @var int Category ID
     */
    private int $category_id;

    /**
     * Name of the category
     * 
     * @ORM\Column(type="string")
     * @var string Category name
     */
    private string $category_name;

    /**
     * Collection of products in this category
     * 
     * @ORM\OneToMany(targetEntity="Products", mappedBy="category")
     * @var Collection<Products> Collection of associated products
     */
    private Collection $products;

    /**
     * Constructor
     * Initializes the products collection
     */
    public function __construct() {
        $this->products = new ArrayCollection();
    }

    /**
     * Get the category ID
     * 
     * @return int The category's unique identifier
     */
    public function getCategoryId(): int {
        return $this->category_id;
    }

    /**
     * Set the category ID
     * 
     * @param int $category_id The category's unique identifier
     * @return self Returns this instance for method chaining
     */
    public function setCategoryId(int $category_id): self {
        $this->category_id = $category_id;
        return $this;
    }

    /**
     * Get the category name
     * 
     * @return string The name of the category
     */
    public function getCategoryName(): string {
        return $this->category_name;
    }

    /**
     * Set the category name
     * 
     * @param string $category_name The name of the category
     * @return self Returns this instance for method chaining
     */
    public function setCategoryName(string $category_name): self {
        $this->category_name = $category_name;
        return $this;
    }

    /**
     * Get the collection of products in this category
     * 
     * @return Collection<Products> Collection of associated products
     */
    public function getProducts(): Collection {
        return $this->products;
    }

    /**
     * Add a product to this category
     * Updates the bidirectional relationship
     * 
     * @param Products $product The product to add
     * @return self Returns this instance for method chaining
     */
    public function addProduct(Products $product): self {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setCategory($this);
        }
        return $this;
    }

    /**
     * Remove a product from this category
     * Updates the bidirectional relationship
     * 
     * @param Products $product The product to remove
     * @return self Returns this instance for method chaining
     */
    public function removeProduct(Products $product): self {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            if ($product->getCategory() === $this) {
                $product->setCategory(null);
            }
        }
        return $this;
    }
}
?>
