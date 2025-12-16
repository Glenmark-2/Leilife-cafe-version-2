<?php

require_once __DIR__ . '/../repositories/ProductRepository.php';

class ProductService {
    private $productRepo;

    public function __construct() {
        $this->productRepo = new ProductRepository();
    }

    public function getMenuStructure() {
        $rawData = $this->productRepo->getFullMenuData();
        $menuData = [];

        foreach ($rawData as $row) {
            $mainCat = $row['main_category'];
            $subCat = $row['sub_category'];
            
            if (!isset($menuData[$mainCat])) {
                $menuData[$mainCat] = [];
            }
            
            if (!isset($menuData[$mainCat][$subCat])) {
                $menuData[$mainCat][$subCat] = [];
            }

            $menuData[$mainCat][$subCat][] = [
                'id' => $row['product_id'],
                'name' => $row['product_name'],
                'price' => (float)$row['price'],
                'image' => $row['image_path'], // Mapping image_path to image for frontend compatibility
                'description' => $row['description']
            ];
        }

        return $menuData;
    }

    public function getProductById($id) {
        return $this->productRepo->findById($id);
    }
}
