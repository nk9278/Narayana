<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check admin authentication here in production
if (!isset($_SESSION['admin_id'])) {
    header("Location: /admin/login.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Narayan Jewelers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            cultured: '#F2F4F3',
                            wine: '#65202F',
                            burgundy: '#49111C',
                            gold: '#D6B36A',
                            lightgold: '#E3C98B'
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; color: #1f2937; }
        .sidebar { transition: transform 0.3s ease-in-out; }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    <!-- Sidebar Included Below -->
