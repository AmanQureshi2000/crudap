<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo App</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #764ba2;
        }
        
        .btn-danger {
            background: #e74c3c;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-success {
            background: #27ae60;
        }
        
        .btn-success:hover {
            background: #229954;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-pending {
            background: #ffc107;
            color: #856404;
        }
        
        .status-completed {
            background: #28a745;
            color: white;
        }
        
        .status-in-progress {
            background: #17a2b8;
            color: white;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .action-buttons a, .action-buttons button {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .status-form {
            display: inline;
        }
        
        .status-select {
            padding: 5px;
            border-radius: 3px;
            border: 1px solid #ddd;
        }
        
        @media (max-width: 768px) {
            .content {
                padding: 15px;
            }
            
            th, td {
                padding: 8px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Todo List App</h1>
            <p>Organize your tasks efficiently</p>
            <div style="margin-top: 15px;">
                <span style="background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 20px; font-size: 14px;">
                    Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>!
                </span>
                <a href="controller.php?action=logout" style="color: white; margin-left: 15px; text-decoration: none; background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 20px; font-size: 14px;">
                    Logout
                </a>
            </div>
        </div>
        
        <div class="content">
            <?php if(isset($_GET['message'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
            <?php endif; ?>
            
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
            
            <a href="controller.php?action=create" class="btn">+ Add New Todo</a>
            
             <!-- In view.php, around line 206, replace the existing foreach section -->
<?php if(empty($todos) || !is_array($todos)): ?>
    <p style="text-align: center; color: #666;">No todos yet. Click "Add New Todo" to create your first task!</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($todos as $todo): ?>
                <tr>
                    <td><?php echo htmlspecialchars($todo['id']); ?></td>
                    <td><strong><?php echo htmlspecialchars($todo['title']); ?></strong></td>
                    <td><?php echo htmlspecialchars($todo['description'] ?? ''); ?></td>
                    <td>
                        <form method="POST" action="controller.php?action=update_status" class="status-form">
                            <input type="hidden" name="id" value="<?php echo $todo['id']; ?>">
                            <select name="status" onchange="this.form.submit()" class="status-select">
                                <option value="pending" <?php echo ($todo['status'] ?? 'pending') == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="in-progress" <?php echo ($todo['status'] ?? '') == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="completed" <?php echo ($todo['status'] ?? '') == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </form>
                    </td>
                    <td><?php echo date('Y-m-d H:i', strtotime($todo['created_at'] ?? 'now')); ?></td>
                    <td class="action-buttons">
                        <a href="controller.php?action=edit&id=<?php echo $todo['id']; ?>" class="btn">Edit</a>
                        <a href="controller.php?action=delete&id=<?php echo $todo['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this todo?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
        </div>
    </div>
</body>
</html>