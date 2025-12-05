<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: main.php");
    exit;
}

// Check if user has role 2
if (!isset($_SESSION['role']) || $_SESSION['role'] != 2) {
    // If not role 2, redirect based on role
    if ($_SESSION['role'] == 1) {
        header("Location: admin1.php");
    } else {
        header("Location: main.php");
    }
    exit;
}

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "wordpress";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle approval/decline actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['demand_id'])) {
        $demand_id = intval($_POST['demand_id']);
        $action = $_POST['action'];
        
        // Prepare status update
        if ($action === 'approve') {
            $status = 'approved';
            $message = "Demand #$demand_id has been approved.";
            $message_type = 'success';
        } elseif ($action === 'decline') {
            $status = 'declined';
            $message = "Demand #$demand_id has been declined.";
            $message_type = 'success';
        } elseif ($action === 'delete') {
            // Delete the demand
            $stmt = $conn->prepare("DELETE FROM demands WHERE id = ?");
            $stmt->bind_param("i", $demand_id);
            if ($stmt->execute()) {
                $message = "Demand #$demand_id has been deleted.";
                $message_type = 'success';
            } else {
                $message = "Error deleting demand: " . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
            
            // Refresh page to show updated list
            header("Location: admin2.php");
            exit;
        }
        
        // Update status if not delete action
        if (isset($status)) {
            $stmt = $conn->prepare("UPDATE demands SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $demand_id);
            if ($stmt->execute()) {
                $_SESSION['message'] = $message;
                $_SESSION['message_type'] = $message_type;
            } else {
                $_SESSION['message'] = "Error updating demand: " . $stmt->error;
                $_SESSION['message_type'] = 'error';
            }
            $stmt->close();
            
            // Refresh page to show updated list
            header("Location: admin2.php");
            exit;
        }
    }
}

// Get all demands from database
$query = "SELECT * FROM demands ORDER BY created_at DESC";
$result = $conn->query($query);

// Check if demands table exists, if not create it
if (!$result) {
    // Create demands table if it doesn't exist
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS demands (
        id INT AUTO_INCREMENT PRIMARY KEY,
        supplier VARCHAR(255) NOT NULL,
        user_name VARCHAR(255) NOT NULL,
        amount_tnd DECIMAL(10,2) NOT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($createTableSQL) === TRUE) {
        $result = $conn->query($query);
    }
}

// Get message from session if exists
$display_message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$display_message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : '';
unset($_SESSION['message'], $_SESSION['message_type']);

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin 2 - Gestion des Demandes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/admin1.css">
    <style>
        /* Additional styles for admin2 */
        .demands-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .demand-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-left: 4px solid;
            margin-bottom: 15px;
        }
        
        .demand-card.pending {
            border-left-color: #ffa500;
        }
        
        .demand-card.approved {
            border-left-color: #28a745;
        }
        
        .demand-card.declined {
            border-left-color: #dc3545;
        }
        
        .demand-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eaeaea;
        }
        
        .demand-id {
            font-weight: bold;
            color: #003f7f;
            font-size: 1.1rem;
        }
        
        .demand-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-declined {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .demand-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 4px;
        }
        
        .info-value {
            font-size: 1rem;
            font-weight: 500;
            color: #333;
        }
        
        .amount-value {
            font-size: 1.2rem;
            font-weight: bold;
            color: #003f7f;
        }
        
        .demand-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 15px;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .no-demands {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 10px;
            color: #6c757d;
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            background: #e9ecef;
            color: #495057;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 600;
        }
        
        .filter-btn.active {
            background: #003f7f;
            color: white;
        }
        
        .filter-btn:hover {
            background: #dee2e6;
        }
        
        .filter-btn.active:hover {
            background: #00264d;
        }
        
        .demand-date {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        
        .logout-btn:hover {
            filter: brightness(1.1);
        }
    </style>
</head>
<body>
<div class="page-wrapper">
    <header class="topbar">
        <h1 class="topbar-title">Admin 2 - Gestion des Demandes</h1>
        <p class="topbar-subtitle">Approuver ou refuser les demandes soumises via l'application mobile</p>
        <a href="logout.php" class="logout-btn">Déconnexion</a>
    </header>
    <br>

    <?php if ($display_message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($display_message_type); ?>">
            <?php echo htmlspecialchars($display_message); ?>
        </div>
    <?php endif; ?>

    <div class="admin-column">
        <h2 class="section-title">Demandes des Utilisateurs</h2>
        <p class="helper-text">Cliquez sur "Approuver" ou "Refuser" pour traiter chaque demande. Vous pouvez également supprimer une demande.</p>
        
        <div class="filter-buttons" id="filterButtons">
            <button class="filter-btn active" data-filter="all">Toutes</button>
            <button class="filter-btn" data-filter="pending">En attente</button>
            <button class="filter-btn" data-filter="approved">Approuvées</button>
            <button class="filter-btn" data-filter="declined">Refusées</button>
        </div>
        
        <div class="demands-container" id="demandsContainer">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php 
                    $status_class = $row['status'] ?? 'pending';
                    $status_text = $status_class;
                    $created_date = date('d/m/Y H:i', strtotime($row['created_at']));
                    ?>
                    <div class="demand-card <?php echo htmlspecialchars($status_class); ?>" data-status="<?php echo htmlspecialchars($status_class); ?>">
                        <div class="demand-header">
                            <div class="demand-id">Demande #<?php echo htmlspecialchars($row['id']); ?></div>
                            <span class="demand-status status-<?php echo htmlspecialchars($status_class); ?>">
                                <?php 
                                $status_labels = [
                                    'pending' => 'En attente',
                                    'approved' => 'Approuvée',
                                    'declined' => 'Refusée'
                                ];
                                echo htmlspecialchars($status_labels[$status_class] ?? $status_class);
                                ?>
                            </span>
                        </div>
                        
                        <div class="demand-info">
                            <div class="info-item">
                                <span class="info-label">Fournisseur</span>
                                <span class="info-value"><?php echo htmlspecialchars($row['supplier']); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Utilisateur</span>
                                <span class="info-value"><?php echo htmlspecialchars($row['user_name']); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Montant</span>
                                <span class="info-value amount-value"><?php echo number_format($row['amount_tnd'], 2); ?> TND</span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Date de soumission</span>
                                <span class="info-value"><?php echo htmlspecialchars($created_date); ?></span>
                            </div>
                        </div>
                        
                        <div class="demand-date">
                            Dernière mise à jour: <?php echo isset($row['updated_at']) ? date('d/m/Y H:i', strtotime($row['updated_at'])) : $created_date; ?>
                        </div>
                        
                        <?php if ($status_class == 'pending'): ?>
                            <div class="demand-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="demand_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Approuver cette demande ?')">
                                        Approuver
                                    </button>
                                </form>
                                
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="demand_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                    <input type="hidden" name="action" value="decline">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Refuser cette demande ?')">
                                        Refuser
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                        
                        <div class="demand-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="demand_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-warning" onclick="return confirm('Supprimer définitivement cette demande ?')">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-demands">
                    <h3>Aucune demande trouvée</h3>
                    <p>Les demandes soumises via l'application mobile apparaîtront ici.</p>
                    <p><small>Assurez-vous que la table "demands" existe dans la base de données.</small></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Filter demands by status
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const demands = document.querySelectorAll('.demand-card');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            
            // Filter demands
            demands.forEach(demand => {
                if (filter === 'all' || demand.getAttribute('data-status') === filter) {
                    demand.style.display = 'block';
                } else {
                    demand.style.display = 'none';
                }
            });
        });
    });
    
    // Auto-refresh page every 30 seconds to check for new demands
    setTimeout(function() {
        window.location.reload();
    }, 30000);
});
</script>
</body>
</html>