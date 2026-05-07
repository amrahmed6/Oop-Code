<?php

class Review {
    private $conn;
    private $table = "Review";

    private $reviewId;
    private $productId;
    private $userId;
    private $rating;
    private $comment;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add review
    public function create($productId, $userId, $rating, $comment) {
        if ($rating < 0 || $rating > 5) {
            return false;
        }

        try {
            $this->conn->beginTransaction();

            $query = "INSERT INTO " . $this->table . "
                      (product_id, user_id, rating, comment)
                      VALUES
                      (:product_id, :user_id, :rating, :comment)";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->bindParam(":rating", $rating);
            $stmt->bindParam(":comment", $comment);

            $stmt->execute();

            $this->updateProductRating($productId);

            $this->conn->commit();

            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Get review by id
    public function getById($reviewId) {
        $query = "SELECT 
                    r.review_id,
                    r.product_id,
                    r.user_id,
                    r.rating,
                    r.comment,
                    p.name AS product_name,
                    u.first_name,
                    u.last_name
                  FROM " . $this->table . " r
                  INNER JOIN Product p ON r.product_id = p.product_id
                  INNER JOIN Users u ON r.user_id = u.user_id
                  WHERE r.review_id = :review_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":review_id", $reviewId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all reviews for product
    public function getByProductId($productId) {
        $query = "SELECT 
                    r.review_id,
                    r.product_id,
                    r.user_id,
                    r.rating,
                    r.comment,
                    u.first_name,
                    u.last_name
                  FROM " . $this->table . " r
                  INNER JOIN Users u ON r.user_id = u.user_id
                  WHERE r.product_id = :product_id
                  ORDER BY r.review_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all reviews by user
    public function getByUserId($userId) {
        $query = "SELECT 
                    r.review_id,
                    r.product_id,
                    r.user_id,
                    r.rating,
                    r.comment,
                    p.name AS product_name,
                    p.image
                  FROM " . $this->table . " r
                  INNER JOIN Product p ON r.product_id = p.product_id
                  WHERE r.user_id = :user_id
                  ORDER BY r.review_id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update review
    public function update($reviewId, $userId, $rating, $comment) {
        if ($rating < 0 || $rating > 5) {
            return false;
        }

        try {
            $this->conn->beginTransaction();

            $oldReview = $this->getById($reviewId);

            if (!$oldReview || $oldReview['user_id'] != $userId) {
                $this->conn->rollBack();
                return false;
            }

            $query = "UPDATE " . $this->table . "
                      SET rating = :rating,
                          comment = :comment
                      WHERE review_id = :review_id
                      AND user_id = :user_id";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":rating", $rating);
            $stmt->bindParam(":comment", $comment);
            $stmt->bindParam(":review_id", $reviewId, PDO::PARAM_INT);
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);

            $stmt->execute();

            $this->updateProductRating($oldReview['product_id']);

            $this->conn->commit();

            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Delete review
    public function delete($reviewId, $userId = null) {
        try {
            $this->conn->beginTransaction();

            $review = $this->getById($reviewId);

            if (!$review) {
                $this->conn->rollBack();
                return false;
            }

            if ($userId !== null && $review['user_id'] != $userId) {
                $this->conn->rollBack();
                return false;
            }

            $query = "DELETE FROM " . $this->table . "
                      WHERE review_id = :review_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":review_id", $reviewId, PDO::PARAM_INT);
            $stmt->execute();

            $this->updateProductRating($review['product_id']);

            $this->conn->commit();

            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Update average rating and review count in Product table
    private function updateProductRating($productId) {
        $query = "UPDATE Product
                  SET 
                    average_rating = (
                        SELECT COALESCE(AVG(rating), 0)
                        FROM Review
                        WHERE product_id = :product_id
                    ),
                    review_count = (
                        SELECT COUNT(*)
                        FROM Review
                        WHERE product_id = :product_id
                    )
                  WHERE product_id = :product_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $productId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}

?>