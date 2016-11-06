<?php

/**
 * @author BEN BRAHIM FOUED
 */
class DbHandler {

    private $conn;

    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /* ------------- `users` table method ------------------ */

    /**
     * Creating new user
     * @param String $name User full name
     * @param String $email User login email id
     * @param String $password User login password
     */
    public function createUser($name, $email, $password) {
        require_once 'PassHash.php';
        $response = array();

        // First check if user already existed in db
        if (!$this->isUserExists($email)) {
            // Generating password hash
            $password_hash = PassHash::hash($password);

            // Generating API key
            $api_key = $this->generateApiKey();

            // insert query
            $stmt = $this->conn->prepare("INSERT INTO users(name, email, password_hash, api_key, status) values(?, ?, ?, ?, 1)");
            $stmt->bind_param("ssss", $name, $email, $password_hash, $api_key);

            $result = $stmt->execute();

            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return USER_ALREADY_EXISTED;
        }

        return $response;
    }

    /**
     * Checking user login
     * @param String $email User login email id
     * @param String $password User login password
     * @return boolean User login status success/fail
     */
    public function checkLogin($email, $password) {
        // fetching user by email
        $stmt = $this->conn->prepare("SELECT password_hash FROM users WHERE email = ?");

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $stmt->bind_result($password_hash);

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

            if (PassHash::check_password($password_hash, $password)) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();

            // user not existed with the email
            return FALSE;
        }
    }

    /**
     * Checking for duplicate user by email address
     * @param String $email email to check in db
     * @return boolean
     */
    private function isUserExists($email) {
        $stmt = $this->conn->prepare("SELECT id from users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT name, email, api_key, status, created_at FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($name, $email, $api_key, $status, $created_at);
            $stmt->fetch();
            $user = array();
            $user["name"] = $name;
            $user["email"] = $email;
            $user["api_key"] = $api_key;
            $user["status"] = $status;
            $user["created_at"] = $created_at;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user api key
     * @param String $user_id user id primary key in user table
     */
    public function getApiKeyById($user_id) {
        $stmt = $this->conn->prepare("SELECT api_key FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            // $api_key = $stmt->get_result()->fetch_assoc();
            // TODO
            $stmt->bind_result($api_key);
            $stmt->close();
            return $api_key;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user id by api key
     * @param String $api_key user api key
     */
    public function getUserId($api_key) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        if ($stmt->execute()) {
            $stmt->bind_result($user_id);
            $stmt->fetch();
            // TODO
            // $user_id = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $user_id;
        } else {
            return NULL;
        }
    }

    /**
     * Validating user api key
     * If the api key is there in db, it is a valid key
     * @param String $api_key user api key
     * @return boolean
     */
    public function isValidApiKey($api_key) {
        $stmt = $this->conn->prepare("SELECT id from users WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Generating random Unique MD5 String for user Api key
     */
    private function generateApiKey() {
        return md5(uniqid(rand(), true));
    }

       
   	  /***************************** METHODES DE LA TABLE ADDRESSES *****************************/


   	public function checkIfAlreadyAdresse($id) {
   		if(!empty($id)) {
   			if($this->checkIfContactExist($id)) {
   				$req = $this->conn->prepare("SELECT id FROM adresses WHERE contact_id = ?");
   				$req->bind_param('i', $id);
   				$req->execute();
   				$req->store_result();
   				if($req->num_rows > 0) {
   					return true;
   				} else {
   					return false;
   				}
   			} else {
   				return "ERREUR : contact introuvable";
   			}
   		} else {
   			return "ERREUR : id du contact non specifie";
   		}
   	}

   	public function getAdresse($id)
   	{	
   		$array = null;
   		if(!empty($id)) {
   			$req = $this->conn->prepare("SELECT * FROM adresses WHERE contact_id = ?");
   			$req->bind_param('i', $id);
   			$req->execute();
   			$result = $req->get_result();
   			$array = $result->fetch_assoc();
   			$req->close();
   		}
   		return $array;
   	}

   	public function AjoutAdresse($data)
   	{	
   		$cree_le = new DateTime();
   		$resultAdresse = null;

   		if(!empty($data['contact_id']) && !empty($data['ville']) && !empty($data['codepostal'])) {

   			if($this->checkIfContactExist($data['contact_id'])) {
   				if(!$this->checkIfAlreadyAdresse($data['contact_id'])) {
					$req = $this->conn->prepare("INSERT INTO adresses(rue, codepostal, ville,cree_le, contact_id) VALUES(?, ?, ?, ?, ?)");
					$req->bind_param('ssssi',$data['rue'], $data['codepostal'], $data['ville'], $cree_le->format('Y-m-d H:i:s'), $data['contact_id']);
					$resultAdresse = $req->execute();
					$req->close();	  					
   				} else {
   					return "ERREUR : ce contact a deja une adresse";
   				}
   			} else {
   				return "Contact introuvable";
   			}

			if($resultAdresse) {
				return "SUCCES : adresse ajoutee avec succes";
			} else {
				return "ERREUR : adresse non ajoutee";
			}

   		} else {
   			return "Parametres manquants pour ajouter une adresse";
   		}
   	}

   	public function ModifierAdresse($data)
   	{
   		$mise_ajour_le = new DateTime();
   		$result = null;

   		if(!empty($data['contact_id'])) {
   			$originalAdresse = $this->getAdresse($data['contact_id']);
   			if(!empty($originalAdresse)) {
	   			$adresseToUpdate = $originalAdresse;

	   			if(!empty($data['codepostal'])) {
	   				$adresseToUpdate['codepostal'] = $data['codepostal'];
	   			}
	   			if(!empty($data['ville'])) {
	   				$adresseToUpdate['ville'] = $data['ville'];
	   			}
	   			if(!empty($data['rue'])) {
	   				$adresseToUpdate['rue'] = $data['rue'];
	   			}
	   			$req = $this->conn->prepare("UPDATE adresses SET
	   			codepostal = ?,
	   			ville = ?,
	   			rue = ?,
	   			mise_ajour_le = ?
	   			WHERE contact_id = ?"
	   			);

	   			$req->bind_param('ssssi', $adresseToUpdate['codepostal'], $adresseToUpdate['ville'], $adresseToUpdate['rue'], $mise_ajour_le->format('Y-m-d H:i:s'), $data['contact_id']);
	   			$result = $req->execute();  				
   			} else {
   				return "ERREUR : adresse introuvable";
   			}
   		} else {
   			return "ERREUR : l'id de l'adresse n'a pas ete specifie";
   		}

   		if($result) {
   			return "SUCCES : l'adresse a bien ete Modifier";
   		} else {
   			return "ERREUR : l'adresse n'a pas pu être Modifier";
   		}
   	}

   	public function SuppriemrAdresse($id) {

   		$result = null;

   		if(!empty($id)) {
	   		$req = $this->conn->prepare("DELETE FROM adresses WHERE contact_id = ?");
	   		$req->bind_param('i', $id);
	   		$result = $req->execute();			
   		}

   		if($result) {
   			return "SUCCES : adresse supprimee";
   		} else {
   			return "ERREUR : adresse non supprimee";
   		}
   	}
        
         /***************************** METHODES DE LA TABLE CONTACT *****************************/

   
   	public function AjoutContact($data)
   	{
   		$cree_le = new DateTime();

   		if(!empty($data['civilite']) && !empty($data['prenom']) && !empty($data['nom'])) {
   			// Ajout du contact
			$req = $this->conn->prepare("INSERT INTO contacts(civilite, nom, prenom, date_naissance, cree_le) values (?, ?, ?, ?, ?)");
			$req->bind_param('sssss', $data['civilite'], $data['nom'], $data['prenom'], $data['date_naissance'], $cree_le->format('Y-m-d H:i:s'));
			$resultContact = $req->execute();
			$req->close();

			if($resultContact) {
				return "Contact ajoute avec succes";
			}

   		} else {
   			return "Parametres manquants pour ajouter un contact";
   		}
   	}

   	public function ModifierContact($data)
   	{	
   		$mise_ajour_le = new DateTime();

   		if(!empty($data['contact_id']) && is_numeric($data['contact_id'])) {
   			if($this->checkIfContactExist($data['contact_id'])) {
   				$originalContact = $this->getContact($data['contact_id']);
   				$toUpdateContact = $originalContact;

   				if(!empty($data['civilite'])) {

   					$toUpdateContact['civilite'] = $data['civilite'];

   				}
   				if(!empty($data['nom'])) {

   					$toUpdateContact['nom'] = $data['nom'];

   				}
   				if(!empty($data['prenom'])) {

   					$toUpdateContact['prenom'] = $data['prenom'];

   				}
   				if(!empty($data['date_naissance'])) {
   					$toUpdateContact['date_naissance'] = $data['date_naissance'];
   				}

   				$req = $this->conn->prepare("UPDATE contacts SET 
   					civilite = ?,
   					prenom = ?,
   					nom = ?,
   					date_naissance = ?,
   					mise_ajour_le = ?
   					WHERE id = ?"
   				);
   				$req->bind_param('sssssi', $toUpdateContact['civilite'], $toUpdateContact['prenom'], $toUpdateContact['nom'], $toUpdateContact['date_naissance'], $mise_ajour_le->format('Y-m-d H:i:s'), $data['contact_id']);
   				$result = $req->execute();

   				if($result) {
   					return "SUCCES : contact bien Modifier";
   				}

   				$req->close();
   			} else {
   				return "ERREUR : contact introuvable";
   			}
   		} else {
   			return "ERREUR : l'id du contact a Modifier nexiste pas";
   		}
   	}
 public function getContact($id)
    {
		$contact = null;
    	$req = $this->conn->prepare("SELECT * FROM contacts INNER JOIN adresses ON contacts.id = adresses.contact_id WHERE contacts.id = ?");
    	$req->bind_param('i', $id);
		$req->execute();
		$result = $req->get_result();
    	$contact = $result->fetch_assoc();
    	$req->close();
    	return $contact;
    }

    public function getContacts()
    {
    	$req = $this->conn->prepare("SELECT * FROM contacts INNER JOIN adresses ON contacts.id = adresses.contact_id");
    	$array = null;
    	$req->execute();
    	$result = $req->get_result();
    	while($row = $result->fetch_assoc()) {
    		$array[] = $row;
    	}
    	$req->close();

    	return $array;
    }

   	public function SuppriemrContact($data)
   	{
   		$result = null;

   		if(!empty($data['contact_id'])) {
	   		if($this->checkIfContactExist($data['contact_id'])) {
	   			$this->SuppriemrAdresse($data['contact_id']);
	   			$req = $this->conn->prepare("DELETE FROM contacts WHERE id = ?");
	   			$req->bind_param('i', $data['contact_id']);
	   			$result = $req->execute();
	   		} else {
	   			return "ERREUR : introuvable";
	   		}		
   		} else {
   			return "ERREUR : id manquant";
   		}

   		if($result) {
   			return "SUCCES : contact supprime";
   		} else {
   			return "ERREUR : contact non supprime";
   		}
   	}

   	public function checkIfContactExist($id)
   	{	
   		if(!empty($id)) {
	   		$req = $this->conn->prepare('SELECT id FROM contacts WHERE id = ?');
	   		$req->bind_param('i', $id);
	   		$req->execute();
	   		$req->store_result();
	   		$rows = $req->num_rows;
	   		if($rows > 0) {
	   			return true;
	   		} else {
	   			return false;
	   		}		
   		} else {
   			return "ERREUR : id non specifie";
   		}
   	}

}