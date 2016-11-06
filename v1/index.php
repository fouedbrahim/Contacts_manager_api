<?php
/**
 * @author BEN BRAHIM FOUED
 */

require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// User id from db - Global Variable
$user_id = NULL;

/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    // Verifying Authorization Header
    if (isset($headers['Authorization'])) {
        $db = new DbHandler();

        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key
        if (!$db->isValidApiKey($api_key)) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoRespnse(401, $response);
            $app->stop();
        } else {
            global $user_id;
            // get user primary key id
            $user_id = $db->getUserId($api_key);
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * ----------- METHODS WITHOUT AUTHENTICATION ---------------------------------
 */
/**
 * User Registration
 * url - /register
 * method - POST
 * params - name, email, password
 */
$app->post('/register', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('name', 'email', 'password'));

            $response = array();

            // reading post params
            $name = $app->request->post('name');
            $email = $app->request->post('email');
            $password = $app->request->post('password');

            // validating email address
            validateEmail($email);

            $db = new DbHandler();
            $res = $db->createUser($name, $email, $password);

            if ($res == USER_CREATED_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "You are successfully registered";
            } else if ($res == USER_CREATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Oops! An error occurred while registereing";
            } else if ($res == USER_ALREADY_EXISTED) {
                $response["error"] = true;
                $response["message"] = "Sorry, this email already existed";
            }
            // echo json response
            echoRespnse(201, $response);
        });

/**
 * User Login
 * url - /login
 * method - POST
 * params - email, password
 */
$app->post('/login', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('email', 'password'));

            // reading post params
            $email = $app->request()->post('email');
            $password = $app->request()->post('password');
            $response = array();

            $db = new DbHandler();
            // check for correct email and password
            if ($db->checkLogin($email, $password)) {
                // get the user by email
                $user = $db->getUserByEmail($email);

                if ($user != NULL) {
                    $response["error"] = false;
                    $response['name'] = $user['name'];
                    $response['email'] = $user['email'];
                    $response['apiKey'] = $user['api_key'];
                    $response['createdAt'] = $user['created_at'];
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            } else {
                // user credentials are wrong
                $response['error'] = true;
                $response['message'] = 'Login failed. Incorrect credentials';
            }

            echoRespnse(200, $response);
        });

// Récupération de la méthode reçue
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
	case 'GET':
		$response = array();
		if(isset($_GET['token']) && !empty($_GET['token'])) {
			if($db->isValidApiKey($_GET['token'])) {
				if(isset($_GET['contact']) && !empty($_GET['contact'])) {
					if($_GET['contact'] == "all") {
						// Voir tous les contacts
						$contacts = $db->getContacts();
						echo utf8_encode(json_encode($contacts));
					} else if(is_numeric($_GET['contact'])) {
						// Voir un contact
						$contact = $db->getContact($_GET['contact']);
						echo utf8_encode(json_encode($contact));
					}
				} else {
					$response['code'] = 404;
					$response['message'] = "Paramètre non fourni";
				}
			} else {
				$response['code'] = 404;
				$response['message'] = "Token invalide";
				echo utf8_encode(json_encode($response));
				return;
			}
		} else {
			$response['code'] = 404;
			$response['message'] = "Token non fourni en parametre";
			echo utf8_encode(json_encode($response));
			return;	
		}
	break;

	case 'POST':
		$response = array();
		if(isset($_POST['token']) && !empty($_POST['token'])) {
			if($db->isValidApiKey($_POST['token'])) {
				if(isset($_POST['endpoint']) && !empty($_POST['endpoint'])) {
					switch ($_POST['endpoint']) {
						// Ajouter un contact
						case 'contact/Ajout':
							$response['code'] = 200;
							$response['message'] = $db->AjoutContact($_POST);
							echo json_encode($response);
						break;
						// Créer utilisateur
						case 'user/create':
							$response['code'] = 200;
							$response['message'] = $db->createUser($_POST['name'], $_POST['email'], $_POST['password']);
							echo json_encode($response);
						break;
					}			
				} else {
					$response['code'] = 404;
					$response['message'] = "Endpoint non fourni";
					echo json_encode($response);
					return;
				}				
			} else{
				$response['code'] = 404;
				$response['message'] = "Token invalide";
				echo json_encode($response);
				return;		
			}
		} else {
			$response['code'] = 404;
			$response['message'] = "Token non fourni en parametre";
			echo json_encode($response);
			return;		
		}
	break;

	case 'PUT':
		parse_str(file_get_contents("php://input"),$_PUT);
		$response = array();
		if(isset($_PUT['token']) && !empty($_PUT['token'])) {
			if($db->isValidApiKey($_PUT['token'])) {
				if(isset($_PUT['endpoint']) && !empty($_PUT['endpoint'])) {
					switch ($_PUT['endpoint']) {
					// Ajouter une adresse sur un contact
					case 'contact/adresse/Ajout':
						$response['message'] = $db->AjoutAdresse($_PUT);
						$response['code'] = 200;
						echo json_encode($response);
						return;
					break;
					// Modifier l'adresse d'un contact
					case 'contact/adresse/Modifier':
						$response['message'] = $db->ModifierAdresse($_PUT);
						$response['code'] = 200;
						echo json_encode($response);
						return;
					break;
					// Modifier un contact
					case 'contact/Modifier':
						$response['message'] = $db->ModifierContact($_PUT);
						$response['code'] = 200;
						echo json_encode($response);
						return;
					break;				
					}			
				} else {
					$response['code'] = 404;
					$response['message'] = "Endpoint non fourni";
					echo json_encode($response);
					return;
				}				
			} else{
				$response['code'] = 404;
				$response['message'] = "Token invalide";
				echo json_encode($response);
				return;		
			}
		} else {
			$response['code'] = 404;
			$response['message'] = "Token non fourni en parametre";
			echo json_encode($response);
			return;		
		}
	break;

	case 'DELETE':
		parse_str(file_get_contents("php://input"),$_DELETE);
		$response = array();
		if(isset($_DELETE['token']) && !empty($_DELETE['token'])) {
			if($db->isValidApiKey($_DELETE['token'])) {
				if(isset($_DELETE['endpoint']) && !empty($_DELETE['endpoint'])) {
					switch ($_DELETE['endpoint']) {
						case 'contact/Suppriemr':
							$response['code'] = 200;
							$response['message'] = $db->SuppriemrContact($_DELETE);
							echo json_encode($response);
							return;
						break;
					}			
				} else {
					$response['code'] = 404;
					$response['message'] = "Endpoint non fourni";
					echo json_encode($response);
					return;
				}				
			} else{
				$response['code'] = 404;
				$response['message'] = "Token invalide";
				echo json_encode($response);
				return;		
			}
		} else {
			$response['code'] = 404;
			$response['message'] = "Token non fourni en parametre";
			echo json_encode($response);
			return;		
		}
	break;
}
?>