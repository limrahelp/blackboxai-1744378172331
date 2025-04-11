<?php
require_once 'config.php';

class Database {
    private $conn = null;
    private $isConnected = false;

    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
            $this->isConnected = true;
        } catch(PDOException $e) {
            // Log error but don't expose details
            error_log("Database connection failed: " . $e->getMessage());
            $this->isConnected = false;
        }
    }

    public function isConnected() {
        return $this->isConnected;
    }

    public function getMosquesByLocation($lat, $lng, $radius = 10) {
        if (!$this->isConnected) {
            return ["error" => "Database connection is not available"];
        }

        try {
            $query = "SELECT *, 
                      (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                       cos(radians(longitude) - radians(?)) + 
                       sin(radians(?)) * sin(radians(latitude)))) AS distance 
                      FROM mosques 
                      HAVING distance < ? 
                      ORDER BY distance";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$lat, $lng, $lat, $radius]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Database query failed: " . $e->getMessage());
            return ["error" => "Failed to fetch mosque data"];
        }
    }

    public function addPendingSubmission($data) {
        if (!$this->isConnected) {
            return ["error" => "Database connection is not available"];
        }

        try {
            $query = "INSERT INTO pending_submissions 
                      (mosque_id, name, address, latitude, longitude, 
                       fajar, zuhar, asar, maghrib, ishaa, juma, 
                       submission_type) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $data['mosque_id'] ?? null,
                $data['name'],
                $data['address'],
                $data['latitude'],
                $data['longitude'],
                $data['fajar'],
                $data['zuhar'],
                $data['asar'],
                $data['maghrib'],
                $data['ishaa'],
                $data['juma'],
                $data['submission_type']
            ]);
            return $this->conn->lastInsertId();
        } catch(PDOException $e) {
            error_log("Database query failed: " . $e->getMessage());
            return ["error" => "Failed to submit mosque data"];
        }
    }

    public function approveMosque($submission_id) {
        if (!$this->isConnected) {
            return ["error" => "Database connection is not available"];
        }

        try {
            $this->conn->beginTransaction();

            // Get submission details
            $stmt = $this->conn->prepare("SELECT * FROM pending_submissions WHERE id = ?");
            $stmt->execute([$submission_id]);
            $submission = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($submission) {
                if ($submission['submission_type'] === 'new') {
                    // Insert new mosque
                    $query = "INSERT INTO mosques 
                             (name, address, latitude, longitude, 
                              fajar, zuhar, asar, maghrib, ishaa, juma) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $this->conn->prepare($query);
                    $stmt->execute([
                        $submission['name'],
                        $submission['address'],
                        $submission['latitude'],
                        $submission['longitude'],
                        $submission['fajar'],
                        $submission['zuhar'],
                        $submission['asar'],
                        $submission['maghrib'],
                        $submission['ishaa'],
                        $submission['juma']
                    ]);
                } elseif ($submission['submission_type'] === 'revision') {
                    // Update existing mosque
                    $query = "UPDATE mosques SET 
                             name = ?, address = ?, latitude = ?, longitude = ?,
                             fajar = ?, zuhar = ?, asar = ?, maghrib = ?, 
                             ishaa = ?, juma = ?, updated_at = NOW()
                             WHERE id = ?";
                    $stmt = $this->conn->prepare($query);
                    $stmt->execute([
                        $submission['name'],
                        $submission['address'],
                        $submission['latitude'],
                        $submission['longitude'],
                        $submission['fajar'],
                        $submission['zuhar'],
                        $submission['asar'],
                        $submission['maghrib'],
                        $submission['ishaa'],
                        $submission['juma'],
                        $submission['mosque_id']
                    ]);
                } elseif ($submission['submission_type'] === 'delete') {
                    // Delete mosque
                    $stmt = $this->conn->prepare("DELETE FROM mosques WHERE id = ?");
                    $stmt->execute([$submission['mosque_id']]);
                }

                // Remove the submission
                $stmt = $this->conn->prepare("DELETE FROM pending_submissions WHERE id = ?");
                $stmt->execute([$submission_id]);

                $this->conn->commit();
                return true;
            }
            return false;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            error_log("Database query failed: " . $e->getMessage());
            return ["error" => "Failed to process mosque submission"];
        }
    }

    public function rejectSubmission($submission_id) {
        if (!$this->isConnected) {
            return ["error" => "Database connection is not available"];
        }

        try {
            $stmt = $this->conn->prepare("DELETE FROM pending_submissions WHERE id = ?");
            $stmt->execute([$submission_id]);
            return true;
        } catch(PDOException $e) {
            error_log("Database query failed: " . $e->getMessage());
            return ["error" => "Failed to reject submission"];
        }
    }

    public function getPendingSubmissions() {
        if (!$this->isConnected) {
            return ["error" => "Database connection is not available"];
        }

        try {
            $stmt = $this->conn->query("SELECT * FROM pending_submissions ORDER BY submitted_at DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Database query failed: " . $e->getMessage());
            return ["error" => "Failed to fetch pending submissions"];
        }
    }
}
