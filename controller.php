<?php
require_once('model.php');

class TodoController {
    private $model;
    
    public function __construct() {
        $this->model = new Model();
    }
    
    public function handleRequest() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'index';
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        // Check if user is logged in for protected actions
        $protectedActions = ['index', 'create', 'store', 'edit', 'update', 'delete', 'update_status'];
        
        if(in_array($action, $protectedActions) && !$this->model->isLoggedIn()) {
            header('Location: controller.php?action=login&error=Please login to continue');
            exit();
        }
        
        switch($action) {
            // Authentication actions
            case 'login':
                $this->login();
                break;
            case 'do_login':
                $this->doLogin();
                break;
            case 'register':
                $this->register();
                break;
            case 'do_register':
                $this->doRegister();
                break;
            case 'logout':
                $this->logout();
                break;
            // Todo actions
            case 'create':
                $this->create();
                break;
            case 'store':
                $this->store();
                break;
            case 'edit':
                $this->edit($id);
                break;
            case 'update':
                $this->update($id);
                break;
            case 'delete':
                $this->delete($id);
                break;
            case 'update_status':
                $this->updateStatus();
                break;
            default:
                $this->index();
                break;
        }
    }
    
    private function index() {
        $todos = $this->model->getAllTodos();
        if (!is_array($todos)) {
            $todos = [];
        }
        include('view.php');
    }
    
    private function create() {
        include('create_view.php');
    }
    
    private function store() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            
            if(empty($title)) {
                header('Location: controller.php?action=create&error=Title is required');
                exit();
            }
            
            if($this->model->createTodo($title, $description)) {
                header('Location: controller.php?action=index&message=Todo created successfully');
            } else {
                header('Location: controller.php?action=create&error=Failed to create todo');
            }
        }
    }
    
    private function edit($id) {
        if(!$id) {
            header('Location: controller.php?action=index&error=Invalid todo ID');
            return;
        }
        
        $todo = $this->model->getTodoById($id);
        if($todo) {
            include('edit_view.php');
        } else {
            header('Location: controller.php?action=index&error=Todo not found');
        }
    }
    
    private function update($id) {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $status = $_POST['status'];
            
            if(empty($title)) {
                header('Location: controller.php?action=edit&id=' . $id . '&error=Title is required');
                exit();
            }
            
            if($this->model->updateTodo($id, $title, $description, $status)) {
                header('Location: controller.php?action=index&message=Todo updated successfully');
            } else {
                header('Location: controller.php?action=edit&id=' . $id . '&error=Failed to update todo');
            }
        }
    }
    
    private function delete($id) {
        if($id && $this->model->deleteTodo($id)) {
            header('Location: controller.php?action=index&message=Todo deleted successfully');
        } else {
            header('Location: controller.php?action=index&error=Failed to delete todo');
        }
    }
    
    private function updateStatus() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $status = $_POST['status'];
            
            if($this->model->updateStatus($id, $status)) {
                header('Location: controller.php?action=index&message=Status updated successfully');
            } else {
                header('Location: controller.php?action=index&error=Failed to update status');
            }
        }
    }
    
    // Authentication methods
    private function login() {
        include('login_view.php');
    }
    
    private function doLogin() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            
            if(empty($username) || empty($password)) {
                header('Location: controller.php?action=login&error=Username/Email and password are required');
                exit();
            }
            
            if($this->model->loginUser($username, $password)) {
                header('Location: controller.php?action=index&message=Welcome back, ' . $_SESSION['username'] . '!');
            } else {
                header('Location: controller.php?action=login&error=Invalid username/email or password');
            }
        }
    }
    
    private function register() {
        include('register_view.php');
    }
    
    private function doRegister() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Validation
            if(empty($username) || empty($email) || empty($password)) {
                header('Location: controller.php?action=register&error=All fields are required');
                exit();
            }
            
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                header('Location: controller.php?action=register&error=Invalid email format');
                exit();
            }
            
            if(strlen($username) < 3) {
                header('Location: controller.php?action=register&error=Username must be at least 3 characters');
                exit();
            }
            
            if(strlen($password) < 6) {
                header('Location: controller.php?action=register&error=Password must be at least 6 characters');
                exit();
            }
            
            if($password !== $confirm_password) {
                header('Location: controller.php?action=register&error=Passwords do not match');
                exit();
            }
            
            if($this->model->registerUser($username, $email, $password)) {
                // Auto login after registration
                $this->model->loginUser($username, $password);
                header('Location: controller.php?action=index&message=Registration successful! Welcome ' . $username . '!');
            } else {
                header('Location: controller.php?action=register&error=Username or email already exists');
            }
        }
    }
    
    private function logout() {
        $this->model->logout();
        header('Location: controller.php?action=login&message=You have been logged out');
    }
}

// Initialize controller and handle request
$controller = new TodoController();
$controller->handleRequest();
?>