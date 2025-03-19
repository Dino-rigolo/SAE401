<?php
//src/Entity/Brands.php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="brands")
 */
class Brands {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $brand_id;

    /**
     * @ORM\Column(type="string")
     */
    private string $brand_name;

    /**
     * @ORM\OneToMany(targetEntity="Products", mappedBy="brand")
     */
    private Collection $products;

    public function __construct() {
        $this->products = new ArrayCollection();
    }

    /**
     * Get the value of brand_id
     *
     * @return int
     */
    public function getBrandId(): int {
        return $this->brand_id;
    }

    /**
     * Set the value of brand_id
     *
     * @param int $brand_id
     * @return self
     */
    public function setBrandId(int $brand_id): self {
        $this->brand_id = $brand_id;
        return $this;
    }

    /**
     * Get the value of brand_name
     *
     * @return string
     */
    public function getBrandName(): string {
        return $this->brand_name;
    }

    /**
     * Set the value of brand_name
     *
     * @param string $brand_name
     * @return self
     */
    public function setBrandName(string $brand_name): self {
        $this->brand_name = $brand_name;
        return $this;
    }

    /**
     * Get the value of products
     *
     * @return Collection
     */
    public function getProducts(): Collection {
        return $this->products;
    }

    /**
     * Add a product to the brand
     *
     * @param Products $product
     * @return self
     */
    public function addProduct(Products $product): self {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setBrand($this);
        }
        return $this;
    }

    /**
     * Remove a product from the brand
     *
     * @param Products $product
     * @return self
     */
    public function removeProduct(Products $product): self {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            // set the owning side to null (unless already changed)
            if ($product->getBrand() === $this) {
                $product->setBrand(null);
            }
        }
        return $this;
    }

    public function __toString() {
        return "<p>{$this->brand_id} - {$this->brand_name}</p>";
    }
}
?>
