<?php



// Initialize messages array
$messages = [];


// ============================================
// HANDLE ACCEPT DEMAND
// ============================================
if (isset($_POST['accept_demand'])) {
    $demand_id = intval($_POST['demand_id']);
    $message = "Demand #". " accepted successfully!";
    $messages[] = [
        'type' => 'success',
        'text' => $message
    ];
}


// ============================================
// HANDLE REFUSE DEMAND
// ============================================
if (isset($_POST['refuse_demand'])) {
    $demand_id = intval($_POST['refuse_id']);
    $csv_file = 'demands.csv';
    
    // Read all demands
    $demands_data = [];
    if (file_exists($csv_file)) {
        if (($handle = fopen($csv_file, 'r')) !== false) {
            $header = fgetcsv($handle);
            $demands_data[] = $header; // Keep header
            
            while (($row = fgetcsv($handle)) !== false) {
                // Skip the refused demand
                if ($row[0] != $demand_id) {
                    $demands_data[] = $row;
                }
            }
            fclose($handle);
            
            // Write back to CSV without the refused demand
            if (($handle = fopen($csv_file, 'w')) !== false) {
                foreach ($demands_data as $row) {
                    fputcsv($handle, $row);
                }
                fclose($handle);
            }
        }
    }
    
    $message = "Demand #" . $demand_id . " refused and removed successfully!";
    $messages[] = [
        'type' => 'success',
        'text' => $message
    ];
}


// ============================================
// LOAD DEMANDS FROM CSV
// ============================================
$demands = [];
$csv_file = 'demands.csv';


if (file_exists($csv_file)) {
    if (($handle = fopen($csv_file, 'r')) !== false) {
        $header = fgetcsv($handle); // Skip header row
        
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 3) {
                $demands[] = [
                    'id' => $row[0],
                    'supplier' => $row[1],
                    'amount' => $row[2],
                    'status' => 'pending',
                    'employee_name' => $row[3] ?? null,
                    'email' => $row[4] ?? null,
                    'seniority' => $row[5] ?? null,
                    'absence' => $row[6] ?? null,
                    'advance' => $row[7] ?? null,
                    'salary_range' => $row[8] ?? null,
                    'created_at' => $row[9] ?? date('Y-m-d H:i:s')
                ];
            }
        }
        fclose($handle);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Demands Management</title>
    <link rel="stylesheet" href="admin1.css">
    <style>
        /* Additional styles for demands page */
        .demands-container {
            margin-top: 20px;
        }


        .demand-card {
            background: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 18px;
            margin-bottom: 16px;
            transition: all 0.3s ease;
        }

        .demand-card.removing {
            opacity: 0;
            transform: slideOutLeft 0.3s ease;
            margin-bottom: 0;
            max-height: 0;
            padding: 0;
            overflow: hidden;
        }

        .demand-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }


        .demand-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
            border-bottom: 2px solid #003f7f;
            padding-bottom: 10px;
        }


        .demand-id {
            font-weight: 700;
            color: #003f7f;
            font-size: 1.1rem;
        }


        .demand-date {
            font-size: 0.85rem;
            color: #888;
        }


        .demand-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            margin-bottom: 16px;
        }


        .info-block {
            background: white;
            padding: 12px;
            border-radius: 6px;
            border-left: 4px solid #00a2c7;
        }


        .info-label {
            font-size: 0.8rem;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 4px;
        }


        .info-value {
            font-size: 0.95rem;
            color: #1a1a1a;
            font-weight: 500;
        }


        .demand-actions {
            display: flex;
            gap: 10px;
            margin-top: 16px;
        }


        .btn-accept, .btn-refuse {
            flex: 1;
            padding: 10px 14px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }


        .btn-accept {
            background: linear-gradient(135deg, #00a86b, #00d084);
            color: white;
        }


        .btn-accept:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 168, 107, 0.3);
        }


        .btn-refuse {
            background: linear-gradient(135deg, #d0002b, #ff3557);
            color: white;
        }


        .btn-refuse:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(208, 0, 43, 0.3);
        }


        .btn-accept:active, .btn-refuse:active {
            transform: translateY(0);
        }


        .no-demands {
            text-align: center;
            padding: 40px 20px;
            color: #999;
            font-size: 1.1rem;
        }


        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }


        .status-pending {
            background: #fff3cd;
            color: #856404;
        }


        .status-accepted {
            background: #d4edda;
            color: #155724;
        }


        .status-refused {
            background: #f8d7da;
            color: #721c24;
        }

        @keyframes slideOutLeft {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(-100%);
            }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <!-- Topbar -->
        <div class="topbar">
            <h1 class="topbar-title">📋 Demands Management</h1>
            <p class="topbar-subtitle">Review and manage pending demands from suppliers</p>
        </div>


        <!-- Alert Messages -->
        <?php if (!empty($messages)): ?>
            <div class="alerts-container">
                <?php foreach ($messages as $msg): ?>
                    <div class="alert alert-<?php echo $msg['type']; ?>">
                        <?php echo htmlspecialchars($msg['text']); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>


        <!-- Demands Container -->
        <div class="admin-column">
            <h2 class="section-title">All Demands</h2>
            <p class="helper-text">Total demands from suppliers. Click "Accept" or "Refuse" to manage each demand.</p>


            <?php if (empty($demands)): ?>
                <div class="no-demands">
                    ✓ No pending demands at this time.
                </div>
            <?php else: ?>
                <div class="demands-container">
                    <?php foreach ($demands as $demand): ?>
                        <div class="demand-card" data-demand-id="<?php echo $demand['id']; ?>">
                            <!-- Header with ID and Date -->
                            <div class="demand-header">
                                <div>
                                    <span class="demand-id">Demand #<?php echo htmlspecialchars($demand['id']); ?></span>
                                    <span class="status-badge status-<?php echo htmlspecialchars($demand['status']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($demand['status'])); ?>
                                    </span>
                                </div>
                                <span class="demand-date"><?php echo date('Y-m-d H:i', strtotime($demand['created_at'] ?? 'now')); ?></span>
                            </div>


                            <!-- Demand Information Grid -->
                            <div class="demand-info-grid">
                                <div class="info-block">
                                    <div class="info-label">Supplier</div>
                                    <div class="info-value"><?php echo htmlspecialchars($demand['supplier']); ?></div>
                                </div>


                                <div class="info-block">
                                    <div class="info-label">Amount (TND)</div>
                                    <div class="info-value"><?php echo number_format($demand['amount'], 2); ?> TND</div>
                                </div>


                                <div class="info-block">
                                    <div class="info-label">Employee Name</div>
                                    <div class="info-value"><?php echo htmlspecialchars($demand['employee_name'] ?? 'N/A'); ?></div>
                                </div>


                                <div class="info-block">
                                    <div class="info-label">Email</div>
                                    <div class="info-value"><?php echo htmlspecialchars($demand['email'] ?? 'N/A'); ?></div>
                                </div>


                                <div class="info-block">
                                    <div class="info-label">Seniority</div>
                                    <div class="info-value"><?php echo htmlspecialchars($demand['seniority'] ?? 'N/A'); ?></div>
                                </div>


                                <div class="info-block">
                                    <div class="info-label">Absence</div>
                                    <div class="info-value"><?php echo htmlspecialchars($demand['absence'] ?? 'N/A'); ?></div>
                                </div>


                                <div class="info-block">
                                    <div class="info-label">Advance</div>
                                    <div class="info-value"><?php echo htmlspecialchars($demand['advance'] ?? 'N/A'); ?></div>
                                </div>


                                <div class="info-block">
                                    <div class="info-label">Salary Range</div>
                                    <div class="info-value"><?php echo htmlspecialchars($demand['salary_range'] ?? 'N/A'); ?></div>
                                </div>
                            </div>


                            <!-- Action Buttons -->
                            <div class="demand-actions">
                                <!-- Accept Form -->
                                <form method="POST" style="flex: 1;">
                                    <input type="hidden" name="accept_demand" value="1">
                                    <input type="hidden" name="demand_id" value="<?php echo $demand['id']; ?>">
                                    <button type="submit" class="btn-accept" style="color: green;">✓ Accept</button>
                                    
                                </form>


                                <!-- Refuse Form -->
                                <form method="POST" style="flex: 1;" class="refuse-form">
                                    <input type="hidden" name="refuse_demand" value="1">
                                    <input type="hidden" name="refuse_id" value="<?php echo $demand['id']; ?>">
                                    <button type="button" class="btn-refuse refuse-btn" data-demand-id="<?php echo $demand['id']; ?>">✕ Refuse</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    document.querySelectorAll('.refuse-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const demandId = this.getAttribute('data-demand-id');
            const demandCard = document.querySelector(`[data-demand-id="${demandId}"]`);
            
            // Create FormData
            const formData = new FormData();
            formData.append('refuse_demand', '1');
            formData.append('refuse_id', demandId);
            
            // Send AJAX request
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Remove card immediately from DOM
                demandCard.remove();
            })
            .catch(error => console.log('Error:', error));
        });
    });
</script>

</body>
</html>
