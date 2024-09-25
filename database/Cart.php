<?php

// php cart class
class Cart
{
    public $db = null;

    public function __construct(DBController $db)
    {
        if (!isset($db->con)) return null;
        $this->db = $db;
    }

    // insert into cart table
    public function insertIntoCart($params = null, $table = "cart"){
        if ($this->db->con != null){
            if ($params != null){
                // get table columns and placeholders for values
                $columns = implode(',', array_keys($params));
                $placeholders = implode(',', array_fill(0, count($params), '?'));
    
                // create sql query
                $query_string = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    
                // prepare the statement
                $stmt = $this->db->con->prepare($query_string);
    
                // bind parameters (use `str_repeat('s', count($params))` for all strings)
                $types = str_repeat('s', count($params));  // assuming all values are strings, adjust as needed
                $stmt->bind_param($types, ...array_values($params));
    
                // execute and return result
                $stmt->execute();
                return $stmt->affected_rows > 0;
            }
        }
        return false;
    }

    
    // to get user_id and item_id and insert into cart table
    public function addToCart($userid, $itemid){
        if (isset($userid) && isset($itemid)){
            $params = array(
                "user_id" => $userid,
                "item_id" => $itemid
            );
    
            // insert data into cart securely
            $result = $this->insertIntoCart($params);
            if ($result){
                header("Location: " . $_SERVER['PHP_SELF']);
            }
        }
    }
    
    // delete cart item using cart item id
    public function deleteCart($item_id = null, $table = 'cart'){
        if ($item_id != null){
            $query = "DELETE FROM {$table} WHERE item_id = ?";
            $stmt = $this->db->con->prepare($query);
    
            // bind the item_id as integer
            $stmt->bind_param('i', $item_id);
    
            // execute and return result
            $stmt->execute();
            if($stmt->affected_rows > 0){
                header("Location: " . $_SERVER['PHP_SELF']);
            }
            return $stmt->affected_rows > 0;
        }
        return false;
    }
    
    // calculate sub total
    public function getSum($arr){
        if(isset($arr)){
            $sum = 0;
            foreach ($arr as $item){
                $sum += floatval($item[0]);
            }
            return sprintf('%.2f' , $sum);
        }
    }

    // get item_it of shopping cart list
    public function getCartId($cartArray = null, $key = "item_id"){
        if ($cartArray != null){
            $cart_id = array_map(function ($value) use($key){
                return $value[$key];
            }, $cartArray);
            return $cart_id;
        }
    }

    // Save for later
    public function saveForLater($item_id = null, $saveTable = "wishlist", $fromTable = "cart"){
        if ($item_id != null){
            // Step 1: Insert into the saveTable
            $query_insert = "INSERT INTO {$saveTable} SELECT * FROM {$fromTable} WHERE item_id = ?";
            $stmt_insert = $this->db->con->prepare($query_insert);
            $stmt_insert->bind_param('i', $item_id);
            $stmt_insert->execute();
    
            // Step 2: Delete from the fromTable
            if($stmt_insert->affected_rows > 0){
                $query_delete = "DELETE FROM {$fromTable} WHERE item_id = ?";
                $stmt_delete = $this->db->con->prepare($query_delete);
                $stmt_delete->bind_param('i', $item_id);
                $stmt_delete->execute();
            }
    
            header("Location: " . $_SERVER['PHP_SELF']);
            return true;
        }
        return false;
    }
    

}