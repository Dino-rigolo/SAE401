<?php
//src/Entity/Categories.php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="categories")
 */
class Categories {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $category_id;

    /**
     * @ORM\Column(type="string")
     */
    private string $category_name;

    /**
     * @ORM\OneToMany(targetEntity="Products", mappedBy="category")
     */
    private Collection $products;

    public function __construct() {
        $this->products = new ArrayCollection();
    }

    /**
     * Get the value of category_id
     *
     * @return int
     */
    public function getCategoryId(): int {
        return $this->category_id;
    }

    /**
     * Set the value of category_id
     *
     * @param int $category_id
     * @return self
     */
    public function setCategoryId(int $category_id): self {
        $this->category_id = $category_id;
        return $this;
    }

    /**
     * Get the value of category_name
     *
     * @return string
     */
    public function getCategoryName(): string {
        return $this->category_name;
    }

    /**
     * Set the value of category_name
     *
     * @param string $category_name
     * @return self
     */
    public function setCategoryName(string $category_name): self {
        $this->category_name = $category_name;
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
     * Add a product to the category
     *
     * @param Products $product
     * @return self
     */
    public function addProduct(Products $product): self {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setCategory($this);
        }
        return $this;
    }

    /**
     * Remove a product from the category
     *
     * @param Products $product
     * @return self
     */
    public function removeProduct(Products $product): self {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            // set the owning side to null (unless already changed)
            if ($product->getCategory() === $this) {
                $product->setCategory(null);
            }
        }
        return $this;
    }
}
?>
