<?php
session_start();
date_default_timezone_set('Asia/Kolkata'); // Set default time zone to IST
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'canteen') {
    header("Location: login.php");
    exit();
}

// Logout the user
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Load existing bills from JSON file
$filename = 'data.json';
if (file_exists($filename)) {
    $bills = json_decode(file_get_contents($filename), true);
    if ($bills === null) {
        die('Error decoding JSON file.');
    }
} else {
    $bills = ["Shuja" => [], "Yusuf" => [], "Faizan" => [], "Farhan" => []];
}

// Save bill to JSON file
function saveBill($bills)
{
    global $filename;
    file_put_contents($filename, json_encode($bills, JSON_PRETTY_PRINT));
}

// Handle bill addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_bill'])) {
    $person = $_POST['person'];
    $items = $_POST['items'];
    $total = 0;
    $validItems = false;

    foreach ($items as $item => $quantity) {
        if ($quantity['quantity'] > 0) {
            $validItems = true;
            $total += $quantity['price'] * $quantity['quantity'];
        }
    }

    if ($validItems) {
        $bills[$person][] = [
            'date' => date('Y-m-d H:i:s'),
            'items' => $items,
            'total' => $total,
            'paid' => 0,
            'remaining' => $total,
            'payments' => []
        ];

        saveBill($bills);
    }
}

// Handle payment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pay_bill'])) {
    $person = $_POST['person'];
    $billIndex = $_POST['bill_index'];
    $amountPaid = $_POST['amount_paid'];

    // Ensure the amount paid does not exceed the remaining amount
    if ($amountPaid > $bills[$person][$billIndex]['remaining']) {
        $amountPaid = $bills[$person][$billIndex]['remaining'];
    }

    $bills[$person][$billIndex]['paid'] += $amountPaid;
    $bills[$person][$billIndex]['remaining'] -= $amountPaid;

    // Record payment date and amount
    $bills[$person][$billIndex]['payments'][] = [
        'date' => date('Y-m-d h:i:s a'),
        'amount' => $amountPaid
    ];

    saveBill($bills);
}

// Handle bill deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_bill'])) {
    $person = $_POST['person'];
    $billIndex = $_POST['bill_index'];

    if (isset($bills[$person][$billIndex])) {
        array_splice($bills[$person], $billIndex, 1);
        saveBill($bills);
    }
}

// Handle delete all bills for a person
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_all_bills'])) {
    $person = $_POST['person'];
    if (isset($bills[$person])) {
        $bills[$person] = [];
        saveBill($bills);
    }
}

// Define items
$items = [
    'chai' => 10,
    'biryani' => 50,
    'cutting-chai' => 5,
    'samosa' => 10,
    'meetha_samosa' => 15,
    'bread_pakora' => 15,
    'egg_bread' => 25,
    'poori_sabzi' => 30,
    'parantha' => 15,
    'cigarette' => 18,
    'sahi_toast' => 15,
    'gulab_jamun' => 15
];

// Calculate total bill amount for each person
$personTotals = [];
foreach ($bills as $person => $personBills) {
    $totalAmount = 0;
    foreach ($personBills as $bill) {
        $totalAmount += $bill['total'];
    }
    $personTotals[$person] = $totalAmount;
}
// Handle marking bill as paid
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_as_paid'])) {
    $person = $_POST['person'];
    $billIndex = $_POST['bill_index'];

    if (isset($bills[$person][$billIndex])) {
        $totalAmount = $bills[$person][$billIndex]['total'];
        $bills[$person][$billIndex]['paid'] = $totalAmount;
        $bills[$person][$billIndex]['remaining'] = 0;

        // Record payment date and amount
        $bills[$person][$billIndex]['payments'][] = [
            'date' => date('Y-m-d h:i:s a'),
            'amount' => $totalAmount
        ];

        saveBill($bills);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canteen Dashboard</title>
    <style>
    :root {
        --primary: rgba(135, 100, 255, .9);
        --background: #f9f9f9;
        --card-background: #ffffff;
        --text-color: #333;
        --border-color: #ddd;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: var(--background);
        margin: 0;
        font-size: 18px;
        padding: 20px;
    }

    .container {
        max-width: 1000px;
        margin: auto;
        background: var(--card-background);
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
    }

    h1 {
        text-align: center;
        margin-bottom: 20px;
        font-size: 28px;
        color: var(--text-color);
    }

    .button {
        background-color: var(--primary);
        color: white;
        padding: 8px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 18px;
        margin-bottom: 20px;
    }

    .button:hover {
        background-color: rgba(135, 100, 255, .7);
    }

    .logout-btn {
        background-color: var(--primary);
        color: white;
        text-align: right;
        margin-top: 0;
    }

    .add-bill-container {
        display: none;
    }

    .add-bill-container,
    .bill {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        margin-bottom: 25px;
        background-color: var(--card-background);
        padding: 20px;
    }

    .item-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .item-row label {
        flex: 1;
        color: var(--text-color);
    }

    .item-row select,
    .item-row input {
        flex: 0 0 10%;
        text-align: center;
    }

    .bill h3 {
        margin: 0;
        margin-bottom: 8px;
        color: var(--text-color);
    }

    .bill-details {
        margin-bottom: 8px;
    }

    .bill-total,
    .bill p {
        margin: 0;
        color: var(--text-color);
    }

    .pay-bill-container input[type="number"] {
        width: 80px;
        margin-right: 10px;
    }

    .delete-button {
        color: var(--primary);
        background: none;
        border: none;
        cursor: pointer;
        font-size: 16px;
    }

    .delete-button:hover {
        text-decoration: underline;
    }

    .add-person-container,
    .remove-person-container {
        margin-top: 20px;
    }

    .add-person-container input[type="text"],
    .remove-person-container select {
        padding: 5px;
        border-radius: 4px;
        border: 1px solid var(--border-color);
    }

    .add-person-container button,
    .remove-person-container button {
        background-color: var(--primary);
        color: white;
        padding: 8px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }

    .remove-person-container button {
        background-color: red;
    }

    .remove-person-container button:hover {
        background-color: darkred;
    }

    select {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        font-size: var(--font-size-large);
        color: var(--text-color);
        background-color: var(--card-background);
        transition: border-color 0.3s ease, background-color 0.3s ease;
    }

    select:focus {
        border-color: var(--primary);
        background-color: rgba(135, 100, 255, 0.1);
        outline: none;
    }

    label {
        font-size: var(--font-size-large);
        color: var(--text-color);
        margin-bottom: 10px;
        display: block;
    }

    .bill {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        margin-bottom: 25px;
        background-color: var(--card-background);
        padding: 20px;
        line-height: 1.6;
    }

    .bill-title {
        margin: 0 0 12px;
        font-size: 22px;
        color: var(--text-color);
    }

    .bill-details {
        margin-bottom: 12px;
    }

    .bill-items-title {
        margin-bottom: 8px;
        font-size: 18px;
        color: var(--text-color);
    }

    .bill-items p {
        margin: 4px 0;
        padding-left: 10px;
        border-left: 3px solid var(--primary);
        font-size: 16px;
        color: var(--text-color);
    }

    .bill-total,
    .bill-payment-status {
        margin: 8px 0;
        font-size: 18px;
        font-weight: bold;
        color: var(--text-color);
    }

    .bill-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
    }

    .bill-actions .pay-bill-container input[type="number"] {
        width: 100px;
        padding: 5px;
        font-size: 16px;
        border-radius: 4px;
        border: 1px solid var(--border-color);
    }

    .bill-actions .pay-bill-container .button {
        padding: 8px 16px;
        margin-left: 10px;
        font-size: 16px;
    }

    .bill-actions .delete-button {
        font-size: 16px;
        color: red;
    }

    .bill-actions .delete-button:hover {
        text-decoration: underline;
    }

    /* Responsive styling for small screens */
    @media (max-width: 768px) {
        body {
            font-size: 16px;
            padding: 10px;
        }

        .container {
            padding: 15px;
        }

        h1 {
            font-size: 24px;
        }

        .button {
            padding: 6px 10px;
            font-size: 16px;
        }

        .bill-title {
            font-size: 20px;
        }

        .bill-items p {
            font-size: 14px;
        }

        .bill-total,
        .bill-payment-status {
            font-size: 16px;
        }

        select {
            padding: 8px;
            font-size: 16px;
        }

        .bill-actions .pay-bill-container input[type="number"] {
            width: 70px;
            padding: 4px;
            font-size: 14px;
        }

        .bill-actions .pay-bill-container .button {
            padding: 6px 12px;
            font-size: 14px;
        }

        .item-row select,
        .item-row input {
            flex: 0 0 20%;
            font-size: 14px;
        }

        .item-row label {
            font-size: 16px;
        }
    }

    /* Further adjustments for very small screens */
    @media (max-width: 480px) {
        body {
            font-size: 14px;
        }

        .container {
            padding: 10px;
        }

        h1 {
            font-size: 20px;
        }

        .button {
            padding: 5px 8px;
            font-size: 14px;
        }

        .bill-title {
            font-size: 18px;
        }

        .bill-items p {
            font-size: 12px;
        }

        .bill-total,
        .bill-payment-status {
            font-size: 14px;
        }

        select {
            padding: 4px;
            font-size: 14px;
        }

        .bill-actions .pay-bill-container input[type="number"] {
            width: 60px;
            padding: 3px;
            font-size: 12px;
        }

        .bill-actions .pay-bill-container .button {
            padding: 5px 10px;
            font-size: 12px;
        }

        .item-row select,
        .item-row input {
            flex: 0 0 30%;
            font-size: 12px;
        }

        .item-row label {
            font-size: 14px;
        }
    }
</style>

</head>

<body>
    <div class="container">
        <h1>VM Canteen Dashboard</h1>
        <!-- Logout Button -->
        <form action="dashboard.php" method="POST" style="text-align: right;">
            <input type="hidden" name="logout" value="1">
            <button type="submit" class="button logout-btn">Logout</button>
        </form>
        <!-- New section for UPI payment -->
        <div class="upi-payment-info" style="margin-bottom: 20px;">
            <p><strong>Canteen UPI ID:</strong> paytmqriyfgmimrkp@paytm</p>
        </div>
        <button class="button" onclick="toggleAddBill()">Add Bill</button>
        <div class="add-bill-container">
            <form action="dashboard.php" method="POST">
                <input type="hidden" name="add_bill" value="1">
                <div>
                    <label for="person">Select Person:</label>
                    <select name="person" id="person" required>
                        <option value="Shuja">Shuja</option>
                        <option value="Yusuf">Yusuf</option>
                        <option value="Faizan">Faizan</option>
                        <option value="Farhan">Farhan</option>
                    </select>
                </div>
                <br>
                <?php foreach ($items as $item => $price): ?>
                    <div class="item-row">
                        <label for="item_<?php echo $item; ?>"><?php echo ucfirst(str_replace('_', ' ', $item)); ?>
                            (₹<?php echo $price; ?>)</label>
                        <select name="items[<?php echo $item; ?>][quantity]" id="item_<?php echo $item; ?>">
                            <?php for ($i = 0; $i <= 10; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                        <input type="hidden" name="items[<?php echo $item; ?>][price]" value="<?php echo $price; ?>">
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="button">Add Items</button>
            </form>
        </div>
        <h2>Existing Bills</h2>
        <?php foreach ($bills as $person => $personBills): ?>
            <h3>
                <?php echo $person; ?>
                <?php if (count($personBills) > 1): ?>
                    <span>(Total: ₹<?php echo $personTotals[$person]; ?>)</span>
                <?php endif; ?>
                <form action="dashboard.php" method="POST" style="display:inline;">
                    <input type="hidden" name="delete_all_bills" value="1">
                    <input type="hidden" name="person" value="<?php echo $person; ?>">
                    <button type="submit" class="delete-button">Delete All Bills</button>
                </form>
            </h3>
            <?php if (empty($personBills)): ?>
                <p>No bills found for <?php echo $person; ?>.</p>
            <?php else: ?>
                <?php foreach ($personBills as $index => $bill): ?>
    <div class="bill">
        <h3>Bill #<?php echo $index + 1; ?> - Date: <?php echo date('d-m-Y h:i:s a', strtotime($bill['date'])); ?>
        </h3>
        <div class="bill-details">
            <p><strong>Items:</strong></p>
            <?php foreach ($bill['items'] as $item => $details): ?>
                <?php if ($details['quantity'] > 0): ?>
                    <p><?php echo ucfirst(str_replace('_', ' ', $item)); ?>: <?php echo $details['quantity']; ?> x
                        ₹<?php echo $details['price']; ?> = ₹<?php echo $details['quantity'] * $details['price']; ?></p>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <p class="bill-total">Total: ₹<?php echo $bill['total']; ?></p>
        <p>Paid: ₹<?php echo $bill['paid']; ?> | Remaining: ₹<?php echo $bill['remaining']; ?></p>
        <form action="dashboard.php" method="POST" class="pay-bill-container">
            <input type="hidden" name="pay_bill" value="1">
            <input type="hidden" name="person" value="<?php echo $person; ?>">
            <input type="hidden" name="bill_index" value="<?php echo $index; ?>">
            <input type="number" name="amount_paid" min="0" max="<?php echo $bill['remaining']; ?>" required>
            <button type="submit" class="button">Pay</button>
        </form>
        <?php if (!empty($bill['payments'])): ?>
            <h4>Payment History:</h4>
            <ul>
                <?php foreach ($bill['payments'] as $payment): ?>
                    <li>₹<?php echo $payment['amount']; ?> on
                        <?php echo date('d-m-Y h:i:s a', strtotime($payment['date'])); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form action="dashboard.php" method="POST" style="display:inline;" id="mark-as-paid-form-<?php echo $index; ?>">
    <input type="hidden" name="mark_as_paid" value="1">
    <input type="hidden" name="person" value="<?php echo $person; ?>">
    <input type="hidden" name="bill_index" value="<?php echo $index; ?>">
    <button type="submit" class="button mark-as-paid-button" <?php if ($bill['remaining'] <= 0) echo 'disabled'; ?>>
        <?php echo $bill['remaining'] <= 0 ? 'Already Paid' : 'Mark as Paid'; ?>
    </button>
</form>

        <form action="dashboard.php" method="POST" style="display:inline;">
            <input type="hidden" name="delete_bill" value="1">
            <input type="hidden" name="person" value="<?php echo $person; ?>">
            <input type="hidden" name="bill_index" value="<?php echo $index; ?>">
            <button type="submit" class="delete-button">Delete Bill</button>
        </form>
    </div>
<?php endforeach; ?>

            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <script>
    function toggleAddBill() {
        var container = document.querySelector('.add-bill-container');
        container.style.display = (container.style.display === 'none') ? 'block' : 'none';
    }

    function validateAddBillForm() {
        var items = document.querySelectorAll('select[name^="items["]');
        var valid = false;

        items.forEach(function (select) {
            if (parseInt(select.value) > 0) {
                valid = true;
            }
        });

        if (!valid) {
            alert('Select at least one item');
            return false; // Prevent form submission
        }
        return true; // Allow form submission
    }

    document.querySelectorAll('.bill').forEach(function (billElement) {
        var remainingAmount = parseFloat(billElement.querySelector('.bill p:nth-of-type(2)').textContent.replace('Remaining: ₹', ''));
        var markAsPaidButton = billElement.querySelector('form[id^="mark-as-paid-form-"] .mark-as-paid-button');

        if (remainingAmount <= 0) {
            markAsPaidButton.disabled = true;
            markAsPaidButton.textContent = 'Already Paid';
        }
    });

    // Attach validation function to form
    document.querySelector('.add-bill-container form').onsubmit = validateAddBillForm;

    // Add event listeners for mark as paid buttons
    document.querySelectorAll('.mark-as-paid-button').forEach(function (button) {
        button.addEventListener('click', function (event) {
            var form = this.closest('form');
            var remainingAmount = parseFloat(form.querySelector('input[name="bill_index"]').closest('.bill').querySelector('.bill p:nth-of-type(2)').textContent.replace('Remaining: ₹', ''));

            if (remainingAmount <= 0) {
                event.preventDefault(); // Prevent form submission
                alert('The bill is already fully paid.');
            }
        });
    });


    </script>
</body>

</html>