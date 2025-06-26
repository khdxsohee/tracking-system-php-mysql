<?php
include 'db_connect.php';

$message = "";
$order_data = null;

// Enable error reporting for debugging (temporary, remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = strtoupper(trim($_POST['order_id']));
    $customer_name = $_POST['customer_name'];
    $customer_phone = $_POST['customer_phone'];
    $customer_address = $_POST['customer_address'];
    $payment_status = isset($_POST['payment_status']) ? trim($_POST['payment_status']) : 'not_paid';
    $delivery_status = isset($_POST['delivery_status']) ? trim($_POST['delivery_status']) : 'pending';
    $admin_notes = $_POST['admin_notes'];

    // Log incoming values
    error_log("POST: Updating order_id=$order_id, payment_status=$payment_status, delivery_status=$delivery_status");

    // Order Items
    $order_items = [];
    $total_amount = 0;

    if (isset($_POST['item_name']) && is_array($_POST['item_name'])) {
        for ($i = 0; $i < count($_POST['item_name']); $i++) {
            $item_name = trim($_POST['item_name'][$i]);
            $item_qty = (int)$_POST['item_qty'][$i];
            $item_price = (float)$_POST['item_price'][$i];

            if (!empty($item_name) && $item_qty > 0 && $item_price >= 0) {
                $order_items[] = [
                    'name' => $item_name,
                    'qty' => $item_qty,
                    'price' => $item_price
                ];
                $total_amount += ($item_qty * $item_price);
            }
        }
    }

    $order_details_json = json_encode($order_items);

    // Check if order exists
    $stmt_check = $conn->prepare("SELECT id FROM orders WHERE order_id = ?");
    $stmt_check->bind_param("s", $order_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // UPDATE existing
        $stmt = $conn->prepare("UPDATE orders SET customer_name = ?, customer_phone = ?, customer_address = ?, order_details = ?, total_amount = ?, payment_status = ?, delivery_status = ?, admin_notes = ? WHERE order_id = ?");
        $stmt->bind_param("ssssdssss", $customer_name, $customer_phone, $customer_address, $order_details_json, $total_amount, $payment_status, $delivery_status, $admin_notes, $order_id);
        if ($stmt->execute()) {
            error_log("Order $order_id updated successfully.");
            $message = "<div style='color: green;'>Order <strong>" . htmlspecialchars($order_id) . "</strong> updated successfully!</div>";
        } else {
            error_log("Error updating order $order_id: " . $stmt->error);
            $message = "<div style='color: red;'>Error updating order: " . $stmt->error . "</div>";
        }
    } else {
        // INSERT new
        $stmt = $conn->prepare("INSERT INTO orders (order_id, customer_name, customer_phone, customer_address, order_details, total_amount, payment_status, delivery_status, admin_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssdsss", $order_id, $customer_name, $customer_phone, $customer_address, $order_details_json, $total_amount, $payment_status, $delivery_status, $admin_notes);
        if ($stmt->execute()) {
            error_log("New order $order_id inserted successfully.");
            $message = "<div style='color: green;'>New order added successfully! Order ID: <strong>" . htmlspecialchars($order_id) . "</strong></div>";
        } else {
            error_log("Error inserting new order $order_id: " . $stmt->error);
            $message = "<div style='color: red;'>Error adding order: " . $stmt->error . "</div>";
        }
    }

    $stmt->close();

    // Reload latest data
    $stmt_load = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmt_load->bind_param("s", $order_id);
    $stmt_load->execute();
    $result_load = $stmt_load->get_result();

    if ($result_load->num_rows > 0) {
        $order_data = $result_load->fetch_assoc();
        $order_data['order_items_decoded'] = json_decode($order_data['order_details'], true);
        error_log("Reloaded order $order_id: Payment = " . $order_data['payment_status'] . ", Delivery = " . $order_data['delivery_status']);
    }

    $stmt_load->close();

} elseif (isset($_GET['load_id'])) {
    $load_id = strtoupper(trim($_GET['load_id']));
    $stmt_load = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmt_load->bind_param("s", $load_id);
    $stmt_load->execute();
    $result_load = $stmt_load->get_result();
    if ($result_load->num_rows > 0) {
        $order_data = $result_load->fetch_assoc();
        $order_data['order_items_decoded'] = json_decode($order_data['order_details'], true);
        error_log("Loaded order $load_id successfully.");
    } else {
        $message = "<div style='color: orange;'>Order ID '<strong>" . htmlspecialchars($load_id) . "</strong>' not found for loading. You can add it as a new order.</div>";
        error_log("Order ID $load_id not found.");
    }
    $stmt_load->close();
}

$conn->close();
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add/Update Order - Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: auto; padding: 25px; border: 1px solid #ccc; border-radius: 8px; background-color: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2, h3 { text-align: center; color: #333; margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"], input[type="number"], textarea, select {
            width: calc(100% - 22px); padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;
        }
        input[type="text"]:focus, input[type="number"]:focus, textarea:focus, select:focus {
            border-color: #88b3e8; outline: none; box-shadow: 0 0 5px rgba(136,179,232,0.5);
        }
        .item-row { display: flex; gap: 10px; margin-bottom: 10px; align-items: center; }
        .item-row input { flex: 1; }
        .item-row button { padding: 8px 12px; background-color: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .item-row button:hover { background-color: #c82333; }
        button[type="submit"] {
            background-color: #28a745; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; width: 100%;
        }
        button[type="submit"]:hover { background-color: #218838; }
        .button-group { text-align: center; margin-top: 20px; }
        .button-group button {
            background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; margin: 0 5px;
        }
        .button-group button:hover { background-color: #0056b3; }
        .message { margin-bottom: 20px; padding: 12px; border-radius: 4px; text-align: center; }
        .message div[style*="green"] { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .message div[style*="red"] { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .message div[style*="orange"] { background-color: #fff3cd; border-color: #ffeeba; color: #856404; }

        .load-order-section {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 5px;
        }
        .load-order-section input {
            width: 200px; /* Adjust width as needed */
            margin-right: 10px;
            margin-bottom: 0; /* Override default margin */
        }
        .load-order-section button {
            padding: 10px 15px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .load-order-section button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add / Update Order</h2>
        <p style="text-align: center; color: #666;">Enter an existing Order ID to update, or a new one to add.</p>

        <div class="load-order-section">
            <input type="text" id="load_order_id" placeholder="Enter Order ID to Load" maxlength="6" value="<?php echo isset($_GET['load_id']) ? htmlspecialchars($_GET['load_id']) : ''; ?>">
            <button onclick="loadOrder()">Load Order</button>
        </div>

        <?php echo $message; ?>

        <form action="add_order.php" method="POST">
            <label for="order_id">Order ID (6 characters - e.g., BHD456):</label>
            <input type="text" id="order_id" name="order_id" maxlength="6" required value="<?php echo $order_data ? htmlspecialchars($order_data['order_id']) : ''; ?>">

            <label for="customer_name">Customer Name:</label>
            <input type="text" id="customer_name" name="customer_name" required value="<?php echo $order_data ? htmlspecialchars($order_data['customer_name']) : ''; ?>">

            <label for="customer_phone">Customer Phone:</label>
            <input type="text" id="customer_phone" name="customer_phone" required value="<?php echo $order_data ? htmlspecialchars($order_data['customer_phone']) : ''; ?>">

            <label for="customer_address">Customer Address:</label>
            <textarea id="customer_address" name="customer_address" rows="3" required><?php echo $order_data ? htmlspecialchars($order_data['customer_address']) : ''; ?></textarea>

            <h3>Order Items:</h3>
            <div id="items_container">
                <?php if ($order_data && !empty($order_data['order_items_decoded'])): ?>
                    <?php foreach ($order_data['order_items_decoded'] as $item): ?>
                        <div class="item-row">
                            <input type="text" name="item_name[]" placeholder="Item Name" value="<?php echo htmlspecialchars($item['name']); ?>">
                            <input type="number" name="item_qty[]" placeholder="Quantity" min="1" value="<?php echo htmlspecialchars($item['qty']); ?>">
                            <input type="number" name="item_price[]" placeholder="Price per unit" step="0.01" min="0" value="<?php echo htmlspecialchars($item['price']); ?>">
                            <button type="button" onclick="removeItem(this)">X</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="item-row">
                        <input type="text" name="item_name[]" placeholder="Item Name (e.g., Mutton Chops)">
                        <input type="number" name="item_qty[]" placeholder="Quantity" min="1">
                        <input type="number" name="item_price[]" placeholder="Price per unit" step="0.01" min="0">
                        <button type="button" onclick="removeItem(this)">X</button>
                    </div>
                <?php endif; ?>
            </div>
            <div class="button-group" style="text-align: left;">
                <button type="button" onclick="addItem()">Add Another Item</button>
            </div>
            <br>

            <label for="payment_status">Payment Status:</label>
<select id="payment_status" name="payment_status">
    <option value="not_paid" <?php echo ($order_data && $order_data['payment_status'] == 'not_paid') ? 'selected' : ''; ?>>Not Paid</option>
    <option value="half_paid" <?php echo ($order_data && $order_data['payment_status'] == 'half_paid') ? 'selected' : ''; ?>>Half Paid</option>
    <option value="full_paid" <?php echo ($order_data && $order_data['payment_status'] == 'full_paid') ? 'selected' : ''; ?>>Full Paid</option>
    <option value="wire_transfer" <?php echo ($order_data && $order_data['payment_status'] == 'wire_transfer') ? 'selected' : ''; ?>>Wire Transfer</option>
</select>


            <label for="delivery_status">Delivery Status:</label>
            <select id="delivery_status" name="delivery_status">
                <option value="pending" <?php echo ($order_data && $order_data['delivery_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="processing" <?php echo ($order_data && $order_data['delivery_status'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                <option value="out_for_delivery" <?php echo ($order_data && $order_data['delivery_status'] == 'out_for_delivery') ? 'selected' : ''; ?>>Out for Delivery</option>
                <option value="delivered" <?php echo ($order_data && $order_data['delivery_status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                <option value="cancelled" <?php echo ($order_data && $order_data['delivery_status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
            </select>
           

            <label for="admin_notes">Admin Notes:</label>
            <textarea id="admin_notes" name="admin_notes" rows="3"><?php echo $order_data ? htmlspecialchars($order_data['admin_notes']) : ''; ?></textarea>

            <button type="submit">Save Order</button>
        </form>
    </div>

    <script>
        function addItem() {
            const container = document.getElementById('items_container');
            const newRow = document.createElement('div');
            newRow.classList.add('item-row');
            newRow.innerHTML = `
                <input type="text" name="item_name[]" placeholder="Item Name">
                <input type="number" name="item_qty[]" placeholder="Quantity" min="1">
                <input type="number" name="item_price[]" placeholder="Price per unit" step="0.01" min="0">
                <button type="button" onclick="removeItem(this)">X</button>
            `;
            container.appendChild(newRow);
        }

        function removeItem(button) {
            const row = button.parentNode;
            if (document.querySelectorAll('.item-row').length > 1) { // Ensure at least one item row remains
                row.parentNode.removeChild(row);
            } else {
                alert("You must have at least one item in the order.");
            }
        }

        function loadOrder() {
            const loadOrderId = document.getElementById('load_order_id').value.trim().toUpperCase();
            if (loadOrderId.length === 6) {
                window.location.href = 'add_order.php?load_id=' + loadOrderId;
            } else {
                alert("Please enter a valid 6-character Order ID to load.");
            }
        }

        // Set focus to the Order ID field on page load
        window.onload = function() {
            document.getElementById('order_id').focus();
        };
    </script>
</body>
</html>