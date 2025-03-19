<?php
//src/Entity/Products.php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="products")
 */
class Products {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $product_id;

    /**
     * @ORM\Column(type="string")
     */
    private string $product_name;

    /**
     * @ORM\ManyToOne(targetEntity="Categories", inversedBy="products")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="category_id")
     */
    private Categories $category;

    /**
     * @ORM\ManyToOne(targetEntity="Brands", inversedBy="products")
     * @ORM\JoinColumn(name="brand_id", referencedColumnName="brand_id")
     */
    private Brands $brand;

    /**
     * @ORM\Column(type="smallint")
     */
    private int $model_year;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private string $list_price;

    // Getters et setters pour chaque propriété

    /**
     * Get the value of product_id
     *
     * @return int
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
}
?>