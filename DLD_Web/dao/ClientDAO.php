<?php
    include(__DIR__ . "/../models/Client.php");

    class ClientDAO implements ClientInterface {

        private $dbConn;

        public function __construct(PDO $dbConn) {
            $this->dbConn = $dbConn;
        }

        public function listClients(int $offset = 0) {
            // Get the numbers of records
            $stmt = $this->dbConn->query("SELECT COUNT(*) FROM clients");
            $queryResult = $stmt->fetch();
            $qty = $queryResult[""];

            if ($qty <= 10) {

                $stmt = $stmt = $this->dbConn->query("SELECT * FROM clients ORDER BY id");
                $queryResult = $stmt->fetchAll();
            } else {

                $stmt = $this->dbConn->query("SELECT * FROM clients ORDER BY id OFFSET $offset ROWS FETCH NEXT 10 ROWS ONLY");
                $queryResult = $stmt->fetchAll();
            }
            
            return [
                "qty" => $qty,
                "results" => $queryResult
            ];

        }

        public function getClient(int $clientId) {
            if (isset($clientId) && !empty($clientId)) {
                $stmt = $this->dbConn->prepare("SELECT * FROM clients WHERE id = :id");
                $stmt->bindValue(":id", $clientId);

                try{
                    $stmt->execute();
                    $queryResult = $stmt->fetch();
                    return $queryResult;

                } catch (PDOException $pdoError) {
                    throw new Exception($pdoError->getMessage());
                }
            }
        }

        public function newClient(Client $client) {
            // Client id it's not automatically generated by DB Engine because i want to use same client id from INOVAFARMA
            // Check if client id is already taken
            $stmt = $this->dbConn->prepare("SELECT COUNT(id) FROM clients WHERE id = :clientId");
            $stmt->bindValue(":clientId", $client->getClientId());

            try {
                $stmt->execute();
                $queryResult = $stmt->fetch();

                if ($queryResult[""] >= 1) {
                    throw new Exception("O código de cliente " . $client->getClientId() . " já existe.");
                    exit;
                }
            } catch (PDOException $pdoError) {
                throw new Exception($pdoError->getMessage());
            }

            // Save client record
            $stmt = $this->dbConn->prepare("INSERT INTO clients (id, name) VALUES (:id, :name)");
            $stmt->bindValue(":id", $client->getClientId());
            $stmt->bindValue(":name", $client->getClientName());

            try {
                
                return $stmt->execute();
            } catch (PDOException $pdoError) {
                throw new Exception($pdoError->getMessage());
            }
        }

        public function updateClient(Client $client) {
            if (($client->getClientId() !== null) && ($client->getClientName() !== null)){
                $stmt = $this->dbConn->prepare("UPDATE clients SET name = :name WHERE id = :id");
                $stmt->bindValue(":name", $client->getClientName());
                $stmt->bindValue(":id", $client->getClientId());

                try{
                    return $stmt->execute();
                } catch (PDOException $pdoError) {
                    throw new Exception($pdoError->getMessage());
                }
            } else {
                return false;
            }
        }

        public function deleteClient(int $clientId) {
            if (isset($clientId) && !empty($clientId)) {
                $stmt = $this->dbConn->prepare("DELETE FROM clients WHERE id = :id");
                $stmt->bindValue(":id", $clientId);
                
                try{
                    
                    return $stmt->execute();
                } catch (PDOException $pdoError) {
                    throw new Exception($pdoError->getMessage());
                }
            }
        }

        public function searchClient(string $criterion, string $search, int $offset = null) {
            
            $search = $search . "%";

            // Get number of records found
            $stmt = $this->dbConn->prepare("SELECT COUNT(*) FROM clients WHERE $criterion LIKE :search");
            $stmt->bindValue(":search", $search);
            $stmt->execute();

            $queryResult = $stmt->fetch();
            $numRecords = $queryResult[""];

            if ($offset == null){

                // Fisrt get number of records found
                $stmt = $this->dbConn->prepare("SELECT COUNT(*) FROM clients WHERE $criterion LIKE :search");
                $stmt->bindValue(":search", $search);
                $stmt->execute();

                $queryResult = $stmt->fetch();
                $numRecords = $queryResult[""];

                if ($numRecords <= 10){
                    
                    // If the number of records is lower or equal 10, return all records found
                    $stmt = $this->dbConn->prepare("SELECT * FROM clients WHERE $criterion LIKE :search ORDER BY id");
                    $stmt->bindValue(":search", $search);
                    
                    try{
                        $stmt->execute();
                        $queryResult = $stmt->fetchAll();
                        return [
                            "qty" => $numRecords,
                            "results" => $queryResult
                        ];
                    } catch (PDOException $pdoError) {
                        throw new Exception($pdoError->getMessage());
                    }
                } else {

                    // If the number of records it's more than 10, return the firsts 10 records
                    $stmt = $this->dbConn->prepare("SELECT * FROM clients WHERE $criterion LIKE :search ORDER BY id OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY");
                    $stmt->bindValue(":search", $search);
                    
                    try{
                        $stmt->execute();
                        $queryResult = $stmt->fetchAll();
                        return [
                            "qty" => $numRecords,
                            "results" => $queryResult
                        ];
                    } catch (PDOException $pdoError) {
                        throw new Exception($pdoError->getMessage());
                    }
                }
            } else {

                // Get number of records found
                $stmt = $this->dbConn->prepare("SELECT COUNT(*) FROM clients WHERE $criterion LIKE :search");
                $stmt->bindValue(":search", $search);
                $stmt->execute();

                $queryResult = $stmt->fetch();
                $numRecords = $queryResult[""];

                $stmt = $this->dbConn->prepare("SELECT * FROM clients WHERE $criterion LIKE :search ORDER BY id OFFSET :offset ROWS FETCH NEXT 10 ROWS ONLY");
                $stmt->bindValue(":search", $search);
                $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    
                try{
                    $stmt->execute();
                    $queryResult = $stmt->fetchAll();
                    
                    return [
                        "qty" => $numRecords,
                        "results" => $queryResult
                    ];
                } catch (PDOException $pdoError) {
                    throw new Exception($pdoError->getMessage());
                }

            }
        }
    }