<?php
//src/Entity/Products.php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Products Entity
 * 
 * Represents a bicycle product in the BikeStore system
 * Manages product information and relationships with brands and categories
 * 
 * @ORM\Entity
 * @ORM\Table(name="products")
 * 
 * @package BikeStore\Entity
 * @author Your Name
 * @version 1.0
 */
class Products {
    /**
     * Unique identifier for the product
     * 
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @var int Product ID
     */
    private int $product_id;

    /**
     * Name of the product
     * 
     * @ORM\Column(type="string")
     * @var string Product name
     */
    private string $product_name;

    /**
     * Category of the product
     * Establishes Many-to-One relationship with Categories
     * 
     * @ORM\ManyToOne(targetEntity="Categories", inversedBy="products")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="category_id")
     * @var Categories Associated category
     */
    private Categories $category;

    /**
     * Brand of the product
     * Establishes Many-to-One relationship with Brands
     * 
     * @ORM\ManyToOne(targetEntity="Brands", inversedBy="products")
     * @ORM\JoinColumn(name="brand_id", referencedColumnName="brand_id")
     * @var Brands Associated brand
     */
    private Brands $brand;

    /**
     * Model year of the product
     * 
     * @ORM\Column(type="smallint")
     * @var int Year of the model
     */
    private int $model_year;

    /**
     * List price of the product
     * Stored as decimal with 2 decimal places
     * 
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @var string Price in decimal format
     */
    private string $list_price;

    /**
     * Get the product's unique identifier
     * 
     * @return int The product ID
     */
    public function getProductId(): int {
        return $this->product_id;
    }

    /**
     * Set the value of product_id
     *
     * @param int $product_id
     * @return self
     */
    public function setProductId(int $product_id): self {
        $this->product_id = $product_id;
        return $this;
    }

    /**
     * Get the value of product_name
     *
     * @return string
     */
    public function getProductName(): string {
        return $this->product_name;
    }

    /**
     * Set the value of product_name
     *
     * @param string $product_name
     * @return self
     */
    public function setProductName(string $product_name): self {
        $this->product_name = $product_name;
        return $this;
    }

    /**
     * Get the value of category
     *
     * @return Categories
     */
    public function getCategory(): Categories {
        return $this->category;
    }

    /**
     * Set the value of category
     *
     * @param Categories $category
     * @return self
     */
    public function setCategory(Categories $category): self {
        $this->category = $category;
        return $this;
    }

    /**
     * Get the value of brand
     *
     * @return Brands
     */
    public function getBrand(): Brands {
        return $this->brand;
    }

    /**
     * Set the value of brand
     *
     * @param Brands $brand
     * @return self
     */
    public function setBrand(Brands $brand): self {
        $this->brand = $brand;
        return $this;
    }

    /**
     * Get the value of model_year
     *
     * @return int
     */
    public function getModelYear(): int {
        return $this->model_year;
    }

    /**
     * Set the value of model_year
     *
     * @param int $model_year
     * @return self
     */
    public function setModelYear(int $model_year): self {
        $this->model_year = $model_year;
        return $this;
    }

    /**
     * Get the value of list_price
     *
     * @return string
     */
    public function getListPrice(): string {
        return $this->list_price;
    }

    /**
     * Set the value of list_price
     *
     * @param string $list_price
     * @return self
     */
    public function setListPrice(string $list_price): self {
        $this->list_price = $list_price;
        return $this;
    }

    /**
     * Convert product to string representation
     * 
     * @return string Product information in HTML format
     */
    public function __toString(): string {
        return "<p>Product: {$this->product_name} ({$this->model_year})<br>
                Brand: {$this->brand->getBrandName()}<br>
                Price: ${$this->list_price}</p>";
    }
}
?>