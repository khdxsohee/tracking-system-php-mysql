<?php
// track_order.php
include 'db_connect.php'; // Database connection include karein

$order_details = null;
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $track_id = strtoupper(trim($_POST['track_id']));

    if (strlen($track_id) == 6 && ctype_alnum($track_id)) { // Validate 6 alphanumeric characters
        $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
        $stmt->bind_param("s", $track_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $order_details = $result->fetch_assoc();
            // Database se retrieve hone ke baad order_details ko decode karein
            $order_details['order_items_decoded'] = json_decode($order_details['order_details'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error_message = "Error decoding order items. Please contact support.";
                $order_details['order_items_decoded'] = []; // Error ki soorat mein khali array
            }
        } else {
            $error_message = "Order ID '<strong>" . htmlspecialchars($track_id) . "</strong>' not found. Please check and try again.";
        }
        $stmt->close();
    } else {
        $error_message = "Please enter a valid 6-character Order ID (alphanumeric only).";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Order</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: auto; background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; margin-bottom: 25px;}
        form { display: flex; justify-content: center; margin-bottom: 25px; }
        input[type="text"] {
            padding: 10px 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; width: 70%; max-width: 300px; margin-right: 10px; box-sizing: border-box;
        }
        input[type="text"]:focus {
            border-color: #88b3e8; outline: none; box-shadow: 0 0 5px rgba(136,179,232,0.5);
        }
        button[type="submit"] {
            background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;
        }
        button[type="submit"]:hover { background-color: #0056b3; }
        .error-message { color: red; text-align: center; margin-top: 15px; padding: 10px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; }
        .order-info { border: 1px solid #eee; padding: 20px; border-radius: 5px; background-color: #f9f9f9; margin-top: 20px; }
        .order-info h3 { text-align: left; margin-top: 0; color: #007bff; }
        .order-info p { margin-bottom: 8px; line-height: 1.5; }
        .order-info strong { color: #555; }
        .order-info ul { list-style: none; padding: 0; }
        .order-info ul li {
            background-color: #e9ecef;
            padding: 8px 12px;
            margin-bottom: 5px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .order-info ul li span { font-weight: bold; color: #007bff; }

        .status-badge {
            display: inline-block; padding: 5px 10px; border-radius: 15px; font-weight: bold; color: white;
            text-transform: capitalize; margin-left: 10px;
        }
        /* Delivery Status Colors */
        .status-pending { background-color: #ffc107; color: #333; } /* Yellow */
        .status-processing { background-color: #17a2b8; } /* Light Blue */
        .status-out_for_delivery { background-color: #007bff; } /* Blue */
        .status-delivered { background-color: #28a745; } /* Green */
        .status-cancelled { background-color: #dc3545; } /* Red */

 /* Payment Status Colors */
.payment-not_paid { background-color: #dc3545; } /* Red */
.payment-half_paid { background-color: #ffc107; color: #333; } /* Yellow */
.payment-full_paid { background-color: #28a745; } /* Green */
.payment-wire_transfer { background-color: #6610f2; } /* Purple */
    </style>
</head>
<body>
    <div class="container">
        <h2>Track Your Order</h2>
        <form action="track_order.php" method="POST">
            <input type="text" name="track_id" placeholder="Enter your 6-character Order ID" required maxlength="6">
            <button type="submit">Track Order</button>
        </form>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if ($order_details): ?>
            <div class="order-info">
                <h3>Order Details for #<?php echo htmlspecialchars($order_details['order_id']); ?></h3>
                <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order_details['customer_name']); ?></p>
                <p><strong>Customer Phone:</strong> <?php echo htmlspecialchars($order_details['customer_phone']); ?></p>
                <p><strong>Total Amount:</strong> PKR <?php echo number_format($order_details['total_amount'], 2); ?></p>
                
                <p>
    <strong>Payment Status:</strong>
    <span class="status-badge payment-<?php echo htmlspecialchars($order_details['payment_status']); ?>">
        <?php echo str_replace('_', ' ', ucfirst(htmlspecialchars($order_details['payment_status']))); ?>
    </span>
</p>
                <p>
                    <strong>Delivery Status:</strong>
                    <span class="status-badge status-<?php echo htmlspecialchars($order_details['delivery_status']); ?>">
                        <?php echo str_replace('_', ' ', htmlspecialchars($order_details['delivery_status'])); ?>
                    </span>
                </p>

                <h4>Ordered Items:</h4>
                <ul>
                    <?php if (!empty($order_details['order_items_decoded'])): ?>
                        <?php foreach ($order_details['order_items_decoded'] as $item): ?>
                            <li>
                                <span><?php echo htmlspecialchars($item['name'] ?? 'N/A'); ?></span>
                                <span>Qty: <?php echo htmlspecialchars($item['qty'] ?? 0); ?></span>
                                <span>Price: PKR <?php echo number_format($item['price'] ?? 0, 2); ?> each</span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No items found for this order.</li>
                    <?php endif; ?>
                </ul>

                <?php if (!empty($order_details['admin_notes'])): ?>
                    <p><strong>Admin Notes:</strong><br><?php echo nl2br(htmlspecialchars($order_details['admin_notes'])); ?></p>
                <?php endif; ?>
                <p style="text-align: right;"><small>Last Updated: <?php echo date('d M, Y h:i A', strtotime($order_details['updated_at'])); ?></small></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>