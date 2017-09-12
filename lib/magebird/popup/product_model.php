<?php class product_model
{
    public function getProductSql($productId, $store_id, $prex)
    {
        $store_id = intval($store_id);
        $productId = intval($productId);
        $sql = "SELECT * FROM (      SELECT          ce.sku,          ea.attribute_id,          ea.attribute_code,          CASE ea.backend_type             WHEN 'varchar' THEN ce_varchar.value             WHEN 'int' THEN ce_int.value             WHEN 'text' THEN ce_text.value             WHEN 'decimal' THEN ce_decimal.value             WHEN 'datetime' THEN ce_datetime.value             ELSE ea.backend_type          END AS value,          ea.is_required AS required,          EAOV.value AS option_value      FROM " . $prex . "catalog_product_entity AS ce      LEFT JOIN " . $prex . "eav_attribute AS ea          ON ce.entity_type_id = ea.entity_type_id      LEFT JOIN " . $prex . "catalog_product_entity_varchar AS ce_varchar
                ON ce.entity_id = ce_varchar.entity_id
                AND ea.attribute_id = ce_varchar.attribute_id
                AND ea.backend_type = 'varchar'
                AND ce_varchar.store_id=$store_id
            LEFT JOIN " . $prex . "catalog_product_entity_int AS ce_int
                ON ce.entity_id = ce_int.entity_id
                AND ea.attribute_id = ce_int.attribute_id
                AND ea.backend_type = 'int'
                AND ce_int.store_id=$store_id
            LEFT JOIN " . $prex . "catalog_product_entity_text AS ce_text
                ON ce.entity_id = ce_text.entity_id
                AND ea.attribute_id = ce_text.attribute_id
                AND ea.backend_type = 'text'
                AND ce_text.store_id=$store_id
            LEFT JOIN " . $prex . "catalog_product_entity_decimal AS ce_decimal
                ON ce.entity_id = ce_decimal.entity_id
                AND ea.attribute_id = ce_decimal.attribute_id
                AND ea.backend_type = 'decimal'
                AND ce_decimal.store_id=$store_id
            LEFT JOIN " . $prex . "catalog_product_entity_datetime AS ce_datetime
                ON ce.entity_id = ce_datetime.entity_id
                AND ea.attribute_id = ce_datetime.attribute_id
                AND ea.backend_type = 'datetime' 
                AND ce_datetime.store_id=$store_id
            LEFT JOIN " . $prex . "eav_attribute_option EAO ON EAO.attribute_id = ea.attribute_id AND ce_int.value=EAO.option_id      LEFT JOIN " . $prex . "eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id AND EAOV.store_id=0
            WHERE ce.entity_id = $productId 
          ) AS tab";
        return $sql;
    }

    public function getProduct($productId, $store_id, $readAdapter, $prex, $request)
    {
        if (!$productId) return false;
        if (isset($this->products[$productId])) return $this->products[$productId];
        $sql = $this->getProductSql($productId, $store_id, $prex);
        $products = $readAdapter->query($sql);
        $products->setFetchMode(PDO::FETCH_ASSOC);
        $product = array();
        foreach ($products as $i => $row) {
            if ($row['attribute_code'] == 'small_image' || $row['attribute_code'] == 'thumbnail') {
                $product[$row['attribute_id']]['value'] = $request->getParam('baseUrl') . "/media/catalog/product/" . $row['value'];
            } elseif ($row['attribute_code'] == 'sku') {
                $product[$row['attribute_id']]['value'] = $row['sku'];
            } elseif ($row['option_value']) {
                $product[$row['attribute_id']]['value'] = $row['option_value'];
            } else {
                $product[$row['attribute_id']]['value'] = $row['value'];
            }
            $product[$row['attribute_id']]['option_value'] = $row['option_value'];
            $product[$row['attribute_id']]['attribute_code'] = $row['attribute_code'];
        }
        if ($store_id != 0) {
            $sql = $this->getProductSql($productId, 0, $prex);
            $products = $readAdapter->query($sql);
            $products->setFetchMode(PDO::FETCH_ASSOC);
            foreach ($products as $i => $row) {
                if ($row['attribute_code'] == 'small_image' || $row['attribute_code'] == 'thumbnail') {
                    $product[$row['attribute_id']]['value'] = $request->getParam('baseUrl') . "/media/catalog/product/" . $row['value'];
                } elseif ($row['attribute_code'] == 'sku') {
                    $product[$row['attribute_id']]['value'] = $row['sku'];
                } elseif ($product[$row['attribute_id']]['value'] == null) {
                    if ($row['attribute_code'] == 'sku') {
                        $product[$row['attribute_id']]['value'] = $row['sku'];
                    } elseif ($row['option_value']) {
                        $product[$row['attribute_id']]['value'] = $row['option_value'];
                    } else {
                        $product[$row['attribute_id']]['value'] = $row['value'];
                    }
                    $product[$row['attribute_id']]['option_value'] = $row['option_value'];
                }
            }
        }
        foreach ($product as $attributeId => $attribute) {
            $attributes[$attribute['attribute_code']] = $attribute['value'];
        }
        $attributes['product_id'] = $productId;
        $attributes['id'] = $productId;
        $this->products[$productId] = $attributes;
        return $attributes;
    }

    function getProductCategories($productId, $store_id, $readAdapter, $prex)
    {
        if (!$productId) return false;
        $sql = "SELECT DISTINCT category_id FROM " . $prex . "catalog_category_product WHERE product_id = " . intval($productId);
        $products = $readAdapter->query($sql);
        $products = $products->fetchAll(PDO::FETCH_COLUMN, 0);
        return $products;
    }
} ?>